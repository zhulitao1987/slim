<?php
header("Content-type: text/html; charset=utf-8");
include_once dirname(__FILE__) . "/../config.php";
include_once API_DIR . "model/Model.php";
include_once API_DIR . "function.php";
include_once API_DIR . "HKPHPPDO.php";
include_once API_DIR . "where.php";
include_once API_DIR . "model/list/bf_quick_apiModel.php";
set_time_limit(0);
class Cli
{
    /*
     * 初始化方法
     * @param string $table_name 数据表名
     */
    public function __construct()
    {
        if (php_sapi_name() !== 'cli') {
            if (WHERE_SERVER == 'server') {
                echo "请在本地测试\n";
                exit;
            }
            $token = isset($_GET["token"]) ? $_GET["token"] : "";
            if ($token != "woshihaoren_5201314") {
                echo "请在本地测试\n";
                exit;
            }
            if (isset($_GET["time_date"])) {
                $_SERVER['REQUEST_TIME'] = time() + $_GET["time_date"] * 24 * 60 * 60;
            }
        }
    }

}