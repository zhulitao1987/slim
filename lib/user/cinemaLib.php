<?php

/**
 * 影院地推类
 * 此文件程序用来做什么的（详细说明，可选。）。
 * @author      cdf
 */
class cinemaLib extends Lib
{
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
            'code' => [0, 'num', "二维码", 'code', 1001],
            'date' => [0, 'string', "日期", 'date', '0000-00-00']
        ];
        //输出的参数
        $this->resRule = [
            'id'              => [1, 'num', "影院编号", 'id', 7],
            'name'            => [1, 'string', "院线名", 'name', '三林大光明影城'],
            'area'            => [1, 'string', '地区', 'area', '浦东新区'],
            'address'         => [1, 'string', '院线地址', 'address', '浦东新区长青路507'],
            'code'            => [1, 'string', '二维码', 'code', '1001'],
            'reg_num'         => [1, 'num', '注册人数', 'reg_num', '1'],
            'real_name_num'   => [1, 'num', '实名人数', 'real_name_num', '1'],
            'invest_num'      => [1, 'num', '投资人数', 'invest_num', '1'],
            'invest_money'    => [1, 'num', '投资金额', 'invest_money', '1'],
            'recive_gift_num' => [1, 'num', '接受礼包人数', 'recive_gift_num', '1'],
            'flow_succ_num'   => [1, 'num', '流量充值成功人数', 'flow_succ_num', '1'],
            'flow_fail_num'   => [1, 'num', '流量充值失败人数', 'flow_fail_num', '1'],
        ];
//        parent::selectOneAndOtherLib(['code:cinema->code:cinema_record']);
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);

        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
//        parent::selectOneLib();
        $_m = M($this->m);
        $return = $_m->getCinemaInfo($get_data);
        if ($return !== false) {
            outJson(0, $return);
        } else {
            outJson(-1, '添加失败');
        }
    }

    /**
     * @author jxy
     * 影院地推记录信息修改
     */
    public function updateCinemaRecord()
    {
        //初始化信息
        $this->__init(-1, "更新用户信息接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'id'              => [1, 'num', "用户编号", 'id', 7],
            'code'            => [0, 'string', '二维码', 'code', '1001'],
            'reg_num'         => [0, 'num', '注册人数', 'reg_num', '1'],
            'real_name_num'   => [0, 'num', '实名人数', 'real_name_num', '1'],
            'invest_num'      => [0, 'num', '投资人数', 'invest_num', '1'],
            'invest_money'    => [0, 'num', '投资金额', 'invest_money', '1'],
            'recive_gift_num' => [0, 'num', '接受礼包人数', 'recive_gift_num', '1'],
            'flow_succ_num'   => [0, 'num', '流量充值成功人数', 'flow_succ_num', '1'],
            'flow_fail_num'   => [0, 'num', '流量充值失败人数', 'flow_fail_num', '1'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);

        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $where = array('id' => $get_data['id']);
        unset($get_data['id']);
        $return = $_m->updateCinemaRecord($get_data, $where);
        if ($return !== false) {
            outJson(0, $return);
        } else {
            outJson(-1, '添加失败');
        }
    }

    /**
     * @author jxy
     * 影院地推记录信息插入
     */
    public function addCinemaRecord()
    {
        //初始化信息
        $this->__init(-1, "更新用户信息接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'code'            => [0, 'string', '二维码', 'code', '1001'],
            'reg_num'         => [0, 'num', '注册人数', 'reg_num', '1'],
            'real_name_num'   => [0, 'num', '实名人数', 'real_name_num', '1'],
            'invest_num'      => [0, 'num', '投资人数', 'invest_num', '1'],
            'invest_money'    => [0, 'num', '投资金额', 'invest_money', '1'],
            'recive_gift_num' => [0, 'num', '接受礼包人数', 'recive_gift_num', '1'],
            'flow_succ_num'   => [0, 'num', '流量充值成功人数', 'flow_succ_num', '1'],
            'flow_fail_num'   => [0, 'num', '流量充值失败人数', 'flow_fail_num', '1'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);

        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $get_data['date'] = date('Y-m-d');
        $return = $_m->addCinemaRecord($get_data);
        if ($return !== false) {
            outJson(0, $return);
        } else {
            outJson(-1, '添加失败');
        }
    }

    /**
     * @author jxy
     * 查询单条用户信息
     */
    public function getFlowLog()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户信息接口", "成功返回单条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id'      => [0, 'num', "影院编号", 'id', 7],
            'user_id' => [0, 'num', "二维码", 'user_id', 1001],
            'code'    => [0, 'string', '二维码', 'code', '1001']
        ];
        //输出的参数
        $this->resRule = [
            'id'         => [1, 'num', "影院编号", 'id', 7],
            'user_id'    => [1, 'num', "影院编号", 'user_id', 7],
            'name'       => [1, 'string', "院线名", 'name', '三林大光明影城'],
            'short_name' => [1, 'string', "院线名", 'short_name', '大光明'],
            'area'       => [1, 'string', '地区', 'area', '浦东新区'],
            'code'       => [1, 'string', '二维码', 'code', '1001'],
            'time'       => [1, 'string', '二维码', 'time', '1001'],
        ];
//        parent::selectOneAndOtherLib(['code:cinema->code:cinema_record']);
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);

        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
//        parent::selectOneLib();
        $_m = M($this->m);
        $return = $_m->getFlowLog($get_data);
        if ($return !== false) {
            outJson(0, $return);
        } else {
            outJson(-1, '添加失败');
        }
    }

  /**
   * @author jxy
   * 影院地推流量充值失败记录
   */
    public function addFlowLog()
    {

        //初始化信息
        $this->__init(-1, "更新用户信息接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [0, 'num', '二维码', 'user_id', '1001'],
            'name'       => [0, 'string', "院线名", 'name', '三林大光明影城'],
            'short_name' => [0, 'string', '院线点位,简称', 'short_name', '1001'],
            'area'       => [0, 'string', '地区', 'area', '浦东新区'],
            'code'       => [0, 'string', '二维码', 'code', '1001'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);

        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $get_data['time'] = date('Y-m-d H:i:s');
        $return = $_m->addFlowLog($get_data);
        if ($return !== false) {
            outJson(0, $return);
        } else {
            outJson(-1, '添加失败');
        }
    }


}
