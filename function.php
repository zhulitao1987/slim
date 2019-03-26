<?php
include_once 'err.php';
/**
 * 检测请求合法性
 */
function chkSign()
{
    $post = $_POST;
    foreach ($post as $key => $val) {
        if (substr($key, 0, 1) != '_' && substr($key, -1) != '_') {
            $_POST[$key] = trim(ase::decrypt($val));
        }
    }
    $fastQueryParameters = "";
    if (isset($_POST["fastQueryParameters"])) {
        $fastQueryParameters = $_POST["fastQueryParameters"];
    }
    //字符串过滤
    $_POST = strFilter($_POST);
    if ($fastQueryParameters) {
        $_POST["_FastQueryParameters"] = getFastQuerySql(json_decode($fastQueryParameters, true));
    }
    $_POST['_RANK'] = [0, 1];
    if (isset($_POST['page_size'])) {
        if ($_POST['page_size'] <= 0 || $_POST['page_size'] > 1000) {
            $_POST['page_size'] = 1000;
        }
    }
    //IP过滤+权限
//    $ip_list = CONFIG::IP_LIST();
//    $ip = getIp(false);
//    if (!in_array($ip, $ip_list)) {
//        outJson(109);
//    }
    return;
    if (!isset($_POST)) {
        outJson(109);
    }
    //IP过滤+权限
    $ip_list = CONFIG::IP_LIST();
    $ip = getIp();
    $token = '';
    $rank = -1;
    if ($ip == '127.0.0.1') {
        $token = 'test';
        $rank = 0;
    } elseif (substr($ip, 0, 7) == '192.168') {
        $token = 'test';
        $rank = 0;
    } elseif (in_array($ip, $ip_list)) {
        $token = isset($ip_list[$ip]['token']) ? $ip_list[$ip]['token'] : "";
        $rank = isset($ip_list[$ip]['rank']) ? $ip_list[$ip]['rank'] : -1;
    }
    if (!$token || (is_numeric($rank) && $rank < 0)) {
        outJson(109);
    }
    $req = $_POST;
    //字符串过滤
    $_POST = strFilter($_POST);
    $_POST['_RANK'] = $rank;
    if (!array_key_exists('_sign_', $req)) {
        outJson(105);
    }
    if (!array_key_exists('_time_', $req)) {
        outJson(106);
    }
    $sign = $req['_sign_'];
    $time = $req['_time_'];
    if (time() - $time > 60) {
        outJson(107);
    }
    unset($req['_sign_']);
    ksort($req);
    $chk = md5(http_build_query($req) . $token);
    if ($sign !== $chk) {
        outJson(108);
    }
}

function getFastQuerySql($params)
{
//		如果传递的条件参数为空则返回空字符串
    if ($params === null) {
        return "";
    }
//		定义条件SQL
    $conditionSql = "";
//		遍历参数，拼接SQL
    foreach ($params as $key => $value) {
        if (trim($value) === "") {
            continue;
        }
        if (strpos($key, '_format') !== false) {
            continue;
        }
        if (strpos($key, '_issend') !== false) {
            continue;
        }
        if ($conditionSql) {
            $conditionSql .= " and ";
        }
        $value = strR($value);
        if (strpos($key, "_")) {
            $field = substr($key, strpos($key, "_") + 1, strlen($key));
            $field = "`$field`";
//				equal
            if (strpos($key, "eq_") === 0) {
                if (!is_numeric($value)) {
                    $value = "'" . $value . "'";
                }
                $conditionSql .= $field . " = $value ";
                continue;
            }
//				not equal
            if (strpos($key, "ne_") === 0) {
                if (!is_numeric($value)) {
                    $value = "'" . $value . "'";
                }
                $conditionSql .= $field . " != $value ";
                continue;
            }
//				like
            if (strpos($key, "lk_") === 0) {
                if (!is_numeric($value)) {
                    $value = "'%" . $value . "%'";
                }
                $conditionSql .= $field . " like $value ";
                continue;
            }
//				right like
            if (strpos($key, "rl_") === 0) {
                if (!is_numeric($value)) {
                    $value = "'%" . $value . "'";
                }
                $conditionSql .= $field . " like $value ";
                continue;
            }
//				left like
            if (strpos($key, "ll_") === 0) {
                if (!is_numeric($value)) {
                    $value = "'" . $value . "%'";
                }
                $conditionSql .= $field . " like $value ";
                continue;
            }
//				is null
            if (strpos($key, "in_") === 0) {
                $conditionSql .= $field . " is null ";
                continue;
            }
//				is not null
            if (strpos($key, "inn_") === 0) {
                $conditionSql .= $field . " is not null ";
                continue;
            }
//				great then
            if (strpos($key, "gt_") === 0) {
                if (!is_numeric($value)) {
                    $value = "'" . $value . "'";
                }
                $conditionSql .= $field . " > $value ";
                continue;
            }
//				great then and equal
            if (strpos($key, "ge_") === 0) {
                if (!is_numeric($value)) {
                    $value = "'" . $value . "'";
                }
                $conditionSql .= $field . " >= $value ";
                continue;
            }
//				less then
            if (strpos($key, "lt_") === 0) {
                if (!is_numeric($value)) {
                    $value = "'" . $value . "'";
                }
                $conditionSql .= $field . " < $value ";
                continue;
            }
//				less then and equal
            if (strpos($key, "le_") === 0) {
                if (!is_numeric($value)) {
                    if(checkShortDate($value)){
                        $value = $value . ' 23:59:59';
                    }
                    $value = "'" . $value . "'";
                }
                $conditionSql .= $field . " <= $value ";
                continue;
            }
        }
    }
//		返回条件SQL
    return $conditionSql;
}

/**
 * 获取当前时间戳
 * @param int $type 0 = YmdHis，1 = 时间戳
 * @return int
 */
function getRequestTime($type = 0)
{
    $t = $_SERVER['REQUEST_TIME'];
    return $type == 0 ? date('Y-m-d H:i:s', $t) : $t;
}

/**
 * 数据过滤方法
 * @param array $str_arr 过滤数组
 * @return string
 */
function strFilter($str_arr = [])
{
    if (!$str_arr) {
        return [];
    }
    foreach ($str_arr as $key => $value) {
        unset($str_arr[$key]);
        //过滤key
        if (is_array($key)) {
            continue;
        } elseif (!is_numeric($key)) {
            $key = strR($key, 1);
        }
        //如果value是数组
        if (is_array($value)) {
            $value = strFilter($value);
        } elseif (!is_numeric($value)) {
            //如果是字符串，过滤
            $value = strR($value);
        }
        $str_arr[$key] = $value;
    }
    return $str_arr;
}

/*
 * 非法字符过滤函数
 * @param string $str 过滤的字符
 * @param int $is_key 是否是key
 * @return string
 */
function strR($str, $is_key = 0)
{
    if ($is_key) {
        $str = str_replace('[', '', $str);
        $str = str_replace(']', '', $str);
    }
    //转换字符串
    $str = htmlspecialchars($str);
    //判断php是否开启自动反斜杠功能
    if (!get_magic_quotes_gpc()) {
        //字符串添加反斜杠
        $str = addslashes($str);
    }
    return $str;
}


/**
 * 获取用户IP地址
 * @param bool $is_post 是否获取传递的
 * @return string
 */
function getIp($is_post = true)
{
    if (isset($_POST['_ip_']) && $is_post) {
        return $_POST['_ip_'];
    }
    $ip = '-';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * 打印
 * @param  int $err 错误ID
 * @param  array|string $data 传递数据
 * @return string
 */
function outJson($err, $data = null)
{
    $_err = "";
    if ($err) {
        $_err = errs_out($err);
        if ($data && is_string($data)) {
            $_err = $data;
        }
    }
    $data = array('time' => date('Y-m-d H:i:s'), 'error_id' => $err, 'error' => $_err, 'msg' => $data);
    echo json_encode($data);
    exit;
}

/**
 * 必须传递的参数
 * @param  array $data 必须要的数据
 * @param  array $req post传递的数据
 * @param  int $type 是否判断参数必须
 * @param  int $is_set 是否填充默认值
 * @return array
 */
function must_post($data, $req, $type = 0, $is_set = 0)
{
    $_data = [];
    foreach ($data as $key => $value) {
        $v_type = $value[1];
        $v_type_list = explode("|", $v_type);
        $_min = 0;
        $_max = 0;
        if (isset($v_type_list[1])) {
            $_l = explode("-", $v_type_list[1]);
            if (count($_l) > 1) {
                $_min = (int)$_l[0];
                $_max = (int)$_l[1];
            } else {
                $_min = (int)$_l[0];
                $_max = (int)$_l[0];
            }
        }
        if ($v_type_list[0] == 'string' && isset($req[$key])) {
            $req[$key] = (string)$req[$key];
            if ($_min || $_max) {
                $_length = mb_strlen($req[$key], 'utf-8');
                if ($_length < $_min || $_length > $_max) {
                    if ($_max > $_min) {
                        outJson(101, $key . "只能传递长度在{$_min}与{$_max}之间的字符串");
                    } else {
                        outJson(101, $key . "只能传递长度为{$_min}的字符串");
                    }
                }
            }
        } elseif ($v_type_list[0] == 'num' && isset($req[$key])) {
            if (!is_numeric($req[$key])) {
                outJson(100, $key . '数据格式错误');
            }
            if ($_min || $_max) {
                if ($req[$key] < $_min || $req[$key] > $_max) {
                    outJson(102, $key . "只能传递大小在{$_min}与{$_max}之间的数字");
                }
            }
        } elseif (isset($req[$key])) {
            outJson(98);
        }
        unset($v_type_list[0]);
        if (!isset($req[$key]) && $value[0] == 1 && $type == 1) {
            outJson(99, $key . ' 为必传参数');
        }
        $d_key = isset($value[3]) && is_string($value[3]) ? $value[3] : $key;
        if (isset($req[$key])) {
            $_data[$d_key] = $req[$key];
        } elseif ($is_set) {
            if ($v_type == 'num') {
                $_data[$d_key] = 0;
            } else {
                $_data[$d_key] = '';
            }
        }
    }
    return $_data;
}

/**
 * 必须传递的参数
 * @param  string $table_name 数据表名称
 * @return Model
 */
function M($table_name)
{
    $file_name = API_DIR . 'model/list/' . $table_name . 'Model.php';
    //是否存在文件,如果不存在，直接调用Model文件
    if (!file_exists($file_name)) {
        return new Model($table_name);
    } else {
        include_once $file_name;
        //是否纯在类
        if (!class_exists($table_name . 'Model')) {
            outJson(15);
        }
        $class_name = $table_name . "Model";
        return new $class_name();
    }
}

/**
 * 获取订单号
 */
function getOrderId()
{
    list($s1, $s2) = explode(' ', microtime());
    return (string)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 10000000000);
}

/**
 * 必须传递的参数
 * @param  array $data 必须要过滤的数据
 * @param  array $res 要过滤的数据格式
 * @param  string $cache_name 缓存的名称
 * @param  int $cache_time 缓存的时间
 * @return array
 */
function res_data($data, $res, $cache_name = '', $cache_time = 0)
{
    $res_data = [];
    foreach ($res as $key => $one) {
        $d_key = $one[3];
        if (isset($data[$d_key])) {
            if ($one[1] == 'num') {
                if (!is_numeric($data[$d_key])) {
                    $data[$d_key] = 0;
                }
                $res_data[$key] = $data[$d_key];
            } elseif ($one[1] == 'array') {
                if (is_array($data[$d_key])) {
                    if (isset($one[4]) && is_array($one[4])) {
                        foreach ($data[$d_key] as $_key => $_one) {
                            $res_data[$key][$_key] = res_data($_one, $one[4]);
                        }
                    } else {
                        $res_data[$key] = [];
                    }
                } else {
                    $res_data[$key] = [];
                }
            } elseif ($one[1] == 'time') {
                $res_data[$key] = date('Y-m-d H:i:s', $data[$d_key]);
            } else {
                $res_data[$key] = (string)$data[$d_key];
            }
        } else {
            if ($one[1] == 'num') {
                $res_data[$key] = 0;
            } elseif ($one[1] == 'array') {
                $res_data[$key] = [];
            } else {
                $res_data[$key] = '';
            }
        }
    }
    if ($cache_name && $cache_time > 0) {
        $s = explode(':', MEMCACHED_SERVER);
        $cache = new \Memcache();
        if ($cache->addServer($s[0], $s[1])) {
            $cache->set($cache_name, $res_data, 0, $cache_time);
        }
    }
    return $res_data;
}

/**
 * 展示条件和接收数据
 * @param  array $req 传递数据
 * @param  array $post_data 展示条件
 * @param  array $res_data 接收数据
 * @param  string $cache_name 缓存名称
 * @param  string $api_explain 接口说明
 * @param  string $res_explain 返回值说明
 * @param  string $fun_rank 方法调用级别
 */
function _show($req, $post_data = [], $res_data = [], $cache_name = '', $api_explain = '', $res_explain = '', $fun_rank = '-1')
{
    $show_data = [];
    $cache_data = [];
    if (isset($req['_POS_']) || isset($req['_RES_'])) {
        $show_data['_API_EXP_'] = $api_explain;
        $show_data['_API_FUN_RANK_'] = $fun_rank;
    }
    if (isset($req['_POS_'])) {
        if (is_array($post_data)) {
            $post_data = _unset($post_data);
        }
        $show_data['_POS_'] = $post_data;
    }
    if (isset($req['_RES_'])) {
        $show_data['_RES_EXP_'] = $res_explain;
        if (is_array($res_data)) {
            $res_data = _unset($res_data);
        }
        $show_data['_RES_'] = $res_data;
    }
    if (isset($req['_MEMCACHE_']) && $req['_MEMCACHE_'] && $cache_name) {
        $s = explode(':', MEMCACHED_SERVER);
        $cache = new \Memcache();
        if ($cache->addServer($s[0], $s[1])) {
            $cache_data = $cache->get($cache_name);
        }
    }
    if (isset($req['_POS_']) || isset($req['_RES_'])) {
        outJson(0, $show_data);
    } elseif ($cache_data) {
        outJson(0, $cache_data);
    }
}

function _unset($arr = [])
{
    foreach ($arr as $key => $value) {
        if (isset($value[4]) && is_array($value[4])) {
            $value[4] = _unset($value[4]);
        }
        unset($value[3]);
        $arr[$key] = $value;
    }
    return $arr;
}

function api_rank($api_rank)
{
    //api_rank小于0为公共方法，无需判断
    if ($api_rank > -1) {
        $_rank = isset($_POST['_RANK']) ? $_POST['_RANK'] : outJson(109);
        if (is_numeric($_rank)) {
            if ($_rank != $api_rank) {
                outJson(109);
            }
        } elseif (!is_array($_rank) || !in_array($api_rank, $_rank)) {
            outJson(109);
        }
    }
}

/**
 * 字符串打码
 */
function da_ma($str, $top = 0, $bottom = 0, $_ = 4)
{
    $length = mb_strlen($str, 'utf-8');
    $topStr = $top ? mb_substr($str, 0, $top, 'utf-8') : '';
    $bIndex = $length - $bottom;
    $bottomStr = $bottom ? mb_substr($str, $bIndex, $bottom, 'utf-8') : "";
    $newStr = $topStr;
    for ($j = 0; $j < $_; $j++) {
        $newStr .= "*";
    }
    $newStr .= $bottomStr;
    return $newStr;
}

/**
 * 文件日志记录
 * auther LHD
 * @param string $type 汇付接口类型
 * @param string $content 汇付返回数据
 */
function fileLog($type, $content)
{
    $ip = getIp();
    if ($ip == '127.0.0.1' || substr($ip, 0, 7) == '192.168') {
        return;
    }
    $file = __DIR__ . '/log' . date('/Y/m/d', time());
    if (!file_exists($file)) {
        mkdir($file, 0775, TRUE);
    }
    $type = empty($type) ? 'default' : $type;
    $file .= '/' . $type . '.log';
    file_put_contents($file, date('[Y-m-d H:i:s]', time()) . $content . "\n", FILE_APPEND);
}

/**
 * 验证手机号
 * auther MZH
 * @param $phone 手机号
 */
function checkPhone($phone)
{
    if (preg_match("/^1[34578]\d{9}$/", $phone)) {
        return $phone;
    }
    return false;
}

/**
 * 验证邮箱
 * auther MZH
 * @param $email 邮箱
 */
function checkEmail($email)
{
    if (preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $email)) {
        return $email;
    }
    return false;
}

/**
 * 验证URL
 * auther JJQ
 * @param $email URL
 */
function checkUrl($url)
{
    $pattern = '/^http[s]?:\/\/'.
        '(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184
        '|'. // 允许IP和DOMAIN（域名）
        '([0-9a-z_!~*\'()-]+\.)*'. // 域名- www.
        '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域名
        '[a-z]{2,6})'.  // first level domain- .com or .museum
        '(:[0-9]{1,4})?'.  // 端口- :80
        '((\/\?)|'.  // a slash isn't required if there is no file name
        '(\/[0-9a-zA-Z_!~\'
\.;\?:@&=\+\$,%#-\/^\*\|]*)?)$/';
    if(preg_match($pattern, $url)){
        return true;
    } else{
        return false;
    }
}

 /** 把银行简码转换成银行名
 * @author cdf@yhxbank.com
 * @date  2016-06-17
 * @param $bankCode 银行简码 如CCB
 * @return string
 */
function transferBankCodeToName($bankCode) {
    $bankImgArray = array(
        'ICBC'  =>  '工商银行',
        'ABC'   =>  '农业银行',
        'BOC'   =>  '中国银行',
        'CCB'   =>  '建设银行',
        'PSBC'  =>  '邮政储蓄银行',
        'CITIC'	=>  '中信银行',
        'CEB'   =>  '光大银行',
        'PAB'   =>  '平安银行',
        'BCOM'  =>  '交通银行',
        'CIB'   =>  '兴业银行',
        'CMBC'  =>  '民生银行',
        'SPDB'  =>  '浦发银行',
        'SHB'   =>  '上海银行',
        'CMB'   =>  '招商银行',
        'HXB'   =>  '华夏银行',
        'GDB'   =>  '广发银行',
    );
    return $bankImgArray[$bankCode];
}

/**
 * @function    网银充值代码和银行匹配；
 * @author      xuwb@yhxbank.com
 * @date        2017-02-15
 */
function transferBankNumberToName($bankNumber) {
    $bankImgArray = array(
        "3050"      => "华夏银行",
        "3037"      => "上海农商银行",
        "3009"      => "兴业银行",
        "3004"      => "浦发银行",
        "3001"      => "招商银行",
        "3036"      => "广发银行",
        "3035"      => "平安银行",
        "3026"      => "中国银行",
        "3022"      => "光大银行",
        "3020"      => "交通银行",
        "3006"      => "民生银行",
        "3005"      => "农业银行",
        "3003"      => "建设银行",
        "3002"      => "工商银行",
        "3032"      => "北京银行",
        "3039"      => "中信银行",
        "3050"      => "华夏银行",
        "3059"      => "上海银行",
        "3038"      => "中国邮政储蓄",
        "3060"      => "北京农商银行",
        "3080001"   => "银联无卡支付",
    );
    return $bankImgArray[$bankNumber];
}



