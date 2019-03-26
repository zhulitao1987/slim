<?php

/**
 * 手机用户手机信息
 * author：cdf
 * date:2016-03-16
 */
class phone_infoLib extends Lib
{
    /**
     * 添加一条记录
     */
    public function add()
    {
        //初始化信息
        $this->__init(-1, "添加活动", "成功返回活动id，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'        => [0, 'num', "用户id号", 'user_id', '1002'],
            'phone'          => [1, 'string', "手机号", 'phone', '18117433089'],
            'phone_model'    => [1, 'string', "手机型号", 'phone_model', 'SM-N9008V'],
            'sdk_version'    => [1, 'string', "sdk版本号", 'sdk_version', '21'],
            'system_version' => [1, 'string', "系统版本", 'system_version', '5.0'],
            'phone_type'     => [1, 'string', "手机操作系统类型", 'phone_type', 'ios'],
            'location'       => [0, 'string', "用户位置", 'location', '上海浦东新区昌里路78号'],
            'contact'        => [0, 'string', "常用联系人", 'contact', '18117433018,18119777890,'],
            'add_time'       => [0, 'string', "数据添加时间", 'add_time', '2016-03-16 11:55:03']
        ];
        parent::insertOneLib(["add_time" => getRequestTime()], ['phone', 'phone_model', 'sdk_version', 'system_version', 'phone_type']);
    }

    /**
     * 更新一条记录
     */
    public function update()
    {
        //初始化信息
        $this->__init(-1, "添加活动", "成功返回活动id，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id'             => [1, 'num', "主键id", 'id', 7],
            'user_id'        => [0, 'num', "用户id号", 'user_id', '1002'],
            'phone'          => [0, 'string', "手机号", 'phone', '18117433089'],
            'phone_model'    => [0, 'string', "手机型号", 'phone_model', 'SM-N9008V'],
            'sdk_version'    => [0, 'string', "sdk版本号", 'sdk_version', '21'],
            'system_version' => [0, 'string', "系统版本", 'system_version', '5.0'],
            'phone_type'     => [0, 'string', "手机操作系统类型", 'phone_type', 'andriod'],
            'location'       => [0, 'string', "用户位置", 'location', '上海浦东新区昌里路78号'],
            'contact'        => [0, 'string', "常用联系人", 'contact', '18117433018,18119777890,'],
            'add_time'       => [0, 'string', "数据添加时间", 'add_time', '2016-03-16 11:55:03']
        ];
        parent::updateLib([], ['id']);
    }

    /**
     * @author jxy
     * 获取记录单条
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "添加活动", "成功返回活动id，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id'         => [0, 'num', "主键id", 'id', 7],
            'user_id'    => [0, 'num', "用户id号", 'user_id', '1002'],
            'phone'      => [0, 'string', "手机号", 'phone', '18117433089'],
            'phone_type' => [0, 'string', "手机操作系统类型", 'phone_type', '正常'],
        ];
        $this->resRule = [
            'id'             => [1, 'num', "主键id", 'id', 7],
            'user_id'        => [0, 'num', "用户id号", 'user_id', '1002'],
            'phone'          => [0, 'string', "手机号", 'phone', '18117433089'],
            'phone_model'    => [0, 'string', "手机型号", 'phone_model', 'SM-N9008V'],
            'sdk_version'    => [0, 'string', "sdk版本号", 'sdk_version', '21'],
            'system_version' => [0, 'string', "系统版本", 'system_version', '5.0'],
            'phone_type'     => [0, 'string', "手机操作系统类型", 'phone_type', 'andriod'],
            'location'       => [0, 'string', "用户位置", 'location', '上海浦东新区昌里路78号'],
            'contact'        => [0, 'string', "常用联系人", 'contact', '18117433018,18119777890,'],
            'add_time'       => [0, 'string', "数据添加时间", 'add_time', '2016-03-16 11:55:03']
        ];
        parent::selectOneLib([], ['id']);
    }

    /**
     * @author jxy
     * 获取记录列表
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "添加活动", "成功返回活动id，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id'         => [0, 'num', "主键id", 'id', 7],
            'user_id'    => [0, 'num', "用户id号", 'user_id', '1002'],
            'phone'      => [0, 'string', "手机号", 'phone', '18117433089'],
            'phone_type' => [0, 'string', "手机操作系统类型", 'phone_type', '正常'],
            'page'       => [1, 'num', "页码", 'page', 0],
            'page_size'  => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list'  => [
                1, 'array', '用户列表', 'list',
                [
                    'id'             => [1, 'num', "主键id", 'id', 7],
                    'user_id'        => [0, 'num', "用户id号", 'user_id', '1002'],
                    'phone'          => [0, 'string', "手机号", 'phone', '18117433089'],
                    'phone_model'    => [0, 'string', "手机型号", 'phone_model', 'SM-N9008V'],
                    'sdk_version'    => [0, 'string', "sdk版本号", 'sdk_version', '21'],
                    'system_version' => [0, 'string', "系统版本", 'system_version', '5.0'],
                    'phone_type'     => [0, 'string', "手机操作系统类型", 'phone_type', 'andriod'],
                    'location'       => [0, 'string', "用户位置", 'location', '上海浦东新区昌里路78号'],
                    'contact'        => [0, 'string', "常用联系人", 'contact', '18117433018,18119777890,'],
                    'add_time'       => [0, 'string', "数据添加时间", 'add_time', '2016-03-16 11:55:03']
                ]
            ],
            'total' => [1, 'num', '查询条数', "total", 100]
        ];
        parent::selectListLib('id desc');
    }

}
