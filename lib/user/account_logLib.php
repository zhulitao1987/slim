<?php

/**
 * 用户普通日志表
 * User: MZH
 * Date: 15/7/10
 * Time: 上午10:53
 */
class account_logLib extends Lib
{
    const yyy_days = 32;

    /**
     * @author jxy
     * 用户多条资金日志查询
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条资金日志接口", "成功返回多条用户资金日志信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [0, 'num', "用户id", 'user_id', 7],
            'type'       => [0, 'string', "资金操作类型", 'type', 'Cash'],
            'page'       => [1, 'num', "页码", 'page', 0],
            'page_size'  => [1, 'num', "显示条数", 'page_size', 20],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-05-05 05:05:05'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2015-05-05 05:05:05']
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
        if (isset($get_data['start_time']))
            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        if (isset($get_data['type']))
            $where[] = ['AND', 'type', '=', $get_data['type']];
        $list = $_m->get_list($where, 'id DESC', $page, $page_size);
        if ($list) {
            $return = array();
            foreach ($list as $key => $value) {
                $return[$key]['add_time'] = $value['add_time'];
                $return[$key]['remark'] = $value['remark'];
                $return[$key]['money'] = round($value['money'], 2);
                $return[$key]['use_money'] = round($value['use_money'], 2);
                $return[$key]['frozen_money'] = round($value['frozen_money'], 2);
                $return[$key]['collection_money'] = round($value['collection_money'], 2);
                $return[$key]['user_id'] = $value['user_id'];
                $return[$key]['type'] = $value['type'];
                $return[$key]['order_id'] = $value['order_id'];
                $return[$key]['in_or_out'] = $value['in_or_out'];
                $return[$key]['total_money'] = $value['remark'];
                $return[$key]['recharge_money'] = $value['recharge_money'];
                $return[$key]['repayment_money'] = $value['repayment_money'];
//                $return[$key]['frozen_money'] = $value['frozen_money'];
//                $return[$key]['collection_money'] = $value['collection_money'];
                $return[$key]['add_ip'] = $value['add_ip'];
            }
            $list = ['list' => $return];
//            $list = res_data(['list' => $list], $this->resRule);
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
//        parent::selectListLib();
    }

    /**
     * @author jxy
     *  用户资金统计查询
     */
    public function statistic()
    {
        //初始化信息
        $this->__init(-1, "用户资金统计查询", "成功返回用户资金统计信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [0, 'num', "用户id", 'user_id', 7],
        ];
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $return = array(
            'use_money'        => '0.00',
            'collection_money' => '0.00',
            'frozen_money'     => '0.00',
            'total_money'      => '0.00',
            'recharge'         => '0.00',
            'has_capital'      => '0.00',
            'has_interest'     => '0.00',
            'tender'           => '0.00',
            'wait_interest'    => '0.00'
        );
        //获取用户资金
        $where = array(
            'user_id' => $get_data['user_id']
        );
        $account = M('account')->get_one($where);
        if ($account) {
            $return['use_money'] = $account['use_money'] > 0 ? $account['use_money'] : 0.00;
            $return['collection_money'] = $account['collection_money'] ? $account['collection_money'] : 0.00;
            $return['frozen_money'] = $account['frozen_money'] ? $account['frozen_money'] : 0.00;
            $return['total_money'] = $account['total_money'] ? $account['total_money'] : 0.00;
        }
        //累计充值金额
        $recharge = M('recharge')->get_one(array('user_id' => $get_data['user_id'], 'status' => '成功'), '', 'sum(account) as recharge_account');
        $return['recharge'] = $recharge['recharge_account'] > 0 ? $recharge['recharge_account'] : 0.00;
        //获取已收本息
        $where = array(
            'invest_user_id' => $get_data['user_id'],
        );
        //获取累计投资
        $tender_where = array(
            'invest_user_id' => $get_data['user_id'],
            ['AND', [
//                ['','status', '=', "投资中"],
                ['', 'status', '=', "已放款"],
                ['OR', 'status', '=', "成功"],
                ['OR', 'status', '=', "已还款"]
            ]
            ],
        );
        $tender = M('borrow_invest')->get_one($tender_where, '', 'sum(money) as tender');
        $return['tender'] = $tender['tender'] > 0 ? $tender['tender'] : 0.00;
        $account_list = M('borrow_invest_repayment')->get_list($where);
        if ($account_list && count($account_list)) {
            foreach ($account_list as $key => $value) {
                if ($value['repay_status'] == '已经还款' && $value['money_type'] == "资金本金")
                    $return['has_capital'] += $value['repay_money'];
                if ($value['repay_status'] == '已经还款' && $value['money_type'] == "资金收益")
                    $return['has_interest'] += $value['repay_money'];
//                if($value['money_type'] == '资金本金')
//                    $return['tender'] += $value['repay_money'];
                if ($value['repay_status'] == '未还款' && $value['money_type'] == "资金收益")
                    $return['wait_interest'] += $value['repay_money'];
            }
        }
        outJson(0, $return);
    }

    /**
     * @author jxy
     * 获取单条记录
     */
    public function selectCount()
    {
        //初始化信息
        $this->__init(-1, "统计资金日志接口", "成功返回多条用户资金日志总数，失败返回错误信息");
        $this->postRule = [
            'user_id'    => [0, 'num', "用户id", 'user_id', 7],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-05-05 05:05:05'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2015-05-05 05:05:05'],
            'type'       => [0, 'string', '资金类型', 'type', 'Cash']
        ];
        //输出的参数
        $this->resRule = [
            'count' => [1, 'num', '数量', 'count', '2']
        ];

        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M('account_log');

        $where['user_id'] = $get_data['user_id'];
        if (isset($get_data['start_time']))
            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        if (isset($get_data['type']))
            $where[] = ['AND', 'type', '=', $get_data['type']];

        $list = $_m->get_list($where, '', 0, 0, '', '');
        if ($list) {
            outJson(0, count($list));
        } else {
            outJson(0, 0);
        }
    }

    /**
     * @author jxy
     * 用户资金详情
     */
    public function accountInfo()
    {
        //初始化信息
        $this->__init(-1, "用户资金详情", "成功返回用户资金明细，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [0, 'num', "用户id", 'user_id', 878],
        ];
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $return = array(
            'invest_money'         => '0.00',
            'invest_count'         => 0,
            'wait_repayment_money' => '0.00',
            'wait_repayment_count' => 0,
            'repayment_money'      => '0.00',
            'repayment_count'      => 0,
            'sum_invest_money'     => '0.00',
            'sum_invest_count'     => 0,

            'wait_interest'        => '0.00',
            'has_interest'         => '0.00',
            'sum_interest'         => '0.00'
        );
        //投资中
        $invest_where = array(
            'invest_user_id' => $get_data['user_id'],
            'status'         => '成功'
        );
        $invest = M('borrow_invest')->get_one($invest_where, '', 'sum(money) as total');

        if (isset($invest['total']) && $invest['total'] > 0) {
            $return['invest_money'] = $invest['total'];//投资中的本金
        }
        $invest_count = M('borrow_invest')->get_one($invest_where, '', 'count(*) as quantity');
        $return['invest_count'] = $invest_count['quantity'] > 0 ? $invest_count['quantity'] : 0;//投资中的本金笔数

        //获取用户资金
        $where = array(
            'invest_user_id' => $get_data['user_id']
        );
        $account_list = M('borrow_invest_repayment')->get_list($where);

        if ($account_list && count($account_list) > 0) {
            foreach ($account_list as $key => $value) {
                if ('已经还款' == $value['repay_status']) {
                    switch ($value['money_type']) {
                        case "资金本金":
                            $return['repayment_money'] += $value['repay_money'];//已收资金本金
                            $return['repayment_count'] += 1;//已收资金本金笔数
                            break;
                        case "资金收益":
                        case "加息收益":
                        case "推荐收益":
                        case "红包收益":
                        case "体验金收益":
                        case "红包本金":
                        case "现金券本金":
                        case "现金券收益":
                        default:
                            $return['has_interest'] += $value['repay_money'];//已收收益
                            break;
                    }
                } elseif ('未还款' == $value['repay_status']) {
                    switch ($value['money_type']) {
                        case "资金本金":
                            $return['wait_repayment_money'] += $value['repay_money'];//代还资金本金
                            $return['wait_repayment_count'] += 1;//代还资金本金笔数
                            break;
                        case "资金收益":
                        case "加息收益":
                        case "推荐收益":
                        case "红包收益":
                        case "体验金收益":
                        case "红包本金":
                        case "现金券本金":
                        case "现金券收益":
                        default:
                            $return['wait_interest'] += $value['repay_money'];//代收收益
                            break;
                    }
                }
            }
        }

        $return['sum_interest'] = $return['has_interest'] + $return['wait_interest'];//总收益
        $return['sum_invest_money'] = $return['invest_money'] + $return['wait_repayment_money'] + $return['repayment_money'];//总本金
        $return['sum_invest_count'] = $return['invest_count'] + $return['wait_repayment_count'] + $return['repayment_count'];//总本金笔数

        outJson(0, $return);
    }

    /**
     * @author jxy
     * 用户还款详情
     */
    public function repaymentInfo()
    {
        //初始化信息
        $this->__init(-1, "用户还款详情", "成功返回用户还款明细，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [0, 'num', "用户id", 'user_id', 878],
        ];
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $where = array(
            'invest_user_id' => $get_data['user_id']
        );
        $step_yyy_config  = CONFIG::yyyStepTime();
        $step_yyy_time    = $step_yyy_config['yyy_step_interest_time'];    ///时间分界点；
        $return = array(
            'repayment_date_list'  => [],
            'repayment_total_list' => [],
            'repayment_count_list' => []
        );

        ////元政盈的项目
        $repayment_list = M('borrow_invest_repayment')->get_list($where, '', 0, 0, 'repay_time', 'sum(repay_money) as total,repay_time,count(distinct invest_id) as quantity');
        foreach ($repayment_list as $k => $v) {
            $date_array[] = date('Ymd', strtotime($v['repay_time']));
            $total_array[] = $v['total'];
            $count_array[] = $v['quantity'];
        }

        $temp_date_array = array();
        $temp_total_array = array();

        /////元月盈的项目
        $sql = 'SELECT * FROM `y1_yyy_borrow_invest` WHERE `invest_user_id`='.$get_data['user_id'].' AND (`return_status`="已赎回" or `return_status`="赎回中")';
        $yyy_list = M('yyy_borrow_invest')->queryAll($sql);
        if (is_array($yyy_list) && !empty($yyy_list)) {
            foreach ($yyy_list as $k => $v) {
                if ($v['first_borrow_time'] > $step_yyy_time) {
                    $interest_money = getStepYyyInterest($v['invest_times'], $v['money'],$v['invest_money'], self::yyy_days, $v['interest_add']);
                } else {
                    $interest_money = calculateValue($v['interest_rate'], $v['invest_times'],  $v['invest_money'], self::yyy_days, $v['interest_add']);
                }
                $total_money = round($interest_money,2);
                $temp_total_array[] = $total_money;
                if ($v['interest_end_time'] != '0000-00-00 00:00:00') {
                    $temp_date_array[] = date('Ymd', strtotime($v['interest_end_time']));
                }
            }
        }

        ////私人尊享项目
        $appoint_sql = 'SELECT * FROM `y1_appoint_borrow_invest` WHERE `user_id`='.$get_data['user_id'].' AND (`invest_status`="已放款" or `invest_status`="已还款")';
        $appoint_list = M('appoint_borrow_invest')->queryAll($appoint_sql);
        if (is_array($appoint_list) && !empty($appoint_list)) {
            foreach ($appoint_list as $k => $v) {
                $temp_total_array[] = round($v['money'] + $v['money'] * ($v['interest_rate'] + $v['interest_add']) / 36500 * $v['interest_days'],2);
                if ($v['interest_end_time'] != '0000-00-00 00:00:00') {
                    $temp_date_array[] = date('Ymd', strtotime($v['interest_end_time']));
                }
            }
        }

        ////元聚盈项目
        $yjy_sql = 'SELECT * FROM `y1_ygzx_borrow_invest` WHERE `user_id`='.$get_data['user_id'].' AND (`invest_status`="已放款" or `invest_status`="已还款")';
        $yjy_list = M('ygzx_borrow_invest')->queryAll($yjy_sql);
        if (is_array($yjy_list) && !empty($yjy_list)) {
            foreach ($yjy_list as $yjy_k => $yjy_v) {
                $temp_total_array[] = round($yjy_v['money'] + $yjy_v['money'] * ($yjy_v['interest_rate'] + $yjy_v['interest_add']) / 36500 * $yjy_v['interest_days'], 2);
                if ($yjy_v['interest_end_time'] != '0000-00-00 00:00:00') {
                    $temp_date_array[] = date('Ymd', strtotime($yjy_v['interest_end_time']));
                }
            }
        }

        ////VIP项目；
        $user_id = $get_data['user_id'];
        $where = array("invest_user_id" => $user_id);
        $vip_invest_arr = M('vip_borrow_invest')->get_list($where);
        $vip_repayment_model = M('vip_borrow_repayment');
        if (is_array($vip_invest_arr) && !empty($vip_invest_arr)) {
            foreach($vip_invest_arr as $k => $v){
                $vip_borrow_id = $v['borrow_id'];
                $vip_repayment_detail = $vip_repayment_model -> get_list(array("borrow_id" => $vip_borrow_id));
                if (is_array($vip_repayment_detail) && !empty($vip_repayment_detail)){
                    foreach($vip_repayment_detail as $m => $n){
                        if ($n['last_term'] == '否'){
                            $temp_interest_sum = round(mbInterest($v['show_money'], $v['interest_rate'] ,$n['repay_date']), 2);
                            $temp_total_array[] = $temp_interest_sum;
                            $temp_date_array[] = date('Ymd', strtotime($n['need_repay_time']));
                        } else {
                            $total_days = M('borrow')->get_one(array("id" => $n["borrow_id"]));
                            $interest_period = $total_days['interest_period'];
                            $total_interest = round(mbInterest($v['show_money'], $v['interest_rate'] ,$interest_period), 2);
                            $temp_interest  = 0;
                            foreach($vip_repayment_detail as $key => $value){
                                if ($value['last_term'] == "否") {
                                    $temp_interest +=  round(mbInterest($v['show_money'], $v['interest_rate'] ,$value['repay_date']),2);
                                }
                            }
                            $temp_interest_sum = round($total_interest - $temp_interest , 2) + $v['show_money'];
                            $temp_total_array[] = $temp_interest_sum;
                            $temp_date_array[] = date('Ymd', strtotime($n['need_repay_time']));
                        }
                    }
                }
            }
        }

        ////秒标项目；
        $mb_sql = 'SELECT * FROM `y1_mb_borrow_invest` WHERE `user_id`='.$get_data['user_id'].' AND (`invest_status`="已放款" OR `invest_status`="已还款")';
        $mb_list = M('mb_borrow_invest')->queryAll($mb_sql);
        if (is_array($mb_list) && !empty($mb_list)) {
            foreach ($mb_list as $k => $v) {
                $temp_total_array[] = round($v['money'] + $v['money'] * $v['interest_rate'] / 36500 * $v['interest_days'],2);
                if ($v['interest_end_time'] != '0000-00-00 00:00:00') {
                    $temp_date_array[] = date('Ymd', strtotime($v['interest_end_time']));
                }
            }
        }
        
        ///推广员收益
        $tgy_sql = 'SELECT * FROM `y1_tgy_log` WHERE `extension_user_id`='.$get_data['user_id'];
        $tgy_list = M('tgy_log')->queryAll($tgy_sql);
        if (is_array($tgy_list) && !empty($tgy_list)) {
            foreach ($tgy_list as $tgy) {
                $temp_total_array[] = $tgy['percentage'];
                $temp_date_array[]  = date('Ymd', strtotime($tgy['return_time']));
            }
        }

        ///薪盈计划收益；
        $wdy_borrow_invest_model    = M('wdy_borrow_invest');
        $wdy_borrow_model           = M('wdy_borrow');
        $wdy_invest_log_model       = M('wdy_borrow_invest_log');
        $wdy_sql    = 'SELECT * FROM `y1_wdy_borrow_invest` WHERE `invest_user_id`='.$get_data['user_id'] .' AND `interest_end_time` != "0000-00-00 00:00:00"';
        $wdy_list   = $wdy_borrow_invest_model->queryAll($wdy_sql);
        $now_time   = getRequestTime();
        if (is_array($wdy_list) && !empty($wdy_list)) {
            foreach ($wdy_list as $wdy) {
                $interest_end_time = date('Ymd', strtotime($wdy['interest_end_time']));
                if ($now_time < $wdy['interest_end_time']) {
                    $temp_total_array[] = 0;
                    $wdy_show_notice[]  = $interest_end_time;
                } else {
                    $wdy_borrow_list        =   $wdy_borrow_model->get_one(array("id" => $wdy["borrow_id"]));
                    $interest_free_period   =   $wdy_borrow_list["interest_free_period"];
                    $interest_period_month  =   $wdy_borrow_list['interest_period_month'];
                    $total_delay            =   $interest_free_period * $interest_period_month / 12;
                    $total_days             =   365 * $interest_period_month / 12;
                    $wdy_borrow_invest_log  =   $wdy_invest_log_model->get_list(array('invest_id'=>$wdy['id']));
                    $sum_delay_days         =   $sum_interest   =   $sum_invest_money  = $delay_interest  =   0;
                    foreach ($wdy_borrow_invest_log as $key => $value) {
                        $sum_delay_days     +=  $value['delay_days'];
                        $sum_interest       +=  $value['interest_money'];
                        $sum_invest_money   +=  $value['invest_money'];
                    }
                    if ($sum_delay_days > $total_delay) {
                        $delay_interest = ($sum_delay_days - $total_delay) * $sum_interest / $total_days;
                    }
                    $show_interest      = round(($sum_interest - $delay_interest), 2) + $sum_invest_money;
                    $temp_total_array[] = $show_interest;
                }
                $temp_date_array[]      = $interest_end_time;
            }
        }

        $new_date_array = array_unique($temp_date_array);
        $show_total_arr = $show_date_arr = $show_count_arr = array();
        foreach($new_date_array as $k => $v){
            $temp_sum = 0; $temp_count = 0;
            foreach($temp_date_array as $m => $n){
                if ($v == $n) {
                    $temp_sum = $temp_sum + $temp_total_array[$m];
                    $temp_count = $temp_count + 1;
                }
            }
            $show_total_arr[] = $temp_sum;
            $show_date_arr[] = $v;
            $show_count_arr[] = $temp_count;
        }

        $last_total_arr = $last_date_arr = $count_arr = array();

        if (count($date_array) <> 0) {
            foreach($date_array as $k => $v) {
                $temp_sum = 0; $temp_count = 0; $status = 0;
                foreach($show_date_arr as $m => $n) {
                    if ($v == $n) {
                        $temp_sum = $show_total_arr[$m] + $total_array[$k];
                        $temp_count = $show_count_arr[$m] + $count_array[$k];
                        $status = 1;
                    }
                }
                if ($status == 0){
                    $temp_sum = $total_array[$k];
                    $temp_count = $count_array[$k];
                }
                $last_total_arr[] = $temp_sum;
                $last_date_arr[] = $v;
                $last_count_arr[] = $temp_count;
            }
        } else {
            foreach ($show_date_arr as $k => $v) {
                $temp_sum = 0;
                $temp_count = 0;
                $status = 0;
                if (count($date_array) > 0) {
                    foreach ($date_array as $m => $n) {
                        if ($v == $n) {
                            $temp_sum = $show_total_arr[$k] + $total_array[$m];
                            $temp_count = $show_count_arr[$k] + $date_array[$m];
                            $status = 1;
                        }
                    }
                }
                if ($status == 0) {
                    $temp_sum = $show_total_arr[$k];
                    $temp_count = $show_count_arr[$k];
                }
                $last_total_arr[] = $temp_sum;
                $last_date_arr[] = $v;
                $last_count_arr[] = $temp_count;
            }
        }
        
        foreach ($show_date_arr as $k => $v) {
            if (!in_array($v, $last_date_arr)) {
                $last_total_arr[] = $show_total_arr[$k];
                $last_date_arr[] = $v;
                $last_count_arr[] = $show_count_arr[$k];
            }
        }

        $return['repayment_date_list']      =   $last_date_arr;
        $return['repayment_total_list']     =   $last_total_arr;
        $return['repayment_count_list']     =   $last_count_arr;
        $return['wdy_date_list']            =   $wdy_show_notice;
        outJson(0, json_encode($return));
    }

    /**
     * 用户还款详情
     */
    public function repaymentDetailByYearMonth()
    {
        //初始化信息
        $this->__init(-1, "用户还款详情", "成功返回用户还款明细，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [0, 'num', "用户id", 'user_id', 878],
            'year'    => [0, 'num', "年", 'year', 2015],
            'month'   => [0, 'num', "月", 'month', 11],
        ];
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        ///用户id
        $user_id  = $get_data['user_id'];
        ///当月开始时间
        $min_time = $get_data['year'] . '-' . $get_data['month'] . '-01 00:00:00';
        ///当月结束时间
        $max_time = $get_data['year'] . '-' . $get_data['month'] . '-31 23:59:59';
        $where = array(
            'invest_user_id' => $get_data['user_id'],
        );
        $yyy_where = array(
        	'invest_user_id' => $user_id,
        	'return_status'  => '已赎回',
        	['or', 'return_status', '=', '赎回中']
        );
        if (isset($get_data['year'])) {
            if ($get_data['month'] < 10) {
                $get_data['month'] = "0" . $get_data['month'];
            }
            $where[] = ['AND', 'repay_time', '>=', $min_time];
            $where[] = ['AND', 'repay_time', '<=', $max_time];
            $yyy_where[] = ['AND', 'interest_end_time', '>=', $min_time];
            $yyy_where[] = ['AND', 'interest_end_time', '<=', $max_time];
        }
        $return = array(
            'repayment_money' => 0,
            'repayment_msg'   => '',
        );
        $repayment_list = M('borrow_invest_repayment')->get_one($where, '', 'sum(repay_money) as total');

        //*** 添加元月盈的时间 ***//
        $step_yyy_config  = CONFIG::yyyStepTime();
        $step_yyy_time    = $step_yyy_config['yyy_step_interest_time'];    ///时间分界点；
        $sql = "SELECT * FROM `y1_yyy_borrow_invest` WHERE `invest_user_id`=".$user_id." AND (`return_status`='已赎回' or `return_status`='赎回中') AND `interest_end_time`>='".$min_time."' AND `interest_end_time`<='".$max_time."'";
        $invest_arr = M('yyy_borrow_invest')->queryAll($sql);
        $sum        = 0;
        foreach($invest_arr as $v){
            if ($v['first_borrow_time'] > $step_yyy_time) {
                $temp = getStepYyyInterest($v['invest_times'], $v['money'],$v['invest_money'], self::yyy_days, $v['interest_add']);
            } else {
                $temp = calculateValue($v['interest_rate'], $v['invest_times'],  $v['invest_money'], self::yyy_days, $v['interest_add']);
            }
            $sum += $temp;
        }
        $sum = round($sum, 2);

        ////添加秒标的累计金额；
        $mb_sum = 0;
        $mb_sql = "SELECT * FROM `y1_mb_borrow_invest` WHERE `user_id`=".$user_id." AND (`invest_status`='已放款' or `invest_status`='已还款') AND `interest_end_time`>='".$min_time."' AND `interest_end_time`<='".$max_time."'";
        $mb_invest_arr = M('mb_borrow_invest')->queryAll($mb_sql);
        foreach($mb_invest_arr as $value){
            $mb_interest = $value['interest_rate'] + $value['interest_add'];
            $mb_temp_money  = $value["money"] + mbInterest($value["money"], $mb_interest, $value['interest_days']);
            $mb_sum += $mb_temp_money;
        }
        $mb_sum = round($mb_sum, 2);

        ////添加私人尊享的累计金额；
        $appoint_sum = 0;
        $appoint_sql = "SELECT * FROM `y1_appoint_borrow_invest` WHERE `user_id`=".$user_id." AND (`invest_status`='已放款' or `invest_status`='已还款') AND `interest_end_time`>='".$min_time."' AND `interest_end_time`<='".$max_time."'";
        $appoint_invest_arr = M('appoint_borrow_invest')->queryAll($appoint_sql);
        foreach($appoint_invest_arr as $n){
            $appoint_interest = $n['interest_rate'] + $n['interest_add'];
            ///mbInterest方法,可以复用
            $appoint_temp_money = $n['money'] + mbInterest($n['money'], $appoint_interest, $n['interest_days']);
            $appoint_sum += $appoint_temp_money;
        }
        $appoint_sum = round($appoint_sum, 2);

        ////添加元聚盈的累计金额；
        $yjy_sum = 0;
        $yjy_sql = "SELECT * FROM `y1_ygzx_borrow_invest` WHERE `user_id`=".$user_id." AND (`invest_status`='已放款' or `invest_status`='已还款') AND `interest_end_time`>='".$min_time."' AND `interest_end_time`<='".$max_time."'";
        $yjy_invest_arr = M('ygzx_borrow_invest')->queryAll($yjy_sql);
        foreach($yjy_invest_arr as $yjy_n){
            $yjy_interest = $yjy_n['interest_rate'] + $yjy_n['interest_add'];
            ///mbInterest方法,可以复用
            $yjy_temp_money = $yjy_n['money'] + mbInterest($yjy_n['money'], $yjy_interest, $yjy_n['interest_days']);
            $yjy_sum += $yjy_temp_money;
        }
        $yjy_sum = round($yjy_sum, 2);


        ////添加VIP的累计金额；
        $vip_sum = 0;
        $where = array("invest_user_id" => $user_id);
        $vip_invest_arr = M('vip_borrow_invest')->get_list($where);
        $vip_repayment_model = M('vip_borrow_repayment');
        if (is_array($vip_invest_arr) && !empty($vip_invest_arr)) {
            foreach($vip_invest_arr as $k => $v){
                $vip_borrow_id = $v['borrow_id'];
                $vip_repayment_detail = $vip_repayment_model -> get_list(array("borrow_id" => $vip_borrow_id));
                if (is_array($vip_repayment_detail) && !empty($vip_repayment_detail)){
                    foreach($vip_repayment_detail as $m => $n){
                        $temp_interest_sum = 0;
                        if (strtotime($n['need_repay_time']) >= strtotime($min_time) && strtotime($n['need_repay_time']) <= strtotime($max_time)) {
                            if ($n['last_term'] == '否'){
                                $temp_interest_sum += round(mbInterest($v['show_money'], $v['interest_rate'] ,$n['repay_date']), 2);
                            } else {
                                $total_days = M('borrow')->get_one(array("id" => $n["borrow_id"]));
                                $interest_period = $total_days['interest_period'];
                                $total_interest = round(mbInterest($v['show_money'], $v['interest_rate'] ,$interest_period), 2);
                                $temp_interest  = 0;
                                foreach($vip_repayment_detail as $key => $value){
                                    if ($value['last_term'] == "否") {
                                        $temp_interest +=  round(mbInterest($v['show_money'], $v['interest_rate'] ,$value['repay_date']),2);
                                    }
                                }
                                $temp_interest_sum += round($total_interest - $temp_interest , 2) + $v['show_money'];
                            }
                        }
                        $vip_sum += $temp_interest_sum;
                    }
                }
            }
        }
        $vip_sum = round($vip_sum, 2);

        ///推广员收益
        $tgy_num = 0;
        $tgy_sql = "SELECT * FROM `y1_tgy_log` WHERE `extension_user_id`=".$user_id." AND `return_time`>='".$min_time."' AND `return_time`<='".$max_time."'";
        $tgy_list = M('tgy_log')->queryAll($tgy_sql);
        foreach ($tgy_list as $tgy) {
            $tgy_num += $tgy['percentage']; 
        }

        ///薪盈计划收益；
        $wdy_borrow_invest_model    = M('wdy_borrow_invest');
        $wdy_borrow_model           = M('wdy_borrow');
        $wdy_invest_log_model       = M('wdy_borrow_invest_log');
        $wdy_sql = "SELECT * FROM `y1_wdy_borrow_invest` WHERE `invest_user_id`=".$user_id." AND (`status`='已放款' OR `status`='已还款') AND `interest_end_time`>='".$min_time."' AND `interest_end_time`<='".$max_time."'";
        $wdy_list= $wdy_borrow_invest_model->queryAll($wdy_sql);
        $wdy_num    =   0;
        foreach ((array)$wdy_list as $wdy) {
            $wdy_borrow_list        =   $wdy_borrow_model->get_one(array("id" => $wdy["borrow_id"]));
            $interest_free_period   =   $wdy_borrow_list["interest_free_period"];
            $interest_period_month  =   $wdy_borrow_list['interest_period_month'];
            $total_delay            =   $interest_free_period * $interest_period_month / 12;
            $total_days             =   365 * $interest_period_month / 12;
            $wdy_borrow_invest_log  =   $wdy_invest_log_model->get_list(array('invest_id'=>$wdy['id']));
            $sum_delay_days         =   $sum_interest   =   $sum_invest_money  = $delay_interest  =   0;
            foreach ($wdy_borrow_invest_log as $key => $value) {
                $sum_delay_days     +=  $value['delay_days'];
                $sum_interest       +=  $value['interest_money'];
                $sum_invest_money   +=  $value['invest_money'];
            }
            if ($sum_delay_days > $total_delay) {
                $delay_interest = ($sum_delay_days - $total_delay) * $sum_interest / $total_days;
            }
            $wdy_num    += round(($sum_interest - $delay_interest), 2) + $sum_invest_money;
        }

        $return['repayment_money'] = isset($repayment_list['total']) ? $repayment_list['total'] : 0;
        $return['repayment_money'] = $return['repayment_money'] + $sum + $mb_sum + $appoint_sum + $vip_sum + $tgy_num + $wdy_num + $yjy_sum;
        $return['repayment_msg'] = $get_data['year'] . "年" . $get_data['month'] . "月还款总额: <i>" . $return['repayment_money'] . "元</i>";
        outJson(0, $return);
    }


}
