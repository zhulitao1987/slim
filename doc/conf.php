<?php
/**
 * Created by PhpStorm.
 * User: YHX
 * Date: 2017/4/28
 * Time: 16:02
 */

if($_SERVER['SERVER_PORT'] == 80) {
    defined("SITE_PROTOCOL") ? "" : define("SITE_PROTOCOL", "http");
}else{
    defined("SITE_PROTOCOL") ? "" : define("SITE_PROTOCOL", "https");
}