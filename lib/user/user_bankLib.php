<?php

/**
 * 用户银行卡类
 * 此文件程序用来做什么的（详细说明，可选。）。
 * @author      LHD
 */
class user_bankLib extends Lib
{
    /**
     * @author jxy
     * 查询单条用户信息
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户银行卡信息", "成功返回单条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [1, 'num', "用户ID", 'user_id', 866],
            'type'       => [1, 'string', "银行卡所属系统", 'type', '宝付'],
            'is_binding' => [0, 'string', "是否绑定", 'is_binding', '是'],
        ];
        //输出的参数
        $this->resRule = [
            'id'            => [1, 'num', "用户编号", 'id', 1],
            'user_id'       => [1, 'num', "用户编号", 'user_id', 866],
            'bank_card'     => [1, 'string', "用户登陆名", 'bank_card', '6222023602075552566'],
            'bank_name'     => [1, 'string', "银行", 'bank_name', '工商银行'],
            'province_code' => [1, 'string', "银行所在省", 'province_code', '浙江'],
            'city_code'     => [1, 'string', "银行所在市", 'city_code', '杭州市'],
            'branch_name'   => [1, 'string', "支行名称", 'branch_name', '西湖支行'],
            'local_bank_id' => [1, 'string', "联行号", 'local_bank_id', '102290029777'],
            'bank_num'      => [1, 'string', "银行编号", 'bank_num', '01020000'],
            'bank_code'     => [1, 'string', "银行简码", 'bank_code', 'ICBC'],
            'type'          => [1, 'string', "绑定所属", 'type', '易联'],
            'is_binding'    => [1, 'string', "是否认证", 'is_binding', '是'],
            'real_name'     => [1, 'string', "姓名", 'real_name', '张三'],
            'bind_id'       => [1, 'string', "绑定标识号", 'bind_id', '201603261412121000009649074'],
        ];
        parent::selectOneLib();
    }

    /**
     * @author jxy
     * 查询多条用户信息
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询用户银行卡信息", "成功返回单条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [1, 'num', "用户ID", 'user_id', 878],
            'type'       => [0, 'string', "银行卡所属系统", 'type', '易联'],
            'is_binding' => [0, 'string', "是否绑定", 'is_binding', '是'],
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '用户充值列表', 'list',
                [
                    'id'         => [1, 'num', "用户编号", 'id', 1],
                    'user_id'    => [1, 'num', "用户编号", 'user_id', 878],
                    'bank_card'  => [1, 'string', "用户登陆名", 'bank_card', '6222023602075552566'],
                    'bank_name'  => [1, 'string', "银行", 'bank_name', '工商银行'],
                    'bank_phone' => [1, 'string', "手机号", 'bank_phone', '13655882245'],
                    'type'       => [1, 'string', "绑定所属", 'type', '易联'],
                    'is_binding' => [1, 'string', "是否认证", 'is_binding', '是'],
                    'real_name'  => [1, 'string', "真实姓名", 'real_name', '小王子'],
                    'id_num'     => [1, 'string', "身份证", 'id_num', '320483198412011154'],
                ]
            ] ,
        'total' => [1, 'num', '查询条数', "total", 100]
        ];
        parent::selectListLib();
    }

  /*
   * 发送绑卡短信
   * @author cdf@yhxbank.com
   * @date  2016-06-15
   * @echo json
   */
    public function sendBindCardMsg()
    {
        //初始化信息
        $this->__init(-1, "宝付绑卡发送手机短信", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'              => [1, 'num', '用户id', 'user_id', 866],
            'bank_card'            => [1, 'num', "银行卡卡号", 'bank_card', '6217000830000123038'],
            'bank_phone'           => [1, 'string', "银行预留手机号", 'bank_phone', '18117433065']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        //查询用户信息
        $user_id = $get_data['user_id'];
        $user_info = M("user")->get_one(["id" => $user_id]);
        if (!$user_info) {
            outJson(-1, '用户信息错误');
        }
        $order_sn = getBFOrderId(); //宝付订单号
        $get_data['order_sn'] = $order_sn;
        $get_data['next_txn_sub_type'] = '01';//下一步进行的交易子类为绑卡
        $return_msg = M('bf_quick_api')->sendMsg($get_data, $user_id);
        if (is_array($return_msg)) {
            M('bf_bind_card')->update(['back_sms_code' => $return_msg['resp_code'], 'back_sms_msg' => $return_msg['resp_msg'], 'back_sms_json' => json_encode($return_msg), 'back_sms_time' => getRequestTime(), 'back_sms_ip' => getIp()], ['order_sn' => $order_sn]);
            if ('0000' === $return_msg['resp_code']) {
                outJson(0, ["resp_msg" => $return_msg['resp_msg'], "order_sn" => $order_sn]);
            }
            outJson(-1, $return_msg['resp_msg']);
        }
        M('bf_bind_card')->update(['back_sms_msg' => $return_msg , 'back_sms_time' => getRequestTime(), 'back_sms_ip' => getIp()], ['order_sn' => $order_sn]);
        outJson(-1, $return_msg);
    }

    /**
    * 实名建立绑定关系类交易
    * @author cdf@yhxbank.com
    * @date  2016-06-15
    * @echo json
    */
    public function bindCard()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户充值列表接口", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'       => [1, 'num', '用户id', 'user_id', 866],
            'bank_card'     => [1, 'num', "银行卡卡号", 'bank_card', '6217000830000123038'],
            'bank_code'     => [1, 'string', "银行卡编码", 'bank_code', 'CCB'],
            'bank_phone'    => [1, 'string', "银行预留手机号", 'bank_phone', '18117433065'],
            'sms_code'      => [1, 'string', "短信验证码", 'sms_code', '201603'],
            'order_sn'      => [1, 'string', "订单号", 'order_sn', 'BF146605619089439392'],
            'bank_name'     => [0, 'string', '银行名称', 'bank_name', '中国建设银行'],
            'id_num'        => [0, 'string', '身份证号码', 'id_num', '321324198604233918'],
            'real_name'     => [0, 'string', "姓名", 'real_name', '陈东风']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_id = $get_data['user_id'];
        $user_info = M("user")->get_one(["id" => $user_id]);
        if (!$user_info) {
            outJson(-1, '用户信息错误');
        }
        if (empty($user_info['real_name']) || empty($user_info['id_number'])) {
            outJson(-1, '请先实名');
        }
        //这两个参数必须传到宝付，如果不传，可直接从数据库读
        isset($get_data['real_name']) ? : $get_data['real_name'] = $user_info['real_name'];
        isset($get_data['id_num']) ? : $get_data['id_num'] = $user_info['id_number'];
        $user_bank_model = M('user_bank');
        $user_bank_info = $user_bank_model->get_one(['bank_card' => $get_data['bank_card'], 'type' => '宝付', 'is_binding' => '是']);
        if (isset($user_bank_info) && $user_bank_info) {
            if ($user_bank_info['user_id'] != $user_id) {
                outJson(-1, "绑卡失败，此卡已被他人绑定！");
            } else {
                outJson(-1, "您已绑卡，请解绑后重新绑定！");
            }
        }
        $is_add_bank = true;
        $user_bank_msg = $user_bank_model->get_one(['bank_card' => $get_data['bank_card'], 'type' => '宝付', 'user_id' => $user_id]);
        if (isset($user_bank_msg) && $user_bank_msg) {
            $is_add_bank = false; //重新绑卡
        }
        $return_msg = M('bf_quick_api')->bindCard($get_data);
        if (is_array($return_msg)) {
            $is_have_def = '否';
            if ($return_msg['trans_id'] !== $get_data['order_sn']) {
                $is_have_def = '是';
            }
            M('bf_bind_card')->update(['return_sn' => $return_msg['trans_id'], 'back_resp_code' => $return_msg['resp_code'], 'back_resp_msg'=>$return_msg['resp_msg'], 'is_have_def' => $is_have_def], ['order_sn' => $get_data['order_sn']]);
            if ('0000' === $return_msg['resp_code']) {
                unset($get_data['sms_code']); //删除短信验证码
                unset($get_data['order_sn']); //删除订单号
                if (!isset($get_data['bank_name']) || empty($get_data['bank_name'])) {
                    $get_data['bank_name'] = transferBankCodeToName($get_data['bank_code']);
                }
                $get_data['bind_id'] = $return_msg['bind_id'];//绑定标志号
                $get_data['is_binding'] = '是';
                $get_data['type'] = '宝付';
                if ($is_add_bank) {
                    $user_bank_model->insert($get_data);
                    $user_bank_model->delete(['user_id' => $user_id, 'type' => '宝付', 'is_binding' => '否']);
                } else {
                    $user_bank_model->update($get_data, ['id' => $user_bank_msg['id']]);
                }
                //绑卡成功发送短信提醒
                $param = serialize(['REAL_NAME' => $user_info['real_name']]);
                M("sms")->smsAdd($user_info['phone'], 'bf_bind_card_success', $param, '0');
                outJson(0, '绑卡成功');
            }
            outJson(-1, $return_msg['resp_msg']);
        }
        outJson(-1, $return_msg);
    }

    /**
     * 发送充值短信
     * @author cdf@yhxbank.com
     * @date  2016-06-15
     * @echo json
     */
    public function sendRechargeMsg()
    {
        //初始化信息
        $this->__init(-1, "充值发送手机短信", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'       => [1, 'num', '用户id', 'user_id', 866],
            'amount'       => [1, 'num', "充值金额", 'amount', 10],
            'bind_id'       => [0, 'num', "绑定标识号", 'bind_id', '201604271949318660']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        //查询用户信息
        $user_id = $get_data['user_id'];
        $user_info = M("user")->get_one(["id" => $user_id]);
        if (!$user_info) {
            outJson(-1, '用户信息错误');
        }
        $user_bank_info = M('user_bank')->get_one(['user_id' => $user_id, 'type' => '宝付', 'is_binding' => '是']);
        if (!$user_bank_info) {
            outJson(-1, '用户未绑卡');
        }
        if ($get_data['amount'] < 1) {
            outJson(-1, '充值金额不能低于1元');
        }
        $limit_num = M('recharge')->get_total([['', 'user_id', '=', $user_id], ['AND', 'account', '<', 1000], ['AND', 'status', '=', '成功'],['AND', 'add_time', '>', date("Y-m-d 00:00:00", getRequestTime(1))]]);
        if ($get_data['amount'] < 1000 && $limit_num >= 2) {
            outJson(-2, '您今天低于1000元的充值机会已使用完毕，如需继续，单笔充值金额需大于1000元');
        }
        if (!isset($get_data['bind_id'])) {
            $get_data['bind_id'] = $user_bank_info['bind_id'];
        }
        //添加充值记录
        $recharge_model = M('recharge');
        $order_sn = getBFOrderId(); //宝付订单号
        $fee = 0; //收取手续费
        $bool_insert_recharge = $recharge_model->addRecharge($user_id, $get_data['amount'], $order_sn, $fee, '宝付', $user_bank_info['bank_code']);
        if (true !== $bool_insert_recharge) {
            outJson(-1, $bool_insert_recharge);
        }
        $get_data['next_txn_sub_type'] = '04';//下一步进行的交易子类为充值
        $quick_model = M('bf_quick_api');
        $get_data['order_sn'] = $order_sn;
        //金额以分为单位
        $get_data['amount'] *= 100;
        $return_msg = $quick_model->sendMsg($get_data, $user_id);
        if (is_array($return_msg)) {
            M('bf_recharge_sms')->update(['back_serial_no' => $return_msg['trans_serial_no'], 'return_json'=> json_encode($return_msg), 'back_resp_code' => $return_msg['resp_code'], 'back_resp_msg'=>$return_msg['resp_msg'], 'return_time'=> getRequestTime(), 'return_ip' => getIp()], ['order_sn' => $get_data['order_sn']]);
            if ('0000' === $return_msg['resp_code']) {
                outJson(0, ["resp_msg" => $return_msg['resp_msg'], "order_sn" => $order_sn]);
            }
            outJson(-1, $return_msg['resp_msg']);
        }
        M('bf_recharge_sms')->update(['back_resp_msg' => $return_msg, 'return_time'=> getRequestTime(), 'return_ip' => getIp()], ['order_sn' => $get_data['order_sn']]);
        outJson(-1, $return_msg);
    }

    /**
     * 宝付充值
     * @author cdf@yhxbank.com
     * @date  2016-06-15
     * @echo json
     */
    public function recharge()
    {
        //初始化信息
        $this->__init(-1, "宝付绑卡、充值等发送短信", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'       => [1, 'num', '用户id', 'user_id', 866],
            'amount'       => [1, 'num', "充值金额", 'amount', 10],
            'sms_code'      => [1, 'string', "短信验证码", 'sms_code', '201603'],
            'order_sn'      => [1, 'string', "订单号", 'order_sn', 'BF146605619089439392'],
            'bind_id'       => [0, 'num', "绑定标识号", 'bind_id', '201604271949318660']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        //查询用户信息
        $user_id = $get_data['user_id'];
        $user_info = M("user")->get_one(["id" => $user_id]);
        if (!$user_info) {
            outJson(-1, '用户信息错误');
        }
        $user_bank_info = M('user_bank')->get_one(['user_id' => $user_id, 'type' => '宝付', 'is_binding' => '是']);
        if (!$user_bank_info) {
            outJson(-1, '用户未绑卡');
        }
        if ($get_data['amount'] < 1) {
            outJson(-1, '充值金额不得小于1元');
        }
        $limit_num = M('recharge')->get_total([['', 'user_id', '=', $user_id], ['AND', 'account', '<', 1000],['AND', 'status', '=', '成功'],['AND', 'add_time', '>', date("Y-m-d 00:00:00", getRequestTime(1))]]);
        if ($get_data['amount'] < 1000 && $limit_num >= 2) {
            outJson(-2, '您今天低于1000元的充值机会已使用完毕，如需继续，单笔充值金额需大于1000元');
        }
        if (!isset($get_data['bind_id'])) {
            $get_data['bind_id'] = $user_bank_info['bind_id'];
        }
        $fee = 0; //充值手续费
        $recharge_model = M('recharge');
        $recharge_msg = $recharge_model->get_one(['order' => $get_data['order_sn']]);
        if (!$recharge_msg) {
            outJson(-1, '不存在此订单!');
        }
        if ('成功' === $recharge_msg['status']) {
            outJson(-1, '此订单已提交，同一订单不能重复使用!');
        }
        //更新充值状态
        if (!$recharge_model->update(['status' => "失败"], ['order' => $get_data['order_sn'], 'status' => '审核中'])) {
            outJson(-1, '此订单已失效!');
        }
        $get_data['amount'] *= 100; //金额以分为单位
        $quick_model = M('bf_quick_api');
        $return_msg = $quick_model->pay($get_data, $user_id);
        if (is_array($return_msg)) {
            //更新交易记录
            $up_data = [
                'back_status'     => "是", 'back_resp_code' => $return_msg['resp_code'], 'back_resp_desc' => $return_msg['resp_msg'],
                'back_json_value' => json_encode($return_msg), 'back_add_time' => getRequestTime(), 'back_ip' => getIp(), 'do_status' => '是'
            ];
            //返回参数是否有异常
            if ($get_data['order_sn'] != $return_msg['trans_id']) {
                $up_data['is_have_def'] = "是";
            }
            M('hf_trade_log')->update($up_data, ['order_id' => $get_data['order_sn'], 'do_status' => '否']);
            if ('0000' === $return_msg['resp_code']) {
                try {
                    $recharge_model->beginTransaction();
                    //更新充值状态
                    if (!$recharge_model->update(['status' => "成功", 'success_time' => getRequestTime(), 'return_order' => $return_msg['trans_id'], 'remark' => $return_msg['resp_msg']], ['order' => $get_data['order_sn'], 'status' => '失败'])) {
                        $recharge_model->rollback();
                        outJson(-1, '更新订单状态失败，请联系管理员！');
                    }
                    //更新用户资金记录状态和添加资金记录
                    $recharge_money = $get_data['amount'] / 100;
                    $real_money = $recharge_money - $fee;
                    $bool_recharge_money = M('new_account')->rechargeMoney($get_data['user_id'], $real_money, "Recharge", '充值成功', getIp(), $get_data['order_sn']);
                    if ($bool_recharge_money !== true) {
                        $recharge_model->rollback();
                        outJson(-1, '添加资金失败!');
                    }
                    $recharge_model->commit();
                    $curAccount = M('new_account')->get_one(['user_id' => $get_data['user_id']]);
                    //充值成功发送短信提醒
                    $param = serialize(['REAL_NAME' => $user_info['real_name'], 'TIME' => getRequestTime(), 'RECHARGE_MONEY' => $recharge_money, 'REAL_MONEY' => $real_money, 'USE_MONEY' => $curAccount['use_money']]);
                    M("sms")->smsAdd($user_info['phone'], 'bf_recharge_success', $param, '0');
                    $return["resp_msg"] = $return_msg["resp_msg"];
                    $return["order_sn"] = $get_data['order_sn'];
                    outJson(0, $return);
                }  catch (Exception $e) {
                    $recharge_model->rollback();
                    outJson(-1, '充值失败，请联系管理员！' . $e->getMessage());
                }
            } else {
                //更新充值状态
                $recharge_model->update([ 'return_order' => $return_msg['trans_id'], 'remark' => $return_msg['resp_msg']], ['order' => $get_data['order_sn'], 'status' => '失败']);
                outJson(-1, $return_msg['resp_msg']);
            }
        } else {
            $up_data = [
                 'back_status'     => "是",  'back_resp_desc' => $return_msg,
                 'back_add_time' => getRequestTime(), 'back_ip' => getIp(), 'do_status' => '是',
            ];
            M('hf_trade_log')->update($up_data, ['order_id' => $get_data['order_sn'], 'do_status' => '否']);
            outJson(-1, $return_msg);
        }
    }

    /**
     * 宝付实名认证
     * @author jiangxy@yhxbank.com
     * @date  2016-06-15
     * @return bool
     */
    public function verifyName()
    {
        //初始化信息
        $this->__init(-1, "验证实名认证", "成功返回成功，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', '用户id', 'user_id', 1002],
            'id_card' => [1, 'string', '身份证号码', 'id_card', '530325198609128316'],
            'id_holder' => [1, 'string', "姓名", 'id_holder', '测试'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_id = $get_data['user_id'];
        $user_info = M("user")->get_one(["id" => $user_id]);
        if (empty($get_data['id_holder'])) {
            outJson(-1, '用户名为空!');
        }
        if (!preg_match('/(^[0-9]{15}$)|(^[0-9]{18}$)|(^[0-9]{17}([0-9]|X|x)$)/', $get_data['id_card'])) {
            outJson(-1, '身份证号码不正确!');
        }
        /// 1. 是否已实名
        if (!empty($user_info['real_name']) && !empty($user_info['id_number'])) {
            outJson(-1, '已经实名过了');
        }
        /// 2. 实名次数是否超过2次
        if ($user_info['verify_times'] >= 2) {
            outJson(-1, '错误认证次数超出限额！');
        }
        /// 3.触发此方法 即增加一次验证次数
        $sql = "update y1_user set verify_times = verify_times + 1 where id = {$user_id}";
        M("user")->doExec($sql);
        /// 4. 查询身份证号是否重复，在验证错误次数之后验证
        $user_info_id_number = M('user')->get_one([
            'id_number' => $get_data['id_card'],
            ['AND', 'id', '<>', $user_id]
        ]);
        if($user_info_id_number){
            outJson(-1, '此身份证号已被注册！如需帮助，请联系客服。');
        }
        /// 4. 实名认证
        unset($get_data['user_id']);
        if (WHERE_SERVER == 'server') {
            $return_data = M('bf_verify_api')->verifyIdCard($get_data);
        } else {
            $return_data['resp_code'] = '0000';
        }
        if (is_array($return_data)) {
            if ($return_data['resp_code'] === '0000') {
                $up_data['real_name'] = $get_data['id_holder'];
                $up_data['id_number'] = $get_data['id_card'];
                $up_data['promoted_code'] = $this->getExtensionCode($user_id);
                $up_data['hf_reg_time'] = date("Y-m-d H:i:s"); // 新增实名时间
                $user_model = M('user');
                $user_model->update($up_data, ['id' => $user_id]);
                $wqh_user_model = M("wqh_user");
                $wqh_user_result=$wqh_user_model->get_one(array("user_id" => $user_id));
                if (array($wqh_user_result) && $wqh_user_result) {
                    $wqh_data["u_name"]     =   $get_data['id_holder'];
                    $wqh_data["id_number"]  =   $get_data['id_card'];
                    $wqh_user_model->update($wqh_data, ['id' => $wqh_user_result["id"]]);
                }
                $yqy_a_user_model = M("yqy_a_user");
                $yqy_user_result  = $yqy_a_user_model->get_one(array("user_id" => $user_id));
                if ($yqy_user_result && is_array($yqy_user_result)) {
                    $yqy_data["u_name"]     =   $get_data['id_holder'];
                    $yqy_data["id_number"]  =   $get_data['id_card'];
                    $yqy_a_user_model->update($yqy_data, ['id' => $yqy_user_result["id"]]);
                }
                outJson(0, '实名认证成功');
            } else {
                outJson(-1, $return_data['resp_msg']);
            }
        } else {
            outJson(-1, $return_data);
        }
    }

    /**
     * 宝付实名认证 是否超过2次提交
     * @author jiangxy@yhxbank.com
     * @date  2016-06-15
     * @return bool
     */
    public function verifyNameTime() {
        //初始化信息
        $this->__init(-1, "验证实名认证次数", "成功返回成功，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'       => [1, 'num', '用户id', 'user_id', 338],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_id = $get_data['user_id'];
        $user_info = M("user")->get_one(["id" => $user_id]);
        $verify_times = $user_info['verify_times'];
        if ($verify_times > 2) {
            outJson(-1, '验证次数已超过2次！');
        } else {
            outJson(0, '可以验证');
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

    /*
     * @author:yyh
     * @function 解绑用户银行卡
     **/
    public function unBindCard(){
        //初始化信息
        $this->__init(-1, "解绑银行卡", "成功返回成功，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'       => [1, 'num', '用户id', 'user_id', 338],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_id = $get_data['user_id'];
        $user_info=M('user')->get_one(['id'=>$user_id]);
        if(empty($user_info)){
            outJson(-1,'该用户不存在!');
        }
        $_m = M($this->m);
        $user_info =$_m->get_one(["user_id" => $user_id]);

        if(empty($user_info) || $user_info['is_binding']=='否'){
            outJson(-1,'该用户未绑卡');
        }
        $data['is_binding']='否';
        $condition['user_id']=$user_id;
        $unbind_status=$_m->update($data,$condition);

        if(!$unbind_status){
            outJson(-1,'解绑失败！');
        }
        outJson(0,'解绑成功!');
    }
}
