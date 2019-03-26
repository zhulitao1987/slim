<?php

/**
 * 提现异常
 * User: MZH
 * Date: 15/7/10
 * Time: 上午10:53
 */
class cash_import_exceptionLib extends Lib
{

    /**
     * @author jxy
     * 异常多条查询
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条异常接口", "成功返回多条异常信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page' => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '用户日志列表', 'list',
                [
                    'id' => [1, 'num', "日志id", 'id', 1],
                    'add_time' => [1, 'string', '订单创建时间', 'add_time', ''],
                    'user_name' => [1, 'string', '收款人姓名', 'user_name', ''],
                    'user_mobile' => [1, 'string', '收款人姓名', 'user_mobile', ''],
                    'bank_code' => [1, 'string', '银行卡号', 'bank_code', ''],
                    'bank_name' => [1, 'string', '银行名称', 'bank_name', ''],
                    'money' => [1, 'string', '订单金额', 'money', ''],
                    'counter_fee' => [1, 'string', '手续费', 'counter_fee', ''],
                    'real_money' => [1, 'string', '实际到账金额', 'real_money', ''],
                    'cash_order' => [1, 'string', "提现订单号", 'cash_order', ''],
                    'add_ip' => [1, 'string', "提现申请ip", 'add_ip', ''],
                    'status' => [1, 'string', '订单状态', 'status', '']
                ]
            ],
            'total' => [1, 'num', '异常数据总额', 'total', 50]
        ];
        parent::selectListLib();
    }
}
