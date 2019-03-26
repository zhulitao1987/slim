<?php

include_once dirname(__FILE__) . "/../cli.php";
header("Content-type: text/html; charset=utf-8");
set_time_limit(0);

/**
 * 私人专项,预约回款
 *
 * @author yhx
 */
class appoint_do_every_day extends Cli {

    /**
     * @function 预约用户项目回款脚本方法
     */
    public function repaymentMoney() {
        $appoint_invest_model = M('appoint_borrow_invest');
        $red_bag_log_model    = M("red_bag_log");
        $account_model    = M('new_account');
        $sms_model        = M('sms');
        $user_model       = M('user');
        ///请求发起时间
        $request_time     = getRequestTime(0);
        $querySql = "SELECT "
                . "A.id,"
                . "A.user_id,"
                . "A.invest_order_id,"
                . "A.money,"
                . "A.interest_rate,"
                . "A.interest_add,"
                . "A.interest_days, "
                . "B.borrow_name "
                . "FROM y1_appoint_borrow_invest AS A "
                . "LEFT JOIN y1_appoint_borrow AS B "
                . "ON A.borrow_id = B.id "
                . "WHERE A.invest_status = '已放款' "
                . "AND A.interest_end_time < '$request_time';";
        $invest_list = $appoint_invest_model->queryAll($querySql);

        if (empty($invest_list)) {
            echo "没有符合回款的记录！";
        }
        foreach ((array) $invest_list as $invest) {
            if (!isset($invest['id'])) {
                continue;
            }
            ///计算回款金额
            $total_interest      = $invest['interest_rate'] + $invest['interest_add'];
            $original_money      = self::getTotalMoney($invest['money'], $total_interest, $invest['interest_days']);
            $repay_money         = round($original_money, 2);
            ///获取投资记录是否有相对应的红,有的话,将红包的资金返给用户
            $red_bag_log_info    = $red_bag_log_model->get_one(array('borrow_invest_id' => $invest['id'], 'borrow_type' => '私人尊享'));
            $red_bag_money       = isset($red_bag_log_info['money']) && !empty($red_bag_log_info['money']) ? $red_bag_log_info['money'] : 0;
            //// 2017-07-20 红包利息计算*********************** START
            $red_bag_interest    = $red_bag_money ? self::getTotalMoney($red_bag_money, $invest['interest_rate'], $invest['interest_days']) : 0;
            $red_bag_interest    = round($red_bag_interest , 2);
            //// 2017-07-20 红包利息计算*********************** END

            $appoint_invest_model->beginTransaction();
            ///修改字段,并且还款
            $invest_update   = array(
                'invest_status'  => '已还款',
            );
            $invest_where = array(
                'id' => $invest['id']
            );
            $appoint_invest_ret = $appoint_invest_model->update($invest_update, $invest_where);
            ///获取用户真实姓名
            $user_info          = $user_model->get_one(array('id' => $invest['user_id']), '', 'real_name,phone,type,co_mobile');
            ///投资本金回款
            $principal_return_status    = $account_model->repaymentMoney($invest['user_id'], $invest['money'], 'appoint_cash', '预约回款', '', $invest['invest_order_id']);
            ///年化收益利息回款
            $benefit_return_status      = $account_model->repaymentMoney($invest['user_id'], $repay_money, 'appoint_cash', '预约回款', '', $invest['invest_order_id']);
            $repay_send_data            = serialize(array(
                                            'REAL_NAME'     => $user_info['real_name'],
                                            'BORROW_NAME'   => $invest['borrow_name'],
                                            'MONEY'         => $repay_money + $red_bag_money + $invest['money'] + $red_bag_interest
                                        ));
            if ($user_info['type'] == "企业用户" || $user_info['type'] == "企业vip") {
                $sms_phone  =   $user_info["co_mobile"];
            } else {
                $sms_phone  =   $user_info["phone"];
            }
            ///使用了红包的话,红包金额回款
            if (!empty($red_bag_money)) {
                $red_bag_return_status  = $account_model->repaymentMoney($invest['user_id'], $red_bag_money, 'appoint_red_bag_cash', '预约红包本金回款', '', $invest['invest_order_id']);
                if (true !== $red_bag_return_status) {
                    $appoint_invest_model->rollback();
                    continue;
                }
                $red_bag_interest_return_status  = $account_model->repaymentMoney($invest['user_id'], $red_bag_interest, 'appoint_red_bag_cash', '预约红包收益回款', '', $invest['invest_order_id']);
                if (true !== $red_bag_interest_return_status) {
                    $appoint_invest_model->rollback();
                    echo "红包收益还款失败";
                    continue;
                }
            }
            if (!empty($appoint_invest_ret) && $principal_return_status === true && $benefit_return_status === true) {
                $appoint_invest_model->commit();
                echo $invest['user_id'] . "回款 success", '<br>';
                ///发送短信发送短信
                $sms_model->smsAdd($sms_phone, 'appoint_repayment_success', $repay_send_data);
                continue;
            } else {
                $appoint_invest_model->rollback();
                echo $invest['user_id'] . "回款 failure", '<br>';
                continue;
            }
        }
    }
    
    /**
     * @function 根据投资金额,年化收益和投资天数
     * @param int $money           投资金额
     * @param float $interest_rate 年化收益
     * @param int $interest_days   投资天数
     * @return float               投资收益金额
     */
    private static function getTotalMoney($money = 0, $interest_rate = 0.00, $interest_days = 0) {
        return $money * $interest_rate * $interest_days / 36500;
    }

}

$appoint_model = new appoint_do_every_day();
$appoint_model->repaymentMoney();

