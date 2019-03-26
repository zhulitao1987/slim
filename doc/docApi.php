<?php
header("Content-type: text/html; charset=utf-8");
include_once('test.php');
include_once('conf.php');
include_once('../lib/Lib.php');
include_once('../aes.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>doApi</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
</head>
<body>
<form method="post">
    <?php
    $cls_arr = array();
    $c_name = isset($_GET['c_name']) ? $_GET['c_name'] : "";
    $m_name = isset($_GET['m_name']) ? $_GET['m_name'] : "";
    $is_show = isset($_GET['is_show']) ? $_GET['is_show'] : 0;
    $post_url = "";
    $c_m_list = [];
    $dir = LIB_DIR;
    $get_cm_list = getClass($dir);
    if ($get_cm_list) {
        echo "<select onchange='location.href=\"/doc/docApi.php?c_name=\"+this.value' style='height: 30px;margin: 10px;'>";
        foreach ($get_cm_list as $key => $value) {
            if (!$c_name) {
                $c_name = $key;
            }
            $selected = "";
            if ($c_name == $key) {
                $selected = "selected='true'";
            }
            echo "<option value='{$key}' " . $selected . " >{$key}</option>";
        }
        echo "</select><br>";
    }
    if ($c_name && isset($get_cm_list[$c_name])) {
        $class_name = $c_name . "Lib";
        include_once("../" . $get_cm_list[$c_name]);
        $cm = get_class_methods($class_name);
        echo "<select onchange='location.href=\"/doc/docApi.php?c_name=" . $c_name . "&m_name=\"+this.value' style='height: 30px;margin: 10px;'>";
        echo "<option value=''> --=请选择方法=-- </option>";
        foreach ($cm as $value) {
            //是否可调用
            if (!is_callable(array($class_name, $value))) {
                continue;
            }
            $selected = "";
            if ($m_name == $value) {
                $selected = "selected='true'";
            }
            echo "<option value='{$value}' " . $selected . " >{$value}</option>";
        }
        echo "</select><br>";

        if ($m_name) {
            foreach ($cm as $value) {
                //是否可调用
                if (!is_callable(array($class_name, $value))) {
                    continue;
                }
                if ($m_name == $value) {
                    $test = new Test();
                    $url = str_replace(['lib/', 'Lib.php'], '', $get_cm_list[$c_name]);
                    $url_list = explode("/", $url);
                    $_v = explode("_", $value);
                    $v = "";
                    if (count($_v) > 1) {
                        for ($j = 1; $j <= count($_v) - 1; $j++) {
                            $v .= $_v[$j];
                            if ($j < count($_v) - 1) {
                                $v .= ".";
                            }
                        }
                    }
                    if ($v) {
                        $v = "/" . $v;
                    }
                    $url_list[1] = str_replace($url_list[0] . "_", "", $url_list[1]);
                    $test->m = implode("/", $url_list);
                    $test->f = '/' . $_v[0] . $v;
                    $post_url = $test->m . $test->f;
                    $get_result = $test->run_get();
                    $pos = $get_result['msg']['_POS_'];
                    $api_exp = $get_result['msg']['_API_EXP_'];
                    echo "<h3>接口名称：</h3>";
                    echo $api_exp;
                    echo "<h3>传递的参数：</h3>";
                    echo "<style> td {border: 1px solid #CCCCCC;text-align: center;width: 150px;} </style>";
                    echo echoStr($pos, 0, '');
                    if ($pos) {
                        $par = [];
                        foreach ($pos as $p_k => $p_v) {
                            $par[$p_k] = $p_v[4];
                        }
                        $test->postRule = $par;
                    }
                    $test->is_show = $is_show;
                }
            }
        }
    }

    function echoStr($arr, $type, $name = '') {
        $str = "";
        if ($name) {
            $str .= "<br>";
        }

        $str .= "<table>" .
            ($name ? "<tr style='background-color:#FFC125;font-weight:bold' align='lest'><td colspan='5'>
            &nbsp;&nbsp;<a name ='" . $name . "'>" . $name . "</a></td></tr>" : '') . "
<tr height='40' style='background-color:#dddddd;font-weight:bold'><td>字段</td><td>是否必须</td><td>类型</td><td>说明</td><td>填写参数</td></tr>";

        if ($arr) {
            $child_list = [];
            $n = 0;
            foreach ($arr as $key => $value) {
                if ($value[1] == 'array' && is_array($value[4])) {
                    $child_list[$key] = $value[4];
                }
                if (is_array($value[4])) {
                    $value[4] = "[]";
                    $key_name = $key;
                    if ($name) {
                        $key_name = $name . '.' . $key;
                    }
                    $key = "<a href='#" . $key_name . "'>{$key}</a>";
                }
                $str .= "<tr height='30'><td>" . $key . "</td><td>" . $value[0] . "</td><td>" . $value[1] .
                    "</td><td>" . $value[2] . "</td><td><input style='width:300px' type='text' name='parameter[]' value='" .
                    (isset($_POST["parameter"][$n]) ? $_POST["parameter"][$n] : $key . '|' . $value[4]) . "'></td></tr>";
                $n++;
            }
            $str .= "</table>";
            if ($child_list) {
                foreach ($child_list as $key => $value) {
                    if ($name) {
                        $key = $name . '.' . $key;
                    }
                    $str .= echoStr($value, $type, $key);
                }
            }
            return $str;
        }
    }

    function getClass($dir) {
        $dh = opendir($dir);
        $cm_list = [];
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (substr($file, -4) !== '.php') {
                $get_cm_list = getClass($dir . $file . "/");
                if ($get_cm_list) {
                    $cm_list = array_merge($cm_list, $get_cm_list);
                }
                continue;
            }
            if (!in_array(substr($file, -7), array('Lib.php'))) {
                continue;
            }
            $file_str = substr($dir, strpos($dir, 'lib/')) . $file;
            $c_name = str_replace("Lib.php", "", $file);
            $cm_list[$c_name] = $file_str;
        }
        closedir($dh);
        return $cm_list;
    }

    echo "<h3>提交的地址：</h3>";
    $post_url =  SITE_PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . '/' . $post_url;
    echo (isset($_POST['_m']) ? $_POST['_m'] : $post_url) . '<br><br>';
    ?>
    <div><input type="reset" value="重置"/><input type="submit" value="提交"></div>
    <?php
    if (!empty($_POST)) {
        $post = array();
        $url = $post_url;
        $token = 'test';
        $par = $_POST['parameter'];
        if (count($par) > 0) {
            foreach ($par as $k => $v) {
                $v = trim($v);
                if (!empty($v)) {
                    $s = explode('|', $v);
                    if (is_array($s)) {
                        if (count($s) > 2) {
                            $post[$s[0]] = substr($v, strlen($s[0]) + 1, strlen($v));
                        } else {
                            $post[$s[0]] = $s[1];
                        }
                    }
                }
            }
        }
        mkChkSign($post, $token);
        echo "<br/>提交数据->";
        var_dump($post);
        echo "返回数据->";
        $res = curl($url, $post);
        $apiData = $res;
        echo "<pre>";
        print_r($apiData);
        echo "</pre>";
    }

    /**
     * 提交数据，现只能用post
     */
    function curl($url, $post) {
        if (empty($post)) {
            echo "请POST提交数据";
            return false;
        }
        foreach ($post as $key => $val){
            if (substr($key, 0, 1) != '_' && substr($key, -1) != '_') {
                $post[$key] = ase::encrypt(rtrim($val));
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $rtn = curl_exec($ch);
        curl_close($ch);
        if (isset($_GET['s'])) {
            print_r($rtn);
            die;
        }
        $get_result = json_decode($rtn, true);
        if (!is_array($get_result)) {
            $get_result = [
                'time' => date('Y-m-d H:i:s'), 'error_id' => '-1',
                'error' => '系统错误，请联系管理员', "msg" => '系统错误，请联系管理员'
            ];
        }
        return $get_result;
    }

    /**
     * 生产验证字符串
     * @param $array
     * @return mixed
     */
    function mkChkSign(&$array, $token) {
        if (array_key_exists('_sign_', $array)) {
            unset($array['_sign_']);
        }
        $array['_time_'] = time();
        ksort($array);
        $array['_sign_'] = md5(http_build_query($array) . $token);
    }

    ?>
</form>
</body>
</html> 