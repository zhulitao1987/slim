<?php

/**
 * 用户普通日志表
 * User: MZH
 * Date: 15/7/10
 * Time: 上午10:53
 */
class user_logLib extends Lib
{

    /**
     * @author jxy
     * 用户日志添加
     */
    public function add()
    {
        $this->__init(-1, '用户日志添加', '用户增加日志，成功返回日志id，失败返回错误记录');
        $this->postRule = [
            'user_id' => [1, 'num', '用户id', 'user_id', 7],
            'remark' => [1, 'string', '用户日志信息', 'remark', '用户XXX修改了密码']
        ];
        parent::insertOneLib(['create_time' => getRequestTime(), 'create_ip' => getIp()], ['user_id', 'remark']);
    }

    /**
     * @author jxy
     * 用户多条操作日志查询
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户日志接口", "成功返回多条用户日志信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [0, 'num', "用户id", 'user_id', 7],
            'page' => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '用户日志列表', 'list',
                [
                    'id' => [1, 'num', "日志id", 'id', 7],
                    'user_id' => [1, 'num', "用户编号", 'user_id', 7],
                    'add_time' => [1, 'string', "用户操作时间", 'add_time', '2015-05-05 05:05:05'],
                    'add_ip' => [1, 'string', "用户操作ip", 'add_ip', '127.0.0.1'],
                    'remark' => [1, 'string', '日志信息', 'remark', '用户XXX修改了密码']
                ]
            ]
        ];
        parent::selectListLib();
    }
}
