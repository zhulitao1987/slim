<?php

/**
 * 提现日志表
 * User: MZH
 * Date: 15/7/10
 * Time: 上午10:53
 */
class cash_import_logLib extends Lib
{

    /**
     * @author jxy
     * 多条提现操作日志查询
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条操作日志接口", "成功返回多条现操作日志信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page' => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '提现操作日志列表', 'list',
                [
                    'id' => [1, 'num', "日志id", 'id', 5],
                    'baofoo_id' => [1, 'num', "宝付订单号", 'baofoo_id', 20470325],
                    'cash_order' => [1, 'string', "提现订单号", 'cash_order', ''],
                    'batch_id' => [1, 'string', "批次号", 'batch_id', ''],
                    'add_time' => [1, 'string', '订单创建时间', 'add_time', ''],
                    'return_time' => [1, 'string', '订单转账返回时间', 'return_time', ''],
                    'do_type' => [1, 'string', '交易类型', 'do_type', ''],
                    'user_name' => [1, 'string', '收款人姓名', 'user_name', ''],
                    'bank_code' => [1, 'string', '银行卡号', 'bank_code', ''],
                    'bank_name' => [1, 'string', '银行名称', 'bank_name', ''],
                    'baofu_remark' => [1, 'string', '宝付备注', 'baofu_remark', ''],
                    'status' => [1, 'string', '订单状态', 'status', ''],
                    'money' => [1, 'string', '订单金额', 'money', ''],
                    'counter_fee' => [1, 'string', '手续费', 'counter_fee', ''],
                    'shanghu_remark' => [1, 'string', '商户备注', 'shanghu_remark', ''],
                    'refund_time' => [1, 'string', '订单转账返回时间', 'refund_time', '']
                ]
            ],
            'total' => [1, 'num', '提现操作日志总额', 'total', 50]
        ];
        parent::selectListLib();
    }
}
