<?php

/**
 * 用户元金币相关接口
 * BY LHD 2015-08-19
 */
class hd_coinLib extends Lib
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
            'user_id'         => [1, 'num', "用户编号", 'user_id', 1],
            'total_coin'      => [1, 'string', "总元金币", 'total_coin', '1300.00'],
            'use_coin'        => [1, 'string', '可用元金币', 'use_coin', '1000.00'],
            'frozen_coin'     => [1, 'string', '冻结元金币', 'frozen_coin', '100.00'],
            'collection_coin' => [1, 'string', '待收元金币', 'collection_coin', '200.00'],
            'draw_num'        => [0, 'string', '抽奖次数', 'draw_num', '20'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $one = $_m->get_one($get_data);
        if (!$one) {
            $one = array(
                'user_id'         => $get_data['user_id'],
                'total_coin'      => '0.00',
                'use_coin'        => '0.00',
                'frozen_coin'     => '0.00',
                'collection_coin' => '0.00',
                'draw_num'        => '0'
            );

        }
        outJson(0, $one);
    }

    /**
     * @author jxy
     * 双12送元金币活动
     */
    public function addDouble12()
    {
        //初始化信息
        $this->__init(-1, "根据用户ID增加双11-50元金币活动接口", "成功返回成功信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'  => [1, 'num', "用户ID", 'user_id', 1],
            'use_coin' => [1, 'num', "新增元金币", 'use_coin', 1]
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);

        $active_info = M("active")->get_one(['active_title' => 'TYJ_03']);
        if (isset($active_info)) {
            if (strtotime($active_info['start_time']) > getRequestTime(1)) {
                outJson(-1, '活动未开始');
            }
            if (strtotime($active_info['end_time']) < getRequestTime(1)) {
                outJson(-1, '活动已结束');
            }
        } else {
            outJson(-1, '活动不存在');
        }
        $user_info = M("user")->getUserInfo($get_data['user_id']);
        if (!isset($user_info)) {
            outJson(-1, '对不起,该手机号尚未注册,请先注册');
        }
        if ($user_info['reg_time'] < $active_info['start_time']) {
            outJson(-1, '玩游戏送元金币活动只对新用户开放');
        }
        $list_data = array(
            'activity_name' => 'double12',
            'phone'         => $user_info['phone'],
            'receive'       => 1,
            ['AND', 'create_time', '>=', $active_info['start_time']],
            ['AND', 'create_time', '<=', $active_info['end_time']]
        );
        $activity_list = M("activity")->get_list($list_data);
        if (count($activity_list) > 3) {
            outJson(1, "最多只能玩3次");
        }
        //先判断活动期间有没有领取过元金币
        $insert_data = array(
            'activity_name' => 'double12',
            'phone'         => $user_info['phone'],
            'receive'       => 1,
            'create_time'   => date('Y-m-d H:i:s')
        );
        M('activity')->addActivity($insert_data);
        M("hd_coin")->rechargeCoin($get_data['user_id'], $get_data['use_coin'], "Recharge", "双12送", "", "", date("Y-m-d 00:00:00", getRequestTime(1) + 366 * 24 * 3600));
        outJson(0, "恭喜您成功领取{$get_data['use_coin']}元金币！");

    }

    /**
     * @author jxy
     * 双11送元金币活动
     */
    public function addDouble11()
    {
        //初始化信息
        $this->__init(-1, "根据用户ID增加双11-50元金币活动接口", "成功返回成功信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'user_id', 1]
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $now = date('Y-m-d H:i:s');
        $now_date = date('Y-m-d');
        $start = '2015-11-10 00:00:00';
        $end = '2015-11-12 23:59:59';

        if ($now >= $start && $now <= $end) {
            $user_info = M("user")->getUserInfo($get_data['user_id']);
            if (!isset($user_info)) {
                outJson(-1, '对不起,该手机号尚未注册,请先注册');
            }

            if (isset($user_info['reg_time']) && $user_info['reg_time'] >= $start) {
                outJson(-1, '对不起,该活动只针对老用户');
            }

            //先判断活动期间有没有领取过元金币
            $user_data = [
                'activity_name' => 'double11',
                'phone'         => $user_info['phone'],
                'receive'       => 1,
                ['AND', 'create_time', '>=', $start],
                ['AND', 'create_time', '<=', $end]
            ];
            $activity = M("activity")->get_one($user_data);

            if (isset($activity) && $activity['id'] > 0) {
                outJson(-1, '对不起,您已经领取过奖品了！');
            }

            //判断今天有没有领取过元金币
            $today_user_data = [
                'activity_name' => 'double11',
                'phone'         => $user_info['phone'],
                ['AND', 'create_time', '>=', $now_date . ' 00:00:00'],
                ['AND', 'create_time', '<=', $now_date . ' 23:59:59']
            ];
            $activity = M("activity")->get_one($today_user_data);

            if (isset($activity) && $activity['id'] > 0) {
                outJson(-1, '对不起,您今日已经参加过活动了！');
            }

            $list_data = array(
                'activity_name' => 'double11',
                'receive'       => 1,
                ['AND', 'create_time', '>=', $now_date . ' 00:00:00'],
                ['AND', 'create_time', '<=', $now_date . ' 23:59:59']
            );
            $activity_list = M("activity")->get_list($list_data);

            if (count($activity_list) >= 100) {
                $insert_data = array(
                    'phone'         => $user_info['phone'],
                    'activity_name' => 'double11',
                    'create_time'   => date('Y-m-d H:i:s'),
                    'receive'       => 0,
                );
                M('activity')->addActivity($insert_data);
                outJson(-1, '对不起,今日奖品已经领完！');
            }

            $insert_data = array(
                'activity_name' => 'double11',
                'phone'         => $user_info['phone'],
                'receive'       => 1,
                'create_time'   => date('Y-m-d H:i:s')
            );
            M('activity')->addActivity($insert_data);
            M("hd_coin")->rechargeCoin($get_data['user_id'], 50, "Recharge", "双11送", "", "", date("Y-m-d 00:00:00", getRequestTime(1) + 366 * 24 * 3600));
            outJson(0, '恭喜您成功领取50元金币！');
        } else {
            outJson(-1, '对不起,您不在双11活动范围内!');
        }
    }
}