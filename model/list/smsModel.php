<?php
require_once  API_DIR .'/plugins/sms/emay/emay_sms.php';
/*require_once '/plugins/sms/ipyy/ipyy_sms.php';
$ipyy_sms = new ipyy_sms();*/
/**
 * Created by PhpStorm.
 * User: mazhihui
 * Date: 15/8/5
 * Time: 下午3:11
 */
class smsModel extends Model
{

    private $emay_sms = null;

    public function __construct()
    {
        $this->emay_sms = new emay_sms();
    }

    /**
     * 添加短信代码
     * @param string $phone 手机号
     * @param string $sms_title 模板名称
     * @param string $params 参数，串行化键值对的数组
     * @param string $ver 验证码
     * @return bool
     */
    public function smsAdd_bak($phone, $sms_title, $params, $ver = "")
    {
        //获取短信内容
        $content = $this->getContent($sms_title, $params, $ver);
        if ($content) {
            //是否需要开启限制
            if ($content['is_limit'] == "是") {
                //查询二分钟内是否有数据
                $find_time = date("Y-m-d H:i:s", getRequestTime(1) - 120);
                if (M('sms_log')->get_one(['phone' => $phone, ['and', 'create_time', '>', $find_time]])) {
                    return false;
                }
            }
            $saveData = [
                'phone'       => $phone,
                'template_id' => $content['template_id'],
                'verfiy'      => $ver,
                'valid'       => '有效',
                'content'     => $content['template'],
                'create_time' => date('Y-m-d H:i:s', time()),
                'send_time'   => '0000-00-00 00:00:00',
                'add_ip'      => getIp()
            ];
            if (!$logId = M('sms_log')->insert($saveData)) {
                return false;
            }
            //添加注册限制以及黑名单功能
//            $ip = getIp();
//            //IP判断
//            if ($ip && $ip != '116.228.155.70' && $ip != '127.0.0.1') {
//                //查询是否存在黑名单IP
//                $back_info = M('back_ip')->get_one(['back_ip' => $ip]);
//                if ($back_info) {
//                    return false;
//                }
//                //查询十分钟内是否有相同注册
//                $find_time = date("Y-m-d H:i:s", getRequestTime(1) - 600);
//                if (M('user')->get_total(['reg_ip' => $ip, ['and', 'reg_time', '>', $find_time]]) >= 4) {
//                    M('back_ip')->insert(['num' => 1, 'back_ip' => $ip]);
//                    return false;
//                }
//            }
            //=============================
            if ($content['is_voice'] == '是') {
                $this->sendYYSms($logId, $sms_title);
            } else {
                if ($sms_title == 'push_red_bag') {
                    $this->sendRedBagSms($logId);
                } else {
                    $this->sendSms($logId);
                }
            }
            return $logId;
        }
        return false;
    }

    /**
     * 根据短信模板和参数获取短信内容
     * @param string $templateName 短信模板
     * @param string $params 参数，串行化键值对的数组
     * @return array|bool|mixed|string 保存结果
     */
    public function getContent($templateName, $params = '', $verify = '')
    {
        //获取短信模板内容
        $template_info = M('sms_template')->get_one(['title' => $templateName, 'is_enable' => "是"]);
        if (!$template_info) {
            return false;
        }
        $template_id = $template_info['id'];
        $template = $template_info['content'];
        if ($params) {
            $paramArr = is_string($params) ? unserialize(html_entity_decode($params)) : '';
            if ($verify) $paramArr['verify'] = $verify;
            if (is_array($paramArr)) {
                foreach ($paramArr as $key => $value)
                    is_string($value) || $value >= 0 ? $template = str_replace('{' . strtoupper($key) . '}', $value, $template) : outJson(-1, '参数非法！');
            } else {
                return false;
            }
        }
        if (stristr($template, '{'))
            return false;
        return array(
            'title'       => $templateName,
            'template'    => $template,
            'template_id' => $template_id,
            'is_voice'    => $template_info['is_voice'],
            'is_limit'    => $template_info['is_limit']
        );
    }

    /**
     * 发送短信方法
     * @param string $log_id 短信ID
     * @return bool
     */
    public function sendSms($log_id)
    {
        $JZYY_URL = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/services/BusinessService?wsdl';
        $JZYY_ACC = 'yuanhengxiang1';
        $JZYY_PASS = '31581600yhx';  //'66781886';
        $sms_log_model = M("sms_log");
        $sms_log_info = $sms_log_model->get_one(["id" => $log_id, "valid" => "有效"]);
        if (!$sms_log_info) {
            return false;
        }
        try {
            $soap = new SoapClient($JZYY_URL);
            if (!$sms_log_model->update(["valid" => "失效", "send_time" => getRequestTime()], ['id' => $log_id, "valid" => "有效"])) {
                return false;
            }
            $phone = $sms_log_info["phone"];
            $content = $sms_log_info["content"];
            if ($phone && $content) {
                $result = "测试";
                if (WHERE_SERVER == "server") {
                    $result = $soap->sendBatchMessage(array(
                        'account'    => $JZYY_ACC,
                        'password'   => $JZYY_PASS,
                        'destmobile' => $phone,
                        'msgText'    => $content . "【元立方】",
                    ));
                    $result = $result->sendBatchMessageReturn;
                }
                $sms_log_model->update(["send_result" => $result], ['id' => $log_id]);
                $template_id = $sms_log_info["template_id"];
                $template_info = M("sms_template")->get_one(['id' => $template_id]);
                if (!$template_info) {
                    return false;
                }
                $send_num = $template_info['send_num'];
                M("sms_template")->update(['send_num' => $send_num + 1], ['id' => $template_id]);
                //获取所有发送条数
                $sum_num_info = M("sms_template")->get_one([], '', 'sum(send_num) as num');
                $sum_num = $sum_num_info["num"];
                $send_num_info = M("sms_template")->get_one(['title' => 'sms_send_num']);
                $send_num = $send_num_info["send_num"];
                $sum_num -= $send_num;
                if ($send_num * 100 <= $sum_num) {
                    if (M("sms_template")->update(['send_num' => $send_num + 1], ['title' => 'sms_send_num', 'send_num' => $send_num])) {
                        $this->smsAdd("13816506449", "sms_send_num", serialize(['NUM' => $send_num, 'TIME' => getRequestTime()]));
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 发送短信方法(仅适用于发送红包)
     * @param string $log_id 短信ID
     * @return bool
     */
    public function sendRedBagSms($log_id)
    {
        $sms_log_model = M("sms_log");
        $sms_log_info = $sms_log_model->get_one(["id" => $log_id, "valid" => "有效"]);

        if (!$sms_log_info) {
            return false;
        }
        try {
            if (!$sms_log_model->update(["valid" => "失效", "send_time" => getRequestTime()], ['id' => $log_id, "valid" => "有效"])) {
                return false;
            }
            $phone = $sms_log_info["phone"];
            $content = $sms_log_info["content"];
            if ($phone && $content) {
                $result = "测试";
                if (WHERE_SERVER == "server") {
                    $result = $this->emay_sms->sendSMS($phone, $content);
                }
                $sms_log_model->update(["send_result" => $result], ['id' => $log_id]);
                $template_id = $sms_log_info["template_id"];
                $template_info = M("sms_template")->get_one(['id' => $template_id]);
                if (!$template_info) {
                    return false;
                }
                $send_num = $template_info['send_num'];
                M("sms_template")->update(['send_num' => $send_num + 1], ['id' => $template_id]);
                //获取所有发送条数
                $sum_num_info = M("sms_template")->get_one([], '', 'sum(send_num) as num');
                $sum_num = $sum_num_info["num"];
                $send_num_info = M("sms_template")->get_one(['title' => 'sms_send_num']);
                $send_num = $send_num_info["send_num"];
                $sum_num -= $send_num;
                if ($send_num * 100 <= $sum_num) {
                    if (M("sms_template")->update(['send_num' => $send_num + 1], ['title' => 'sms_send_num', 'send_num' => $send_num])) {
                        $this->smsAdd("13816506449", "sms_send_num", serialize(['NUM' => $send_num, 'TIME' => getRequestTime()]));
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 发送语音消息
     * @param string $log_id 短信ID
     * @param string $sms_title 短信标题，用以区分
     * @return bool
     */
    public function sendYYSms($log_id, $sms_title)
    {
        $sms_log_model = M("sms_log");
        $sms_log_info = $sms_log_model->get_one(["id" => $log_id, "valid" => "有效"]);
        if (!$sms_log_info) {
            return false;
        }
        try {
            if (!$sms_log_model->update(["valid" => "失效", "send_time" => getRequestTime()], ['id' => $log_id, "valid" => "有效"])) {
                return false;
            }
            $phone = $sms_log_info["phone"];
            $content = $sms_log_info["content"];
            if ($phone && $content) {
                $result = "语音测试";
                if (WHERE_SERVER == "server") {
                    $argv = array(
                        'sn'        => 'SDK-WSS-010-07846', //换成您自己的序列号
                        'pwd'       => strtoupper(md5('SDK-WSS-010-07846' . '6116@a46')), //此处密码需要加密 加密方式为 md5(sn+password)
                        'title'     => iconv("UTF-8", "gb2312//IGNORE", $sms_title),
                        'mobile'    => $phone,//手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
                        'txt'       => iconv("UTF-8", "gb2312//IGNORE", $content),  //语音文本 和语音文件content 选择其中一个进行传值 即可，不需要同时传值 也不可同时不传值
                        'content'   => '',
                        'srcnumber' => '',//默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值
                        'stime'     => ''//定时时间 格式为2011-6-29 11:09:21
                    );
                    $params = "";
                    $flag = 0;
                    foreach ($argv as $key => $value) {
                        if ($flag != 0) {
                            $params .= "&";
                        }
                        $params .= $key . "=";
                        $params .= urlencode($value);
                        $flag = 1;
                    }
                    $length = strlen($params);
                    $fp = fsockopen("sdk3.entinfo.cn", 8060, $errno, $errstr, 10) or exit($errstr . "--->" . $errno);
                    $header = "POST /webservice.asmx/mdAudioSend HTTP/1.1\r\n";
                    $header .= "Host:sdk3.entinfo.cn\r\n";
                    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                    $header .= "Content-Length: " . $length . "\r\n";
                    $header .= "Connection: Close\r\n\r\n";
                    $header .= $params . "\r\n";
                    fputs($fp, $header);
                    $inheader = 1;
                    $line = "";
                    while (!feof($fp)) {
                        $line = fgets($fp, 1024); //去除请求包的头只显示页面的返回数据
                        if ($inheader && ($line == "\n" || $line == "\r\n")) {
                            $inheader = 0;
                        }
                        if ($inheader == 0) {

                        }
                    }
                    $line = str_replace("<string xmlns=\"http://tempuri.org/\">", "", $line);
                    $line = str_replace("</string>", "", $line);
                    $result = $line;
                }
                $sms_log_model->update(["send_result" => $result], ['id' => $log_id]);
                $template_id = $sms_log_info["template_id"];
                $template_info = M("sms_template")->get_one(['id' => $template_id]);
                if (!$template_info) {
                    return false;
                }
                $send_num = $template_info['send_num'];
                M("sms_template")->update(['send_num' => $send_num + 1], ['id' => $template_id]);
                //获取所有发送条数
                $sum_num_info = M("sms_template")->get_one([], '', 'sum(send_num) as num');
                $sum_num = $sum_num_info["num"];
                $send_num_info = M("sms_template")->get_one(['title' => 'sms_send_num']);
                $send_num = $send_num_info["send_num"];
                $sum_num -= $send_num;
                if ($send_num * 100 <= $sum_num) {
                    if (M("sms_template")->update(['send_num' => $send_num + 1], ['title' => 'sms_send_num', 'send_num' => $send_num])) {
                        $this->smsAdd("13816506449", "sms_send_num", serialize(['NUM' => $send_num, 'TIME' => getRequestTime()]));
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 添加短信代码
     * @param string $phone 手机号
     * @param string $sms_title 模板名称
     * @param string $params 参数，串行化键值对的数组
     * @param string $ver 验证码
     * @return bool
     */
    public function smsAdd($phone, $sms_title, $params, $ver = "")
    {

        //获取短信内容
        $content = $this->getContent($sms_title, $params, $ver);
        if ($content) {
            //是否需要开启限制
            if ($content['is_limit'] == "是") {
                //查询二分钟内是否有数据
                $find_time = date("Y-m-d H:i:s", getRequestTime(1) - 120);
                if (M('sms_log')->get_one(['phone' => $phone, ['and', 'create_time', '>', $find_time]])) {
                    return false;
                }
            }
            $saveData = [
                'phone'       => $phone,
                'template_id' => $content['template_id'],
                'verfiy'      => $ver,
                'valid'       => '有效',
                'content'     => $content['template'],
                'create_time' => date('Y-m-d H:i:s', time()),
                'send_time'   => '0000-00-00 00:00:00',
                'add_ip'      => getIp()
            ];
            if (!$logId = M('sms_log')->insert($saveData)) {
                return false;
            }
            if ($sms_title == 'push_red_bag') {
                $this->sendRedBagSms($logId);
            } else {
                $this->doSendSmsCommon($logId, $content['is_voice'], $params);
            }
            return $logId;
        }
        return false;
    }

    /**
     * 发送短信方法
     * @param string $log_id 短信ID
     * @param string $is_voice 是否语音
     * @param array $params 参数
     * @return bool
     */
    public function doSendSmsCommon($log_id, $is_voice = '', $params = array())
    {
        $sms_log_model = M("sms_log");
        $sms_log_info = $sms_log_model->get_one(["id" => $log_id, "valid" => "有效"]);
        if (!$sms_log_info) {
            return false;
        }
        try {
            if (!$sms_log_model->update(["valid" => "失效", "send_time" => getRequestTime()], ['id' => $log_id, "valid" => "有效"])) {
                return false;
            }
            $phone = $sms_log_info["phone"];
            $content = $sms_log_info["content"];
            $verfiy = $sms_log_info["verfiy"];
            if ($phone && $content) {
                $result = "测试";
                //$result = $ipyy_sms->snedSMS($phone, '【元立方】' . $content);
                if (WHERE_SERVER == "server") {
                    if($is_voice == '是'){
                        if(!$verfiy){
                            $paramArr = is_string($params) ? unserialize(html_entity_decode($params)) : '';
                            if(isset($paramArr['VERIFY_CODE'])) $verfiy = $paramArr['VERIFY_CODE'];
                        }
                        $result = $this->emay_sms->sendVoice($phone, $verfiy);
                    }else {
                        $result = $this->emay_sms->sendSMS($phone, $content);
                    }
                }
                $sms_log_model->update(["send_result" => $result], ['id' => $log_id]);
                $template_id = $sms_log_info["template_id"];
                $template_info = M("sms_template")->get_one(['id' => $template_id]);
                if (!$template_info) {
                    return false;
                }
                $send_num = $template_info['send_num'];
                M("sms_template")->update(['send_num' => $send_num + 1], ['id' => $template_id]);
                //获取所有发送条数
                $sum_num_info = M("sms_template")->get_one([], '', 'sum(send_num) as num');
                $sum_num = $sum_num_info["num"];
                $send_num_info = M("sms_template")->get_one(['title' => 'sms_send_num']);
                $send_num = $send_num_info["send_num"];
                $sum_num -= $send_num;
                if ($send_num * 100 <= $sum_num) {
                    if (M("sms_template")->update(['send_num' => $send_num + 1], ['title' => 'sms_send_num', 'send_num' => $send_num])) {
                        $this->smsAdd("13816506449", "sms_send_num", serialize(['NUM' => $send_num, 'TIME' => getRequestTime()]));
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}