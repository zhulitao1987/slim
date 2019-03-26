<?php

/**
 * 用户扩展信息类
 * 存放用户的扩展信息，实名认证等信息
 * @author      MZH
 */
class user_infoLib extends Lib
{

    /**
     * @author jxy
     * 用户扩展信息添加
     */
    public function add()
    {
        //初始化信息
        $this->__init(-1, "用户扩展信息接口", "这是用户扩展接口，成功返回用户扩展信息ID，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户id", 'user_id', '7'],
            'real_name' => [1, 'string', "用户真实姓名", 'real_name', '马志辉'],
            'id_number' => [1, 'string', "用户身份证号", 'id_number', '370523199010155513'],
//            'sex' => [1, 'num', "用户性别", 'sex', '1'],
//            'birthday' => [1, 'string', "用户出生日期", 'birthday', '2015-05-05 05:05:05'],
        ];
//        $sexSign =
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $get_data['birthday'] = substr($get_data['id_number'], 6, 4).'-'.substr($get_data['id_number'], 10, 2).'-'.substr($get_data['id_number'], 12, 2).' 00:00:00';
        if(substr($get_data['id_number'], 16,1) % 2 > 0) {
            $userSetInfo = M('user_set')->get_one(array('type' => 'sex', 'key' => 'MAN'), '', 'value');
        }
        else{
            $userSetInfo = M('user_set')->get_one(array('type' => 'sex', 'key' => 'WOMEN'), '', 'value');
        }
        $get_data['sex']  = $userSetInfo['value'];
        $_m = M($this->m);
        $insert_id = $_m->insert($get_data);
        if ($insert_id !== false) {
            outJson(0, $insert_id);
        } else {
            outJson(-1, '添加失败');
        }
    }

    /**
     * @author jxy
     * 查询单条用户扩展信息
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户扩展信息接口", "成功返回单条用户扩展信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'user_id', 7]
        ];
        //输出的参数
        $this->resRule = [
            'user_id' => [1, 'num', "用户id", 'user_id', '7'],
            'real_name' => [1, 'string', "用户真实姓名", 'real_name', '马志辉'],
            'id_number' => [1, 'string', "用户身份证号", 'id_number', '370523199010155513'],
            'sex' => [1, 'num', "用户性别", 'sex', '1'],
            'birthday' => [1, 'string', "用户出生日期", 'birthday', '2015-05-05 05:05:05'],
        ];
        parent::selectOneLib();
    }

    /**
     * @author jxy
     * 查询单条用户扩展信息
     */
    public function selectOne_0_0_1()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户扩展信息接口", "成功返回单条用户扩展信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'user_id', 7]
        ];
        //输出的参数
        $this->resRule = [
            'user_id' => [1, 'num', "用户id", 'user_id', '8'],
            'real_name' => [1, 'string', "用户真实姓名", 'real_name', '马志辉'],
            'id_number' => [1, 'string', "用户身份证号", 'id_number', '370523199010155513'],
            'sex' => [1, 'num', "用户性别", 'sex', '1'],
            'birthday' => [1, 'string', "用户出生日期", 'birthday', '2015-05-05 05:05:05'],
        ];
        parent::selectOneLib();
    }

    /**
     * @author jxy
     * 查询用户扩展信息列表
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户扩展信息接口", "成功返回多条用户扩展信息，失败返回错误信息");
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
                    'user_id' => [1, 'num', "用户id", 'user_id', '7'],
                    'real_name' => [1, 'string', "用户真实姓名", 'real_name', '马志辉'],
                    'id_number' => [1, 'string', "用户身份证号", 'id_number', '370523199010155513'],
                    'sex' => [1, 'num', "用户性别", 'sex', '1'],
                    'birthday' => [1, 'string', "用户出生日期", 'birthday', '2015-05-05 05:05:05'],
                ]
            ]
        ];
        parent::selectUserListLib();
    }

    /**
     * @author jxy
     * 用户扩展信息修改
     */
    public function update()
    {
        //初始化信息
        $this->__init(-1, "更新用户扩展信息接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户id", 'user_id', '7'],
            'real_name' => [1, 'string', "用户真实姓名", 'real_name', '马志辉'],
            'id_number' => [1, 'string', "用户身份证号", 'id_number', '370523199010155513'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 0);
        $_m = M($this->m);
        $where = ['user_id'=>$get_data['user_id']];
        $get_data['birthday'] = substr($get_data['id_number'], 6, 4).'-'.substr($get_data['id_number'], 10, 2).'-'.substr($get_data['id_number'], 12, 2).' 00:00:00';
        if(substr($get_data['id_number'], 16,1) % 2 > 0)
            $get_data['sex']  = 2;
        else
            $get_data['sex']  = 1;
        unset($get_data['user_id']);
        $up_num = $_m->update($get_data, $where);
        if ($up_num) {
            //$up_num = res_data(['up_num' => $up_num], $this->resRule);
            outJson(0, $up_num);
        } else {
            outJson(-1, '更新失败');
        }
        parent::updateLib([], ['id']);
    }

    /**
     * @author jxy
     * 用户删除
     */
    public function delete()
    {
        //初始化信息
        $this->__init(0, "删除用户扩展信息接口", "返回是否成功删除用户信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户编号", 'user_id', 1]
        ];
        parent::deleteLib();
    }
}
