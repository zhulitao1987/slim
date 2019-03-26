<?php
header("Content-type:application/json; charset=utf-8");
include_once 'Slim/Slim.php';
include_once 'config.php';
include_once 'function.php';
include_once 'where.php';
include_once 'HKPHPPDO.php';
include_once 'model/Model.php';
include_once 'lib/Lib.php';
require_once 'aes.php';
//设定上海为默认时间
ini_set('date.timezone', 'Asia/Shanghai');
error_reporting(E_ALL & ~E_NOTICE);

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// 只允许POST传递
$app->post('/:_w/:_l/:_m(/:_v)', function ($_w = '', $_l = '', $_m = '', $_v = '') {
    //认证
    chkSign();

    $file = 'lib/' . $_w . '/' . $_l . 'Lib.php';
    //是否纯在文件
    if (!file_exists($file)) {
        $_l = $_w . "_" . $_l;
        $file = 'lib/' . $_w . '/' . $_l . 'Lib.php';
        //是否纯在文件
        if (!file_exists($file)) {
            outJson(10);
        }
    }


    include_once $file;
    $className = $_l . 'Lib';

    //是否纯在类
    if (!class_exists($className)) {
        outJson(10);
    }
    if (!isset($_m)) {
        outJson(11);
    }
    //类方法是否存在
    $m = new $className();
    $is_method_exists = false;
    if ($_v) {
        $_v_array = explode(".", $_v);
        for ($i = count($_v_array); $i >= 0; $i--) {
            $_m_new = $_m;
            for ($j = 0; $j < $i; $j++) {
                $_m_new .= "_" . $_v_array[$j];
            }
            if (method_exists($m, $_m_new) && is_callable(array($m, $_m_new))) {
                $_m = $_m_new;
                $is_method_exists = true;
                break;
            }
        }
        if (!$is_method_exists) {
            outJson(11);
        }
    } else {
        if (!method_exists($m, $_m) || !is_callable(array($m, $_m))) {
            outJson(11);
        }
    }
    $m->$_m();
}
);

$app->run();
