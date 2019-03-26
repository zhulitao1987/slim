<?php
require_once('/plugins/nusoaplib/nusoap.php');

/**
 * Created by PhpStorm.
 * User: jjq
 * Date: 2016/8/26
 * Time: 9:25
 */

class ipyy_sms_ws{

    /**
     * @var string 地址
     */
    private $url = 'http://sh2.ipyy.com/WebService.asmx?wsdl';



    /**
     *以下为参数
     */

    /**
     * @var string 发送用户帐号
     */
    private $userName = 'gs13';

    /**
     * @var string 发送帐号密码
     */
    private $password = 'a123456';

    /**
     * @var string 短信参数对象
     */
    private $sms = '';

    /**
     * @var string 全部被叫号码 (短信发送的目的号码.多个号码之间用半角逗号隔开)
     */
    private $msisdns = '';

    /**
     * @var string 发送内容
     */
    private $smsContent = '';

    /**
     * @var string 定时发送时间
     */
    private $planSendTime = '';

    /**
     * @var string 扩展子号
     */
    private $extNumber = '';


    /**
     * webservice客户端
     */
    private $soap;

    /**
     * 默认命名空间
     */
    private $namespace = 'http://sh2.ipyy.com/';

    /**
     * 往外发送的内容的编码,默认为 UTF-8
     */
    private $outgoingEncoding = "UTF-8";

    private $proxyhost = false;
    private $proxyport = false;
    private $proxyusername = false;
    private $proxypassword = false;
    private $timeout = 0;
    private $response_timeout = 30;

    /**
     * 构造函数
     */
    public function __construct(){
        /**
         * 初始化 webservice 客户端
         */
        $this->soap = new nusoap_client($this->url,'wsdl',$this->proxyhost,$this->proxyport,$this->proxyusername,$this->proxypassword,$this->timeout,$this->response_timeout);
        //$this->soap = new nusoap_client($this->url,'wsdl');
        $this->soap->soap_defencoding = $this->outgoingEncoding;
        $this->soap->decode_utf8 = false;
    }

    /**
     * @param $phone_arr 手机数组
     * @param $content 内容
     */
    public function snedSMS($phone_arr,$content){
        $this->msisdns = implode(',', $phone_arr);
        $this->smsContent = $content;
        $arr = array(
            'Msisdns' => $this->msisdns,
            'SMSContent' => $this->smsContent,
            //'PlanSendTime' => $this->planSendTime,
            //'ExtNumber' => $this->extNumber,
        );
        $this->sms = (object)$arr;
        $params = array(
            'userName' => $this->userName,
            'password' => $this->password,
            'sms' => $this->sms
        );
        /*$params = array(
            'arg0' => $this->userName,
            'arg1' => $this->password,
            'arg2' => $this->sms
        );*/
        //$result = $this->soap->call("SendSms",$params,$this->namespace);
        $result = $this->soap->call("SendSms",$params);
        //outJson(-1,$result);
        return $result['StatusCode'];
    }


}