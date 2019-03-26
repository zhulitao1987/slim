<?php

/**
 * 用户提现表
 * User: PJK
 * Date: 2015/7/20
 */
class cashLib extends Lib
{

    /**
     * @author jxy
     * 用户添加提现
     */
    public function add()
    {
        $this->__init(-1, '添加用户提现', '成功返回提现id,失败返回错误记录');
        $this->postRule = [
            'user_id'      => [1, 'num', "用户编号", 'user_id', 7],
            'cash_order'   => [1, 'num', '用户提现订单号', 'cash_order', '20150309212515522783'],
            'cash_account' => [0, 'num', '用户提现金额', 'cash_account', 100.00],
            'bank_name'    => [0, 'string', '用户提现银行', 'bank_name', '建设银行'],
            'counter_fee'  => [0, 'num', '用户提现手续费', 'counter_fee', 0.00],
        ];
        parent::insertLib(['add_time' => getRequestTime(), 'add_ip' => getIp()]);
    }

    /**
     * 用户提现状态修改
     */
    public function update()
    {
        //初始化信息
        $this->__init(-1, "更新用户提现状态接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'id'      => [1, 'num', '提现编号', 'id', 4],
            'auditor' => [0, 'num', '审核人', 'auditor', 4],
            'status'  => [0, 'string', '用户提现状态', 'status', '受理中'],
            'remark'  => [0, 'string', '审核备注', 'remark', '未通过'],

        ];
        parent::updateLib([], ['id']);
    }

    /**
    * 查询单条用户提现信息
    */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户提现接口", "成功返回单条用户提现信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id' => [1, 'num', "提现记录ID", 'id', 2]
        ];
        //输出的参数
        $this->resRule = [
            'id'           => [1, 'num', "用户编号", 'id', 2],
            'user_id'      => [1, 'num', "用户编号", 'user_id', 7],
            'cash_order'   => [1, 'string', '用户提现订单号', 'cash_order', '20150309212515522783'],
            'cash_account' => [0, 'num', '用户提现金额', 'cash_account', 100.00],
            'bank_name'    => [0, 'string', '用户提现银行', 'bank_name', '建设银行'],
            'counter_fee'  => [0, 'num', '用户提现手续费', 'counter_fee', 0.00],
            'status'       => [0, 'string', '用户提现状态', 'status', '0'],
            'add_time'     => [0, 'string', '用户提现申请时间', 'add_time', '2015-03-03 03:03:03'],
            'add_ip'       => [0, 'string', '用户提现申请ip', 'add_ip', '192.168.1.1']
        ];
        parent::selectUserOneLib();
    }

    /**
     *用户提现列表查询
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户提现列表接口", "成功返回多条用户提现信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'   => [0, 'num', '用户id', 'user_id', 7],
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20],
            'status'    => [0, 'string', "状态", 'status', '申请中']
        ];
        //输出的参数
        $this->resRule = [
            'list'  => [
                1, 'array', '用户提现列表', 'list',
                [
                    'id'             => [1, 'num', "用户编号", 'id', 2],
                    'user_id'        => [1, 'num', "用户编号", 'user_id', 7],
                    'user_name'      => [0, 'num', "用户编号", 'user_name', 'aa'],
                    'real_name'      => [0, 'num', "用户编号", 'real_name', 'bb'],
                    'user_type'      => [0, 'string', "用户类型", 'user_type', '企业用户'],
                    'bank_card'      => [0, 'num', "银行卡号", 'bank_card', 'bank_card'],
                    'bank_name'      => [1, 'string', '用户提现银行', 'bank_name', '建设银行'],
                    'province_code'      => [1, 'string', '用户提现银行', 'province_code', '开户省'],
                    'city_code'      => [1, 'string', '用户提现银行', 'city_code', '开户市'],
                    'branch_name'      => [1, 'string', '用户提现银行', 'branch_name', '开户支行名称'],
                    'remark'      => [1, 'string', '用户提现银行', 'remark', '备注'],
                    'cash_order'     => [1, 'string', '用户提现订单号', 'cash_order', '20150309212515522783'],
                    'cash_account'   => [1, 'num', '用户提现金额', 'cash_account', 100.00],
                    'counter_fee'    => [1, 'num', '用户提现手续费', 'counter_fee', 0.00],
                    'use_money'    => [1, 'num', '当前账户余额', 'use_money', 0.00],
                    'show_use_money'    => [1, 'num', '当前账户余额', 'show_use_money', 0.00],
                    'real_cash_account'    => [1, 'num', '用户实际提现金额', 'real_cash_account', 0.00],
                    'management_fee' => [0, 'num', '用户提现管理费', 'management_fee', 0.00],
                    'status'         => [1, 'string', '用户提现状态', 'status', '提现中'],
                    'add_time'       => [1, 'string', '用户提现申请时间', 'add_time', '2015-03-03 03:03:03'],
                    'add_ip'         => [1, 'string', '用户提现申请ip', 'add_ip', '192.168.1.1'],
                    'warning'        => [1, 'string', '是否警告', 'warning', '是']
                ]
            ],
            'total' => [1, 'num', '提现数据总额', 'total', 50]
        ];
        parent::selectUserListLib('id desc');
    }

    /* 请不要删！！！！！！！！！！！！！！！！！！！！！！
    * 会员资料:用户提现列表查询
     * author:pjk
     * date:2015-07-15
     * email:peijk@yhxbank.com
    */
    public function selectCashList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户提现列表接口", "成功返回多条用户提现信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'      => [0, 'num', '用户id', 'user_id', 1],
            'page'         => [1, 'num', "页码", 'page', 0],
            'page_size'    => [1, 'num', "显示条数", 'page_size', 20],
            'cash_order'   => [0, 'string', '用户提现订单号', 'cash_order', '20150309212515522783'],
            'cash_account' => [0, 'num', '用户提现金额', 'cash_account', 100.00],
            'bank_name'    => [0, 'string', '用户提现银行', 'bank_name', '建设银行'],
            'counter_fee'  => [0, 'num', '用户提现手续费', 'counter_fee', 0.00],
            'status'       => [0, 'string', '用户提现状态', 'status', '1'],
            'add_time'     => [0, 'string', '用户提现申请时间', 'add_time', '2015-03-03 03:03:03'],
            'add_ip'       => [0, 'string', '用户提现申请ip', 'add_ip', '192.168.1.1']
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '用户提现列表', 'list',
                [
                    'id'             => [1, 'num', "用户编号", 'id', 2],
                    'user_id'        => [1, 'num', "用户编号", 'user_id', 7],
                    'cash_order'     => [1, 'string', '用户提现订单号', 'cash_order', '20150309212515522783'],
                    'cash_account'   => [0, 'num', '用户提现金额', 'cash_account', 100.00],
                    'bank_name'      => [0, 'string', '用户提现银行', 'bank_name', '建设银行'],
                    'counter_fee'    => [0, 'num', '用户提现手续费', 'counter_fee', 0.00],
                    'management_fee' => [0, 'num', '用户提现管理费', 'management_fee', 0.00],
                    'status'         => [0, 'string', '用户提现状态', 'status', '0'],
                    'add_time'       => [0, 'string', '用户提现申请时间', 'add_time', '2015-03-03 03:03:03'],
                    'add_ip'         => [0, 'string', '用户提现申请ip', 'add_ip', '192.168.1.1'],
                    'real_name'      => [0, 'string', '用户名', 'real_name', 'xiaolizi']
                ]
            ]
        ];
        parent::selectUserListLib();
    }

    /**
     * pc端提现记录
     */
    public function cashLogList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户提现列表接口", "成功返回多条用户提现信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [0, 'num', '用户id', 'user_id', 1],
            'page'       => [1, 'num', "页码", 'page', 0],
            'page_size'  => [1, 'num', "显示条数", 'page_size', 20],
            'status'     => [0, 'string', '用户提现状态', 'status', '实际提现成功'],
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
        if (isset($get_data['status'])) {
            if ('' == $get_data['status']) {
                unset($where['status']);
            } elseif ('成功' == $get_data['status']) {
                $where['status'] = ['and', [
                    ['', 'status', '=', '提现成功'],
                    ['or', 'status', '=', '实际提现成功']
                ]];
            } elseif ('审核中' == $get_data['status']) {
                $where['status'] = ['and', [
                    ['', 'status', '=', '申请中'],
                    ['or', 'status', '=', '已受理'],
                    ['or', 'status', '=', '打款中'],
                    ['or', 'status', '=', '提现中'],
                ]];
            } else {
                $where['status'] = $get_data['status'];
            }
        }
        if (isset($get_data['start_time']))
            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        $list = $_m->get_list($where, 'id DESC', $page, $page_size);
        $total = $_m->get_total([], 1);
        if ($list) {
            $list = ['list' => $list, 'total' => $total];
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /**
     * 获取提现成功金额和提现失败金额
     */
    public function selectCashInfo()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户充值列表接口", "成功返回多条用户充值信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [0, 'num', '用户id', 'user_id', 863],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-01-01 00:00:00'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2015-12-30 23:59:59'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $cash_model = M('cash');
        $where = array(
            'user_id' => $get_data['user_id']
        );
        $where['status'] = ['and', [
            ['', 'status', '=', '提现成功'],
            ['or', 'status', '=', '实际提现成功']
        ]];
        if (isset($get_data['start_time']))
            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        $success_recharge = $cash_model->get_one($where, '', 'sum(cash_account) as successAccount');


        $where_false = array(
            'user_id' => $get_data['user_id']
        );
        $where_false['status'] = ['and', [
            ['', 'status', '=', '实际提现失败'],
            ['or', 'status', '=', '提现失败']
        ]];
        if (isset($get_data['start_time']))
            $where_false[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where_false[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        $false_recharge = $cash_model->get_one($where_false, '', 'sum(cash_account) as falseAccount');
        if ($success_recharge['successAccount'] >= 0 && $false_recharge['falseAccount'] >= 0)
            outJson(0, array('successAcc' => $success_recharge['successAccount'], 'falseAcc' => $false_recharge['falseAccount']));
        outJson(-1, '没有数据');
    }

    /** 请不要删！！！！！！！！！！！！！！！！！！！！！！
    * 会员资料:用户提现开始
     * author:pjk
     * date:2015-07-18
     * email:peijk@yhxbank.com
    */
    public function toCash()
    {
        //初始化信息
        $this->__init(-1, "用户充值接口", "成功跳转汇付，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'      => [1, 'num', '用户id', 'user_id', 1],
            'cash_account' => [0, 'num', '用户提现金额', 'cash_account', 1000.00],
            'bank_name'    => [0, 'string', '用户提现状态', 'bank_name', 'ABC'],
            'open_acct_id' => [0, 'string', '用户提现申请时间', 'open_acct_id', '601382080009316977'],
            'mer_priv'     => [0, 'string', '商户私有域', 'mer_priv', 'wap']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_account_model = M('account');
        $user_cash_model = M('cash');
        $user_id = trim($get_data['user_id']);//7
        $cash_account = trim($get_data['cash_account']);//350
        $bank_name = trim($get_data['bank_name']);
        $open_acct_id = trim($get_data['open_acct_id']);//"601382080009316977"
        if ($cash_account < 2) {
            outJson(-1, "提现金额不得低于2元");
        }
        //判断提取金额是否足够可以被提取

        $zero_bool = $user_cash_model->boolAccountEnough($user_id, $cash_account);
//        outJson(-1, $cash_account);
        //足够提现
        if ($zero_bool) {
            $counter_fee = $user_cash_model->getCounterFee($user_id);//手续费
            $management_fee = $user_cash_model->getManagementFee($user_id, $cash_account);//管理费
            $order_id = getOrderId();
            try {
                $user_cash_model->beginTransaction();
                $which_money = $user_account_model->whichMoney($user_id, $cash_account, 2);//获取冻结可用金额【提现冻结，投资冻结】
                $freeze_recharge_money = $which_money['recharge_money'];//冻结的充值金额
                $freeze_repayment_money = $which_money['repayment_money'];//冻结的还款金额
                $bool_insert_cash = $user_cash_model->addCash($user_id, $order_id, $cash_account, $bank_name, $counter_fee, $management_fee, $freeze_recharge_money, $freeze_repayment_money);//添加提现记录
                //判断提交提现记录是否成功
                if (true === $bool_insert_cash) {
                    //判断冻结资金是否成功
//                    $bool_look_money = $user_account_model->lockMoney($user_id, $freeze_recharge_money, $freeze_repayment_money, 'Cash', '提现金额:' . $cash_account, getIp(), $order_id);//冻结用户资金
//                    if ($bool_look_money > 0) {
                    $fee = round($management_fee + $counter_fee, 2);
                    $fee = sprintf('%.2f', $fee);
                    if (!strpos($fee, '.'))
                        $fee = $fee . '.00';
                    isset($get_data['mer_priv']) ? $mer_priv = $get_data['mer_priv'] : $mer_priv = '';
                    $res = $user_cash_model->toHfCash($user_id, $cash_account, $fee, $order_id, $open_acct_id, $mer_priv);//访问汇付取现api
                    if ($res) {
                        $user_cash_model->commit();
                        outJson(0, $res);
                    } else {
                        $user_cash_model->rollback();
                        outJson(-1, '冻结资金失败!原因:跳转汇付失败');
                    }
//                    } else {
//                        $user_cash_model->rollback();
//                        outJson(-1, '冻结资金失败!原因:' . $bool_look_money);
//                    }
                } else {
                    $user_cash_model->rollback();
                    outJson(-1, '提交提现记录失败!原因:' . $bool_insert_cash);
                }
            } catch (Exception $e) {
                $user_cash_model->rollback();
                outJson(-1, $e->getMessage());
            }
        } else {
            outJson(-1, '由于用户金额不足,无法提现');
        }
    }

    /**
     * 查询投资列表统计（时间搜索）
     */
    public function selectCashListByTime()
    {

        //初始化信息
        $this->__init(-1, "查询多条投资信息接口", "成功返回多条投资信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'start_time' => [0, 'string', '搜索开始时间', 'start_time', '2015-03-03 03:03:03'],
            'end_time'   => [0, 'string', '搜索结束时间', 'end_time', '2015-07-30 23:03:03'],
            'status'     => [0, 'string', '状态', 'status', '实际提现成功'],
            'type'       => [0, 'string', '状态', 'type', '汇付'],
            'page'       => [1, 'num', "页码", 'page', 0],
            'page_size'  => [1, 'num', "显示条数", 'page_size', 20]
        ];

        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M('cash');

        if (!isset($get_data['start_time']) && !isset($get_data['end_time'])) {
            $get_data['start_time'] = date('Y-m-d');
            $get_data['end_time'] = date('Y-m-d');
        }
        $where = [];
        if (isset($get_data['start_time'])) {
            $where[] = ['', 'add_time', '>=', $get_data['start_time'] . ' 00:00:00'];
        }
        if (isset($get_data['end_time'])) {
            $where[] = ['and', 'add_time', '<=', $get_data['end_time'] . ' 23:59:59'];
        }
        if (isset($get_data['status']) && $get_data['status'] != '') {
            $where[] = ['and', 'status', '=', $get_data['status']];
        }
        if (isset($get_data['type'])) {
            $where[] = ['and', 'type', '=', $get_data['type']];
        }
        $res = $_m->get_list($where, 'id DESC', $get_data['page'], $get_data['page_size'], '', '');

        $user_model = M('user');
        $bank_model = M('user_bank');

        foreach ($res as $key => $value) {
            if (isset($value['user_id'])) {
                $uid = intval($value['user_id']);
                $info = $user_model->getUserInfo($uid);
                $res[$key]['id_number'] = $info['id_number'];
                $res[$key]['real_name'] = $info['real_name'];
                $res[$key]['user_id'] = $value['user_id'];

                $bank = $bank_model->get_one(array('user_id' => $uid));
                $res[$key]['bank_card'] = $bank['bank_card'];
                $res[$key]['province'] = $bank['province'];
                $res[$key]['city'] = $bank['city'];
                $res[$key]['branch_name'] = $bank['branch_name'];
                $res[$key]['local_bank_id'] = $bank['local_bank_id'];
                $res[$key]['bank_num'] = $bank['bank_num'];
            }
        }

        $money_sum = $_m->get_one($where, '', 'sum(cash_account) as money_sum,count(*) as total');

        $return = array(
            'list'      => $res,
            'money_sum' => $money_sum['money_sum'],
            'total'     => $money_sum['total']
        );
        outJson(0, $return);
    }

    /**
 * 用户提现方法
 */
    public function toNewCash()
    {
        //初始化信息
        $this->__init(-1, "用户提现接口", "成功返回成功信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'      => [1, 'num', '用户id', 'user_id', 1],
            'cash_account' => [1, 'num', '用户提现金额', 'cash_account', 1000.00]
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_account_model = M('new_account');
        $user_cash_model = M('cash');
        $user_id = trim($get_data['user_id']);//7
        $cash_account = trim($get_data['cash_account']);//350
        if ($cash_account < 2) {
            outJson(-1, "提现金额不能小于2元");
        }
        //查询用户信息
        $user_info = M("user")->get_one(["id" => $user_id]);
        /// 查询绑卡信息
        $user_bank_info = M("user_bank")->get_one(["user_id" => $user_id, 'type'=> '宝付'] );
        if (!$user_info) {
            outJson(-1, '用户信息错误');
        }

        /// 判断是否绑定宝付
        if(!is_array($user_bank_info) || $user_bank_info['is_binding'] == '否'){
            outJson(-1, '请完成版本升级再进行提现');
        }

        if ($user_info['type'] == '虚拟账户') {
            outJson(-1, '虚拟账户禁止提现');
        }
        //判断提取金额是否足够可以被提取
        $which_money = $user_account_model->whichMoney($user_id, $cash_account, 2);//获取冻结可用金额【提现冻结，投资冻结】
        if (!is_array($which_money)) {
            outJson(-1, $which_money);
        }
        $recharge_money = $which_money['recharge_money'];//冻结的充值金额
        $repayment_money = $which_money['repayment_money'];//冻结的还款金额
        //足够提现
        $counter_fee = 0;//手续费
        $management_fee = $user_cash_model->getNewManagementFee($recharge_money);//管理费
        $order_id = getOrderId();
        try {
            $user_cash_model->beginTransaction();
            //提现记录添加失败
            if (!$user_cash_model->addNewCash($user_id, $order_id, $cash_account, $counter_fee, $management_fee, $recharge_money, $repayment_money)) {
                $user_cash_model->rollback();
                outJson(-1, '提交失败，请联系客服！');
            }
            //提现冻结资金失败
            if ($user_account_model->lockMoney($user_id, $recharge_money, $repayment_money, 'Cash', '提现冻结', '', $order_id) !== true) {
                $user_cash_model->rollback();
                outJson(-1, '资金冻结失败，请联系客服！');
            }
            //提现成功
            $user_cash_model->commit();
            outJson(0, '提交成功，请等待审核！');
        } catch (Exception $e) {
            $user_cash_model->rollback();
            outJson(-1, '提交失败，请联系管理员！' . $e->getMessage());
        }
    }

    /**
     * 审核提现接口
     */
    public function auditCash()
    {
        //初始化信息
        $this->__init(-1, "审核提现接口", "成功返回成功信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id'         => [1, 'num', '提现ID', 'id', 1],
            'status'     => [1, 'string', '变更的状态', 'status', '已拒绝'],
            'auditor'    => [1, 'num', '操作人ID', 'auditor', 1],
            'audit_type' => [1, 'string', '操作人类型', 'audit_type', '用户']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        try {
            $_m->beginTransaction();
//            '提现失败','提现成功','用户取消','已拒绝','已受理','申请中','打款中'
            $id = $get_data['id'];
            $status = $get_data['status'];
            $auditor = $get_data['auditor'];
            $audit_type = $get_data['audit_type'];
            $cash_model = M("cash");
            //获取提现数据
            $cash_info = $cash_model->get_one(['id' => $id]);
            if (!$cash_info) {
                outJson(-1, '错误的信息！');
            }
            $new_account_model = M('new_account');
            $cash_audit_model = M("cash_audit");
            $can_do = false;
            switch ($audit_type) {
                case '用户':
                    if ($status != '用户取消') {
                        outJson(-1, '错误的状态类型！');
                    }
                    if (!$cash_model->update(['status' => $status], ['id' => $id, 'user_id' => $auditor, 'status' => '申请中'])) {
                        outJson(-1, '取消失败，状态已变更！');
                    }
                    if ($new_account_model->unLockMoney($cash_info['user_id'], $cash_info['recharge_money'], $cash_info['repayment_money'],
                            $cash_info['frozen_money'], "Cash", "提现取消", '', $cash_info['cash_order']) !== true
                    ) {
                        $_m->rollback();
                        outJson(-1, '取消失败，请联系客服！');
                    }
                    break;
                case '管理员':
                    if ($status != '已受理' && $status != '已拒绝' && $status != '打款中') {
                        outJson(-1, '错误的状态类型！');
                    }
                    if ($status == '已受理') {
                        if (!$cash_model->update(['status' => $status], ['id' => $id, 'status' => '申请中'])) {
                            outJson(-1, '状态变更失败！');
                        }
                    }
                    if ($status == '已拒绝') {
                        if ($cash_info['status'] != '申请中' && $cash_info['status'] != '已受理') {
                            outJson(-1, '状态不可变更！');
                        }
                        if (!$cash_model->update(['status' => $status], ['id' => $id, 'status' => $cash_info['status']])) {
                            outJson(-1, '状态变更失败！');
                        }
                        if ($new_account_model->unLockMoney($cash_info['user_id'], $cash_info['recharge_money'], $cash_info['repayment_money'],
                                $cash_info['frozen_money'], "Cash", "提现拒绝", '', $cash_info['cash_order']) !== true
                        ) {
                            $_m->rollback();
                            outJson(-1, '拒绝失败，请联系管理员！');
                        }
                    }
                    if ($status == '打款中') {
                        $can_do = true;
                        if (!$cash_model->update(['status' => $status], ['id' => $id, 'status' => "已受理"])) {
                            outJson(-1, '状态变更失败！');
                        }
                    }
                    break;
                default:
                    outJson(-1, '没有此类型！');
                    break;
            }
            $cash_audit_model->insert([
                'cash_id'     => $cash_info['id'],
                'user_id'     => $auditor,
                'user_type'   => $audit_type,
                'add_time'    => getRequestTime(),
                'from_status' => $cash_info['status'],
                'to_status'   => $status
            ]);
            if($cash_info['cash_account'] == 2 && $audit_type == '管理员' && $status == '已受理'){
                $this->to_cash_success($id, $auditor, $audit_type);
            }
            //提现成功
            $_m->commit();
            if ($can_do) {
                $cash_msg = M("yl_api")->ylPay($cash_info['user_id'], $cash_info['cash_order'],
                    ($cash_info['cash_account'] - $cash_info['counter_fee'] - $cash_info['management_fee']));
                if ($cash_msg === false) {
                    $cash_model->update(['status' => "已受理"], ['id' => $id, 'status' => "打款中"]);
                    outJson(-1, "代付申请失败");
                } elseif ($cash_msg !== true) {
                    $cash_model->update(['status' => "已受理"], ['id' => $id, 'status' => "打款中"]);
                    outJson(-1, $cash_msg);
                }
            }
            outJson(0, '处理成功');
        } catch (Exception $e) {
            $_m->rollback();
            outJson(-1, '状态修改失败，请联系管理员！' . $e->getMessage());
        }
    }

    /**
     * 修改状态（多id）
     */
    public function auditCashByIds()
    {
        //初始化信息
        $this->__init(-1, "审核提现接口", "成功返回成功信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'ids'         => [1, 'string', 'ID', 'ids', 1],
            //'order_ids'   => [1, 'string', '订单ID', 'order_ids', 1],
            'status'     => [1, 'string', '变更的状态', 'status', '打款中'],
            'auditor'    => [1, 'num', '操作人ID', 'auditor', 1],
            'audit_type' => [1, 'string', '操作人类型', 'audit_type', '管理员']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $ids = array();
        if($get_data['ids']){
            $ids = explode(",", $get_data['ids']);
            $ids = array_filter($ids);
        }/*else if($get_data['order_ids']){
            $order_ids = explode(",", $get_data['order_ids']);
            $order_ids = array_filter($order_ids);
            $cash_model = M("cash");
            $cash_ids = $cash_model->queryAll('select id from ');
        }*/else{
            outJson(-1, '错误的信息！');
        }
        $_m = M($this->m);
        try {
            $_m->beginTransaction();
//            '提现失败','提现成功','用户取消','已拒绝','已受理','申请中','打款中'
            //$id = $get_data['id'];


            foreach($ids as $id) {
                $cash_model = M("cash");
                $new_account_model = M('new_account');
                $cash_audit_model = M("cash_audit");

                $status = $get_data['status'];
                $auditor = $get_data['auditor'];
                $audit_type = $get_data['audit_type'];
                //获取提现数据
                $cash_info = $cash_model->get_one(['id' => $id]);
                if (!$cash_info) {
                    outJson(-1, '错误的信息！没有找到订单号!');
                }
                $can_do = false;
                switch ($audit_type) {
                    case '用户':
                        if ($status != '用户取消') {
                            outJson(-1, '错误的状态类型！');
                        }
                        if (!$cash_model->update(['status' => $status], ['id' => $id, 'user_id' => $auditor, 'status' => '申请中'])) {
                            outJson(-1, '取消失败，状态已变更！');
                        }
                        if ($new_account_model->unLockMoney($cash_info['user_id'], $cash_info['recharge_money'], $cash_info['repayment_money'],
                                $cash_info['frozen_money'], "Cash", "提现取消", '', $cash_info['cash_order']) !== true
                        ) {
                            $_m->rollback();
                            outJson(-1, '取消失败，请联系客服！');
                        }
                        break;
                    case '管理员':
                        if ($status != '已受理' && $status != '已拒绝' && $status != '打款中') {
                            outJson(-1, '错误的状态类型！');
                        }
                        if ($status == '已受理') {
                            if (!$cash_model->update(['status' => $status], ['id' => $id, 'status' => '申请中'])) {
                                outJson(-1, '状态变更失败！');
                            }
                        }
                        if ($status == '已拒绝') {
                            if ($cash_info['status'] != '申请中' && $cash_info['status'] != '已受理') {
                                outJson(-1, '状态不可变更！');
                            }
                            if (!$cash_model->update(['status' => $status], ['id' => $id, 'status' => $cash_info['status']])) {
                                outJson(-1, '状态变更失败！');
                            }
                            if ($new_account_model->unLockMoney($cash_info['user_id'], $cash_info['recharge_money'], $cash_info['repayment_money'],
                                    $cash_info['frozen_money'], "Cash", "提现拒绝", '', $cash_info['cash_order']) !== true
                            ) {
                                $_m->rollback();
                                outJson(-1, '拒绝失败，请联系管理员！');
                            }
                        }
                        if ($status == '打款中') {
                            $can_do = false;
                            if (!$cash_model->update(['status' => $status], ['id' => $id, 'status' => "已受理"])) {
                                outJson(-1, '状态变更失败！');
                            }
                        }
                        break;
                    default:
                        outJson(-1, '没有此类型！');
                        break;
                }
                $cash_audit_model->insert([
                    'cash_id' => $cash_info['id'],
                    'user_id' => $auditor,
                    'user_type' => $audit_type,
                    'add_time' => getRequestTime(),
                    'from_status' => $cash_info['status'],
                    'to_status' => $status
                ]);
            }
            //提现成功
            $_m->commit();
            if ($can_do) {
                $cash_msg = M("yl_api")->ylPay($cash_info['user_id'], $cash_info['cash_order'],
                    ($cash_info['cash_account'] - $cash_info['counter_fee'] - $cash_info['management_fee']));
                if ($cash_msg === false) {
                    $cash_model->update(['status' => "已受理"], ['id' => $id, 'status' => "打款中"]);
                    outJson(-1, "代付申请失败");
                } elseif ($cash_msg !== true) {
                    $cash_model->update(['status' => "已受理"], ['id' => $id, 'status' => "打款中"]);
                    outJson(-1, $cash_msg);
                }
            }
            outJson(0, '处理成功');
        } catch (Exception $e) {
            $_m->rollback();
            outJson(-1, '状态修改失败，请联系管理员！' . $e->getMessage());
        }
    }

    /**
     * 宝付导入excel处理
     * @author: jingjq@yhxbank.com
     * @date: 2016-07-20
     */
    public function auditCashForImportExecl()
    {
        //初始化信息
        $this->__init(-1, "审核提现接口", "成功返回成功信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'datas'   => [1, 'string', '订单ID', 'datas', ''],
            //'status'     => [0, 'string', '变更的状态', 'status', '申请中'],
            'auditor'    => [1, 'num', '操作人ID', 'auditor', 1],
            'audit_type' => [1, 'string', '操作人类型', 'audit_type', '管理员']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $jsons = $get_data['datas'];
        $order_paras_all = is_string($jsons) ? unserialize(html_entity_decode($jsons)) : '';

        $order_paras = [];
        foreach ($order_paras_all as $order){
            if($order_paras[$order['businessOrderNo']]){
                if($order_paras[$order['businessOrderNo']]['baofuOrderNo'] == $order['baofuOrderNo']){
                    $order_paras[$order['businessOrderNo']]['is_error'] = '是';
                }else {
                    $order_paras[$order['businessOrderNo']]['orderMoney'] += $order['orderMoney'];
                    $order_paras[$order['businessOrderNo']]['fee'] += $order['fee'];
                }
            }else{
                $order_paras[$order['businessOrderNo']] = $order;
            }
        }
        $_m = M($this->m);
        try {
            $_m->beginTransaction();
//            '提现失败','提现成功','用户取消','已拒绝','已受理','申请中','打款中'
            //$id = $get_data['id'];

            foreach($order_paras as $order){
                $cash_model = M("cash");
                $new_account_model = M('new_account');
                $cash_audit_model = M("cash_audit");
                $cash_import_log_model = M("cash_import_log");
                $cash_import_exception_model = M("cash_import_exception");
                $user_model = M("user");

                $cash_order = $order['businessOrderNo'];
                $status = $order['orderStatus'];
                $auditor = $get_data['auditor'];
                $audit_type = $get_data['audit_type'];
                if($status == '成功'){
                    $status = '提现成功';
                }else if($status == '失败'){
                    $status = '提现失败';
                }else{
                    $status = '提现中';
                }
                //获取提现数据
                $cash_info = $cash_model->get_one(['cash_order' => $cash_order]);
                if (!$cash_info) {
                    outJson(-1, '错误的信息！找不到订单号!');
                }
                if ($cash_info['status'] == '提现成功') {
                    outJson(-1, $cash_order . '此订单已经提现成功!');
                }
                //查询用户手机号
                $user_info = $user_model->get_one(['id' => $cash_info['user_id']]);
                if (!$user_info) {
                    outJson(-1, '错误的用户信息！');
                }
                //异常数据处理
                if($status ==  '提现中'){
                    continue;
                }
                //$cash_import_log_info = $cash_import_log_model->get_one(['cash_order' => $cash_order]);
                //if($cash_import_log_info){
                if($order_paras[$order['businessOrderNo']]['is_error'] == '是' || bccomp($cash_info['cash_account'], $order['orderMoney'] + $cash_info['management_fee'], 4) != 0 ) {
                    foreach ($order_paras_all as $order_one) {
                        if ($order_one['businessOrderNo'] == $order['businessOrderNo']) {
                            //插入已成功数据
                            /*$cash_import_exception_model->insert([
                                'add_time' => $cash_import_log_info['add_time'],
                                'user_name' => $cash_import_log_info['user_name'],
                                'user_mobile' => $user_info['phone'],
                                'bank_code' => $cash_import_log_info['bank_code'],
                                'bank_name' => $cash_import_log_info['bank_name'],
                                'money' => $cash_import_log_info['money'],
                                'counter_fee' => $cash_import_log_info['counter_fee'],
                                'real_money' => $cash_import_log_info['money'],
                                'cash_order' => $cash_import_log_info['cash_order'],
                                'add_ip' => getIp(),
                                'status' => $cash_import_log_info['status'],
                            ]);*/
                            $cash_import_exception_model->insert([
                                'add_time' => $order_one['createTime'],
                                'user_name' => $order_one['payeeName'],
                                'user_mobile' => $user_info['phone'],
                                'bank_code' => $order_one['payeeBankNo'],
                                'bank_name' => $order_one['payeeBankName'],
                                'money' => $order_one['orderMoney'],
                                'counter_fee' => $order_one['fee'],
                                'real_money' => $order_one['orderMoney'],
                                'cash_order' => $order_one['businessOrderNo'],
                                'add_ip' => getIp(),
                                'status' => $order_one['orderStatus'],
                                'record_add_time' => getRequestTime()
                            ]);
                            //导入日志
                            $cash_import_log_model->insert([
                                'baofoo_id' => $order_one['baofuOrderNo'],
                                'cash_order' => $order_one['businessOrderNo'],
                                'batch_id' => $order_one['batchNo'],
                                'add_time' => $order_one['createTime'],
                                'return_time' => $order_one['transTime'],
                                'do_type' => $order_one['orderType'],
                                'user_name' => $order_one['payeeName'],
                                'bank_code' => $order_one['payeeBankNo'],
                                'bank_name' => $order_one['payeeBankName'],
                                'baofu_remark' => $order_one['baofuRemark'],
                                'status' => $order_one['orderStatus'],
                                'money' => $order_one['orderMoney'],
                                'counter_fee' => $order_one['fee'],
                                'shanghu_remark' => $order_one['payeeRemark'],
                                'refund_time' => $order_one['reTime'],
                                'record_add_time' => getRequestTime()
                            ]);
                        }
                    }
                    continue;
                }

                switch ($audit_type) {
                    case '用户':
                        outJson(-1, '错误的用户类型');
                        break;
                    case '管理员':
                        if ($status != '提现成功' && $status != '提现失败' && $status != '提现中') {
                            outJson(-1, '错误的状态类型！');
                        }
                        if (!$cash_model->update(['status' => $status, 'return_time' => getRequestTime()], ['cash_order' => $cash_order, 'status' => "打款中"])) {
                            $_m->rollback();
                            outJson(-1, '状态更改失败！');
                        }
                        if($status == '提现成功'){
                            $res = $new_account_model->subLockMoney($cash_info['user_id'], $order['orderMoney'] + $cash_info['management_fee'],
                                'Cash', '提现成功', getIp(), $cash_info['cash_order']);//$order['payeeRemark']=>提现成功
                            if($res !== true)
                            {
                                $_m->rollback();
                                outJson(-1, $res);
                                //outJson(-1, '提现出错！');
                            }
                        }
                        if($status == '提现失败'){
                            $res = $new_account_model->unLockMoney($cash_info['user_id'], $cash_info['recharge_money'], $cash_info['repayment_money'],
                                $cash_info['frozen_money'], "Cash", "提现失败", getIp(), $cash_info['cash_order']);
                            if ($res !== true) {
                                $_m->rollback();
                                outJson(-1, '拒绝失败，请联系管理员！'.$res);
                            }
                        }
                        break;
                    default:
                        outJson(-1, '没有此类型！');
                        break;
                }
                $cash_audit_model->insert([
                    'cash_id' => $cash_info['id'],
                    'user_id' => $auditor,
                    'user_type' => $audit_type,
                    'add_time' => getRequestTime(),
                    'from_status' => $cash_info['status'],
                    'to_status' => $status
                ]);
                //导入日志
                foreach ($order_paras_all as $order_one) {
                    if($order_one['businessOrderNo'] == $order['businessOrderNo']) {
                        $cash_import_log_model->insert([
                            'baofoo_id' => $order_one['baofuOrderNo'],
                            'cash_order' => $order_one['businessOrderNo'],
                            'batch_id' => $order_one['batchNo'],
                            'add_time' => $order_one['createTime'],
                            'return_time' => $order_one['transTime'],
                            'do_type' => $order_one['orderType'],
                            'user_name' => $order_one['payeeName'],
                            'bank_code' => $order_one['payeeBankNo'],
                            'bank_name' => $order_one['payeeBankName'],
                            'baofu_remark' => $order_one['baofuRemark'],
                            'status' => $order_one['orderStatus'],
                            'money' => $order_one['orderMoney'],
                            'counter_fee' => $order_one['fee'],
                            'shanghu_remark' => $order_one['payeeRemark'],
                            'refund_time' => $order_one['reTime'],
                            'record_add_time' => getRequestTime()
                        ]);
                    }
                }
            }
            //提现成功
            $_m->commit();
            outJson(0, '处理成功');
        } catch (Exception $e) {
            $_m->rollback();
            outJson(-1, '状态修改失败，请联系管理员！' . $e->getMessage());
        }
    }

    public function to_cash_success($cash_id, $auditor, $audit_type){
        $cash_model = M("cash");
        $user_model = M("user");
        $cash_audit_model = M("cash_audit");
        $new_account_model = M('new_account');
        $status = '提现成功';
        $cash_info = $cash_model->get_one(['id' => $cash_id]);
        if ($cash_info['status'] != '已受理') {
            outJson(-1, $cash_info['cash_order'] . '此订单不允许更改状态为提现成功!');
        }
        if ($cash_info['status'] == '提现成功') {
            outJson(-1, $cash_info['cash_order'] . '此订单已经提现成功!');
        }

        $user_info = $user_model->get_one(['id' => $cash_info['user_id']]);
        if (!$user_info) {
            return '错误的用户信息！';
        }
        try {
            $cash_model->beginTransaction();
            if (!$cash_model->update(['status' => $status, 'return_time' => getRequestTime()], ['cash_order' => $cash_info['cash_order'], 'status' => "已受理"])) {
                $cash_model->rollback();
                return '状态更改失败！';
            }
            $res = $new_account_model->subLockMoney($cash_info['user_id'], $cash_info['cash_account'],
                'Cash', '提现成功', getIp(), $cash_info['cash_order']);
            if ($res !== true) {
                $cash_model->rollback();
                return false;
            }

            $cash_audit_model->insert([
                'cash_id' => $cash_info['id'],
                'user_id' => $auditor,
                'user_type' => $audit_type,
                'add_time' => getRequestTime(),
                'from_status' => $cash_info['status'],
                'to_status' => $status
            ]);
            $cash_model->commit();
            return true;
        }catch (Exception $e){
            $cash_model->rollback();
            return false;
        }
        return true;
    }


}
