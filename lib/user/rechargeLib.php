<?php

/**
 * 用户提现
 * @author:PJK   2015-07-20  peijk@yhxbank.com
 */
class rechargeLib extends Lib
{
    /**
     * @author jxy
     * 用户充值添加
     */
    public function toRecharge()
    {
        outJson(-1, '汇付充值已关闭');
        //初始化信息
        $this->__init(-1, "查询多条用户充值列表接口", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'  => [1, 'num', '用户id', 'user_id', 1],
            'account'  => [1, 'num', "充值金额", 'account', 1500],
            'bank'     => [0, 'string', '充值银行', 'bank', 'CCB'],
            'channel'  => [0, 'string', '充值渠道', 'channel', 'B2C'],
            'mer_priv' => [0, 'string', '商户私有域', 'mer_priv', 'wap']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);

        $recharge_model = M('recharge');
        try {
            $recharge_model->beginTransaction();
            $user_id = $get_data['user_id'];
            $account = $get_data['account'];
            isset($get_data['channel']) ? $gate_busi_id = $get_data['channel'] : $gate_busi_id = 'B2C';
            isset($get_data['mer_priv']) ? $mer_priv = $get_data['mer_priv'] : $mer_priv = '';
            if ($account < 1) {
                outJson(-1, '充值金额不得低于1元');
            }
            //查询用户信息
            $user_info = M("user")->get_one(["id" => $user_id]);
            if (!$user_info) {
                outJson(-1, '用户信息错误');
            }
            if (!$user_info['hf_user_id']) {
                outJson(-1, '请先开通汇付');
            }
            $order = getOrderId();
            $fee = 0;
            $bool_insert_recharge = $recharge_model->addRecharge($user_id, $account, $order, $fee);//添加充值记录
            if (true === $bool_insert_recharge) {
                $bank_info = isset($get_data['bank']) ? $get_data['bank'] : "";
                //数据添加成功,跳汇付页面
                $res = $recharge_model->toHfNetSave($user_id, $order, $account, $bank_info, $gate_busi_id, $mer_priv);
                if ($res) {
                    $recharge_model->commit();
                    outJson(0, $res);
                } else {
                    $recharge_model->rollback();
                    outJson(-1, '充值失败!原因:跳转汇付失败');
                }
            } else {
                outJson(-1, '充值失败!原因:数据库添加失败!');
                $recharge_model->rollback();
                exit();
            }
        } catch (Exception $e) {
            outJson(-1, '充值失败!原因:' . $e->getMessage());
            $recharge_model->rollback();
            exit();
        }
    }

    /**
     * @author jxy
     * 用户充值添加
     */
    public function toYLRecharge()
    {
        outJson(-1, '充值接口关闭！');
        //初始化信息
        $this->__init(-1, "查询多条用户充值列表接口", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [1, 'num', '用户id', 'user_id', 1338],
            'account'    => [1, 'num', "充值金额", 'account', 100],
            'bank_card'  => [0, 'string', '银行卡号', 'bank_card', '6222152244550071'],
            'bank_phone' => [0, 'string', '银行预留手机号', 'bank_phone', '13655225588'],
            'real_name'  => [0, 'string', '真实姓名', 'real_name', '小王子'],
            'id_number'  => [0, 'string', '身份证号', 'id_number', '320483195512011452'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);

        $recharge_model = M('recharge');
        try {
            $recharge_model->beginTransaction();
            $user_id = $get_data['user_id'];
            $account = $get_data['account'];
            $bank_card = "";
            $real_name = "";
            $id_number = "";
            $bank_phone = isset($get_data['bank_phone']) ? $get_data['bank_phone'] : "";
            //是否需要绑卡
            $is_add_bank = false;
            //是否需要实名
            $is_up_bank = false;
            //查询绑卡信息
            $user_bank_info = M("user_bank")->get_one(['user_id' => $user_id, 'type' => '易联']);
            if ($user_bank_info) {
                if (isset($get_data['bank_card']) && $user_bank_info['bank_card'] != $get_data['bank_card']) {
                    $is_up_bank = true;
                    $bank_card = trim($get_data["bank_card"]);
                } else {
                    $bank_card = trim($user_bank_info['bank_card']);
                }
            } elseif (isset($get_data['bank_card'])) {
                $is_add_bank = true;
                $bank_card = trim($get_data["bank_card"]);
            } else {
                outJson(-1, "请输入银行卡号");
            }
            //查询是否已经绑卡
            $user_bank_info = M("user_bank")->get_one(['user_id' => $user_id, 'type' => '易联', 'is_binding' => "是"]);
            if ($user_bank_info) {
                $is_up_bank = false;
            }
            $get_result = M("yl_api")->verifyQuery($bank_card);
            if ($get_result === false) {
                outJson(-1, "验卡失败，请联系管理员！");
            }
            if (!is_numeric($get_result)) {
                outJson(-1, $get_result);
            }
            //查询用户信息
            $user_info = M("user")->get_one(["id" => $user_id]);
            if (!$user_info) {
                outJson(-1, '用户信息错误');
            }
            if (!$bank_phone) {
                $bank_phone = $user_info['phone'];
            }
            if ($user_info['real_name'] && $user_info['id_number']) {
                $real_name = $user_info['real_name'];
                $id_number = $user_info['id_number'];
            } elseif (isset($get_data['real_name']) && isset($get_data['id_number'])) {
//                $is_add_real = true;
                $real_name = trim($get_data['real_name']);
                $id_number = trim($get_data['id_number']);
                $user_model = M('user');
                $get_one = $user_model->get_one(['id_number' => $id_number]);
                if ($get_one && $get_one['id'] != $user_id) {
                    outJson(-1, "用户实名失败，此身份证已被实名！");
                }
            } else {
                outJson(-1, "请输入真实姓名或身份证信息");
            }
//            $mer_priv = isset($get_data['mer_priv']) ? $get_data['mer_priv'] : '';
            if ($account < 2) {
                outJson(-1, '充值金额不得小于2元');
            }
            $order = getYLOrderId();
            //新用户，
            $fee = 0;
            //测试正式取消管理费
            if ($account < 100) {
                $fee = 1;
            }
            $bool_insert_recharge = $recharge_model->addRecharge($user_id, $account, $order, $fee, '易联');//添加充值记录
            if (true === $bool_insert_recharge) {
                if ($is_add_bank || $is_up_bank) {
                    $user_bank_model = M('user_bank');
                    $get_one = $user_bank_model->get_one(['bank_card' => $bank_card, 'type' => '易联', 'is_binding' => '是']);
                    if ($get_one) {
                        outJson(-1, "绑卡失败，此卡已被他人绑定！");
                    }
                    if ($is_add_bank) {
                        if (!$user_bank_model->insert(['user_id' => $user_id, 'bank_card' => $bank_card, 'bank_phone' => $bank_phone,
                                                       'type'    => '易联', 'real_name' => $real_name, 'id_num' => $id_number])
                        ) {
                            outJson(-1, "绑卡失败，请联系客服！");
                        }
                    }
                    if ($is_up_bank) {
                        $user_bank_model->update([
                            'bank_card' => $bank_card, 'bank_phone' => $bank_phone,
                            'type'      => '易联', 'real_name' => $real_name, 'id_num' => $id_number],
                            ['id' => $user_bank_info['id']]);
                    }
                }
//                $sex = "男";
//                if ($id_number) {
//                    $sex_num = substr($id_number, -2, 1);
//                    $sex_list = [0, 2, 4, 6, 8];
//                    if (in_array($sex_num, $sex_list)) {
//                        $sex = "女";
//                    }
//                }
//                if ($is_add_real) {
//                    $user_model = M('user');
//                    $get_one = $user_model->get_one(['id_number' => $id_number]);
//                    if ($get_one) {
//                        outJson(-1, "用户实名失败，此身份证已被实名！");
//                    }
//                    //用户推广码
//                    $promoted_code = $this->getExtensionCode($user_id);
//                    if (!$user_model->update([
//                        'real_name'   => $real_name, 'id_number' => $id_number, "sex" => $sex,
//                        'hf_reg_time' => getRequestTime(), 'promoted_code' => $promoted_code],
//                        ['id' => $user_id, 'real_name' => '', 'id_number' => '', 'promoted_code' => ''])
//                    ) {
//                        outJson(-1, "用户实名失败，请联系管理员！");
//                    }
//                }

//                $get_html = M("ll_api")->llPay($user_id, $order, $account, $bank_card, $real_name, $id_number);
//                if ($get_html !== false) {
//                    $recharge_model->commit();
//                    outJson(0, $get_html);
//                } else {
//                    outJson(-1, '充值失败，请联系客服！');
//                }
//                M("yl_api")->ylSendSms($user_id, $order, $bank_card);
//                $order = "YL14480037220277929984";
//                $get_result = M("yl_api")->verifyQuery($bank_card, $order);
//                if ($get_result === false) {
//                    outJson(-1, "验卡失败，请联系管理员！");
//                }
                $recharge_model->commit();
                if ($get_result == 2) {
                    $out_result = M("yl_api")->ylGather($user_id, $order, $account, $bank_card);
                    if ($out_result === true) {
                        outJson(0, ["do" => "PHONE"]);//显示外呼验密操作流程
                    }
                } else {
                    $out_result = M("yl_api")->ylSendSms($user_id, $order, $account, $bank_card);
                    if ($out_result === true) {
                        outJson(0, ["do" => "SMS", "order" => $order]);//显示验证码页面
                    }
                }
                if ($out_result === false) {
                    outJson(-1, '充值失败，请联系客服！');
                } else {
                    outJson(-1, $out_result);
                }
            } else {
                $recharge_model->rollback();
                outJson(-1, $bool_insert_recharge);
            }
        } catch (Exception $e) {
            $recharge_model->rollback();
            outJson(-1, '充值失败，请联系管理员！' . $e->getMessage());
        }
    }

    /**
     * @author jxy
     * 用户充值添加
     */
    public function toOrderRecharge()
    {
        outJson(-1, "关闭易联充值");
        //初始化信息
        $this->__init(-1, "用户订单充值接口", "成功返回用户充值成功信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'  => [1, 'num', '用户id', 'user_id', 1338],
            'order'    => [1, 'string', "订单号", 'order', '1455236633254254563'],
            'sms_code' => [1, 'string', "短信验证码", 'sms_code', '123456'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_id = $get_data['user_id'];
        //有验证码说明是提交订单验证码
        $get_order_return = M("yl_api")->ylOrderGather($user_id, $get_data['order'], $get_data['sms_code']);
        if ($get_order_return === true) {
            outJson(0, "交易处理中");//可显示：交易处理中
        } else {
            outJson(-1, $get_order_return);
        }
    }

    /**
     * @author jxy
     * 用户绑卡认证
     */
    public function toVerify()
    {
        //初始化信息
        $this->__init(-1, "用户绑卡认证接口", "成功返回用户处理信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [1, 'num', '用户id', 'user_id', 1338],
            'bank_card'  => [1, 'string', '银行卡号', 'bank_card', '6222152244550071'],
            'bank_phone' => [0, 'string', '银行预留手机号', 'bank_phone', '13655225588'],
            'real_name'  => [0, 'string', '真实姓名', 'real_name', '小王子'],
            'id_number'  => [0, 'string', '身份证号', 'id_number', '320483195512011452'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);

        $user_bank_model = M("user_bank");
        try {
            $user_bank_model->beginTransaction();
            $user_id = $get_data['user_id'];
            $bank_card = "";
            $real_name = "";
            $id_number = "";
            $bank_phone = isset($get_data['bank_phone']) ? $get_data['bank_phone'] : "";
            //查询是否已经绑卡
            $user_bank_info = M("user_bank")->get_one(['user_id' => $user_id, 'type' => '易联', 'is_binding' => "是"]);
            if ($user_bank_info) {
                outJson(-1, "已绑定银行卡，切勿重复绑定");
            }
            //是否需要绑卡
            $is_add_bank = false;
            //是否需要实名
            $is_up_bank = false;
            //查询绑卡信息
            $user_bank_info = M("user_bank")->get_one(['user_id' => $user_id, 'type' => '易联']);
            if ($user_bank_info) {
                if ((isset($get_data['bank_card']) && $user_bank_info['bank_card'] != $get_data['bank_card']) ||
                    (isset($get_data['real_name']) && $user_bank_info['real_name'] != $get_data['real_name']) ||
                    (isset($get_data['id_number']) && $user_bank_info['id_number'] != $get_data['id_number']) ||
                    (isset($get_data['bank_phone']) && $user_bank_info['bank_phone'] != $get_data['bank_phone'])
                ) {
                    $is_up_bank = true;
                    $bank_card = trim($get_data["bank_card"]);
                } else {
                    $bank_card = trim($user_bank_info['bank_card']);
                }
            } elseif (isset($get_data['bank_card'])) {
                $is_add_bank = true;
                $bank_card = trim($get_data["bank_card"]);
            } else {
                outJson(-1, "请输入银行卡号");
            }
            $get_one = $user_bank_model->get_one(['bank_card' => $bank_card, 'type' => '易联', 'is_binding' => '是']);
            if ($get_one) {
                outJson(-1, "认证失败，此卡已被他人认证！");
            }
            $get_result = M("yl_api")->verifyQuery($bank_card);
            if ($get_result === false) {
                outJson(-1, "验卡失败，请联系管理员！");
            }
            if (!is_numeric($get_result)) {
                outJson(-1, $get_result);
            }
            //查询用户信息
            $user_info = M("user")->get_one(["id" => $user_id]);
            if (!$user_info) {
                outJson(-1, '用户信息错误');
            }
            if (!$bank_phone) {
                $bank_phone = $user_info['phone'];
            }
            if ($user_info['real_name'] && $user_info['id_number']) {
                $real_name = $user_info['real_name'];
                $id_number = $user_info['id_number'];
            } elseif (isset($get_data['real_name']) && isset($get_data['id_number'])) {
//                $is_add_real = true;
                $real_name = trim($get_data['real_name']);
                $id_number = trim($get_data['id_number']);
                $user_model = M('user');
                $get_one = $user_model->get_one(['id_number' => $id_number]);
                if ($get_one) {
                    outJson(-1, "用户实名失败，此身份证已被实名！");
                }
            } else {
                outJson(-1, "请输入真实姓名或身份证信息");
            }
            $order = getYLOrderId();
            if ($is_add_bank || $is_up_bank) {
                $user_bank_model = M('user_bank');
                $get_one = $user_bank_model->get_one(['bank_card' => $bank_card, 'type' => '易联', 'is_binding' => '是']);
                if ($get_one) {
                    outJson(-1, "绑卡失败，此卡已被他人绑定！");
                }
                if ($is_add_bank) {
                    if (!$user_bank_model->insert(['user_id' => $user_id, 'bank_card' => $bank_card, 'bank_phone' => $bank_phone,
                                                   'type'    => '易联', 'real_name' => $real_name, 'id_num' => $id_number])
                    ) {
                        outJson(-1, "绑卡失败，请联系客服！");
                    }
                }
                if ($is_up_bank) {
                    $user_bank_model->update([
                        'bank_card' => $bank_card, 'bank_phone' => $bank_phone,
                        'type'      => '易联', 'real_name' => $real_name, 'id_num' => $id_number],
                        ['id' => $user_bank_info['id']]);
                }
            }
//            $sex = "男";
//            if ($id_number) {
//                $sex_num = substr($id_number, -2, 1);
//                $sex_list = [0, 2, 4, 6, 8];
//                if (in_array($sex_num, $sex_list)) {
//                    $sex = "女";
//                }
//            }
//            if ($is_add_real) {
//                $user_model = M('user');
//                $get_one = $user_model->get_one(['id_number' => $id_number]);
//                if ($get_one) {
//                    outJson(-1, "用户实名失败，此身份证已被实名！");
//                }
//                //用户推广码
//                $promoted_code = $this->getExtensionCode($user_id);
//                if (!$user_model->update([
//                    'real_name'   => $real_name, 'id_number' => $id_number, "sex" => $sex,
//                    'hf_reg_time' => getRequestTime(), 'promoted_code' => $promoted_code],
//                    ['id' => $user_id, 'real_name' => '', 'id_number' => '', 'promoted_code' => ''])
//                ) {
//                    outJson(-1, "用户实名失败，请联系管理员！");
//                }
//            }
            $user_bank_model->commit();
            if ($get_result == 2) {
                //未认证
                $out_result = M("yl_api")->verify($user_id, $bank_card, $order);
                if ($out_result === true) {
                    outJson(0, "认证已提交！");
                } elseif ($out_result === false) {
                    outJson(-1, '认证失败，请联系客服！');
                } else {
                    outJson(-1, $out_result);
                }
            } else {
                //已认证
                outJson(-1, "此卡已被认证！");
            }
        } catch (Exception $e) {
            $user_bank_model->rollback();
            outJson(-1, '认证失败，请联系管理员！' . $e->getMessage());
        }
    }

    /**
     * @author jxy
     * 易联短信重发接口
     */
    public function toYlSms()
    {
        outJson(-1, "易联短信重发接口关闭");
        //初始化信息
        $this->__init(-1, "易联短信重发接口", "成功返回成功信息，失败返回失败信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', '用户id', 'user_id', 1338],
            'order'   => [1, 'string', "订单号", 'order', '1455236633254254563']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_id = $get_data['user_id'];
        //无验证吗说明是重新发送验证码
        $get_order_return = M("yl_api")->ylOrderSendSms($user_id, $get_data['order']);
        if ($get_order_return === true) {
            outJson(0, "短信已发送");
        } else {
            outJson(-1, $get_order_return);
        }
    }

    /**
     * @author jxy
     * 获取推广码，如推广码已存在，重新生成
     */
    private function getExtensionCode($id)
    {
        $code = strtoupper(substr(md5($id), 13, 8));
        $info = M('user')->get_one(['promoted_code' => $code]);
        if ($info) {
            return $this->getExtensionCode($id . 'ylf');
        } else {
            return $code;
        }
    }

    /**
     * @author jxy
     * 用户宝付充值
     */
    public function toBfRecharge()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户充值列表接口", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'  => [1, 'num', '用户id', 'user_id', 1],
            'account'  => [1, 'num', "充值金额", 'account', 1500],
            'pay_id'   => [1, 'num', '支付方式', 'pay_id', 3001],
            'mer_priv' => [0, 'string', '商户私有域', 'mer_priv', 'wap']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);

        $recharge_model = M('recharge');
        try {
            $recharge_model->beginTransaction();
            $user_id = $get_data['user_id'];
            $account = $get_data['account'];
            $gate_pay_id = $get_data['pay_id'];
            $mer_priv = isset($get_data['mer_priv']) ? $get_data['mer_priv'] : '';
            if ($account < 1) {
                outJson(-1, '充值金额不得小于1元');
            }
            //查询用户信息
            $user_info = M("user")->get_one(["id" => $user_id]);
            if (!$user_info) {
                outJson(-1, '用户信息错误');
            }
            if(!in_array($user_info['type'], ['内部借款','内部投资','外部借款','外部投资','vip','微企汇','元企盈-范忆敏'])){
                outJson(-1, '用户类型错误,请正确使用！');
            }
            //判断实名
            if( M('user')->checkRealName($user_id) !== true){
                outJson(-1, '请先实名！');
            }
            $order = getBFOrderId();
            //新用户，
            $fee = 0;
            //测试正式取消管理费
            /*if ($account < 100) {
                $fee = 1;
            }*/
            $bool_insert_recharge = $recharge_model->addRecharge($user_id, $account, $order, $fee, '宝付', $gate_pay_id, '失败');//添加充值记录
            if (true === $bool_insert_recharge) {
                //数据添加成功,跳汇付页面
                $res = $recharge_model->toBfRecharge($user_id, $account, $order, $gate_pay_id, $mer_priv);
                if ($res) {
                    $recharge_model->commit();
                    outJson(0, $res);
                } else {
                    $recharge_model->rollback();
                    outJson(-1, '充值失败!原因:跳转宝付失败');
                }
            } else {
                $recharge_model->rollback();
                outJson(-1, '充值失败!原因:数据库添加失败!');
            }
        } catch (Exception $e) {
            $recharge_model->rollback();
            outJson(-1, '充值失败!原因:' . $e->getMessage());
        }
    }

    /**
     * @author jxy
     * 用户充值状态修改
     */
    public function update()
    {
        //初始化信息
        $this->__init(-1, "更新用户充值状态接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'id'     => [1, 'num', '充值编号', 'id', 1],
            'status' => [1, 'num', '用户充值状态', 'status', '-1'],
        ];
        parent::updateLib([], ['id']);
    }

   /*
    * 当天指定金额充值次数
    * @author cdf@yhxbank.com
    * @date  2016-06-21
    * @echo json
    */
    public function curDayMinRecTimes()
    {
        //初始化信息
        $this->__init(-1, "当天最小金额充值次数", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'user_id'       => [1, 'num', '用户id', 'user_id', 866],
            'account'       => [1, 'num', "充值金额", 'account', 1000],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $limit_num = M('recharge')->get_total([['', 'user_id' , '=', $get_data['user_id']], ['AND', 'account', '<', $get_data['account']],['AND', 'status', '=', '成功'],['AND', 'add_time', '>', date("Y-m-d 00:00:00", getRequestTime(1))]]);
        outJson(0, $limit_num);
    }

    /**
     * @author jxy
     * 用户充值列表查询
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户充值列表接口", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'   => [0, 'num', '用户id', 'user_id', 7],
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '用户充值列表', 'list',
                [
                    'id'                    => [1, 'num', '充值id', 'id', 1],
                    'user_id'               => [1, 'num', '用户id', 'user_id', 7],
                    'user_recharge_account' => [1, 'num', '用户充值金额', 'user_recharge_account', 100.00],
                    'user_recharge_order'   => [0, 'string', '用户充值订单号', 'user_recharge_order', '20150309212515522783'],
                    'counter_fee'           => [0, 'num', '用户充值手续费', 'counter_fee', 0.00],
                    'status'                => [0, 'num', '用户充值状态', 'status', '0'],
                    'create_time'           => [0, 'string', '用户充值时间', 'create_time', '2015-03-03 03:03:03'],
                    'create_ip'             => [0, 'string', '用户充值ip', 'create_ip', '192.168.1.1'],
                    'remark'                => [0, 'string', '用户充值备注', 'remark', '超限']
                ]
            ]
        ];
        parent::selectUserListLib();
    }

    /**
     * @author jxy
     * 获取充值成功金额和充值失败金额
     */
    public function selectRechargeInfo()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户充值列表接口", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [0, 'num', '用户id', 'user_id', 1],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-03-03 03:03:03'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2015-03-03 03:03:03'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $recharge_model = M('recharge');
        $where = array(
            'user_id' => $get_data['user_id'],
            'status'  => '成功'
        );
        if (isset($get_data['start_time']))
            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        $success_recharge = $recharge_model->get_one($where, '', 'sum(account) as successAccount');
//        outJson(0, $success_recharge);
        $where = array(
            'status'  => '审核中',
            'user_id' => $get_data['user_id'],
//            [' AND ', 'status', ' IN', '("审核中","失败")']
        );
        if (isset($get_data['start_time']))
            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        $false_recharge = $recharge_model->get_one($where, '', 'sum(account) as falseAccount');
        if ($success_recharge['successAccount'] >= 0 && $false_recharge['falseAccount'] >= 0)
            outJson(0, array('successAcc' => $success_recharge['successAccount'], 'falseAcc' => $false_recharge['falseAccount']));
        outJson(-1, '没有数据');
    }

    /**
     *   请不要删！！！！！！！！！！！！！！！！！！！！！！
     * 会员资料:用户充值列表查询
     * author:pjk
     * date:2015-07-15
     * email:peijk@yhxbank.com
     * @update_time 2015-07-23 15:30:00
    */
    public function selectRechargeList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户充值列表接口", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'       => [1, 'num', "页码", 'page', 0],
            'page_size'  => [1, 'num', "显示条数", 'page_size', 20],
            'user_id'    => [0, 'num', '用户id', 'user_id', 1],
            'status'     => [0, 'string', '用户充值状态', 'status', '成功'],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-03-03 03:03:03'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2015-03-03 03:03:03'],
        ];
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $page = isset($get_data['page']) ? $get_data['page'] : 0;
        $page_size = isset($get_data['page_size']) ? $get_data['page_size'] : 0;
        unset($get_data['page']);
        unset($get_data['page_size']);
        $where = array(
            'user_id' => $get_data['user_id'],
        );
        if (isset($get_data['status']))
            $where[] = ['AND', 'status', '=', $get_data['status']];
        if (isset($get_data['start_time']))
            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        $list = $_m->get_list($where, 'id DESC', $page, $page_size);
        $total = $_m->get_total([], 1);
        if ($list) {
//            $return = array();
//            foreach($list as $key => $value) {
//                $return[$key]['add_time'] = $value['add_time'];
//                $return[$key]['remark'] = $value['remark'];
//                $return[$key]['money'] = round($value['money'], 2);
//                $return[$key]['use_money'] = round($value['use_money'], 2);
//                $return[$key]['frozen_money'] = round($value['frozen_money'], 2);
//                $return[$key]['collection_money'] = round($value['collection_money'], 2);
//            }
            $list = ['list' => $list, 'total' => $total];
//            $list = res_data(['list' => $list], $this->resRule);
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /**
     * @function    根据ID查找recharge表记录查询
     * @author      xuwb@yhxbank.com
     * @date        2017-02-08
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "根据ID查找记录", "成功返回单条产品信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id' => [1, 'string', "产品ID", 'id', 1],
        ];
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);

        $recharge_model =   M('recharge');
        $sql            = "SELECT A.*,U.real_name FROM y1_recharge as A LEFT JOIN y1_user AS U ON A.user_id = U.id where A.order = '". $get_data["id"] ."'";
        $user_info      = $recharge_model ->  queryOne($sql);
        if (is_numeric($user_info["bank"])) {
            $user_bank_code = transferBankNumberToName($user_info["bank"]);
        } else {
            $user_bank_code = transferBankCodeToName($user_info["bank"]);
        }
        $user_info["bank"] = $user_bank_code ? $user_bank_code : $user_info["bank"];
        outJson(0, $user_info);
    }
}

