<?php

/**
 * 用户提现
 * @author:PJK   2015-07-26  peijk@yhxbank.com
 */
class userModel extends Model
{

    /**
     * 查询用户名
     * @author PJK
     */
    public function getUserInfo($user_id)
    {
        if ($user_id > 0) {
            $_m = M('user');
            $user_info = $_m->get_one(array('id' => $user_id));
            return $user_info;
        }
        return false;
    }

    /**
     * 注册方法,如果有相同数据不插入
     * @param array $insert_list 插入的结果集
     * @param array $judge_list 插入判断的结果集
     */

    public function insertUserOne($insert_list = [], $judge_list = [])
    {
        $return['bool'] = false;
        $get_data = $insert_list;
        if (!checkPhone($get_data['phone'])) {
            $return['msg'] = '手机号格式错误';
            return $return;
        }
        $_m = M('user');
        $insert_id = $_m->insert($get_data);
        if ($insert_id !== false) {
            //生成用户名
            $user_name = 'ylf' . substr($get_data['phone'], 5, 6);
            if ($insert_id < 10)
                $user_name = '0' . $insert_id;
            elseif ($insert_id >= 100)
                $user_name = $user_name . substr($insert_id, -2, 2);
            else
                $user_name = $user_name . $insert_id;
            $user_update = $_m->update(array('user_name' => $user_name), array('id' => $insert_id));
            if ($user_update) {
                $return['bool'] = true;
                $return['msg'] = $user_name;
                $return['uid'] = $insert_id;
            } else {
                $return['msg'] = '添加失败!';
            }
        } else {
            $return['msg'] = '添加失败!';
        }
        return $return;
    }

    /**
     * 判断实名
     * @param $user_info user_id或user数组
     */
    public function checkRealName($user_info){
        if(!is_array($user_info)){
            $user_id = $user_info;
            $user_model = M("user");
            $user_info = $user_model->get_one(['id' => $user_id]);
        }
        if ( $user_info["real_name"] && $user_info['id_number'] && $user_info["type"] != "虚拟账户") {
            return true;
        }
        return false;
    }


}
