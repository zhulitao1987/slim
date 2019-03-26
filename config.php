<?php
include_once 'where.php';
# memcached
defined("API_DIR") ? "" : define("API_DIR", dirname(__FILE__) . "/");
defined("HF_PREFIX") ? "" : define("HF_PREFIX", "yxzc_");


class CONFIG
{
    //api端口，没设置值以及方法或者设置-1以及方法，代表公共！设置0以及方法，代表私有，因为后期可能加入根据rank调用不同的方法
    static public function IP_LIST()
    {
        return [
            '58.247.45.102' => ['token' => 'test', 'rank' => [0]]
        ];
    }

    /*
     * 记录公司所有的资金账户
     */
    static public function fundAccount()
    {
        if (WHERE_SERVER == "server") {
            //正式环境
            return [
                "资金收益"  => ["type" => "repayment", "new_type" => "new_account", "out_cust_id" => '6000060073665709', "out_acct_id" => "MDT000001", "replace_user_id" => 397],
//                "资金收益"  => ["type" => "repayment", "new_type" => "new_account", "out_cust_id" => '6000060026167256', "out_acct_id" => "MDT000001", "replace_user_id" => 397],
                "资金本金"  => ["type" => "repayment", "new_type" => "new_account", "out_cust_id" => '', "out_acct_id" => ""],
                "推荐收益"  => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000002"],
                "红包收益"  => ["type" => "transfer", "new_type" => "coin", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000008"],
                "红包本金"  => ["type" => "transfer", "new_type" => "coin", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000008"],
                "元金币收益"  => ["type" => "transfer", "new_type" => "yjb", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "元金币本金"  => ["type" => "transfer", "new_type" => "yjb", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "现金券本金" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000002"],
                "现金券收益" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000002"],
                "体验金收益" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000010"],
                "充值手续费" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000003"],
                "提现手续费" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000004"],
                "提现服务费" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060001885152', "out_acct_id" => "MDT000006"]
            ];
        } else {
            //测试环境
            return [
                "资金收益"  => ["type" => "repayment", "new_type" => "new_account", "out_cust_id" => '', "out_acct_id" => ""],
                "资金本金"  => ["type" => "repayment", "new_type" => "new_account", "out_cust_id" => '', "out_acct_id" => ""],
                "推荐收益"  => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "红包收益"  => ["type" => "transfer", "new_type" => "coin", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "红包本金"  => ["type" => "transfer", "new_type" => "coin", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "元金币收益"  => ["type" => "transfer", "new_type" => "yjb", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "元金币本金"  => ["type" => "transfer", "new_type" => "yjb", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "现金券本金" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "现金券收益" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "体验金收益" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "充值手续费" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "提现手续费" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
                "提现服务费" => ["type" => "transfer", "new_type" => "new_account", "out_cust_id" => '6000060000205282', "out_acct_id" => "MDT000001"],
            ];
        }
    }

    /**
     * 大转盘奖品配置
     * @author: cdf@yhxbank.com
     * @date: 2016-05-10
     * @return array
     */
    static public function dzpPrize()
    {
        return [
            "Iphone6s"    => ["prize" => "Iphone6s" , "position_id" => 2, "num" => 1,  "rate" => 1, 'perPrice' => 5288, 'isInvest' => 1, 'start_time' => '2016-05-11 00:00:00', 'end_time' => '2016-06-15 23:59:59'],
            "Ipad mini2"  => ["prize" => "Ipad mini2", "position_id" => 5, "num" => 2, "rate" => 10, 'perPrice' => 1988, 'isInvest' => 1, 'start_time' => '2016-05-11 00:00:00', 'end_time' => '2016-06-15 23:59:59'],
            "年费视频会员"=> ["prize" => "年费视频会员", "position_id" => 3, "num" => 10, "rate" => 100, 'perPrice' => 200, 'isInvest' => 1],
            "50元话费"    => ["prize" => "50元话费", "position_id" => 1, "num" => 30, "rate" => 600, 'perPrice' => 50, 'isInvest' => 1],
            "20元话费"    => ["prize" => "20元话费", "position_id" => 4, "num" => 100, "rate" => 789, 'perPrice' => 20, 'isInvest' => 0],
            "5元话费"     => ["prize" => "5元话费", "position_id" => 6, "num" => 800, "rate" => 8500, 'perPrice' => 5, 'isInvest' => 0]
        ];
    }


    /**
     * 九月活动抽奖
     * @author: cdf@yhxbank.com
     * @date: 2016-05-10
     * @return array
     */
    static public function lotteryDraw()
    {
        return [
            "10元现金"       => ["prize" => "10元现金" ,  "id" => 1,  "rate" => 8, "num" => 80, 'perPrice' => 10],
            "660元投资红包"  => ["prize" => "660元投资红包",  "id" => 2, "rate" => 90, "num" => 900, 'perPrice' => 660],
            "500M流量"      =>  ["prize" => "500M流量", "id" => 3, "rate" => 2, "num" => 20, 'perPrice' => 500],
        ];
    }


    /**
     * 双11微信活动抽奖
     * @author: yz
     * @date: 2017-11-11
     * @return array
     */
    static public function wxLotteryDraw()
    {
        return [
            "10元现金"         => ["prize" => "10元现金" ,  "id" => 1,  "rate" => 8, "num" => 80, 'perPrice' => 10],
            "660元投资红包"    => ["prize" => "660元投资红包",  "id" => 2, "rate" => 90, "num" => 900, 'perPrice' => 660],
            "优酷VIP会员"      =>  ["prize" => "优酷VIP会员", "id" => 3, "rate" => 2, "num" => 20, 'perPrice' => 30],
        ];
    }

    /**
     * @author: zhult@yhxbank.com
     * @date: 2016-11-02
     * @return array
     */
    static public function yyyStepTime() {
        return [
            'yyy_step_interest_time' => '2016-11-15 18:50:00'
        ];
    }

    /**
     * @author  xuwb@yhxbank.com
     * @date 2017-08-17
     */
    static public function wqhExtensionCode() {
        return [
            'extension_code' => 'B52AD152'
        ];
    }

    /**
     * @author xuwb@yhxbank.com
     * @date   2017-11-22 11:03
     */
    static public function yqyExtensionCode() {
        return [
            "fym_extension_code" => "D4F37000"
        ];
    }
}

