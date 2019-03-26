<?php
//defined("WHERE_SERVER") ? "" : define("WHERE_SERVER", "server");
defined("WHERE_SERVER") ? "" : define("WHERE_SERVER", "dev");
defined("ROOT_PATH") ? "" : define("ROOT_PATH", dirname(__FILE__) . "/");
defined("SITE_PROTOCOL") ? "" : define("SITE_PROTOCOL", "http");

if (WHERE_SERVER == "dev") {
    /********************测试环境数据库*********************************************/
    defined("MYSQL_DSN") ? "" : define("MYSQL_DSN", "mysql:host=rds0cd3g6s22098iws29.mysql.rds.aliyuncs.com;port=3306;dbname=ylf_online");
    defined("MYSQL_USER") ? "" : define("MYSQL_USER", "ylfcf_dev");
    defined("MYSQL_PASS") ? "" : define("MYSQL_PASS", "Ylfcf31581600");
    /***********************测试数据库**********************************************/

    /***************线上临时测试环境数据库************************/
//    defined("MYSQL_DSN") ? "" : define("MYSQL_DSN", "mysql:host=192.168.8.30;port=3306;dbname=dev_ylf");
//    defined("MYSQL_USER") ? "" : define("MYSQL_USER", "root");
//    defined("MYSQL_PASS") ? "" : define("MYSQL_PASS", "redhat");
    /****************线上临时测试环境数据库***********************/
    
    /***************线上临时测试环境数据库************************/
//    defined("MYSQL_DSN") ? "" : define("MYSQL_DSN", "mysql:host=192.168.8.30;port=3306;dbname=yunying");
//    defined("MYSQL_USER") ? "" : define("MYSQL_USER", "root");
//    defined("MYSQL_PASS") ? "" : define("MYSQL_PASS", "redhat");
    /****************线上临时测试环境数据库***********************/

    /***************开发环境引子数据库************************/
//    defined("MYSQL_DSN") ? "" : define("MYSQL_DSN", "mysql:host=192.168.8.30;port=3306;dbname=ylf_online");
//    defined("MYSQL_USER") ? "" : define("MYSQL_USER", "root");
//    defined("MYSQL_PASS") ? "" : define("MYSQL_PASS", "redhat");
    /****************线上临时测试环境数据库***********************/
    
    /****************************本地数据库*****************************************/
//    defined("MYSQL_DSN") ? "" : define("MYSQL_DSN", "mysql:host=192.168.8.30;port=3306;dbname=ylf_online");
//    defined("MYSQL_USER") ? "" : define("MYSQL_USER", "root");
//    defined("MYSQL_PASS") ? "" : define("MYSQL_PASS", "redhat");
    /****************************本地数据库*****************************************/



}