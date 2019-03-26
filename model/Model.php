<?php

/**
 * 数据库基础类
 * 此文件程序用来做什么的（详细说明，可选。）。
 * @author      LHD
 */
date_default_timezone_set('PRC');

class Model
{
    protected $db = ''; //数据库连接
    protected $tableName = ''; //表名
    protected $mysqlDsn = MYSQL_DSN;
    protected $mysqlUser = MYSQL_USER;
    protected $mysqlPass = MYSQL_PASS;
    protected $mysqlPrefix = "y1_"; //表头
    protected $mysqlCharset = "utf8";


    /*
     * 初始化方法
     * @param string $table_name 数据表名
     */
    public function __construct($table_name = '')
    {
        $dsn = [
            'DSN'     => $this->mysqlDsn,
            'USER'    => $this->mysqlUser,
            'PASS'    => $this->mysqlPass,
            'PREFIX'  => $this->mysqlPrefix,
            'CHARSET' => $this->mysqlCharset,
        ];
        $pdo = \HKPHP\HKPHPPDO::getInstance($dsn);
        $this->db = $pdo;
        if ($table_name) {
            $this->tableName = $table_name;
        } elseif (!$this->tableName) {
            $this->tableName = str_replace('Model', '', get_class($this));
        }
    }

    /*
     * 插入数据
     * @param array $insert_data 插入的数组
     * @return int|bool
     */
    public function insert($insert_data)
    {
        return $this->db->table($this->tableName)->insert($insert_data);
    }

    /*
     * 获取一条数据
     * @param array $where 查询条件
     * @param string $order 排序字段
     * @param string $fields 查询的字段
     */
    public function get_one($where = [], $order = '', $fields = "")
    {
        return $this->db->table($this->tableName)->fields($fields)->where($where)->order($order)->selectOne();
    }

    /*
     * 更新数据
     * @param array $arr 更新的数据
     * @param array $where 更新的条件
     */
    public function update($arr, $where)
    {
        return $this->db->table($this->tableName)->where($where)->update($arr);
    }

    /*
     * 删除数据
     * @param array $where 删除数据的where条件
     */
    public function delete($where = [])
    {
        return $this->db->table($this->tableName)->where($where)->delete();
    }

    /*
     * 查询数据列表
     */
    public function get_list($where = [], $order = '', $page = 0, $pageSize = 0, $group = '', $fields = '')
    {
        return $this->db->table($this->tableName)->fields($fields)->where($where)->order($order)->group($group)->limit($page * $pageSize, $pageSize)->selectAll();
    }

    /*
     * 查询数据列表
     */
    public function get_list2($where = [], $order = '', $page = 0, $pageSize = 0, $group = '', $fields = '',$join = [])
    {
        return $this->db->table($this->tableName)->fields($fields)->join($join)->where2($where)->order($order)->group($group)->limit($page * $pageSize, $pageSize)->selectAll();
    }

    /*
     * 查询总记录数
     */
    public function get_total($where = [], $is_bf = 0, $group = '')
    {
        if ($where) {
            return $this->db->table($this->tableName)->where($where)->group($group)->getTotal($is_bf);
        } else {
            return $this->db->table($this->tableName)->group($group)->getTotal($is_bf);
        }
    }

    /*
     * 查询总记录数
     */
    public function getListAndTotal($where = [], $order = '', $page = 0, $pageSize = 20, $group = '', $fields = '')
    {
        $data['list'] = $this->db->table($this->tableName)->fields($fields)->where($where)->order($order)->group($group)->limit($page * $pageSize, $pageSize)->selectAll();
        $data['total'] = $this->db->table($this->tableName)->getTotal(1);
        if ($data['list']) {
            return $data;
        } else {
            return false;
        }
    }

    /*
     * 启用事务
     */
    public function beginTransaction()
    {
        return $this->db->table($this->tableName)->beginTransaction();
    }

    /*
     * 提交事务
     */
    public function commit()
    {
        return $this->db->table($this->tableName)->commit();
    }

    /*
     * 回滚事务
     */
    public function rollback()
    {
        return $this->db->table($this->tableName)->rollback();
    }

    /*
     * 回滚事务
     */
    public function getLastSql()
    {
        return $this->db->table($this->tableName)->getLastSql();
    }

    /**
     * 必须传递的参数
     * @param  array $insert_list 插入的数据
     * @param  array $judge_list 需要判断唯一性的数据  例子：['admin_id','phone'] 这是“和”的关系   [['admin_id'],['phone']] 这是“或”的关系
     * @param  Model $m model类
     * @return bool|string
     */
    protected function judge($insert_list, $judge_list, $m)
    {
        $where = [];
        //循环判断数组
        foreach ($judge_list as $value) {
            //如果是二维数组，是或的关系，匹配到下一层
            if (is_array($value)) {
                $_str = $this->judge($insert_list, $value, $m);
                if ($_str !== true) {
                    return $_str;
                }
            } else {
                //添加至where条件
                if (isset($insert_list[$value])) {
                    $where[$value] = $insert_list[$value];
                }
            }
        }
        //没有where条件直接返回true
        if (!$where) {
            return true;
        }
        //查询是否有数据
        $getInfo = $m->get_one($where);
        //有数据的话，返回信息
        if ($getInfo) {
            return false;
        }
        return true;
    }

    /*
     * 查询sql
     */
    public function queryOne($sql)
    {
        return $this->db->table($this->tableName)->query($sql)->fetch();
    }

    /*
     * 查询sql
     */
    public function queryAll($sql)
    {
        return $this->db->table($this->tableName)->query($sql)->fetchAll();
    }

    /*
     * 执行更新sql
     */
    public function doExec($sql)
    {
        return $this->db->table($this->tableName)->exec($sql);
    }

    /*
     * 开启sql
     */
    public function is_open_show()
    {
        $this->db->is_show = true;
    }
}
