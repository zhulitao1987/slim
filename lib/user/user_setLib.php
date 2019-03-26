<?php

/**
 * 用户字段配置
 * 此文件是用来配置一些用户相关常量
 * User: MZH
 * Date: 15/7/9
 * Time: 下午3:22
 */
class infoLib extends Lib
{
    /**
     * @author jxy
     * 查询单条用户信息
     * 根据type和key获取value
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户配置接口", "成功返回单条用户配置信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'user_id', 4]
        ];
        //输出的参数
        $this->resRule = [
            'user_id' => [1, 'num', "用户编号", 'user_id', 4],
            'user_name' => [1, 'string', "用户登陆名", 'user_name', 'a123456'],
            'phone' => [1, 'string', '用户手机号', 'phone', '13685225544']
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
            'page' => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '用户列表', 'list',
                [
                    'user_id' => [1, 'num', "用户编号", 'user_id', 4],
                    'user_name' => [1, 'string', "用户登陆名", 'user_name', 'a123456'],
                    'phone' => [1, 'string', "用户手机号", 'phone', '13685225544']
                ]
            ]
        ];
        parent::selectUserListLib();
    }

}
