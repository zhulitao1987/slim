<?php

/**
 * 用户快捷支付
 * @author      peijk@yhxbank.com
 */
class user_fast_cardLib extends Lib
{
    /**
     * @author jxy
     * 用户快捷支付信息添加
     */
    public function add()
    {
        //初始化信息
        $this->__init(-1, "用户注册接口", "这是用户注册接口，成功返回用户ID，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'   => [1, 'num', "用户登录密码", 'user_id', '827'],
            'real_name' => [1, 'string', "用户手机号", 'real_name', '13685225544'],
            'bank_code' => [0, 'string', "用户来源", 'bank_code', 'BOC'],
            'bank_num'  => [0, 'string', "银行卡号码", 'bank_num', '6222123456789000'],
            'channel'   => [0, 'string', "支付方式 QP为快捷支付", 'channel', 'QP'],
        ];
        $this->insertOneLib(['add_time' => getRequestTime()], [['user_id']]);
    }

    /**
     * @author jxy
     * 查询单条用户信息
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户信息接口", "成功返回单条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id'      => [0, 'num', "用户ID", 'id', 1],
            'user_id' => [0, 'num', "用户登录密码", 'user_id', '827'],
        ];
        //输出的参数
        $this->resRule = [
            'id'        => [1, 'num', "用户编号", 'id', 1],
            'user_id'   => [1, 'num', "用户登录密码", 'user_id', '827'],
            'real_name' => [1, 'string', "用户手机号", 'real_name', '13685225544'],
            'bank_code' => [0, 'string', "用户来源", 'bank_code', 'BOC'],
            'bank_num'  => [0, 'string', "银行卡号码", 'bank_num', '6222123456789000'],
            'channel'   => [0, 'string', "支付方式 QP为快捷支付", 'channel', 'QP'],
        ];
        parent::selectOneLib();
    }

    /**
     * @author jxy
     * 查询用户列表
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户信息接口", "成功返回多条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list'  => [
                1, 'array', '用户列表', 'list',
                [
                    'id'        => [1, 'num', "用户编号", 'id', 1],
                    'user_id'   => [1, 'num', "用户登录密码", 'user_id', '827'],
                    'real_name' => [1, 'string', "用户手机号", 'real_name', '13685225544'],
                    'bank_code' => [0, 'string', "用户来源", 'bank_code', 'BOC'],
                    'bank_num'  => [0, 'string', "银行卡号码", 'bank_num', '6222123456789000'],
                    'channel'   => [0, 'string', "支付方式 QP为快捷支付", 'channel', 'QP'],
                ]
            ],
            'total' => [1, 'num', '查询条数', "total", 100]
        ];
        parent::selectUserListLib('id desc');
    }

    /**
     * @author jxy
     * 用户信息修改
     */
    public function update()
    {
        //初始化信息
        $this->__init(-1, "更新用户信息接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'id'        => [1, 'num', "用户编号", 'id', 1],
            'user_id'   => [1, 'num', "用户登录密码", 'user_id', '827'],
            'real_name' => [1, 'string', "用户手机号", 'real_name', '13685225544'],
            'bank_code' => [0, 'string', "用户来源", 'bank_code', 'BOC'],
            'bank_num'  => [0, 'string', "银行卡号码", 'bank_num', '6222123456789000'],
            'channel'   => [0, 'string', "支付方式 QP为快捷支付", 'channel', 'QP'],
        ];
        parent::updateLib([], ['id']);
    }

    /**
     * @author jxy
     * 用户删除
     */
    public function delete()
    {
        //初始化信息
        $this->__init(0, "删除用户信息接口", "返回是否成功删除用户信息");
        //需要传递的参数
        $this->postRule = [
            'id' => [0, 'num', "用户编号", 'id', 1]
        ];
        parent::deleteLib();
    }

}
