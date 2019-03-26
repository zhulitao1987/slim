<?php

/**
 * 查询短信发送列表接口
 * User: renxf <renxf@yhxbank.com>
 * Date: 2015/7/10 10:32
 */
class sms_logLib extends Lib
{
    /**
     * @author jjq
     * 保存到短信发送队列
     */
    public function create()
    {
        //初始化信息
        $this->__init(-1, "发送短信接口", "这是发送短信接口，成功返回发送队列ID，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'phone'    => [1, 'string', "手机号码", 'phone', '15171392025'],
            'template' => [1, 'string', "短信模板", 'template_id', "register_code"],
            'params'   => [0, 'string', "模板所需参数，串行化键值对的数组", 'params', serialize(['VERIFY_CODE' => '0000'])],
            'verfiy'   => [0, 'string', '验证码', 'verfiy', '6666']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        // 首次使用登录
        /*require_once '/plugins/sms/emay/emay_sms.php';
        $emay_sms = new emay_sms();
        $emay_sms->login();*/

        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        // 注册短信 防刷
        // 紧急处理， 强行阻止3702
        if($get_data['template_id'] == 'register_code' && $get_data['verfiy'] == '3702'){
            outJson(0, rand(150000, 999999));
        }

        if($get_data['template_id'] == 'register_code') {
            // 手机号限制 一天不能发送大于3次
            $total = M($this->m)->get_total([
                'phone' => $get_data['phone'],
                'template_id' => 1,
                ['AND', 'create_time', '>=', date('Y-m-d 00:00:00')],
                ['AND', 'create_time', '<=', date('Y-m-d 23:59:59')]
            ]);
            if ($total >= 3) {
                outJson(-1, '注册时请不要使用wifi，请使用手机3G/4G网络!');
            }
            // IP限制
            $total = M($this->m)->get_total([
                'add_ip' => getIp(),
                'template_id' => 1,
                ['AND', 'create_time', '>=', date('Y-m-d 00:00:00')],
                ['AND', 'create_time', '<=', date('Y-m-d 23:59:59')]
            ]);
            if ($total > 3) {
                outJson(-1, '注册时请不要使用wifi，请使用手机3G/4G网络!');
            }
            // verfiy限制
            if($get_data['verfiy']) {
                $total = M($this->m)->get_total([
                    'verfiy' => $get_data['verfiy'],
                    'template_id' => 1,
                    ['AND', 'create_time', '>=', date('Y-m-d 00:00:00')],
                    ['AND', 'create_time', '<=', date('Y-m-d 23:59:59')]
                ]);
                if ($total > 2) {
                    outJson(-1, '注册时请不要使用wifi，请使用手机3G/4G网络!');
                }
            }
        }
        $logId = M("sms")->smsAdd($get_data['phone'], $get_data['template_id'], $get_data['params'], $get_data['verfiy']);
        $logId ? outJson(0, $logId) : outJson(-1, '系统繁忙，请稍后再试！');
    }

    /**
     * @author jxy
     * 保存到短信发送队列
     */
    public function create_bak()
    {
        //初始化信息
        $this->__init(-1, "保存待发送短信接口", "这是保存待发送短信接口，成功返回发送队列ID，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'phone'    => [1, 'string', "手机号码", 'phone', '18516501673'],
            'template' => [1, 'string', "短信模板", 'template_id', "register_code"],
            'params'   => [0, 'string', "模板所需参数，串行化键值对的数组", 'params', serialize(['VERIFY_CODE' => '张三丰'])],
            'verfiy'   => [0, 'string', '验证码', 'verfiy', '1234']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        // 注册短信 防刷

        // 紧急处理， 强行阻止3702
        if($get_data['template_id'] == 'register_code' && $get_data['verfiy'] == '3702'){
            outJson(0, rand(150000, 999999));
        }

        if($get_data['template_id'] == 'register_code') {
            // 手机号限制 一天不能发送大于3次
            $total = M($this->m)->get_total([
                'phone' => $get_data['phone'],
                'template_id' => 1,
                ['AND', 'create_time', '>=', date('Y-m-d 00:00:00')],
                ['AND', 'create_time', '<=', date('Y-m-d 23:59:59')]
            ]);
            if ($total >= 3) {
                outJson(-1, '参数错误!');
            }
            // IP限制
            $total = M($this->m)->get_total([
                'add_ip' => getIp(),
                'template_id' => 1,
                ['AND', 'create_time', '>=', date('Y-m-d 00:00:00')],
                ['AND', 'create_time', '<=', date('Y-m-d 23:59:59')]
            ]);
            if ($total > 3) {
                outJson(-1, '参数错误!');
            }
            // verfiy限制
            if($get_data['verfiy']) {
                $total = M($this->m)->get_total([
                    'verfiy' => $get_data['verfiy'],
                    'template_id' => 1,
                    ['AND', 'create_time', '>=', date('Y-m-d 00:00:00')],
                    ['AND', 'create_time', '<=', date('Y-m-d 23:59:59')]
                ]);
                if ($total > 2) {
                    outJson(-1, '参数错误!');
                }
            }
        }
        
        $logId = M("sms")->smsAdd($get_data['phone'], $get_data['template_id'], $get_data['params'], $get_data['verfiy']);
        $logId ? outJson(0, $logId) : outJson(-1, '短信队列添加失败！');
    }

    /**
     * @author jxy
     * 发送短信方法
     */
    private function sendSms()
    {
        $JZYY_URL = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/services/BusinessService?wsdl';
        $JZYY_ACC = 'yuanhengxiang1';
        $JZYY_PASS = '66781886';
        $sms_log_model = M("sms_log");
        $get_phone_list = $sms_log_model->get_list(["valid" => "有效"]);
        try {
            $soap = new SoapClient($JZYY_URL);
            foreach ($get_phone_list as $value) {
                $phone = $value["phone"];
                $content = $value["content"];
                if ($phone && $content) {
                    $result = $soap->sendBatchMessage(array(
                        'account'    => $JZYY_ACC,
                        'password'   => $JZYY_PASS,
                        'destmobile' => $phone,
                        'msgText'    => $content . "【元立方】",
                    ));
                }
                $sms_log_model->update(["valid" => "失效", "send_time" => getRequestTime()], ['id' => $value['id']]);
            }
        } catch (Exception $e) {
            outJson(-1, $e->getMessage());
        }
    }

    /**
     * @author jxy
     * 根据短信模板和参数获取短信内容
     * @param $templateName 短信模板
     * @param string $params 参数，串行化键值对的数组
     * @return array|bool|mixed|string 保存结果
     */
    private function _get_content($templateName, $params = '', $verify = '')
    {
//        outJson(0, $templateName);
        //获取短信模板内容
        $template = M('sms_template')->get_one(['title' => $templateName]);
        $template_id = $template['id'] ? $template['id'] : '';
        $template = $template && is_array($template) ? $template['content'] : '';
        if ($params) {
            $paramArr = is_string($params) ? unserialize(html_entity_decode($params)) : '';
            if ($verify) $paramArr['verify'] = $verify;
            if (is_array($paramArr)) {
                foreach ($paramArr as $key => $value)
                    is_string($value) || $value >= 0 ? $template = str_replace('{' . strtoupper($key) . '}', $value, $template) : outJson(-1, '参数非法！');
            } else {
                outJson(-1, '参数非法！');
            }
        }
        if (stristr($template, '{'))
            outJson(-1, '请根据短信模板传递完整的参数！');
        return array(
            'template'    => $template,
            'template_id' => $template_id
        );
    }

    /**
     * @author jxy
     * 发送短信
     * @param $id 短信发送日志ID
     * @return bool 发送成功
     */
    public function send()
    {
        //初始化信息
        $this->__init(-1, "重新发送短信接口", "这是重新发送短信接口，成功返回1，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id' => [1, 'num', "短信队列ID", 'id', '1'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $data = M('sms_log')->get_one(['id' => $get_data['id']]);
        if ($data) {
            $this->_send_sms($data['phone'], $data['content']);
            M('sms_log')->update(['valid' => '失效', 'send_time' => date('Y-m-d H:i:s', time())], ['id' => $get_data['id']]);
        }
    }

    /**
     * @author jxy
     * 发送短信
     * @param int $phone 手机号码
     * @param string $content 短信内容
     * @return null
     */
    private function _send_sms($phone, $content)
    {
        if ($phone && $content) {
            $phone = 18516501673;
            $content .= '【元立方】';
            //发送投标成功提示短信
            $url = "http://sdk2.zucp.net/webservice.asmx/SendSMS?";
            $data = "sn=%s&pwd=%s&mobile=%s&content=%s&Ext=&stime=&Rrid=";
            $sn = 'SDK-WSS-010-07846';
            $pwd = '6116@a46';
            $content = iconv("UTF-8", "GB2312", $content);
            $rdata = sprintf($data, $sn, $pwd, $phone, $content);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $rdata);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
            //短信发送结束
        }
    }

    /**
     * @author jxy
     * 分页查询短信发送日志信息
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "分页查询短信发送日志接口", "成功分页返回短信发送日志信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20],
            'valid'     => [0, 'string', "是否有效", 'valid', NULL],
            'phone'     => [0, 'string', "手机号码", 'phone', NULL]
        ];
        //输出的参数
        $this->resRule = [
            'list'  => [
                1, 'array', '短信发送日志列表', 'list',
                [
                    'id'          => [1, 'num', "日志ID", 'id', 1],
                    'phone'       => [1, 'string', "手机号码", 'phone', '13800000000'],
                    'template_id' => [1, 'num', "短信模板ID", 'template_id', 1],
                    'verfiy'      => [1, 'string', '短信验证码', 'verfiy', '0'],
                    'valid'       => [1, 'string', "是否有效", 'valid', '有效'],
                    'content'     => [1, 'string', "发送内容", 'content', '恭喜张三，您已经注册成功！'],
                    'create_time' => [1, 'string', "创建时间", 'create_time', '2015-07-07 10:00:00'],
                    'send_time'   => [1, 'string', "发送时间", 'send_time', '2015-07-07 10:00:00'],
                ]
            ],
            'total' => [1, 'num', '查询条数', "total", 100]
        ];
        parent::selectListLib('id DESC');
    }

    /**
     * @author jxy
     * 平安扇验证
     */
    public function peace_fan_verify()
    {
        //初始化信息
        $this->__init(-1, "查询单条信息接口", "成功返回单条信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id'          => [0, 'num', "短信ID号", 'id', 1],
            'phone'       => [0, 'string', "手机号码", 'phone', '13800000000'],
            'verfiy'      => [0, 'string', '短信验证码', 'verfiy', '0'],
            'template_id' => [0, 'string', "短信模板", 'template_id', "1"],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        //获取短信模板内容
        $template = M('sms_template')->get_one(['title' => $get_data['template_id']]);
        $template_id = $template['id'] ? $template['id'] : '';
        $get_data['template_id'] = $template_id;
        $where = $get_data;
        $data = M('sms_log')->get_one($where);
        if (isset($data)) {
            $phone = $data['phone'];
            $verfiy = $data['verfiy'];
            $template_id = $data['template_id'];
            if ($verfiy == $get_data['verfiy']) {
                $template = M('sms_template')->get_one(['id' => $template_id]);
                //判断是否领取 通过手机和活动名获取
                $bool_get = M('activity')->get_one(['phone' => $phone, 'activity_name' => $template['title']]);
                if (isset($bool_get) && $bool_get['id'] > 0) {
                    outJson(-1, '很抱歉,该用户已经领取过!');
                } else {
                    $user_model = M('user');
                    try {
                        //注册新用户
                        $user_model->beginTransaction();
                        if ('2015-09-16' == date('Y-m-d')) {
                            $user_from_sub = '上海张江微电子港';
                        } elseif ('2015-09-17' == date('Y-m-d')) {
                            $user_from_sub = '上海张江华虹';
                        } else {
                            $user_from_sub = '平安扇';
                        }
                        $bool_reg = $user_model->insertUserOne(['password' => md5(substr($phone, -6)), 'phone' => $phone, 'user_from' => '元立方活动', 'user_from_sub' => $user_from_sub, 'reg_time' => getRequestTime(), 'reg_ip' => getIp()], [['phone']], 1);
                        if (true === $bool_reg['bool']) {
                            //新增领取奖品记录
                            $insert['activity_name'] = $template['title'];
                            $insert['phone'] = $phone;
                            $bool_insert = M('activity')->addActivity($insert);
                            if (true === $bool_insert) {
                                //2w元体验金
                                // M("experience")->insertTYJ(['active_title' => "TYJ_01", 'user_id' => $bool_reg['uid']], [['active_title', 'user_id'], ['experience_code']]);
                                $user_model->commit();
                                outJson(0, '恭喜,领取成功！');
                            } else {
                                $user_model->rollback();
                                outJson(-1, $bool_insert);
                            }
                        } else {
                            $user_model->rollback();
                            outJson(-1, $bool_reg['msg']);
                        }
                    } catch (Exception $e) {
                        $user_model->rollback();
                        outJson(-1, $e->getMessage());
                    }
                }
            } else {
                outJson(-1, '验证码校验失败!');
            }
        } else {
            outJson(-1, '验证码校验失败!');
        }
    }
}