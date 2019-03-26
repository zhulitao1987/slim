<?php

include_once dirname(__FILE__) . "/../cli.php";
header("Content-type: text/html; charset=utf-8");
set_time_limit(0);

/**
 * 元月盈利息计算
 *
 * @author yhx
 */
class yyy_invest_interest extends Cli {
    
    ///元月盈锁定期
    const invest_period = 32;
    /**
     * @comments 老的投资用户,不使用阶梯
     * 元月盈,计算满标后,用户的投资利息,包括复投
     */
    public function calculateInterest() {
        $yyy_borrow_model = M('yyy_borrow');
        $yyy_invest_model = M('yyy_borrow_invest');
        $yyy_return_model = M('yyy_return');
        $account_model    = M('new_account');
        $user_model       = M('user');
        $sms_model        = M('sms');
        $reivst_log_model = M('yyy_reinvest_log');
        ///投资天数
        $invest_period    = self::invest_period;
        ///阶梯上线时间
        $step_yyy_config  = CONFIG::yyyStepTime();
        $step_yyy_time    = $step_yyy_config['yyy_step_interest_time'];
        $request_time     = getRequestTime(0);
        ///读取已满标,并且到了结息时间和元月盈标的投资记录
        $querySql = "SELECT "
                . "A.id,"
                . "A.borrow_id,"
                . "A.invest_order_id,"
                . "A.first_borrow_time,"
                . "A.money,"
                . "A.invest_money,"
                . "A.invest_status,"
                . "A.invest_times,"
                . "A.interest_rate,"
                . "A.interest_add,"
                . "A.invest_user_id,"
                . "A.return_status,"
                . "A.repeat_time,"
                . "A.interest_start_time,"
                . "A.interest_end_time,"
                . "B.borrow_name "
                . "FROM y1_yyy_borrow_invest AS A "
                . "LEFT JOIN y1_yyy_borrow AS B "
                . "ON A.borrow_id       = B.id "
                . "WHERE B.money_status = '已满标' "
                . "AND B.borrow_status  = '发布' "
                . "AND B.is_show        = '是' "
                . "AND A.interest_end_time <= '" . $request_time . "'  "                 
                . "AND A.first_borrow_time <'" . $step_yyy_time . "' " 
                . "AND A.return_status != '已赎回' "
                . "AND A.type = '用户投资'";
        $invest_list = $yyy_borrow_model->queryAll($querySql);
        if (empty($invest_list)) {
            echo '到期的元月盈标,还没有符合复投条件的记录', '<br>';
        }
        foreach ((array) $invest_list as $invest) {
            ///用户到了结息时间,还在继续投资的,自动转入复投
            if (!isset($invest['return_status'])) {
                continue;
            }
            ///计算本息
            $calculate_money  = calculateValue($invest['interest_rate'], $invest['invest_times'], $invest['invest_money'], $invest_period, $invest['interest_add']); 
            ///获取用户真实姓名
            $user_info        = $user_model->get_one(array('id' => $invest['invest_user_id']), '', 'real_name,phone,type,co_mobile,extension_user_id');
            $phone_number     = in_array($user_info['type'], array('企业用户', '企业vip')) ? $user_info['co_mobile'] : $user_info['phone'];
            ///投资用户的推荐人用户id
            $extension_user_id      = isset($user_info['extension_user_id']) ? $user_info['extension_user_id'] : 0;
            ///结息时间转为时间戳,方便以下使用
            $interest_end_timestamp = strtotime($invest['interest_end_time']);
            ///复投起息时间,预约回款时间
            $repeat_start_time      = date("Y-m-d", $interest_end_timestamp + 86400) . " 00:00:00";
            $ymd_repeat_start_time  = date("Y年m月d日", $interest_end_timestamp + 86400);
            if ('投资中' == $invest['return_status']) {
                $yyy_invest_model->beginTransaction();
                ///复投次数
                $repeat_times = $invest['invest_times'] + 1;
                ///复投的话,开始结息时间
                $repeat_end_time     = date("Y-m-d", $interest_end_timestamp + $invest_period * 86400) . " 00:00:00";
                $ymd_repeat_end_time = date("Y年m月d日", $interest_end_timestamp + $invest_period * 86400);
                ///记录在投金额,使用四舍五入方法后去记录
                $record_money        = round($calculate_money, 2);
                $update_invest_data  = array(
                    'invest_money'   => $record_money,
                    'invest_status'  => '复投',
                    'invest_times'   => $repeat_times,
                    'repeat_time'    => $repeat_start_time,
                    'interest_start_time'   => $repeat_start_time,
                    'interest_end_time'     => $repeat_end_time,
                    'tgy_benefit'           => '是',
                    'lcs_benefit'           => '否'
                );
                $update_invest_where = array(
                    'id' => $invest['id']
                );
                $repeat_invest_ret = $yyy_invest_model->update($update_invest_data, $update_invest_where);
                ///复投时,产生的新的利息转入待收金额
                ///复投时,产生的新的本息之和
                $new_collection_money = calculateValue($invest['interest_rate'], $repeat_times, $calculate_money, $invest_period, $invest['interest_add']) - $calculate_money;
                ///本次复投,根据新的本金计算得到的本期收益
                $collection_money     = round($new_collection_money, 2);
                $collection_ret = $account_model->addCollectionMoney($invest['invest_user_id'], $collection_money, "yyy_Loans", "元月盈复投,待收收益", '', $invest['invest_order_id']);
                ///用户发送短信提示数据format
                $repeat_send_data = serialize(array(
                    'REAL_NAME'         => $user_info['real_name'],
                    'BORROW_NAME'       => $invest['borrow_name'],
                    'TIME'              => $ymd_repeat_start_time,
                    'INVEST_TIMES'      => $repeat_times,
                    'INVEST_MONEY'      => $record_money,
                    'INTEREST_END_TIME' => $ymd_repeat_end_time
                ));
                ///复投时,推广员添加对应的复投记录
//                if (!empty($extension_user_id)) {
                    ///insert
                    $reinvest_ret = $reivst_log_model->insert(array(
                        'invest_id'         => $invest['id'],
                        'invest_user_id'    => $invest['invest_user_id'],
                        'invest_time'       => $repeat_start_time,
                        'invest_money'      => $record_money,
                        'interest_days'     => $invest_period,
                        'borrow_name'       => $invest['borrow_name'],
                        'investor'          => $user_info['real_name'],
                        'iphone'            => $phone_number,
                        'extension_user_id' => $extension_user_id,
                        'term'              => $repeat_times,
                        'add_time'          => $request_time
                    ));
                    if (empty($reinvest_ret)) {
                        $yyy_invest_model->rollback();
                        continue;
                    }
//                }
                ///2016中秋大转盘 投资可以获得到除以10000的转盘机会；
                //$dzp_result = M('hd_zqj_turntable')->dzpInvestReward($invest['invest_user_id'], $invest_money, ZQJ_DZP_ACTIVE_TITLE);
                if (!empty($repeat_invest_ret) && $collection_ret === true) {
                    $yyy_invest_model->commit();
                    echo $invest['invest_user_id'] . "复投 success",'<br>';
                    ///发送短信
                    $sms_model->smsAdd($phone_number, 'yyy_re_invest_success', $repeat_send_data);
                } else {
                    $yyy_invest_model->rollback();
                    echo $invest['invest_user_id'] . "复投 failure",'<br>';
                }
                continue;
                ///赎回中的记录,到了结息时间,计算利息并回款
            } elseif ('赎回中' == $invest['return_status']) {
                ///获取用户回款金额,精确到小数点后两位,不作四舍五入处理
                $return_money     = getFormatData($calculate_money);
                $yyy_invest_model->beginTransaction();
                ///修改字段,并且还款
                $return_update   = array(
                    'invest_money'   => $return_money,
                    'return_status'  => '已赎回',
                    'tgy_benefit'    => '否'
                );
                $return_where = array(
                    'id' => $invest['id']
                );
                $return_invest_ret = $yyy_invest_model->update($return_update, $return_where);
                ///更新return记录
                $user_return_data = array(
                    'invest_money'  => $return_money,
                    'status'        => '已赎回',
                    'return_time'   => $repeat_start_time
                );
                $user_return_where = array(
                    'invest_id' => $invest['id']
                );
                $user_return_info = $yyy_return_model->update($user_return_data, $user_return_where);
                ///用户回款
                $return_status    = $account_model->repaymentMoney($invest['invest_user_id'], $return_money, 'yyy_cash', '元月盈回款', '', $invest['invest_order_id']);
                ///用户利息总计数据格式化
                $interest_benifit = round(($return_money - $invest['money']), 2);
                ///用户汇款数据format
                $repay_send_data  = serialize(array(
                    'REAL_NAME'     => $user_info['real_name'],
                    'BORROW_NAME'   => $invest['borrow_name'],
                    'MONEY'         => $return_money,
                    'INTEREST'      => $interest_benifit
                ));
                if (!empty($return_invest_ret) && !empty($user_return_info) && $return_status === true) {
                    $yyy_invest_model->commit();
                    echo $invest['invest_user_id'] . "回款 success",'<br>';
                    ///发送短信
                    $sms_model->smsAdd($phone_number, 'yyy_repayment_success', $repay_send_data);
                } else {
                    $yyy_invest_model->rollback();
                    echo $invest['invest_user_id'] . "回款 failure",'<br>';
                }
                continue;
            }
        }
    }
    
    /**
     * @comments 阶梯年化收益,新投资用户完全走阶梯利息逻辑
     * 元月盈,计算满标后,用户的投资利息,包括复投
     */
    public function stepYyyInterest(){
        $yyy_borrow_model = M('yyy_borrow');
        $yyy_invest_model = M('yyy_borrow_invest');
        $yyy_return_model = M('yyy_return');
        $account_model    = M('new_account');
        $user_model       = M('user');
        $sms_model        = M('sms');
        $reivst_log_model = M('yyy_reinvest_log');
        $red_bag_log_model= M('red_bag_log');

        ///投资天数
        $invest_period    = self::invest_period;
        ///阶梯上线时间
        $step_yyy_config  = CONFIG::yyyStepTime();
        $step_yyy_time    = $step_yyy_config['yyy_step_interest_time'];
        $request_time     = getRequestTime(0);
        ///读取已满标,并且到了结息时间和元月盈标的投资记录
        $querySql = "SELECT "
                . "A.id,"
                . "A.borrow_id,"
                . "A.invest_order_id,"
                . "A.first_borrow_time,"
                . "A.money,"
                . "A.invest_money,"
                . "A.invest_status,"
                . "A.invest_times,"
                . "A.interest_rate,"
                . "A.interest_add,"
                . "A.invest_user_id,"
                . "A.return_status,"
                . "A.repeat_time,"
                . "A.interest_start_time,"
                . "A.interest_end_time,"
                . "B.borrow_name "
                . "FROM y1_yyy_borrow_invest AS A "
                . "LEFT JOIN y1_yyy_borrow AS B "
                . "ON A.borrow_id       = B.id "
                . "WHERE B.money_status = '已满标' "
                . "AND B.borrow_status  = '发布' "
                . "AND B.is_show        = '是' "
                . "AND A.interest_end_time <= '" . $request_time . "' "
                . "AND A.first_borrow_time >='" . $step_yyy_time. "' " 
                . "AND A.return_status != '已赎回' "
                . "AND A.type = '用户投资'";
        $invest_list = $yyy_borrow_model->queryAll($querySql);
        if (empty($invest_list)) {
            echo '阶梯利率,到期的元月盈标,还没有符合复投条件的记录','<br>';
        }
        foreach ((array) $invest_list as $invest) {
            ///用户到了结息时间,还在继续投资的,自动转入复投
            if (!isset($invest['return_status'])) {
                continue;
            }
            ///计算本息,四舍五入精确到小数点后两位
            $calculate_money  = getStepYyyInterest($invest['invest_times'], $invest['money'], $invest['invest_money'], $invest_period, $invest['interest_add']);
            ///获取用户真实姓名
            $user_info        = $user_model->get_one(array('id' => $invest['invest_user_id']), '', 'real_name,type,phone,co_mobile,type,sales_phone,extension_user_id');
            if (empty($user_info)) {
                continue;
            }
            ///投资用户的推荐人用户id
            $extension_user_id      = isset($user_info['extension_user_id']) ? $user_info['extension_user_id'] : 0;
            ///结息时间转为时间戳,方便以下使用
            $interest_end_timestamp = strtotime($invest['interest_end_time']);
            ///复投起息时间,预 约回款时间
            $repeat_start_time      = date("Y-m-d", $interest_end_timestamp + 86400) . " 00:00:00";
            $ymd_repeat_start_time  = date("Y年m月d日", $interest_end_timestamp + 86400);
            ///发送短信的手机号码
            $phone_number           = in_array($user_info['type'], array('企业用户', '企业vip')) ? $user_info['co_mobile'] : $user_info['phone'];
            if ('投资中' == $invest['return_status']) {
                $yyy_invest_model->beginTransaction();
                ///复投次数
                $repeat_times = $invest['invest_times'] + 1;
                ///根据本期复投次数,确定年化收益率
                $interest_rate= getInterestRateByInvestTimes($repeat_times);
                ///复投的话,开始结息时间
                $repeat_end_time     = date("Y-m-d", $interest_end_timestamp + $invest_period * 86400) . " 00:00:00";
                $ymd_repeat_end_time = date("Y年m月d日", $interest_end_timestamp + $invest_period * 86400);
                ///记录在投金额,使用四舍五入方法后去记录
                $record_money        = round($calculate_money, 2);
                $update_invest_data  = array(
                    'invest_money'   => $record_money,
                    'invest_status'  => '复投',
                    'invest_times'   => $repeat_times,
                    'interest_rate'  => $interest_rate,
                    'repeat_time'    => $repeat_start_time,
                    'interest_start_time'   => $repeat_start_time,
                    'interest_end_time'     => $repeat_end_time,
                    'tgy_benefit'           => '是',
                    'lcs_benefit'           => '否'
                );
                $update_invest_where = array(
                    'id' => $invest['id']
                );
                $repeat_invest_ret = $yyy_invest_model->update($update_invest_data, $update_invest_where);
                ///复投时,产生的新的利息转入待收金额
                ///复投时,产生的新的本息之和
                $new_collection_money = getStepYyyInterest($repeat_times, $invest['money'], $calculate_money, $invest_period, $invest['interest_add']) - $calculate_money;
                ///本次复投,根据新的本金计算得到的本期收益
                $collection_money     = round($new_collection_money, 2);
                $collection_ret = $account_model->addCollectionMoney($invest['invest_user_id'], $collection_money, "yyy_Loans", "元月盈复投,待收收益", '', $invest['invest_order_id']);
                ///用户发送短信提示数据format
                $repeat_send_data = serialize(array(
                    'REAL_NAME'         => $user_info['real_name'],
                    'BORROW_NAME'       => $invest['borrow_name'],
                    'TIME'              => $ymd_repeat_start_time,
                    'INVEST_TIMES'      => $repeat_times,
                    'INVEST_MONEY'      => $record_money,
                    'INTEREST_END_TIME' => $ymd_repeat_end_time
                ));
                $user_real_name_sms =   da_ma($user_info['real_name'], 1, 0, 2);
                $user_phone_sms     =   da_ma($phone_number, 3, 4, 4);
                $send_vip_manager = serialize(array(
                    'REAL_NAME'         => $user_real_name_sms,
                    'MOBILE'            => $user_phone_sms,
                    'TIME'              => $ymd_repeat_start_time,
                    'BORROW_NAME'       => $invest['borrow_name'],
                    'INVEST_MONEY'      => $record_money,
                ));
                ///复投时,推广员添加对应的复投记录
//                if (!empty($extension_user_id)) {
                    ///insert
                    $reinvest_ret = $reivst_log_model->insert(array(
                        'invest_id'         => $invest['id'],
                        'invest_user_id'    => $invest['invest_user_id'],
                        'invest_time'       => $repeat_start_time,
                        'invest_money'      => $record_money,
                        'interest_days'     => $invest_period,
                        'borrow_name'       => $invest['borrow_name'],
                        'investor'          => $user_info['real_name'],
                        'iphone'            => $phone_number,
                        'extension_user_id' => $extension_user_id,
                        'term'              => $repeat_times,
                        'add_time'          => $request_time
                    ));
                    if (empty($reinvest_ret)) {
                        $yyy_invest_model->rollback();
                        continue;
                    }
//                }
                ///2016中秋大转盘 投资可以获得到除以10000的转盘机会；
                //$dzp_result = M('hd_zqj_turntable')->dzpInvestReward($invest['invest_user_id'], $invest_money, ZQJ_DZP_ACTIVE_TITLE);
                if (!empty($repeat_invest_ret) && $collection_ret === true) {
                    $yyy_invest_model->commit();
                    echo $invest['invest_user_id'] . "复投 success",'<br>';
                    ///发送短信
                    if (!empty($user_info["sales_phone"]) && $user_info["sales_phone"] != "VIP0001") {
                        $sales_info =  M("sales_user") -> get_one(array("mobile" => $user_info["sales_phone"], "status" => "0"));
                        if ($sales_info) {
                            $sms_model->smsAdd($user_info["sales_phone"], 'yyy_reinvest_sms', $send_vip_manager);
                        }
                    }
                    $sms_model->smsAdd($phone_number, 'yyy_re_invest_success', $repeat_send_data);
                } else {
                    $yyy_invest_model->rollback();
                    echo $invest['invest_user_id'] . "复投 failure",'<br>';
                }
                continue;
                ///赎回中的记录,到了结息时间,计算利息并回款
            } elseif ('赎回中' == $invest['return_status']) {
                ////判断此用户是否用过红包，如果用过红包，再回款的时候添加上红包的本息；
                $red_bag_list   = $red_bag_log_model->get_one(array("borrow_type"=>"元月盈", "borrow_invest_id"=>$invest['id']));
                $red_bag_money  = $red_bag_interest = 0;
                if (is_array($red_bag_list)) {
                    $red_bag_money     = $red_bag_list["money"];
                    $red_bag_interest  = getStepYyyInterest(0, $red_bag_list["money"], $red_bag_list["money"], $invest_period, 0) - $red_bag_money;
                    $red_bag_interest  = round($red_bag_interest, 2);
                }

                ///获取用户回款金额,精确到小数点后两位,不作四舍五入处理
                $return_money     = getFormatData($calculate_money);
                $yyy_invest_model->beginTransaction();
                ///修改字段,并且还款
                $return_update   = array(
                    'invest_money'   => $return_money,
                    'return_status'  => '已赎回',
                    'tgy_benefit'    => '否'
                );
                $return_where = array(
                    'id' => $invest['id']
                );
                $return_invest_ret = $yyy_invest_model->update($return_update, $return_where);
                ///更新return记录
                $user_return_data = array(
                    'invest_money'  => $return_money,
                    'status'        => '已赎回',
                    'return_time'   => $repeat_start_time
                );
                $user_return_where = array(
                    'invest_id' => $invest['id']
                );
                $user_return_info = $yyy_return_model->update($user_return_data, $user_return_where);
                ///用户回款
                $return_status    = $account_model->repaymentMoney($invest['invest_user_id'], $return_money, 'yyy_cash', '元月盈回款', '', $invest['invest_order_id']);
                ///红包回款
                if ($red_bag_money) {
                    $red_bag_status    = $account_model->repaymentMoney($invest['invest_user_id'], $red_bag_money, 'yyy_cash', '元月盈红包本金', '', $invest['invest_order_id']);
                    if ($red_bag_status != true) {
                        $yyy_invest_model->rollback();
                        echo $invest['invest_user_id'].'红包本金发放失败','<br>';;
                    }
                }
                if ($red_bag_interest) {
                    $red_bag_interest_status    = $account_model->repaymentMoney($invest['invest_user_id'], $red_bag_interest, 'yyy_cash', '元月盈红包收益', '', $invest['invest_order_id']);
                    if ($red_bag_interest_status != true) {
                        $yyy_invest_model->rollback();
                        echo $invest['invest_user_id'].'红包本金发放失败','<br>';;
                    }
                }


                ///用户利息总计数据格式化
                $interest_benifit = round(($return_money - $invest['money']), 2);
                ///用户汇款数据format
                $repay_send_data  = serialize(array(
                    'REAL_NAME'     => $user_info['real_name'],
                    'BORROW_NAME'   => $invest['borrow_name'],
                    'MONEY'         => $return_money,
                    'INVESTMONEY'   => $invest['money'],
                    'INTEREST'      => $interest_benifit
                ));
                if (!empty($return_invest_ret) && !empty($user_return_info) && $return_status === true) {
                    $yyy_invest_model->commit();
                    echo $invest['invest_user_id'] . "回款 success",'<br>';
                    ///发送短信
                    $sms_model->smsAdd($phone_number, 'yyy_repayment_success', $repay_send_data);
                } else {
                    $yyy_invest_model->rollback();
                    echo $invest['invest_user_id'] . "回款 failure",'<br>';
                }
                continue;
            }
        }
        
    }

    /*
     * 某一个标,如果对应的所有的投资记录都已经赎回,修改表的状态
     */
    public function fullReturnInvest(){
        $yyy_borrow_model = M('yyy_borrow');
        $yyy_invest_model = M('yyy_borrow_invest');
        
        ///当前时间
        $currentTime      = getRequestTime(0); 
        ///按元月盈ID分组,查询对应的总的投资本金之和
        $querySql = "SELECT "
                . "A.borrow_id,"
                . "SUM(A.money) as totalMoney "
                . "FROM y1_yyy_borrow_invest as A "
                . "WHERE A.return_status = '已赎回' "
                . "GROUP BY borrow_id";
        $return_list = $yyy_invest_model->queryAll($querySql);
        if (empty($return_list) || !is_array($return_list)) {
            return false;
        }
        foreach ($return_list as $return) {
            $borrow_id   = $return['borrow_id'];
            $borrow_info = $yyy_borrow_model->get_one(array('id' => $borrow_id, 'is_end' => 0), '', 'total_money');
            ///查询对应的标的信息,假如投资额度和所有已赎回的投资记录之和相等,表明该元月盈已结束了
            if (isset($borrow_info['total_money']) && $return['totalMoney'] == $borrow_info['total_money']) {
                $borrow_update_data = array(
                    'end_time' => $currentTime,
                    'is_end' => 1
                );
                $borrow_update_where = array(
                    'id' => $borrow_id
                );
                $yyy_borrow_model->update($borrow_update_data, $borrow_update_where);
            }
        }
    }

}

$yyy_invest_model = new yyy_invest_interest(); 
$yyy_invest_model->calculateInterest();
$yyy_invest_model->stepYyyInterest();
$yyy_invest_model->fullReturnInvest();

