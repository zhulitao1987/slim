<?php

/**
 * 供项目关联公司操作类
 * User: cdf
 * Date: 2016/4/25
 * Time: 13:52
 */
class associated_companyLib extends Lib
{
    /**
     * 添加项目
     */
    public function add()
    {
        $this->__init(-1, "添加项目", "输入项目信息数据，并保存到数据库");
        $this->postRule = [
            'company_name'      => [0, 'string', '公司名称', 'company_name', '深证元立方'],
            'user_name'         => [0, 'string', '元立方平台用户名', 'user_name', '小丽'],
            'id_number'         => [0, 'string', '公司法人身份证号', 'id_number', '3213240987678923176'],
            'introduce'         => [0, 'string', '公司介绍', 'introduce', '介绍内容'],
            'materials_mark'    => [0, 'string', '材料（打码）', 'materials_mark', '图片url'],
            'materials_nomark'  => [0, 'string', '材料（无码）', 'materials_nomark', '图片url'],
            'materials_name'    => [0, 'string', '资料名称', 'materials_name', '担保措施'],
            'company_chapter'   => [0, 'string', '公司章', 'company_chapter', '公司章'],
            'status'            => [0, 'string', '审核状态', 'status', '项目有效'],
            'add_user_id'       => [0, 'num', '添加者用户id号', 'add_user_id', 2024],
            'add_time'          => [0, 'string', '添加时间', 'add_time', '2016-04-26 00:00:00'],
            'add_ip'            => [0, 'string', '添加者ip地址', 'add_ip', '192.168.11.24']
        ];
        parent::insertOneLib(['add_time' => getRequestTime(), 'add_ip' => getIp()]);
    }

    /**
     * 更新项目
     */
    public function update()
    {
        //初始化信息
        $this->__init(-1, "更新项目", "输入项目信息数据，并保存到数据库");
        //需要传递的参数
        $this->postRule = [
            'id'                => [1, 'num', 'ID号', 'id', 12],
            'company_name'      => [0, 'string', '公司名称', 'company_name', '深证元立方'],
            'introduce'         => [0, 'string', '公司介绍', 'introduce', '介绍内容'],
            'materials_mark'    => [0, 'string', '材料（打码）', 'materials_mark', '图片url'],
            'materials_nomark'  => [0, 'string', '材料（无码）', 'materials_nomark', '图片url'],
            'materials_name'    => [0, 'string', '资料名称', 'materials_name', '担保措施'],
            'company_chapter'   => [0, 'string', '公司章', 'company_chapter', '公司章'],
            'status'            => [0, 'string', '审核状态', 'status', '项目有效'],
            'add_user_id'       => [0, 'num', '添加者用户id号', 'add_user_id', 2024],
            'add_time'          => [0, 'string', '添加时间', 'add_time', '2016-04-26 00:00:00'],
            'add_ip'            => [0, 'string', '添加者ip地址', 'add_ip', '192.168.11.24'],
            'audit_user_id'     => [0, 'string', '审核用户id', 'audit_user_id', '12'],
            'reason'            => [0, 'string', '审核失败原因', 'reason', '不符合要求'],
        ];
        $this->resRule = [
            'res' => [1, 'num', "执行是否成功", 'id', 0]
        ];
        parent::updateLib(['add_time' => getRequestTime(), 'add_ip' => getIp()], ['id']);
    }

    /**
     * 查询单条项目信息接口
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
            'id'                => [1, 'num', 'ID号', 'id', 12],
            'company_name'      => [0, 'string', '公司名称', 'company_name', '深证元立方'],
            'introduce'         => [0, 'string', '公司介绍', 'introduce', '介绍内容'],
            'materials_mark'    => [0, 'string', '材料（打码）', 'materials_mark', '图片url'],
            'materials_nomark'  => [0, 'string', '材料（无码）', 'materials_nomark', '图片url'],
            'materials_name'    => [0, 'string', '资料名称', 'materials_name', '担保措施'],
            'status'            => [0, 'string', '审核状态', 'status', '项目有效'],
            'company_chapter'   => [0, 'string', '公司章', 'company_chapter', '公司章'],
            'add_user_id'       => [0, 'num', '添加者用户id号', 'add_user_id', 2024],
            'add_time'          => [0, 'string', '添加时间', 'add_time', '2016-04-26 00:00:00'],
            'add_ip'            => [0, 'string', '添加者ip地址', 'add_ip', '192.168.11.24'],
            'audit_user_id'     => [0, 'string', '审核用户id', 'audit_user_id', '12'],
            'reason'            => [0, 'string', '审核失败原因', 'reason', '不符合要求'],
        ];
        $set = [];
        $where = ['id'];
        $is_new = 0;
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

    /**
     * 查询多条产品信息接口
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条产品信息接口", "成功返回多条产品信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20],
            'status'    => [0, 'string', '审核状态', 'status', '项目有效']
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '项目列表', 'list',
                [
                    'id'                => [1, 'num', 'ID号', 'id', 12],
                    'company_name'      => [0, 'string', '公司名称', 'company_name', '深证元立方'],
                    'introduce'         => [0, 'string', '公司介绍', 'introduce', '介绍内容'],
                    'materials_mark'    => [0, 'string', '材料（打码）', 'materials_mark', '图片url'],
                    'materials_nomark'  => [0, 'string', '材料（无码）', 'materials_nomark', '图片url'],
                    'materials_name'    => [0, 'string', '资料名称', 'materials_name', '担保措施'],
                    'company_chapter'   => [0, 'string', '公司章', 'company_chapter', '公司章'],
                    'status'            => [0, 'string', '审核状态', 'status', '项目有效'],
                    'add_user_id'       => [0, 'num', '添加者用户id号', 'add_user_id', 2024],
                    'add_time'          => [0, 'string', '添加时间', 'add_time', '2016-04-26 00:00:00'],
                    'add_ip'            => [0, 'string', '添加者ip地址', 'add_ip', '192.168.11.24'],
                    'audit_user_id'     => [0, 'string', '审核用户id', 'audit_user_id', '12'],
                    'reason'            => [0, 'string', '审核失败原因', 'reason', '不符合要求'],
                ]
            ]
        ];
        parent::selectListLib(' id desc ');
    }

    /**
     *通过项目信息获取借款|担保|推荐方信息
     */
    public function getCompanyInfo(){
        //初始化信息
        $this->__init(-1, "查询多条信息接口", "成功返回多条产品信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'loan_id'       => [0, 'num', "借款方", 'loan_id', 10],
            'recommend_id'  => [0, 'num', "推荐方", 'recommend_id', 20],
            'guarantee_id'  => [0, 'num', '担保方', 'guarantee_id', 12]
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        $_m = M($this->m);
        $returnInfo = [];
        //借款方
        if ($this->req['loan_id']) {
            $one = $_m->get_one(['id' => $this->req['loan_id']]);
            if ($one) {
                $one['markPics'] = $this->pattenPicUrl($one['materials_mark']);
                $one['noMarkPics'] = $this->pattenPicUrl($one['materials_nomark']);
                $one['materials_names'] = explode('|', $one['materials_name']);
                $one['introduce'] = htmlspecialchars_decode($one['introduce']);
                $returnInfo['loan'] = $one;
            }
        }
        //推荐方
        if ($this->req['recommend_id']) {
            $one = $_m->get_one(['id' => $this->req['recommend_id']]);
            if ($one) {
                $one['markPics'] = $this->pattenPicUrl($one['materials_mark']);
                $one['noMarkPics'] = $this->pattenPicUrl($one['materials_nomark']);
                $one['materials_names'] = explode('|', $one['materials_name']);
                $one['introduce'] = htmlspecialchars_decode($one['introduce']);
                $returnInfo['recommend'] = $one;
            }
        }
        //担保方
        if ($this->req['guarantee_id']) {
            $one = $_m->get_one(['id' => $this->req['guarantee_id']]);
            if ($one) {
                $one['markPics'] = $this->pattenPicUrl($one['materials_mark']);
                $one['noMarkPics'] = $this->pattenPicUrl($one['materials_nomark']);
                $one['materials_names'] = explode('|', $one['materials_name']);
                $one['introduce'] = htmlspecialchars_decode($one['introduce']);
                $returnInfo['guarantee'] = $one;
            }
        }

        if ($returnInfo) {
            outJson(0, $returnInfo);
        }else{
            outJson(-1, '无数据');
        }

    }

    /**
     * 匹配出字符串中的url地址
     * @param $materials
     * @return mixed
     */
    public function pattenPicUrl($materials) {
        $pattern = "/(http:[\/\w\.]+\w\.[a-z]+)/";
        preg_match_all($pattern, $materials, $m);
        return $m[1];
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


}