<?php

/**
 * Created by PhpStorm.
 * Author: wb
 * Date: 2015/7/23
 * Time: 17:09
 */
class projectLib extends Lib
{
    /**
     * 添加项目
     */
    public function add()
    {
        $this->__init(-1, "添加项目", "输入项目信息数据，并保存到数据库");
        $this->postRule = [
            'name'             => [1, 'string', '项目名称', 'name', '项目1'],
            'img'              => [0, 'string', '项目图片', 'img', 'img'],
            'type'             => [0, 'string', '项目类型', 'type', '1'],
            'danbaohan'        => [0, 'string', '担保函图片', 'danbaohan', 'images'],
            'summary'          => [0, 'string', '项目说明', 'summary', 'test 项目'],
            'loan_id'          => [0, 'num', '借款方公司id', 'loan_id', '12'],
            'recommend_id'     => [0, 'num', '推荐方公司id', 'recommend_id', '11'],
            'guarantee_id'     => [0, 'num', '担保方公司id', 'guarantee_id', '9'],
            'measures'         => [0, 'string', '担保措施', 'measures', '担保措施'],
            'repay_from'       => [0, 'string', '还款来源', 'repay_from', 'test 项目'],
            'materials'        => [0, 'string', '相关资料', 'materials', 'images'],
            'materials_nomark' => [0, 'string', '相关资料无打码', 'materials_nomark', 'images'],
            'imgs_name'        => [0, 'string', '图片名称', 'imgs_name', 'images'],
            'status'           => [0, 'string', '项目是否有效', 'status', '项目有效'],
            'add_time'         => [0, 'string', '项目添加', 'add_time', '2015-07-23 00:00:00'],
            'update_time'      => [0, 'string', '项目最后更新时间', 'update_time', '2015-07-23 00:00:00'],
            'safe'             => [0, 'string', '项目安全保障(已废弃)', 'safe', '元立方'],
            'capital_safe'     => [0, 'string', '资金安全(已废弃)', 'capital_safe', '资金安全'],
            'capital'          => [0, 'string', '资金用途(已废弃)', 'capital', '资金用途'],
            'introduced'       => [0, 'string', '融资方介绍(已废弃)', 'introduced', '融资方介绍'],
            'invest_point'     => [0, 'string', '投资亮点(已废弃)', 'invest_point', 'test 项目'],
        ];
//        parent::insertOneLib(['add_time' => getRequestTime(), 'update_time' => getRequestTime(), 'add_ip' => getIp()]);
        $this->insertOne(['add_time' => getRequestTime(), 'update_time' => getRequestTime(), 'add_ip' => getIp()]);
    }

    /**
     * 更新
     */
    public function update()
    {
        $this->__init(-1, "添加项目", "输入项目信息数据，并保存到数据库");
        $this->postRule = [
            'id'               => [1, 'num', '项目ID号', 'id', '5'],
            'name'             => [1, 'string', '项目名称', 'name', '项目1'],
            'type'             => [1, 'string', '项目分类', 'type', '1'],
            'img'              => [0, 'string', '项目图片', 'img', 'img'],
            'danbaohan'        => [0, 'string', '担保函图片', 'danbaohan', 'images'],
            'summary'          => [0, 'string', '项目说明', 'summary', 'test 项目'],
            'loan_id'          => [0, 'num', '借款方公司id', 'loan_id', '12'],
            'recommend_id'     => [0, 'num', '推荐方公司id', 'recommend_id', '11'],
            'guarantee_id'     => [0, 'num', '担保方公司id', 'guarantee_id', '9'],
            'measures'         => [0, 'string', '担保措施', 'measures', '担保措施'],
            'repay_from'       => [0, 'string', '还款来源', 'repay_from', 'test 项目'],
            'materials'        => [0, 'string', '相关资料', 'materials', 'images'],
            'materials_nomark' => [0, 'string', '相关资料无打码', 'materials_nomark', 'images'],
            'imgs_name'        => [0, 'string', '图片名称', 'imgs_name', 'images'],
            'status'           => [0, 'string', '项目是否有效', 'status', '项目有效'],
            'add_time'         => [0, 'string', '项目添加', 'add_time', '2015-07-23 00:00:00'],
            'update_time'      => [0, 'string', '项目最后更新时间', 'update_time', '2015-07-23 00:00:00'],
            'safe'             => [0, 'string', '项目安全保障(已废弃)', 'safe', '元立方'],
            'capital_safe'     => [0, 'string', '资金安全(已废弃)', 'capital_safe', '资金安全'],
            'capital'          => [0, 'string', '资金用途(已废弃)', 'capital', '资金用途'],
            'introduced'       => [0, 'string', '融资方介绍(已废弃)', 'introduced', '融资方介绍'],
            'invest_point'     => [0, 'string', '投资亮点(已废弃)', 'invest_point', 'test 项目'],
        ];
        $this->resRule = [
            'res' => [1, 'num', "执行是否成功", 'id', 0]
        ];

//        outJson(0,json_encode($_POST));
        parent::updateLib(['update_time' => getRequestTime()], ['id']);
    }

    /**
     * 查询一条
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "查询单条项目信息接口", "成功返回单条项目信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id' => [1, 'num', "项目ID", 'id', 1]
        ];
        //输出的参数
        $this->resRule = [
            'id'               => [1, 'num', '项目ID号', 'id', '5'],
            'name'             => [1, 'string', '项目名称', 'name', '项目1'],
            'type'             => [1, 'string', '项目分类', 'type', '1'],
            'img'              => [0, 'string', '项目图片', 'img', 'img'],
            'danbaohan'        => [0, 'string', '担保函图片', 'danbaohan', 'images'],
            'summary'          => [0, 'string', '项目说明', 'summary', 'test 项目'],
            'loan_id'          => [0, 'num', '借款方公司id', 'loan_id', '12'],
            'recommend_id'     => [0, 'num', '推荐方公司id', 'recommend_id', '11'],
            'guarantee_id'     => [0, 'num', '担保方公司id', 'guarantee_id', '9'],
            'measures'         => [0, 'string', '担保措施', 'measures', '担保措施'],
            'repay_from'       => [0, 'string', '还款来源', 'repay_from', 'test 项目'],
            'materials'        => [0, 'string', '相关资料', 'materials', 'images'],
            'materials_nomark' => [0, 'string', '相关资料无打码', 'materials_nomark', 'images'],
            'imgs_name'        => [0, 'string', '图片名称', 'imgs_name', 'images'],
            'status'           => [0, 'string', '项目是否有效', 'status', '项目有效'],
            'add_time'         => [0, 'string', '项目添加', 'add_time', '2015-07-23 00:00:00'],
            'update_time'      => [0, 'string', '项目最后更新时间', 'update_time', '2015-07-23 00:00:00'],
            'safe'             => [0, 'string', '项目安全保障(已废弃)', 'safe', '元立方'],
            'capital_safe'     => [0, 'string', '资金安全(已废弃)', 'capital_safe', '资金安全'],
            'capital'          => [0, 'string', '资金用途(已废弃)', 'capital', '资金用途'],
            'introduced'       => [0, 'string', '融资方介绍(已废弃)', 'introduced', '融资方介绍'],
            'invest_point'     => [0, 'string', '投资亮点(已废弃)', 'invest_point', 'test 项目'],
        ];

        self::selectOneLib([], ['id']);
    }

    /**
     * 查询多条
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条产品信息接口", "成功返回多条产品信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20],
            'status'    => [0, 'string', '项目是否有效', 'status', '项目有效']
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '项目列表', 'list',
                [
                    'id'               => [1, 'num', '项目ID号', 'id', '5'],
                    'name'             => [1, 'string', '项目名称', 'name', '项目1'],
                    'type'             => [1, 'string', '项目分类', 'type', '1'],
                    'img'              => [0, 'string', '项目图片', 'img', 'img'],
                    'danbaohan'        => [0, 'string', '担保函图片', 'danbaohan', 'images'],
                    'summary'          => [0, 'string', '项目说明', 'summary', 'test 项目'],
                    'loan_id'          => [0, 'num', '借款方公司id', 'loan_id', '12'],
                    'recommend_id'     => [0, 'num', '推荐方公司id', 'recommend_id', '11'],
                    'guarantee_id'     => [0, 'num', '担保方公司id', 'guarantee_id', '9'],
                    'measures'         => [0, 'string', '担保措施', 'measures', '担保措施'],
                    'repay_from'       => [0, 'string', '还款来源', 'repay_from', 'test 项目'],
                    'materials'        => [0, 'string', '相关资料', 'materials', 'images'],
                    'materials_nomark' => [0, 'string', '相关资料无打码', 'materials_nomark', 'images'],
                    'imgs_name'        => [0, 'string', '图片名称', 'imgs_name', 'images'],
                    'status'           => [0, 'string', '项目是否有效', 'status', '项目有效'],
                    'add_time'         => [0, 'string', '项目添加', 'add_time', '2015-07-23 00:00:00'],
                    'update_time'      => [0, 'string', '项目最后更新时间', 'update_time', '2015-07-23 00:00:00'],
                    'safe'             => [0, 'string', '项目安全保障(已废弃)', 'safe', '元立方'],
                    'capital_safe'     => [0, 'string', '资金安全(已废弃)', 'capital_safe', '资金安全'],
                    'capital'          => [0, 'string', '资金用途(已废弃)', 'capital', '资金用途'],
                    'introduced'       => [0, 'string', '融资方介绍(已废弃)', 'introduced', '融资方介绍'],
                    'invest_point'     => [0, 'string', '投资亮点(已废弃)', 'invest_point', 'test 项目'],
                ]
            ]
        ];
        parent::selectListLib(' id desc ');
    }

    /**
     * 删除
     */
    public function delete()
    {
        $this->__init(0, "删除项目信息接口", "返回是否成功删除项目信息");
        $this->postRule = [
            'id' => [1, 'num', '项目ID号', 'id', '4'],
        ];
        parent::deleteLib();
    }

    /*
     * 插入方法,如果有相同数据不插入
     * $param array $insert_list 插入的结果集
     * $param array $judge_list 插入判断的结果集
     */
    protected function insertOne($insert_list = [], $judge_list = [])
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        if ($insert_list && $get_data) {
            $get_data = array_merge($get_data, $insert_list);
        }
        $_m = M($this->m);
        foreach ($get_data as $k => $v) {
            $get_data[$k] = $v;//把html实体代码转化为html标签
        }

        $insert_id = $_m->insert($get_data);
        if ($insert_id) {
            outJson(0, $insert_id);
        } else {
            outJson(-1, "插入失败");
        }
    }

    /**
     * 后台项目列表专用，使用视图查询
     */
    public function selectListWithCompany()
    {
        //初始化信息
        $this->__init(-1, "查询多条产品信息接口", "成功返回多条产品信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20],
            'status'    => [0, 'string', '项目是否有效', 'status', '项目有效'],
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '项目列表', 'list',
                [
                    'id'                        => [1, 'num', '项目ID号', 'id', '5'],
                    'name'                      => [1, 'string', '项目名称', 'name', '项目1'],
                    'img'                       => [0, 'string', '项目图片', 'img', 'img'],
                    'danbaohan'                 => [0, 'string', '担保函图片', 'danbaohan', 'images'],
                    'summary'                   => [0, 'string', '项目说明', 'summary', 'test 项目'],
                    'loan_id'                   => [0, 'num', '借款方公司id', 'loan_id', '12'],
                    'recommend_id'              => [0, 'num', '推荐方公司id', 'recommend_id', '11'],
                    'guarantee_id'              => [0, 'num', '担保方公司id', 'guarantee_id', '9'],
                    'loan_company_name'         => [0, 'string', '借款方公司id', 'loan_company_name', '12'],
                    'recommend_company_name'    => [0, 'string', '推荐方公司id', 'recommend_company_name', '11'],
                    'guarantee_company_name'    => [0, 'string', '担保方公司id', 'guarantee_company_name', '9'],
                    'measures'                  => [0, 'string', '担保措施', 'measures', '担保措施'],
                    'repay_from'                => [0, 'string', '还款来源', 'repay_from', 'test 项目'],
                    'materials'                 => [0, 'string', '相关资料', 'materials', 'images'],
                    'materials_nomark'          => [0, 'string', '相关资料无打码', 'materials_nomark', 'images'],
                    'imgs_name'                 => [0, 'string', '图片名称', 'imgs_name', 'images'],
                    'status'                    => [0, 'string', '项目是否有效', 'status', '项目有效'],
                    'add_time'                  => [0, 'string', '项目添加', 'add_time', '2015-07-23 00:00:00'],
                    'update_time'               => [0, 'string', '项目最后更新时间', 'update_time', '2015-07-23 00:00:00'],
                    'safe'                      => [0, 'string', '项目安全保障(已废弃)', 'safe', '元立方'],
                    'capital_safe'              => [0, 'string', '资金安全(已废弃)', 'capital_safe', '资金安全'],
                    'capital'                   => [0, 'string', '资金用途(已废弃)', 'capital', '资金用途'],
                    'introduced'                => [0, 'string', '融资方介绍(已废弃)', 'introduced', '融资方介绍'],
                    'invest_point'              => [0, 'string', '投资亮点(已废弃)', 'invest_point', 'test 项目'],
                ]
            ]
        ];
        $this->m = 'project_company';
        parent::selectListLib(' id desc ');
    }


    /**
     * 查询单条（是否有更新操作）
     * @param  array $set 更新的数组字段
     * @param  array $where 更新的where条件
     * @param  int $is_new 是否获取更新后的数据
     */
    protected function selectOneLib($set = [], $where = [], $is_new = 0)
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $one = $_m->get_one($get_data);
        if ($one) {
            //查看是否做更新操作
            if ($set && $where) {
                $_where = [];
                //获取更新条件
                foreach ($where as $value) {
                    if (isset($one[$value])) {
                        $_where[$value] = $one[$value];
                    }
                }
                if ($_where) {
                    //更新数据
                    $up_result = $_m->update($set, $_where);
                    if (!$up_result) {
                        outJson(-1, '查询时数据更新失败');
                    }
                }
            }
            //是否需要跟新过后的数据
            if ($is_new) {
                $one = $_m->get_one($get_data);
            }
            $one = res_data($one, $this->resRule);
            foreach ($one as $k => $v) {
                $one[$k] = htmlspecialchars_decode($v);//把html实体代码转化为html标签
            }
            outJson(0, $one);
        } else {
            outJson(-1, '没有数据');
        }
    }

}