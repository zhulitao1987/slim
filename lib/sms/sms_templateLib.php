<?php

/**
 * 短信模板管理接口
 * User: renxf <renxf@yhxbank.com>
 * Date: 2015/7/10 10:32
 */
class sms_templateLib extends Lib
{

    /**
     * @author jxy
     * 根据短信模板ID修改短信模板信息
     */
    public function add()
    {
        //初始化信息
        $this->__init(-1, "查询短信模板信息接口", "成功返回结果，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'title'     => [1, 'string', '短信模板英文名称', 'title', 'register'],
            'name'      => [1, 'string', '短信模板中文名称', 'name', '注册短信模板'],
            'content'   => [1, 'string', "短信模板内容", 'content', '恭喜{USERNAME}注册成功！'],
            'is_limit'  => [1, 'string', "是否限制", 'is_limit', '是'],
            'is_enable' => [0, 'string', "是否开启", 'is_enable', '是']
//            'is_voice'  => [0, 'string', "是否语音", 'is_voice', '否'],
//            'send_num'  => [0, 'num', "是否开启", 'send_num', '0']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $_m = M($this->m);
        $get_data = must_post($this->postRule, $this->req, 1);
        $title=isset($get_data['title'])?$get_data['title']:'';
        $condition['title']=$title;
        $sms_msg=M($this->m)->get_one($condition);
        if(isset($sms_msg) && is_array($sms_msg)){
            outJson(-1, '短信模板英文名称不能重复');
        }
        $insert_id = $_m->insert($get_data);
        if ($insert_id) {
            outJson(0, $insert_id);
        } else {
            outJson(-1, "插入失败");
        }

    }
    /**
     * @author jxy
     * 根据短信模板ID修改短信模板信息
     */
    public function selectOne()
    {
        //初始化信息
        $this->__init(-1, "查询短信模板信息接口", "成功返回结果，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'id' => [1, 'num', "用户ID", 'id', 7]
        ];
        //输出的参数
        $this->resRule = [
            'id'        => [1, 'num', "友情链接ID", 'id', 1],
            'title'     => [0, 'string', '短信模板英文名称', 'title', 'register'],
            'name'      => [0, 'string', '短信模板中文名称', 'name', '注册短信模板'],
            'content'   => [0, 'string', "短信模板内容", 'content', '恭喜{USERNAME}注册成功！'],
            'is_limit'  => [0, 'string', "是否限制", 'is_limit', '是'],
            'is_enable' => [0, 'string', "是否开启", 'is_enable', '是'],
        ];
        parent::selectOneLib();
    }
    /**
     * @author jxy
     * 根据短信模板ID修改短信模板信息
     */
    public function update()
    {
        //初始化信息
        $this->__init(-1, "更新短信模板信息接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'id'        => [1, 'num', "友情链接ID", 'id', 1],
            'title'     => [0, 'string', '短信模板英文名称', 'title', 'register'],
            'name'      => [0, 'string', '短信模板中文名称', 'name', '注册短信模板'],
            'content'   => [0, 'string', "短信模板内容", 'content', '恭喜{USERNAME}注册成功！'],
            'is_limit'  => [0, 'string', "是否限制", 'is_limit', '是'],
            'is_enable' => [0, 'string', "是否开启", 'is_enable', '是'],
        ];
        parent::updateLib([], ['id']);
    }


    /**
     * @author jxy
     * 分页查询短信模板信息
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "分页查询短信模板信息接口", "成功分页返回短信模板信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list'  => [
                1, 'array', '短信模板列表', 'list',
                [
                    'id'        => [1, 'num', "短信模板ID", 'id', 4],
                    'title'     => [1, 'string', '短信模板英文名称', 'title', 'register'],
                    'name'      => [1, 'string', '短信模板中文名称', 'name', '注册短信模板'],
                    'content'   => [1, 'string', "短信模板内容", 'content', '恭喜{USERNAME}注册成功！'],
                    'is_limit'  => [1, 'string', "是否限制", 'is_limit', '是'],
                    'is_enable' => [1, 'string', "是否开启", 'is_enable', '是'],
                ]
            ],
            'total' => [1, 'num', '查询条数', "total", 100]
        ];
        parent::selectListLib('id desc');
    }
}