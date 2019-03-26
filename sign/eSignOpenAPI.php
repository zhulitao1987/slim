<?php

/**
 * @author zhult
 * @function 签章SDK,匹配版本
 *
*/
//生成环境，请禁用错误报告
//error_reporting();

//定义SDK相关目录，不要随意修改
define("ESIGN_ROOT", __DIR__);
define("ESIGN_CLASS_PATH", ESIGN_ROOT . "/core/");

//调试模式，false：不打印相关日志；true、请设置日志文件目录以及读写权限
define('ESIGN_DEBUGE', true);

//日志文件目录
define("ESIGN_LOG_DIR", realpath(ESIGN_ROOT . '/'). "/logs/");
//if (ESIGN_DEBUGE && !is_dir(ESIGN_LOG_DIR)) {
//    mkdir(ESIGN_LOG_DIR, 0777);
//}

//项目ID等配置文件
require_once(ESIGN_ROOT . "/comm/initConfig.php");
//COMM
require_once(ESIGN_ROOT . '/constants/ErrorConstant.php');
require_once(ESIGN_ROOT . '/constants/EventType.php');
require_once(ESIGN_ROOT . '/constants/LicenseType.php');
require_once(ESIGN_ROOT . '/constants/OrganRegType.php');
require_once(ESIGN_ROOT . '/constants/OrganType.php');
require_once(ESIGN_ROOT . '/constants/OrganizeTemplateType.php');
require_once(ESIGN_ROOT . '/constants/PersonArea.php');
require_once(ESIGN_ROOT . '/constants/PersonTemplateType.php');
require_once(ESIGN_ROOT . '/constants/SealColor.php');
require_once(ESIGN_ROOT . '/constants/SignType.php');
require_once(ESIGN_ROOT . '/constants/StoreType.php');
require_once(ESIGN_ROOT . '/constants/UserType.php');
//CORE
require_once(ESIGN_ROOT . '/core/HttpUtils.php');
require_once(ESIGN_ROOT . '/core/JavaComm.php');
require_once(ESIGN_ROOT . '/core/Log.php');
require_once(ESIGN_ROOT . '/core/Recorder.php');
require_once(ESIGN_ROOT . '/core/Upload.php');
require_once(ESIGN_ROOT . '/core/Util.php');
require_once(ESIGN_ROOT . '/core/eSign.php');
//result
require_once(ESIGN_ROOT . '/result/AbstractResult.php');
require_once(ESIGN_ROOT . '/result/AddAccountResult.php');
require_once(ESIGN_ROOT . '/result/AddEventCertResult.php');
require_once(ESIGN_ROOT . '/result/AddTemplateResult.php');
require_once(ESIGN_ROOT . '/result/EvidenceResult.php');
require_once(ESIGN_ROOT . '/result/FileSignResult.php');
require_once(ESIGN_ROOT . '/result/GetSignDetailResult.php');
require_once(ESIGN_ROOT . '/result/Result.php');
require_once(ESIGN_ROOT . '/result/TextSignResult.php');
require_once(ESIGN_ROOT . '/result/VerifyPdfResult.php');







