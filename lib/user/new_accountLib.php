<?php
/**
 * 用户资金相关接口-易联
 * @author peijk <peijk@yhxbank.com>
 * @date 2015-12-09 11:00:00
 */
class new_accountLib extends Lib
{
    /**
     * 查询一条记录
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "根据用户ID查询用户资金-易联接口", "成功返回用户资金信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'user_id', 878]
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
    }
    
    /**
     * 获取平台总的待收金额
     */
    public function getTotalCollectMoney() {
        //初始化信息
        $this->__init(-1, "获取平台总的待收金额", "返回成功或失败");
        //需要传递的参数
        $this->postRule = [
            'is_request' => [0, 'num', "是否请求API", 'is_request', 0],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $_m = M('new_account');
        $sql = "SELECT SUM(collection_money) as totalCollectMoney FROM y1_new_account";
        $result = $_m->queryOne($sql);
        outJson(0, $result);
    }

}