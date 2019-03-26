<?php
/**
 * 用户资金相关接口
 * @author renxf <renxf@yhxbank.com>
 * @date 2015-07-22 11:00:00
 */
class accountLib extends Lib
{
    /**
     * 查询一条记录
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "根据用户ID查询用户资金接口", "成功返回用户资金信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'user_id', 1]
        ];
        //输出的参数
        $this->resRule = [
            'user_id' => [1, 'num', "用户编号", 'user_id', 1],
            'total_money' => [1, 'string', "总金额", 'total_money', '1300.00'],
            'use_money' => [1, 'string', '可用金额', 'use_money', '1000.00'],
            'frozen_money' => [1, 'string', '冻结金额', 'frozen_money', '100.00'],
            'collection_money' => [1, 'string', '待收金额', 'collection_money', '200.00'],
            'recharge_money' => [1, 'string', '充值金额', 'recharge_money', '1300.00'],
            'repayment_money' => [1, 'string', '还款金额', 'repayment_money', '0.00'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $one = $_m->get_one($get_data);
        if (!$one) {
            $one = array(
                'user_id' => $get_data['user_id'],
                'total_money' => '0.00',
                'use_money' => '0.00',
                'frozen_money' => '0.00',
                'collection_money' => '0.00',
                'recharge_money' => '0.00',
                'repayment_money' => '0.00',
            );

        }
        outJson(0, $one);
//        parent::selectOneLib();
    }
    
    /**
     * @function:获得投资总额 含虚拟账户的投资额度
     * @author: xuwb@yhxbank.com
     * @date: 2016-08-05
     * @retunr: 投资返回的总金额
     */
    public function totalInvestment ()
    {
    	//初始化信息
    	$this->__init(-1, "获得投资总额 含虚拟账户的投资额度", "返回成功或失败");
    	//需要传递的参数
    	$this->postRule = [
    			'user_id' => [0, 'num', "用户编号(无实质意义，随意传值)", 'user_id', 7],
    			];
    	//是否显示post参数或者res参数
    	_show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        $get_data   =  must_post($this->postRule, $this->req, 1);
        //过滤参数，查看是否传递必须的数据
    	$_m             =   M('borrow_invest');
    	$yyy_m          =   M('yyy_borrow_invest');
    	$vip_m          =   M('vip_borrow_invest');
        $appoint_m      =   M('appint_borrow_invest');
        $wdy_m          =   M('wdy_borrow_invest');

    	$sql            =   "SELECT sum(money) as money FROM y1_borrow_invest";             ///元政盈
    	$yyy_sql        =   "SELECT sum(money) as money FROM y1_yyy_borrow_invest";         ///元月盈
    	$vip_sql        =   "SELECT sum(money) as money FROM y1_vip_borrow_invest";         ///VIP
    	$appoint_sql    =   "SELECT sum(money) as money FROM y1_appoint_borrow_invest";     ///私人尊享
        $wdy_sql        =   "SELECT sum(show_money) as money FROM y1_wdy_borrow_invest";    ///薪盈计划

    	$result         =   $_m -> queryOne($sql);
    	$yyy_result     =   $yyy_m -> queryOne($yyy_sql);
    	$vip_result     =   $vip_m -> queryOne($vip_sql);
        $appoint_result =   $appoint_m -> queryOne($appoint_sql);
        $wdy_result     =   $wdy_m -> queryOne($wdy_sql);
        $temp_count     =   $result['money'] + $yyy_result['money'] + $vip_result['money'] + $appoint_result['money'] + $wdy_result['money'];
    	$count          =   $temp_count + 2683200; //根据领导要求，在原有基础上加268.32w
    	outJson( 0 , $count );
    }
}