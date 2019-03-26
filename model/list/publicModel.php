<?php

/**
 * 用户活动公共类
 */
class publicModel extends Model
{
    /**
     * 用户是否投资过
     * @param $user_id 用户id
     * @return bool
     */
    private function userIsInvest($user_id)
    {
        //获取用户投资次数
        $get_user_invest_one = M("borrow_invest")->get_one(['invest_user_id' => $user_id, ['and', 'status', 'in', ['成功', '已放款', '已还款']]], '', 'count(*) as num');
        if ($get_user_invest_one['num'] > 0) {
            return true;
        } else {
            return false;
        }
    }

}
