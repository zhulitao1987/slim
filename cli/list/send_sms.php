<?php
die("就是不给你执行，你打我啊？");
include_once dirname(__FILE__) . "/../cli.php";
set_time_limit(0);
define('JZYY_URL', 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/services/BusinessService?wsdl');
define('JZYY_ACC', 'yuanhengxiang1');
define('JZYY_PASS', '66781886');

class send_sms extends Cli
{

    /*
     * 发送短信方法
     */
    public function sendSmsCli()
    {
        $sms_log_model = M("sms_log");
        $get_phone_list = $sms_log_model->get_list(["valid" => "有效"]);
        try {
            $soap = new SoapClient(JZYY_URL);
            foreach ($get_phone_list as $value) {
                $phone = $value["phone"];
                $content = $value["content"];
                if ($phone && $content) {
                    $result = $soap->sendBatchMessage(array(
                        'account'    => JZYY_ACC,
                        'password'   => JZYY_PASS,
                        'destmobile' => $phone,
                        'msgText'    => $content . "【元亨祥】",
                    ));
                    echo $result->sendBatchMessageReturn;
                }
                $sms_log_model->update(["valid" => "失效", "send_time" => getRequestTime()], ['id' => $value['id']]);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

$send_sms = new send_sms();
$send_sms->sendSmsCli();
