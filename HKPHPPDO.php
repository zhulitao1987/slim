<?php
namespace HKPHP;
include_once 'function.php';
include_once 'config.php';

/**
 * 数据库类
 * @author 黄珂
 */
class HKPHPPDO
{
    /**
     * @var \PDO
     */
    private static $pdo = null;
    private $sqlArr = array();
    private $account = null;

    private $_fields = '*';
    private $_table = null;
    private $_join = null;
    private $bf_join = null;
    private $_where = null;
    private $bf_where = null;
    private $_order = null;
    private $_limit = null;
    private $_group = null;

    private $_callCode = '';

    static private $transactionCount = 0;
    public $is_show = false;

    /**
     * 实例
     * @param array $dsn
     * @return HKPHPPDO
     */
    public static function getInstance($dsn)
    {
        $md5 = md5(implode('', $dsn));
        static $_i = [];
        if (isset($_i[$md5])) {
            return $_i[$md5];
        }
        $_i[$md5] = new self;
        $_i[$md5]->account = $dsn;
        return $_i[$md5];
    }

    /**
     * 初始化类
     *
     * @return \PDO
     */
    public function connect()
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        self::$pdo = new \PDO($this->account['DSN'], $this->account['USER'], $this->account['PASS'], [
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->account['CHARSET'],
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ]);
        return self::$pdo;
    }


    /**
     * 分析WHERE
     * [
     *  'k1' => 'v1',
     *  ['AND', 'k2', '=', 'v2'],
     *  ['OR', [
     *          ['', 'k3', '<=', 'substr(now(),1,10)'],
     *          ['AND', 'k4', '<', '100'],
     *          ['OR', [
     *                      ['', 'k5', '<=', 'substr(now(),1,10)'],
     *                      ['AND', 'k6', '<=', '200']
     *                  ]
     *          ]
     *         ],
     *  ],
     *  ['OR', [
     *          ['', 'ISNULL(k7)'],
     *          ['AND', 'k8', '<=', 'v8']
     *      ]
     *  ],
     * ]
     * @param $data
     * @return string
     */
    private function parseWhere($data)
    {
        $sql = '';
        foreach ($data as $k1 => $v1) {
            // 普通模式 [k=>v]
            if (!is_array($v1))
                $sql .= " AND " . $this->quoteKey($k1) . "=" . $this->quote($v1);
            else {
                // 自定义模式嵌套 [or,[[],[]...]]
                if (is_array($v1[1]) && is_array($v1[1][0])) {
                    $_sql = $this->parseWhere($v1[1]);
                    $sql .= " $v1[0] ($_sql)";
                } else {
                    // 自定义模式 [or,k,>=,v]
                    if (count($v1) > 2) {
                        $quote_str = "";
                        if (is_array($v1[3])) {
                            foreach ($v1[3] as $qs_value) {
                                if ($quote_str) {
                                    $quote_str .= ',';
                                }
                                if (is_numeric($qs_value)) {
                                    $quote_str .= $qs_value;
                                } else {
                                    $quote_str .= $this->quote($qs_value);
                                }
                            }
                            $quote_str = "(" . $quote_str . ")";
                        } else {
                            $quote_str = $this->quote($v1[3]);
                        }
                        if ($quote_str) {
                            $sql .= " $v1[0] " . $this->quoteKey($v1[1]) . "$v1[2]" . $quote_str;
                        }
                    } else {
                        // 自定义子模式 [or,fun(x)]
                        $sql .= " $v1[0] " . $this->quote($v1[1]);
                    }
                }
            }
        }
//        outJson(0,$sql);
        return $sql;
    }

    private function parseWhere2($data)
    {
        //outJson(-1,$data);
        $sql = '';
        foreach ($data as $k1 => $v1) {
            // 普通模式 [k=>v]
            if (!is_array($v1)) {
                if(strpos($k1, '.')){
                    $k1_arr = explode('.',$k1);
                    $sql .= " AND " .$this->quoteKey($this->account['PREFIX'] . $k1_arr[0]) . '.' . $this->quoteKey($k1_arr[1]) . "=" . $this->quote($v1);
                }else {
                    $sql .= " AND " . $this->quoteKey($k1) . "=" . $this->quote($v1);
                }
            }
            else {
                // 自定义模式嵌套 [or,[[],[]...]]
                if (is_array($v1[1]) && is_array($v1[1][0])) {
                    $_sql = $this->parseWhere($v1[1]);
                    $sql .= " $v1[0] ($_sql)";
                } else {
                    // 自定义模式 [or,k,>=,v]
                    if (count($v1) > 2) {
                        $quote_str = "";
                        if (is_array($v1[3])) {
                            foreach ($v1[3] as $qs_value) {
                                if ($quote_str) {
                                    $quote_str .= ',';
                                }
                                if (is_numeric($qs_value)) {
                                    $quote_str .= $qs_value;
                                } else {
                                    $quote_str .= $this->quote($qs_value);
                                }
                            }
                            $quote_str = "(" . $quote_str . ")";
                        } else {
                            $quote_str = $this->quote($v1[3]);
                        }
                        if ($quote_str) {
                            $v1_key = $v1[1];
                            if(strpos($v1_key, '.')){
                                $v1_key_arr = explode('.',$v1_key);
                                $sql .= " $v1[0] " .$this->quoteKey($this->account['PREFIX'] . $v1_key_arr[0]) . '.' . $this->quoteKey($v1_key_arr[1]) . "$v1[2]" . $quote_str;
                            }else {
                                $sql .= " $v1[0] " . $this->quoteKey($v1[1]) . "$v1[2]" . $quote_str;
                            }
                        }
                    } else {
                        // 自定义子模式 [or,fun(x)]
                        $sql .= " $v1[0] " . $this->quote($v1[1]);
                    }
                }
            }
        }
        //outJson(0,$sql);
        return $sql;
    }

    /**
     * 设置查询字段
     * @param string $fields 查询字段
     * @return HKPHPPDO
     */
    public function fields($fields)
    {
        if ($fields) {
            if(strpos($fields, '.')){
                $fields_str = '';
                if(strpos($fields, ',')) {
                    $fields_arr = explode(',', $fields);
                    foreach ($fields_arr as $fields_one) {
                        $fields_str .= self::explode_fields_dot(trim($fields_one));
                    }
                }else{
                    $fields_str .= self::explode_fields_dot($fields);
                }
                if(substr($fields_str,-1,1) == ','){
                    $fields_str = substr($fields_str,0,-1);
                }
                $this->_fields = $fields_str;
            }else {
                $this->_fields = $fields;
            }
        }
        //outJson(-1,$this->_fields);
        return $this;
    }

    /**
     * 拆分查询字段以.号拆分
     * @param string $fields_one 查询字段
     * @return string
     */
    protected function explode_fields_dot($fields_one){
        $fields_str = '';
        $fields_one = trim($fields_one);
        if (strpos($fields_one, '.')) {
            $fields_one_arr = explode('.', $fields_one);
            //给查询的字段加``
            /*$fields_one_field = '';
            if ($fields_one_arr[1] == '*') {
                $fields_one_field = $fields_one_arr[1];
            } else {
                $fields_one_field = $this->quoteKey($fields_one_arr[1]);
            }*/
            $fields_str .= $this->quoteKey($this->account['PREFIX'] . $fields_one_arr[0]) . '.' . $fields_one_arr[1] . ',';
        } else {
            $fields_str .= $fields_one;
        }
        return $fields_str;
    }

    /**
     * 设置查询表
     * @param string $table 查询表
     * @return HKPHPPDO
     */
    public function table($table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * 设置连表查询，由于时间关系简单写
     * @param string $key
     * @return HKPHPPDO
     */
    public function join($join)
    {
        if (is_array($join)) {
            $join_sql = '';
                foreach ($join as $one) {
                    $join_sql .= ' ' . $one[0] . ' join `' . $this->account['PREFIX'] . $one[1] . '`' . ' on ';
                    $where_all = $one[2];
                    $on = '';
                    foreach($where_all as $where) {
                        if (is_array($where)) {
                            foreach ($where as $key => $value) {
                                if (strpos($key, '.')) {
                                    $key_arr = explode('.', $key);
                                    $on .= 'and ' . $this->quoteKey($this->account['PREFIX'] . $key_arr[0]) . '.' . $this->quoteKey($key_arr[1]) . '=';
                                    if (strpos($value, '.')) {
                                        $value_arr = explode('.', $value);
                                        $on .= $this->quoteKey($this->account['PREFIX'] . $value_arr[0]) . '.' . $this->quoteKey($value_arr[1]) . ' ';
                                    }else{
                                        $on .= $this->quote($value) . ' ';
                                    }
                                } else {
                                    $on .= 'and ' . $this->quoteKey($this->account['PREFIX'] . $key) . '=' . $this->account['PREFIX'] . $value . ' ';
                                }
                            }
                        }
                    }
                    if(substr($on,0,4) == 'and '){
                        $on = substr($on,4);
                    }
                    $join_sql .= $on;
                }
            $this->_join = $join_sql;
        }
        $this->bf_join = $this->_join;
        //outJson(-1,$join_sql);
        return $this;
    }

    /**
     * 设置查询条件
     * @param array $array 查询条件
     * @return HKPHPPDO
     */
    public function where($array)
    {
        if (empty($array)) {
            $this->_where = '';
            if (isset($_POST['_FastQueryParameters']) && $_POST['_FastQueryParameters']) {
                $this->_where = ' WHERE ' . $_POST["_FastQueryParameters"];
                unset($_POST["_FastQueryParameters"]);
            }
        } else {
            $_w = trim($this->parseWhere($array));
            if (substr($_w, 0, 4) == 'AND ')
                $this->_where = ' WHERE ' . substr($_w, 4);
            else $this->_where = ' WHERE ' . $_w;
            if (isset($_POST['_FastQueryParameters']) && $_POST['_FastQueryParameters']) {
                $this->_where .= " AND " . $_POST["_FastQueryParameters"];
                unset($_POST["_FastQueryParameters"]);
            }
        }
        $this->bf_where = $this->_where;
        return $this;
    }

    /**
     * 设置查询条件
     * @param array $array 查询条件
     * @return HKPHPPDO
     */
    public function where2($array)
    {
        if (empty($array)) {
            $this->_where = '';
            if (isset($_POST['_FastQueryParameters']) && $_POST['_FastQueryParameters']) {
                $this->_where = ' WHERE ' . $_POST["_FastQueryParameters"];
                unset($_POST["_FastQueryParameters"]);
            }
        } else {
            $_w = trim($this->parseWhere2($array));
            if (substr($_w, 0, 4) == 'AND ')
                $this->_where = ' WHERE ' . substr($_w, 4);
            else $this->_where = ' WHERE ' . $_w;
            if (isset($_POST['_FastQueryParameters']) && $_POST['_FastQueryParameters']) {
                $this->_where .= " AND " . $_POST["_FastQueryParameters"];
                unset($_POST["_FastQueryParameters"]);
            }
        }
        $this->bf_where = $this->_where;
        return $this;
    }

    /**
     * 设置排序
     * @param string $key
     * @return HKPHPPDO
     */
    public function order($key)
    {
        if ($key) {
            $this->_order = ' ORDER BY ' . $key;
        }
        return $this;
    }

    /**
     * 设置分组查询
     * @param string $key
     * @return HKPHPPDO
     */
    public function group($key)
    {
        if ($key) {
            $this->_group = ' GROUP BY ' . $key;
        }
        return $this;
    }

    /**
     * 设置查询数据限制
     * @param int $count
     * @param int $offset
     * @return HKPHPPDO
     */
    public function limit($count, $offset = 0)
    {
        if ($count < 0) {
            $count = 0;
        }
        if ($offset < 0) {
            $offset = 0;
        }
        if (intval($count) || intval($offset)) {
            $this->_limit = ' LIMIT ' . intval($count) . ',' . intval($offset);
        }
        return $this;
    }

    /**
     * 查询操作
     * 返回数据操作对象
     * @return \PDOStatement|bool 成功返回操作对象，失败返回FALSE
     */
    public function select()
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        $this->connect();

        $sql = "SELECT " . $this->_fields . " FROM `" . $this->account['PREFIX'] . $this->_table . "`";
        $this->_fields = '*';
        $this->_table = null;
        // join
        if ($this->_join)
            $sql .= $this->_join;
        $this->_join = null;
        // where
        if ($this->_where)
            $sql .= $this->_where;
        $this->_where = null;
        // group
        if ($this->_group)
            $sql .= $this->_group;
        $this->_group = null;
        // order
        if ($this->_order)
            $sql .= $this->_order;
        $this->_order = null;
        // limit
        if ($this->_limit)
            $sql .= $this->_limit;
        if ($this->is_show) {
            outJson(-1, $sql);
        }
        $this->_limit = null;
        //outJson(-1,$sql);
        return $this->query($sql);
    }


    /**
     * 查询操作，返回第一条数据
     * @return array|bool 成功返回操作对象，失败返回FALSE
     */
    public function selectOne()
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        $st = $this->select();
        if ($st)
            return $st->fetch();
        return FALSE;
    }

    /**
     * 查询操作，返回所有数据
     * @return array|bool 成功返回操作对象，失败返回FALSE
     */
    public function selectAll()
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        $st = $this->select();
        if ($st)
            return $st->fetchAll();
        return FALSE;
    }

    /**
     * 返回总数据量
     * @param int $is_bf 是否使用备份where,join
     * @return int
     */
    public function getTotal($is_bf = 0)
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        $this->connect();

        $sql = "SELECT COUNT(0) FROM `" . $this->account['PREFIX'] . $this->_table . "`";
        $this->_table = null;
        // join
        if ($is_bf && $this->bf_join) {
            $sql .= $this->bf_join;
        } elseif ($this->_join) {
            $sql .= $this->_join;
        }
        $this->_join = null;

        if ($is_bf && $this->bf_where) {
            $sql .= $this->bf_where;
        } elseif ($this->_where) {
            $sql .= $this->_where;
        }
        $this->_where = null;
        // group
        if ($this->_group)
            $sql .= $this->_group;
        $this->_group = null;

        //outJson(-1,$sql);
        $st = $this->query($sql);
        $row = $st->fetch(\PDO::FETCH_NUM);
        return $row[0];
    }

    /**
     * 插入操作
     * @param array $set 将要插入的数组 array( key1 => value1, key2 => value2)
     * @return int|bool 成功返回最后插入的ID,失败返回false
     */
    public function insert(array $set)
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        $this->connect();
        $_1 = array();
        $_2 = array();
        foreach ($set as $a => $b) {
            $_1[] = '`' . $a . '`';
            $_2[] = $this->quote($b);
        }
        $sql = "INSERT INTO `" . $this->account['PREFIX'] . $this->_table . "` (" . implode(',', $_1) . ") VALUES (" . implode(',', $_2) . ")";
        $this->_table = null;
        if ($this->exec($sql)) {
            return self::$pdo->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * 更新操作
     * @param array $set 更新的字段数组 array( key1 => value1, key2 => value2)
     * @return int|bool 成功返回影响的记录行数,失败返回false
     */
    public function update(array $set)
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        $this->connect();
        $_set = array();
        foreach ($set as $a => $b) {
            if (is_numeric($a)) {
                // 自定义
                $_set[] = $b;
            } else {
                $_set[] = "`{$a}`=" . $this->quote($b);
            }
        }
        if (!$this->_where)
            return FALSE;
        $sql = "UPDATE `" . $this->account['PREFIX'] . $this->_table . "` SET " . implode(',', $_set) . $this->_where;
        $this->_table = null;
        $this->_where = null;
        $rtn = $this->exec($sql);
        if ($rtn === FALSE)
            return FALSE;
        return $rtn;
    }

    /**
     * 删除操作
     * @return int|bool 成功返回影响的记录行数,失败返回false
     */
    public function delete()
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        $this->connect();
        if (!$this->_where)
            return FALSE;
        $sql = "DELETE FROM `" . $this->account['PREFIX'] . $this->_table . "`" . $this->_where;
        $this->_table = null;
        $this->_where = null;
        $rtn = $this->exec($sql);
        if ($rtn === FALSE)
            return FALSE;
        return $rtn;
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @return int|bool 成功返回影响的记录行数,失败返回false
     */
    public function exec($sql)
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        isset($this->account['sqlLogFun']) && $this->account['sqlLogFun']($sql, $this->_callCode);
        $this->sqlArr[] = $sql;
        fileLog("sql", $sql);
        if ($this->is_show) {
            outJson(-1, $sql);
        }
//        outJson(1,$sql);
        $this->connect();
        return self::$pdo->exec($sql);
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @return \PDOStatement
     */
    public function query($sql)
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        isset($this->account['sqlLogFun']) && $this->account['sqlLogFun']($sql, $this->_callCode);
        $this->sqlArr[] = $sql;
        fileLog("sql", $sql);
//        outJson(0,$sql);
        $this->connect();
        return self::$pdo->query($sql);
    }

    /**
     * 转换字段
     * @param $str
     * @return string
     */
    private function quoteKey($str)
    {
        if (is_array($str))
            return $str[1];
        return "`{$str}`";
    }

    /**
     * 安全转换字符串
     * @param $str
     * @return string
     */
    private function quote($str)
    {
        if (is_array($str))
            return $str[1];
        $this->connect();
        return self::$pdo->quote($str);
    }

    /**
     * 启用事务
     * @return HKPHPPDO
     */
    public function beginTransaction()
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        self::$transactionCount++;
        isset($this->account['sqlLogFun']) && $this->account['sqlLogFun']('SET AUTOCOMMIT=0;', $this->_callCode);
        $this->sqlArr[] = 'SET AUTOCOMMIT=0;';
        fileLog("sql", 'SET AUTOCOMMIT=0;');
        $this->connect();
        if (self::$transactionCount == 1) {
            self::$pdo->beginTransaction();
        }
        return $this;
    }

    /**
     * 提交事务
     * @return HKPHPPDO
     */
    public function commit()
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        isset($this->account['sqlLogFun']) && $this->account['sqlLogFun']('COMMIT;', $this->_callCode);
        $this->sqlArr[] = 'COMMIT;';
        fileLog("sql", 'COMMIT;');
        if (self::$transactionCount == 1) {
            self::$pdo->commit();
        }
        self::$transactionCount--;
        return $this;
    }

    /**
     * 回滚事务
     * @return HKPHPPDO
     */
    public function rollback()
    {
        if (!$this->_callCode) {
            $trace = debug_backtrace();
            $this->_callCode = $trace[0]['file'] . "[{$trace[0]['line']}]";
        }
        isset($this->account['sqlLogFun']) && $this->account['sqlLogFun']('ROLLBACK;', $this->_callCode);
        $this->sqlArr[] = 'ROLLBACK;';
        fileLog("sql", 'ROLLBACK;');
        if (self::$transactionCount == 1) {
            self::$pdo->rollBack();
        }
        self::$transactionCount--;
        return $this;
    }

    /**
     * 返回所有执行的SQL语句
     * @return array
     */
    public function getAllSql()
    {
        return $this->sqlArr;
    }

    /**
     * 返回执行的最后一条SQL语句
     * @return mixed
     */
    public function getLastSql()
    {
        return $this->sqlArr[count($this->sqlArr) - 1];
    }
}