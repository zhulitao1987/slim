<?php

/**
 * 用户普通日志表-易联
 * User: Pjk
 * Date: 15/12/09
 */
class new_account_logLib extends Lib
{
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
            'user_id'    => [0, 'num', "用户id", 'user_id', 878],
            'type'       => [0, 'string', "资金操作类型", 'type', 'Repayment'],
            'page'       => [1, 'num', "页码", 'page', 0],
            'page_size'  => [1, 'num', "显示条数", 'page_size', 20],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-01-01 00:00:00'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2017-12-05 05:05:05']
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
        $total_result = $_m->get_one($where, '', 'count(*) as total');
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
                $return[$key]['total_money'] = $value['total_money'];
                if ($value["remark"] == "现金券收益") {
                    $value["remark"] = "红包收益";
                } elseif ($value["remark"] == "现金券本金") {
                    $value["remark"] = "红包本金";
                }
                $return[$key]['remark'] = $value['remark'];
                $return[$key]['recharge_money'] = $value['recharge_money'];
                $return[$key]['repayment_money'] = $value['repayment_money'];
                $return[$key]['add_ip'] = $value['add_ip'];
            }
            $list = ['list' => $return, 'total' => $total_result['total']];
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }

   /**
    * @author jxy
    * 用户资金统计查询
    */
    public function statistic()
    {
        //初始化信息
        $this->__init(-1, "用户资金统计查询", "成功返回用户资金统计信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [0, 'num', "用户id", 'user_id', 878],
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
        $account = M('new_account')->get_one($where);
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
                if ($value['repay_status'] == '未还款' && $value['money_type'] == "资金收益")
                    $return['wait_interest'] += $value['repay_money'];
            }
        }
        outJson(0, $return);
    }

    /**
     * @author jxy
     * 获取记录总数
     */
    public function selectCount()
    {
        //初始化信息
        $this->__init(-1, "统计资金日志接口", "成功返回多条用户资金日志总数，失败返回错误信息");
        $this->postRule = [
            'user_id'    => [0, 'num', "用户id", 'user_id', 878],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-05-05 05:05:05'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2018-05-05 05:05:05'],
            'type'       => [0, 'string', '资金类型', 'type', 'Repayment']
        ];
        //输出的参数
        $this->resRule = [
            'count' => [1, 'num', '数量', 'count', '2']
        ];

        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M('new_account_log');

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
   * 获得易联、汇付、宝付的资金明细总记录数
   * @author cdf@yhxbank.com
   * @date  2016-06-24
   * @echo json
   */
    public function selectCommonList(){
        //初始化信息
        $this->__init(-1, "查询多条资金日志接口", "成功返回多条用户资金日志信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [0, 'num', "用户id", 'user_id', 878],
            'type'       => [0, 'string', "资金操作类型", 'type', 'Repayment'],
            'page'       => [1, 'num', "页码", 'page', 0],
            'page_size'  => [1, 'num', "显示条数", 'page_size', 20],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-01-01 00:00:00'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2017-12-05 05:05:05']
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
        $total_result = $_m->get_one($where, '', 'count(*) as total');
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
                $return[$key]['total_money'] = $value['total_money'];
                $return[$key]['remark'] = $value['remark'];
                $return[$key]['recharge_money'] = $value['recharge_money'];
                $return[$key]['repayment_money'] = $value['repayment_money'];
                $return[$key]['add_ip'] = $value['add_ip'];
            }
            $list = ['list' => $return, 'total' => $total_result['total']];
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }


//    public function selectCommonList()
//    {
//        //初始化信息
//        $this->__init(-1, "查询多条资金日志接口", "成功返回多条用户资金日志信息，失败返回错误信息");
//        //需要传递的参数
//        $this->postRule = [
//            'user_id'    => [0, 'num', "用户id", 'user_id', 878],
//            'type'       => [0, 'string', "资金操作类型", 'type', 'Repayment'],
//            'page'       => [1, 'num', "页码", 'page', 0],
//            'page_size'  => [1, 'num', "显示条数", 'page_size', 20],
//            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-01-01 00:00:00'],
//            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2017-12-05 05:05:05']
//        ];
//
//        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
//        //过滤参数，查看是否传递必须的数据
//        $get_data = must_post($this->postRule, $this->req, 1);
//        $_m = M('common_account_log');
//        $page = isset($get_data['page']) ? $get_data['page'] : 0;
//        $page_size = isset($get_data['page_size']) ? $get_data['page_size'] : 0;
//        unset($get_data['page']);
//        unset($get_data['page_size']);
//        $where = array(
//            'user_id' => $get_data['user_id'],
//        );
//        if (isset($get_data['start_time']))
//            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
//        if (isset($get_data['end_time']))
//            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
//        if (isset($get_data['type']))
//            $where[] = ['AND', 'type', '=', $get_data['type']];
//        $list = $_m->get_list($where, 'id DESC', $page, $page_size);
//        $total_result = $_m->get_one($where, '', 'count(*) as total');
//        if ($list) {
//            $return = array();
//            foreach ($list as $key => $value) {
//                $return[$key]['add_time'] = $value['add_time'];
//                $return[$key]['remark'] = $value['remark'];
//                $return[$key]['money'] = round($value['money'], 2);
//                $return[$key]['use_money'] = round($value['use_money'], 2);
//                $return[$key]['frozen_money'] = round($value['frozen_money'], 2);
//                $return[$key]['collection_money'] = round($value['collection_money'], 2);
//                $return[$key]['user_id'] = $value['user_id'];
//                $return[$key]['type'] = $value['type'];
//                $return[$key]['order_id'] = $value['order_id'];
//                $return[$key]['in_or_out'] = $value['in_or_out'];
//                $return[$key]['total_money'] = $value['total_money'];
//                $return[$key]['remark'] = $value['remark'];
//                $return[$key]['recharge_money'] = $value['recharge_money'];
//                $return[$key]['repayment_money'] = $value['repayment_money'];
//                $return[$key]['add_ip'] = $value['add_ip'];
//            }
//            $list = ['list' => $return, 'total' => $total_result['total']];
//            outJson(0, $list);
//        } else {
//            outJson(-1, '没有数据');
//        }
//    }


   /**
    * 获得易联、汇付、宝付的资金明细总记录数
    * @author cdf@yhxbank.com
    * @date  2016-06-24
    * @echo json
    */
    public function selectCommonCount()
    {
        //初始化信息
        $this->__init(-1, "统计资金日志接口", "成功返回多条用户资金日志总数，失败返回错误信息");
        $this->postRule = [
            'user_id'    => [0, 'num', "用户id", 'user_id', 878],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-05-05 05:05:05'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2018-05-05 05:05:05'],
            'type'       => [0, 'string', '资金类型', 'type', 'Repayment']
        ];
        //输出的参数
        $this->resRule = [
            'count' => [1, 'num', '数量', 'count', '2']
        ];

        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M('common_account_log');

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

}
