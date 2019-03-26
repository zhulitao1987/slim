<?php

/**
 * 用户普通日志表
 * User: MZH
 * Date: 15/7/10
 * Time: 上午10:53
 */
class hd_coin_logLib extends Lib
{
    /**
     * @author jxy
     * 用户多条资金日志查询
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条资金日志接口", "成功返回多条用户资金日志信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'    => [0, 'num', "用户id", 'user_id', 7],
            'type'       => [0, 'string', "资金操作类型", 'type', 'Cash'],
            'page'       => [1, 'num', "页码", 'page', 0],
            'page_size'  => [1, 'num', "显示条数", 'page_size', 20],
            'start_time' => [0, 'string', '开始时间搜索', 'start_time', '2015-05-05 05:05:05'],
            'end_time'   => [0, 'string', '结束时间搜索', 'end_time', '2015-05-05 05:05:05']
        ];

        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $page = isset($get_data['page']) ? $get_data['page'] : 0;
        $page_size = isset($get_data['page_size']) ? $get_data['page_size'] : 0;
        unset($get_data['page']);
        unset($get_data['page_size']);
        $where = array(
            'user_id' => $get_data['user_id'],
        );
        if (isset($get_data['start_time']))
            $where[] = ['AND', 'add_time', '>=', $get_data['start_time']];
        if (isset($get_data['end_time']))
            $where[] = ['AND', 'add_time', '<=', $get_data['end_time']];
        if (isset($get_data['type']))
            $where[] = ['AND', 'type', '=', $get_data['type']];
        $list = $_m->get_list($where, 'id DESC', $page, $page_size);
        $total = $_m->get_total([], 1);
        if ($list) {
            $return = array();
            foreach ($list as $key => $value) {
                $return[$key]['add_time'] = $value['add_time'];
                $return[$key]['remark'] = $value['remark'];
                $return[$key]['coin'] = round($value['coin'], 2);
                $return[$key]['use_coin'] = round($value['use_coin'], 2);
                $return[$key]['frozen_coin'] = round($value['frozen_coin'], 2);
                $return[$key]['collection_coin'] = round($value['collection_coin'], 2);
                $return[$key]['user_id'] = $value['user_id'];
                $return[$key]['type'] = $value['type'];
                $return[$key]['in_or_out'] = $value['in_or_out'];
            }
            $list = ['list' => $return, 'total' => $total];
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }
}
