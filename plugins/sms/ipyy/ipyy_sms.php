<?php
/**
 * Created by PhpStorm.
 * User: jjq
 * Date: 2016/8/26
 * Time: 9:25
 */

class ipyy_sms{

    /**
     * @var string 对应UTF-8 地址
     */
    private $url = 'http://sh2.ipyy.com/sms.aspx';

    /**
     * @var string 对应GB2312 地址
     */
    private $url_gbk = 'http://sh2.ipyy.com/smsGBK.aspx';

    /**
     * @var string 对应UTF-8(返回值为json格式) 地址
     */
    private $url_json = 'http://sh2.ipyy.com/smsJson.aspx?action=send';

    /**
     * @var string 对应UTF-8(加密传输,使用json) 地址
     */
    private $url_en = 'http://sh2.ipyy.com/ensms.ashx';


    /**
     *以下为参数
     */

    /**
     * @var string 企业id
     */
    private $userid = '';

    /**
     * @var string 发送用户帐号
     */
    private $account = 'gs13';

    /**
     * @var string 发送帐号密码
     */
    private $password = 'a123456';

    /**
     * @var string 全部被叫号码 (短信发送的目的号码.多个号码之间用半角逗号隔开)
     */
    private $mobile = '';

    /**
     * @var string 发送内容
     */
    private $content = '';

    /**
     * @var string 定时发送时间
     */
    private $sendTime = '';

    /**
     * @var string 发送任务命令
     */
    private $action = '';

    /**
     * @var string 扩展子号
     */
    private $extno = '';


    /**
     * @param $phone_arr 手机数组或字符串
     * @param $content 内容
     */
    public function snedSMS($phone_arr,$content){
        if(is_array($phone_arr)) {
            $this->mobile = implode(',', $phone_arr);
        }else{
            $this->mobile = $phone_arr;
        }
        $this->content = $content;
        $post = array(
            'userid' => $this->userid,
            'account' => $this->account,
            'password' => $this->password,
            'mobile' => $this->mobile,
            'content' => $this->content,
            'sendTime' => $this->sendTime,
            'action' => $this->action,
            'extno' => $this->extno,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $this->url_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $res = curl_exec($ch);
        curl_close($ch);
        //outJson(-1,$res);
        $res = json_decode($res);
        return $res->returnstatus;
    }


}