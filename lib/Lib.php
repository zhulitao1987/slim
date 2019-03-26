<?php
header("Content-type: text/html; charset=utf-8");
define("LIB_DIR", dirname(__FILE__) . "/");

/**
 * 控制器基础类
 * 此文件程序用来做什么的（详细说明，可选。）。
 * @author      LHD
 */
class Lib
{
    protected $req = ''; //post传递参数
    protected $funRank = "-1"; //方法调用级别
    protected $apiExplain = "这是接口中文名称"; //这是接口中文名称
    protected $postRule = []; //需要传递的参数
    protected $resRule = []; //输出的参数
    protected $resExplain = "这是接口详细描述"; //这是接口详细描述
    protected $cacheName = ""; //缓存的名称
    protected $cacheTime = 1800; //缓存的时间
    protected $m = ''; //方法名称

    public function __construct()
    {
        $this->req = $_POST;
        if (!$this->m) {
            $this->m = str_replace('Lib', '', get_class($this));
        }
    }

    /*
     * 初始化部分数据
     * $param int $fun_rank 调用级别以及判断
     * $param string $api_explain 这是方法说明
     * $param string $res_explain 这是返回值说明
     */
    protected function __init($fun_rank = -1, $api_explain = '', $res_explain = '')
    {
        //方法调用级别
        $this->funRank = $fun_rank;
        //调用级别判断,-1代表公共方法
        api_rank($fun_rank);
        //接口名称
        $this->apiExplain = $api_explain;
        //接口详细描述
        $this->resExplain = $res_explain;
    }

    /*
     * 插入方法
     * $param array $insert_list 插入的结果集
     */
    protected function insertLib($insert_list = [])
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        if ($insert_list && $get_data) {
            $get_data = array_merge($get_data, $insert_list);
        }
        $_m = M($this->m);
        $insert_id = $_m->insert($get_data);
        if ($insert_id !== false) {
            outJson(0, $insert_id);
        } else {
            outJson(-1, '添加失败');
        }
    }

    /*
     * 插入方法,如果有相同数据不插入
     * $param array $insert_list 插入的结果集
     * $param array $judge_list 插入判断的结果集
     */
    protected function insertOneLib($insert_list = [], $judge_list = [])
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        if ($insert_list && $get_data) {
            $get_data = array_merge($get_data, $insert_list);
        }
        $_m = M($this->m);
        //需要判断是否更新或退出
        if ($judge_list) {
            $_str = $this->judge($get_data, $judge_list, $_m);
            if ($_str !== true) {
                outJson(-1, "数据重复，不能添加");
            }
        }
        $insert_id = $_m->insert($get_data);
        if ($insert_id) {
            outJson(0, $insert_id);
        } else {
            outJson(-1, "插入失败");
        }
    }

    /**
     * 必须传递的参数
     * @param  array $insert_list 插入的数据
     * @param  array $judge_list 需要判断唯一性的数据  例子：['admin_id','phone'] 这是“和”的关系   [['admin_id'],['phone']] 这是“或”的关系
     * @param  Model $m model类
     * @return bool|string
     */
    protected function judge($insert_list, $judge_list, $m)
    {
        $where = [];
        //循环判断数组
        foreach ($judge_list as $value) {
            //如果是二维数组，是或的关系，匹配到下一层
            if (is_array($value)) {
                $_str = $this->judge($insert_list, $value, $m);
                if ($_str !== true) {
                    return $_str;
                }
            } else {
                //添加至where条件
                if (isset($insert_list[$value])) {
                    $where[$value] = $insert_list[$value];
                }
            }
        }
        //没有where条件直接返回true
        if (!$where) {
            return true;
        }
        //查询是否有数据
        $getInfo = $m->get_one($where);
        //有数据的话，返回信息
        if ($getInfo) {
            $_str = "";
            foreach ($this->postRule as $_key => $_value) {
                if (isset($where[$_value[3]])) {
                    if ($_str) {
                        $_str .= ',';
                    }
                    $_str .= $_key;
                }
            }
            return $_str;
        }
        return true;
    }

    /*
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

            outJson(0, $one);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /*
     * 查询单条(用户登录)
     */
    protected function selectUserLib($set = [], $where = [], $is_new = 0, $is_log = array())
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $one = $_m->get_one($get_data);
        if ($one['type'] == "企业用户" || $one['type'] == "企业vip"){
            outJson(-2, "您是企业用户，请在企业用户处登陆");
        }
        if (!$one) {
            $get_data['phone'] = $get_data['user_name'];
            unset($get_data['user_name']);
            $one = $_m->get_one($get_data);
        }
        if (!$one) {
            $get_data['email'] = $get_data['phone'];
            unset($get_data['phone']);
            $one = $_m->get_one($get_data);
        }
        if ($one) {
            //添加用户登录日志
//            $userSetInfo = M('user_set')->get_one(array('type' => 'user_login', 'key' => 'LOG'), '', 'value');
            $loginInfo = array(
                'user_id'  => $one['id'],
                'type'     => '登录',
                'add_time' => getRequestTime(),
                'add_ip'   => getIp(),
                'remark'   => $one['user_name'] . '登录系统', //str_replace('#username#', $one['user_name'], $userSetInfo['value']),
            );
            M('user_log')->insert($loginInfo);
            $one = res_data($one, $this->resRule);
            ///2017年迎春领压岁钱活动 START
            $sql = "SELECT id FROM y1_hd_prize WHERE user_id ='".$one["id"]."' AND active_title ='".SF_ACTIVE_TITLE
                    . "' AND (source='已注册用户' OR source = '活动期间注册')";
            $search_result = M("hd_prize")->queryOne($sql);
            if(!is_array($search_result) && empty($search_result) && !isset($search_result['id'])){
                M('hd_spring_festival')->springFestivalRegGiveExtensionUserReward($one['id'], SF_ACTIVE_TITLE, "已注册用户");
            }
            ///2017年迎春领压岁钱活动 END

            /// 登录，给十月活动用户赠送转盘抽奖机会
            if( isset($one["id"]) && !empty($one["id"])  )
            {
                M('hd_draw_reward')->sendLotteryChance($one["id"]);

            }
           /// 2017十月活动  END
            outJson(0, $one);
        } else {
            outJson(-1, '没有数据');
        }
    }

    //企业用户登录
    protected function companyLogin(){
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $one = $_m->get_one(['organization_code' => $get_data['user_name'], 'password' => $get_data['password'],'status'=>0]);
        if ($one) {
            //添加用户登录日志
            $loginInfo = array(
                'user_id'  => $one['id'],
                'type'     => '登录',
                'add_time' => getRequestTime(),
                'add_ip'   => getIp(),
                'remark'   => $one['company_name'] . '登录系统', //str_replace('#username#', $one['user_name'], $userSetInfo['value']),
            );
            M('company_user_log')-> insert($loginInfo);
            $one = res_data($one, $this->resRule);
            outJson(0, $one);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /*
    * 查询单条
    */
    protected function selectUserOneLib($set = [], $where = [], $is_new = 0, $is_log = array())
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $one = $_m->get_one($get_data);
        if ($one) {
            $one = res_data($one, $this->resRule);
            //查询用户名
            if ($one && $one['user_id'] > 0) {
                $userInfo = $this->getUserName($one['user_id']);
                $one['user_name'] = $userInfo['user_name'];
            }
            outJson(0, $one);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /*
     * 查询单条以及关系表
     * @param  array $jude 查询对应字段名称数据，例如：1.['org_list'=>'admin_id:admin_org->org_id:org'] 查询的结果是一个数组结果集，以二维数组的形式添加到数据里
     *   2.['admin_id:admin_org->org_id:org']    查询的结果是一条结果集，以字段的形式添加到数据里
     *   3.['org_list'=>'org_id:org']   4.['org_id:org']   3、4和1、2的区别是不用通过关系表查询另一张表的字段，所以首先在原表要现有对应的数据ID
     */
    protected function selectOneAndOtherLib($jude = [])
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $one = $_m->get_one($get_data);
        if ($one) {
            //是否有规则
            if ($jude) {
                //循环规则数组
                foreach ($jude as $key => $value) {
                    $v_list = explode('->', $value);
                    if (!$v_list) {
                        continue;
                    }
                    if (count($v_list) == 1) {
                        //如果直接是
                        $this->otherOneLib($key, $value, $one);
                    } else {
                        $this->otherTwoLib($key, $value, $one);
                    }
                }
            }
            $one = res_data($one, $this->resRule);
            outJson(0, $one);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /*
     * 查询列表
     * @param  string $order 排序
     * @param  string $group 分组
     * @param  string $fields 查询
     */
    protected function selectListLib($order = '', $group = '', $fields = '')
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $page = isset($get_data['page']) ? $get_data['page'] : 0;
        $page_size = isset($get_data['page_size']) ? $get_data['page_size'] : 0;
        unset($get_data['page']);
        unset($get_data['page_size']);
        $where = $get_data;
        $list = $_m->get_list($where, $order, $page, $page_size, $group, $fields);
        $total = $_m->get_total([], 1);
        if ($list) {
            $list = res_data(['list' => $list, 'total' => $total], $this->resRule);
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /*
     * 查询列表（用户专用）
     * @param  string $order 排序
     * @param  string $group 分组
     * @param  string $fields 查询
     */
    protected function selectUserListLib($order = '', $group = '', $fields = '')
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $page = isset($get_data['page']) ? $get_data['page'] : 0;
        $page_size = isset($get_data['page_size']) ? $get_data['page_size'] : 0;
        unset($get_data['page']);
        unset($get_data['page_size']);
        $where = $get_data;
        $list = $_m->get_list($where, $order, $page, $page_size, $group, $fields);
        $total = $_m->get_total([], 1);
        if ($list) {
            $list = res_data(['list' => $list, 'total' => $total], $this->resRule);
            if ($list['list']) {
                foreach ($list['list'] as $key => $value) {
                    if (isset($value['user_id']) && $value['user_id'] > 0) {
                        $userInfo = $this->getUserName($value['user_id']);
                        //$userAccount = $this->getUserAccount($value['user_id']);
                        //$userBank = $this->getUserBank($value['user_id']);
                        $userAccountLog = $this->getUserAccountLog($value['cash_order']);

                        $list['list'][$key]['user_name'] = $userInfo['user_name'];
                        $list['list'][$key]['real_name'] = $userInfo['real_name'];
                        if($userInfo['type'] == '企业用户' || $userInfo['type'] == '企业vip') {
                            $list['list'][$key]['user_type'] = '企业';
                        }else{
                            $list['list'][$key]['user_type'] = '个人';
                        }

                        $user_info = M('user')->get_one(['id' => $value['user_id']]);
                        if($user_info['type'] == '企业用户' || $user_info['type'] == '企业vip'){
                            $co_user_info = $this->getCoUserInfo($value['user_id']);
                            $list['list'][$key]['bank_card'] = $co_user_info['bank_card'];
                            $list['list'][$key]['bank_name'] = $co_user_info['bank_name'];
                        }else {
                            $userBank = $this->getUserBank($value['user_id']);
                            $list['list'][$key]['bank_card'] = $userBank['bank_card'];
                            $list['list'][$key]['bank_name'] = transferBankCodeToName($userBank['bank_code']); //$userBank['bank_name'];
                            $list['list'][$key]['province_code'] = $userBank['province_code'];
                            $list['list'][$key]['city_code'] = $userBank['city_code'];
                            $list['list'][$key]['branch_name'] = $userBank['branch_name'];
                        }
                        if($userAccountLog) {
                            $list['list'][$key]['use_money'] = $userAccountLog['use_money'];
                        }else{
                            $list['list'][$key]['use_money'] = 0;
                        }
                        $list['list'][$key]['show_use_money'] = number_format($list['list'][$key]['use_money'] + $value['cash_account'], 2);
                        $list['list'][$key]['real_cash_account'] = number_format($value['cash_account'] - $value['management_fee'], 2);
                    }
                }
            }
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /*
     * 查询用户名
     * @author MZH
     */
    protected function getUserName($user_id)
    {
        if ($user_id > 0) {
            $_m = M('user');
            $user_info = $_m->get_one(array('id' => $user_id));
            return $user_info;
        }
        return false;
    }

     /*
     * 查询用户
     * @author MZH
     */
    protected function getUserBank($user_id)
    {
        if ($user_id > 0) {
            $_m = M('user_bank');
            $userBank_info = $_m->get_one(array('user_id' => $user_id, 'type' => '宝付', 'is_binding' => '是'));
            return $userBank_info;
        }
        return false;
    }

    /*
    * 查询企业用户信息
    * @author MZH
    */
    protected function getCoUserInfo($user_id)
    {
        if ($user_id > 0) {
            $_m = M('co_user_info');
            $co_user_info = $_m->get_one(array('user_id' => $user_id));
            return $co_user_info;
        }
        return false;
    }

    /*
     * 查询用户资金
     * @author MZH
     */
    protected function getUserAccount($user_id)
    {
        if ($user_id > 0) {
            $_m = M('new_account');
            $userAccount_info = $_m->get_one(array('user_id' => $user_id));
            return $userAccount_info;
        }
        return false;
    }

    /*
     * 查询用户资金记录
     * @author MZH
     */
    protected function getUserAccountLog($order_id)
    {
        if ($order_id > 0) {
            $_m = M('new_account_log');
            $userAccountLog_info = $_m->get_one(array('order_id' => $order_id));
            return $userAccountLog_info;
        }
        return false;
    }

    /*
     * 查询多条以及关系表
     *  @param  array $jude 查询对应字段名称数据，例如：1.['org_list'=>'admin_id:admin_org->org_id:org'] 查询的结果是一个数组结果集，以二维数组的形式添加到数据里
     *   2.['admin_id:admin_org->org_id:org']    查询的结果是一条结果集，以字段的形式添加到数据里
     *   3.['org_list'=>'org_id:org']   4.['org_id:org']   3、4和1、2的区别是不用通过关系表查询另一张表的字段，所以首先在原表要现有对应的数据ID
     *   5.添加数据兼容，'org_id:org' 可以写为 'org_id=id:org' 也就是第一张表查询的字段为org_id，关联的表主键为id，也可达到关联查询
     */
    protected function selectListAndOtherLib($jude = [], $order = '', $unset_key = [])
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $page = isset($get_data['page']) ? $get_data['page'] : 0;
        $page_size = isset($get_data['page_size']) ? $get_data['page_size'] : 0;
        unset($get_data['page']);
        unset($get_data['page_size']);
        $where = $get_data;
        $_m = M($this->m);
        $list = $_m->get_list($where, $order, $page, $page_size);
        $total = $_m->get_total([], 1);
        if ($list) {
            //是否有规则
            if ($jude) {
                foreach ($list as $one_key => $one) {
                    //循环规则数组
                    foreach ($jude as $key => $value) {
                        $v_list = explode('->', $value);
                        if (!$v_list) {
                            continue;
                        }
                        if (count($v_list) == 1) {
                            $this->otherOneLib($key, $value, $one, $unset_key);
                        } else {
                            $this->otherTwoLib($key, $value, $one, $unset_key);
                        }
                    }
                    $list[$one_key] = $one;
                }
            }
            $one = res_data(['list' => $list, 'total' => $total], $this->resRule);
            outJson(0, $one);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /*
     * 查询多条以及关系表
     *  @param  array $jude 查询对应字段名称数据，例如：1.['org_list'=>'admin_id:admin_org->org_id:org'] 查询的结果是一个数组结果集，以二维数组的形式添加到数据里
     *   2.['admin_id:admin_org->org_id:org']    查询的结果是一条结果集，以字段的形式添加到数据里
     *   3.['org_list'=>'org_id:org']   4.['org_id:org']   3、4和1、2的区别是不用通过关系表查询另一张表的字段，所以首先在原表要现有对应的数据ID
     *   5.添加数据兼容，'org_id:org' 可以写为 'org_id=id:org' 也就是第一张表查询的字段为org_id，关联的表主键为id，也可达到关联查询
     */
    protected function selectListTotalAndOtherLib($jude = [])
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $page = isset($get_data['page']) ? $get_data['page'] : 0;
        $page_size = isset($get_data['page_size']) ? $get_data['page_size'] : 0;
        unset($get_data['page']);
        unset($get_data['page_size']);
        $where = $get_data;
        $_m = M($this->m);
        $data = $_m->getListAndTotal($where, '', $page, $page_size);
        if (!$data) {
            outJson(-1, '没有数据');
        }
        if ($data['list']) {
            //是否有规则
            if ($jude) {
                foreach ($data['list'] as $one_key => $one) {
                    //循环规则数组
                    foreach ($jude as $key => $value) {
                        $v_list = explode('->', $value);
                        if (!$v_list) {
                            continue;
                        }
                        if (count($v_list) == 1) {
                            $this->otherOneLib($key, $value, $one);
                        } else {
                            $this->otherTwoLib($key, $value, $one);
                        }
                    }
                    $data['list'][$one_key] = $one;
                }
            }
            $data = res_data($data, $this->resRule);
            outJson(0, $data);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /*
     * 查询对应数据
     * @param  string $key 关键字，如果非数字，查询结果列表，用二维数组方式
     * @param  string $value 查询的规则字符
     * @param  array $one 原数据的单条结果集
     */
    protected function otherOneLib($key, $value, &$one, $unset_key = [])
    {
        $list_name = "";
        //判断是否为数字
        if (!is_numeric($key)) {
            $list_name = $key;
        }
        $v_list = explode('->', $value);
        $o_list = explode(':', $v_list[0]);
        $k_list = explode('=', $o_list[0]);
        if (!isset($one[$k_list[0]])) {
            return false;
        }
        $o_where = [];
        $w_key = isset($k_list[1]) ? $k_list[1] : $k_list[0];
        $o_where[$w_key] = $one[$k_list[0]];
        $o_m = M($o_list[1]);
        //非数字，结果集用二维数组保持。  数字，加入到原数据中
        if ($list_name) {
            //查询对应表数据
            $o_get_List = $o_m->get_list($o_where);
            if (!$o_get_List) {
                return false;
            }
            $one[$list_name] = $o_get_List;
        } else {
            //查询对应表数据
            $o_get_one = $o_m->get_one($o_where);
            if (!$o_get_one) {
                return false;
            }
            foreach ($unset_key as $value) {
                unset($one[$value]);
            }
            $one = array_merge($o_get_one, $one);
        }
        return true;
    }

    /*
     * 先查询对应关系再查询对应数据
     * @param  string $key 关键字，如果非数字，查询结果列表，用二维数组方式
     * @param  string $value 查询的规则字符
     * @param  array $one 原数据的单条结果集
     */
    protected function otherTwoLib($key, $value, &$one, $unset_key = [])
    {
        $list_name = "";
        //判断是否为数字
        if (!is_numeric($key)) {
            $list_name = $key;
        }
        $v_list = explode('->', $value);
        $o_list = explode(':', $v_list[0]);
        $k_list = explode('=', $o_list[0]);
        $t_list = explode(':', $v_list[1]);
        $_k_list = explode('=', $t_list[0]);
        if (!isset($one[$k_list[0]])) {
            return false;
        }
        $o_where = [];
        $w_key = isset($k_list[1]) ? $k_list[1] : $k_list[0];
        $o_where[$w_key] = $one[$k_list[0]];
        $o_m = M($o_list[1]);
        $t_m = M($t_list[1]);
        //非数字，结果集用二维数组保持。  数字，加入到原数据中
        if ($list_name) {
            //先查询关系数据列表
            $o_get_list = $o_m->get_list($o_where);
            if (!$o_get_list) {
                return false;
            }
            $r_list = [];
            //循环查询对应数据
            foreach ($o_get_list as $o_value) {
                $t_where = [];
                $_w_key = isset($_k_list[1]) ? $_k_list[1] : $_k_list[0];
                $t_where[$_w_key] = $o_value[$_k_list[0]];
                //查询表数据
                $t_get_one = $t_m->get_one($t_where);
                if (!$t_get_one) {
                    continue;
                }
                $r_list[] = $t_get_one;
            }
            if ($r_list) {
                $one[$list_name] = $r_list;
            }
        } else {
            //先查询关系数据单条
            $o_get_one = $o_m->get_one($o_where);
            if (!$o_get_one || !isset($o_get_one[$_k_list[0]])) {
                return false;
            }
            $_w_key = isset($_k_list[1]) ? $_k_list[1] : $_k_list[0];
            //查询表数据
            $t_where[$_w_key] = $o_get_one[$_k_list[0]];
            $t_get_one = $t_m->get_one($t_where);
            if (!$t_get_one) {
                return false;
            }
            foreach ($unset_key as $value) {
                unset($one[$value]);
            }
            //合并查询结果到原数据中
            $one = array_merge($t_get_one, $one);
        }
        return true;
    }

    /*
     * 修改
     * @param  array $set_list 更新的数组
     * @param  array $where_list 更新的条件
     */
    protected function updateLib($set_list = [], $where_list = [])
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 0);
        $_m = M($this->m);
        $where = [];
        if ($get_data && $set_list) {
            $get_data = array_merge($get_data, $set_list);
        }
        foreach ($where_list as $value) {
            $where[$value] = $get_data[$value];
            unset($get_data[$value]);
        }
        $up_num = $_m->update($get_data, $where);
        if ($up_num) {
            outJson(0, $up_num);
        }else if($up_num == 0){
            outJson(-1, '没有要更新的数据');
        }else {
            outJson(-1, '更新失败');
        }
    }

    /*
     * 删除
     */
    protected function deleteLib()
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 0);
        $_m = M($this->m);
        $del_num = $_m->delete($get_data);
        if ($del_num) {
            //$del_num = res_data(['del_num' => $del_num], $this->resRule);
            outJson(0, $del_num);
        } else {
            outJson(-1, '删除失败');
        }
    }

    /*
     * 查询单条------内容为文本编辑器显示（是否有更新操作）
     * @param  array $set 更新的数组字段
     * @param  array $where 更新的where条件
     * @param  int $is_new 是否获取更新后的数据
     */
    protected function selectOneHtmlLib($set = [], $where = [], $is_new = 0)
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
