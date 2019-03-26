<?php

/**
 * 用户信息类
 * 此文件程序用来做什么的（详细说明，可选。）。
 * @author      LHD
 */
class userLib extends Lib
{
    /**
     * @author jxy
     * 用户添加
     */
    public function reg()
    {
        //初始化信息
        $this->__init(-1, "用户注册接口", "这是用户注册接口，成功返回用户ID，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'password'       => [1, 'string', "用户登录密码", 'password', 'c4ca4238a0b923820dcc509a6f75849b'],
            'phone'          => [1, 'string', "用户手机号", 'phone', '13685225544'],
            'open_id'        => [0, 'string', "用户登录密码", 'open_id', 'test'],
            'user_from'      => [0, 'string', "用户来源", 'user_from', '元立方官网'],
            'user_from_sub'  => [0, 'string', "用户来源，二级联盟", 'user_from_sub', 'A100000001|q1|0000'],
            'user_from_host' => [0, 'string', "用户来源网址", 'user_from_host', 'www.baidu.com'],
            'extension_code' => [0, 'string', "推广码(手机号)", 'extension_code', '13511111111'],
            'type'           => [0, 'string', "用户来源", 'type', 'vip'],
            'sales_phone'    => [0, 'string', "理财师手机", 'sales_phone', '15502115105'],
        ];
        $this->insertUserOne(['reg_time' => getRequestTime(), 'reg_ip' => getIp()], [['phone']]);
    }

    /**
     * @author jxy
     * 注册方法,如果有相同数据不插入
     * $param array $insert_list 插入的结果集
     * $param array $judge_list 插入判断的结果集
     */
    protected function insertUserOne($insert_list = [], $judge_list = [])
    {
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        //get_data数据；
        if ($insert_list && $get_data) {
            $get_data = array_merge($get_data, $insert_list);
        }
        $extension_code     =   $get_data['extension_code'];

        //对得到的数据处理；
        if (empty($get_data['type'])) {
            $get_data['type'] = '外部投资';
        }
        if (!checkPhone($get_data['phone'])) {
            outJson(-1, '手机号格式错误');
        }
        if (!isset($get_data['user_from'])) {
            $get_data['user_from'] = "";
        }
        $get_data['user_from'] = checkFrom($get_data['user_from']);

        //实例化例；
        $_m                             =   M('user');
        $wqh_user_model                 =   M("wqh_user");
        $add_interest_use_rule_model    =   M("add_interest_use_rule");
        $yqy_a_user_model               =   M("yqy_a_user");

        //添加获取手机号码归属地
        try {
            $url            = 'https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=' . $get_data['phone'];
            $get_result     = iconv('gbk', 'UTF-8', get_curl($url));
            $cut_str_list1  = explode('{', $get_result);
            $cut_str_list2  = explode('province:', $cut_str_list1[1]);
            $cut_str_list   = explode(',', $cut_str_list2[1]);
            $cut_str        = str_replace("'", "", $cut_str_list[0]);
        } catch (Exception $e) {
            $cut_str = "";
        }
        if ($cut_str) {
            $get_data['phone_province'] = $cut_str;
        }

        //需要判断是否更新或退出
        if ($judge_list) {
            $_str   =   $this->judge($get_data, $judge_list, $_m);
            if ($_str !== true) {
                outJson(-1, $_str . ' 数据重复，不能添加');
            }
        }

        ///数据值；
        $wqh_code   =   CONFIG::wqhExtensionCode();
        $wqh_code   =   $wqh_code["extension_code"];    ///微企会A+的CODE码；
        $fym_code   =   CONFIG::yqyExtensionCode();
        $fym_code   =   $fym_code["fym_extension_code"];

        //对填写了推荐手机号的数据处理；
        $recommended_code   =   "";
        if (isset($get_data['extension_code']) && trim($get_data['extension_code'])) {
            ///根据注册手机号查询出推荐码；
            try {
                $info = $_m->get_one( array('phone' => strtoupper($get_data['extension_code'])));
                if ($info == false) {
                    $co_info = $_m->get_one(array('co_mobile' => strtoupper($get_data['extension_code'])));
                    if ($co_info == false) {
                        outJson(-1, '推荐人手机号错误');
                    } else { ///企业用户推荐的
                        $recommended_code   =   $co_info["promoted_code"];
                    }
                } else { ///个人用户推荐的；
                    $recommended_code       =   $info["promoted_code"];
                }
            } catch (Exception $e) {
                $recommended_code = "";
            }

            ///得到推荐人的详细信息；
            $sale_user_id               = $info["id"] ? $info["id"] : $co_info["id"];
            $sale_user_name             = $info["real_name"] ? $info["real_name"] : $co_info["real_name"];
            $extension_user_phone       = $get_data["extension_code"];
            $get_data["extension_code"] = $info["promoted_code"] ? $info["promoted_code"] : $co_info["promoted_code"];
            $get_data["type"]           = $info["type"] ? $info["type"] : "外部投资";

            ///微企汇A级用户，对type | 推荐人处理；
            if ($wqh_code == trim($recommended_code)) {
                $get_data['type']               = "微企汇";
                $get_data["extension_code"]     = "";
                $get_data["extension_user_id"]  = 0;
            }
            if ($fym_code == trim($recommended_code)) {
                $get_data['type']               = "元企盈-" . $sale_user_name;
                $get_data["extension_user_id"]  = $sale_user_id;
            }
            if ($info["phone"]) {
                $sales_info = M("sales_user")->get_one(array("mobile" => $info["phone"], "status" => "0"));
                if ($sales_info) {
                    $get_data["sales_phone"] = $info["phone"];
                }
            }
            if ($fym_code == trim($recommended_code)) {
                $get_data["sales_phone"] = $info["phone"] ? $info["phone"] : $co_info["co_mobile"];
            }
        }

        // 综合考虑，只能在此处加锁了。
        $insert_id = false;
        $fp = null;
        try {
            $fp = fopen(ROOT_PATH . 'lock_reg.txt', 'r');
            $try = 10; //尝试10次
            do {
                $lock = flock($fp, LOCK_EX);
                if (!$lock) {
                    usleep(500);
                } else {
                    //需要判断是否更新或退出
                    if ($judge_list) {
                        $_str = $this->judge($get_data, $judge_list, $_m);
                        if ($_str !== true) {
                            outJson(-1, "该手机号码已被注册");
                        }
                    }
                    $insert_id = $_m->insert($get_data);
                    flock($fp, LOCK_UN); //释放锁
                }
            } while (!$lock && --$try > 0);
        }catch (Exception $e){
            outJson(-1, '注册出现错误:' . $e->getMessage());
        }finally{
            if($lock){
                flock($fp, LOCK_UN); //释放锁
            }
            if($fp) {
                fclose($fp);//关闭资源
            }
        }
        ///插入成功后，数据处理。
        if ($insert_id !== false) {
            ///对微企汇用户，注册成功后是扫二维码的用户进入y1_wqh_user表；
            if (trim($recommended_code) && $extension_code) {
                if ( $recommended_code == $wqh_code ){ ///如果是微企汇用户；
                    $wqh_user_array =   array(
                        "user_id"       =>  $insert_id,
                        "mobile"        =>  $get_data["phone"],
                        "user_status"   =>  0,
                        "c_name"        =>  "百城万企",
                        "add_time"      =>  getRequestTime()
                    );
                    $wqh_insert =  $wqh_user_model->insert($wqh_user_array);
                    if (!$wqh_insert) {
                        outJson(-1, "数据加入微企汇列表失败!");
                    }
                    ///微企汇A级用户的推荐人和推广码设置为空。
                    $up_arr["extension_user_id"]    =   0;
                    $up_arr["extension_code"]       =   "";
                }
                if ( $recommended_code == $fym_code ){ ///如果是范忆敏用户；
                    $fym_wqy_array  =   array(
                        "user_id"       =>  $insert_id,
                        "c_uid"         =>  $sale_user_id,
                        "c_name"        =>  $sale_user_name,
                        "mobile"        =>  $get_data["phone"],
                        "add_time"      =>  getRequestTime(),
                        "user_type"     =>  "元企盈-" . $sale_user_name
                    );
                    $yqy_insert =  $yqy_a_user_model->insert($fym_wqy_array);
                    if (!$yqy_insert) {
                        outJson(-1, "数据加入元企盈A级用户列表失败!");
                    }
                }

            }
            ///新注册的用户赠送30元金币
            $coin = 0;
            if (!isQdUser($get_data['user_from'])) {
                $coin = 30;
                M("hd_coin")->rechargeCoin($insert_id, $coin, "Recharge", "注册送", "", "", date("Y-m-d 00:00:00", getRequestTime(1) + 366 * 24 * 3600));
            }
            ///理财师推荐好友自动赠送加息券
            if ($sales_info) {
                $add_interest_use_rule_result   =   $add_interest_use_rule_model -> get_list( array("auto_send" => "1") );
                $ticket                         =   "";
                foreach ($add_interest_use_rule_result as $k => $v) {
                    $ticket .=   $v["id"] .",";
                }
                $ticket                         =   substr($ticket, 0 , strlen($ticket) -1);
                $add_interest_start_time        =   getRequestTime();
                $add_interest_end_time          =   date('Y-m-d 23:59:59', strtotime ("+9 days", strtotime($add_interest_start_time)));
                $send_add_interest              =   M("add_interest")->addInterest($sale_user_id, $ticket, $add_interest_start_time, $add_interest_end_time, '', '自动');
            }
            //来源添加
            if (isset($get_data['user_from_host'])) {
                M("source")->addInfo($get_data['user_from_host']);
            }

            //生成用户名
            $user_name = 'ylf' . substr($get_data['phone'], 5, 6);
            if ($insert_id < 10)
                $user_name = '0' . $insert_id;
            elseif ($insert_id >= 100)
                $user_name = $user_name . substr($insert_id, -2, 2);
            else
                $user_name = $user_name . $insert_id;
            $up_arr = array('user_name' => $user_name);

            ///注册会员时填写推荐码赠送0.2的加息券
            if (isset($extension_user_phone) && trim($extension_user_phone)) {
                $info   =   $_m->get_one( array('phone' => $extension_user_phone) );
                if ($info == false) {
                    $info =  $_m->get_one( array('co_mobile' => $extension_user_phone) );
                }
                $up_arr['extension_code'] = strtoupper($info['promoted_code']);
                if ($info) {
                    ///被推荐的好友获赠0.2%的加息券
                    $up_arr['extension_user_id']    = $info['id'];
                    $active_title                   = 'JXQ_09';
                    $insert_list                    = [
                                                        'active_title' => $active_title,
                                                        "user_id"      => $insert_id,
                                                        "start_time"   => getRequestTime(),
                                                        "end_time"     => date("Y-m-d 00:00:00", getRequestTime(1) + 365 * 24 * 3600),
                                                        "add_time"     => getRequestTime(),
                                                    ];
                    M("hd_interest")->insertInterest($insert_list, [['active_title', 'user_id']]);
                }
            }

            ///微企汇A级用户的推荐人和推广码设置为空。
            if (trim($recommended_code) && $recommended_code == $wqh_code && $extension_code) {
                $up_arr["extension_user_id"]    =   0;
                $up_arr["extension_code"]       =   "";
            }
            $user_update = $_m->update($up_arr, array('id' => $insert_id));
            if ($user_update) {
                $return = [
                    'id'        => $insert_id,
                    'user_name' => $user_name,
                    'coin'      => $coin,
                    'type'      => $get_data["type"]
                ];
                //只用手机号注册的用户发送短信 规则为md5的手机号码后5位
                if ($get_data['password'] == md5(substr($get_data['phone'], -6))) {
                    M("sms")->smsAdd($get_data['phone'], 'peace_fan_success', serialize(['USER_NAME' => $user_name]), 0);
                }
                outJson(0, $return);
            }
        } else {
            outJson(-1, '添加失败');
        }
    }

    /**
     * @author jxy
     * 这里放所有用户注册添加的活动接口
     */
    private function regUserDo($user_id)
    {
        //体验金活动03期
//        M("experience")->insertTYJ(['active_title' => "TYJ_03", 'user_id' => $user_id], [['active_title', 'user_id'], ['experience_code']]);
    }

    /**
     * @author jxy
     * 用户汇付注册
     */
    public function regHf()
    {
        //初始化信息
        $this->__init(-1, "用户汇付开户接口", "用户汇付开户接口，成功返回汇付跳转页面。");
        //需要传递的参数
        $this->postRule = [
            'user_id'       => [1, 'num', "用户ID", 'id', '589'],
            'callback_from' => [0, 'string', "回调来源", 'callback_from', 'web'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);

        $bg_ret_url = BG_RET_URL;
        $ret_url = RET_URL;
        $u_model = M("user");
        $u_info = $u_model->get_one(['id' => $get_data['id']]);
        if (!$u_info) {
            outJson(-1, "无效的用户ID");
        }
        if (!$u_info['user_name'] || !$u_info['phone']) {
            outJson(-1, "用户名或手机号无效");
        }

        $callback_from = isset($get_data['callback_from']) ? $get_data['callback_from'] : '';
        $h_a_model = M("hf_api");
        $from_html = $h_a_model->userRegister($bg_ret_url, $ret_url, $u_info['user_name'], "", "", "", $u_info['phone'], "", "", $callback_from);
        outJson(0, $from_html);
    }

  /**
   * @author jxy
   * 判断用户是否开通汇付账户
   */
    public function isHfUser()
    {
        //初始化信息
        $this->__init(-1, "用户汇付开户接口", "用户汇付开户接口，成功返回汇付跳转页面。");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'id', '589']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
//        $bg_ret_url = BG_RET_URL;
//        $ret_url = RET_URL;
        $u_model = M("user");
        $u_info = $u_model->get_one(['id' => $get_data['id']]);
        if (!$u_info) {
            outJson(-1, "无效的用户ID");
        }
        if (!$u_info['hf_user_id']) {
            outJson(-1, '未开通汇付账户');
        } else {
            outJson(0, '已开通汇付账户');
        }
    }


    /**
     * 汇付用户添加银行卡,已废弃
     * @author cdf@yhxbank.com
     * @date  2016-06-17
     * @echo json
     */
    public function addBank()
    {
        //初始化信息
        $this->__init(-1, "用户汇付绑卡接口", "用户汇付绑卡接口，成功返回汇付跳转页面。如果未注册汇付，跳转汇付注册。");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'id', '589']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        outJson(-1, "汇付绑卡已废弃");
        $bg_ret_url = BG_RET_URL;
        $u_model = M("user");
        $u_info = $u_model->get_one(['id' => $get_data['id']]);
        if (!$u_info) {
            outJson(-1, "无效的用户ID");
        }
        $h_a_model = M("hf_api");
        if ($u_info['hf_user_id']) {
            $from_html = $h_a_model->userBindCard($u_info['hf_user_id'], $bg_ret_url);
        } else {
            $ret_url = RET_URL;
            $from_html = $h_a_model->userRegister($bg_ret_url, $ret_url, $u_info['user_name'], "", "", "", $u_info['phone'], "", "");
        }
        outJson(0, $from_html);
    }

    /**
     * @author jxy
     * 用户汇付添加银行卡
     */
    public function selectBank()
    {
        //初始化信息
        $this->__init(-1, "用户汇付查询银行卡接口", "用户汇付查询银行卡接口。");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'id', '589']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $u_model = M("user");
        $u_info = $u_model->get_one(['id' => $get_data['id']]);
        if (!$u_info) {
            outJson(-1, "无效的用户ID");
        }
        $h_a_model = M("hf_api");
        $bank_list = [];
        if ($u_info['hf_user_id']) {
            $hf_back = $h_a_model->QueryCardInfo($u_info['hf_user_id'], "");
            $bank_list = $hf_back["UsrCardInfolist"];
        }
        if (!$bank_list) {
            outJson(-1, "此用户未绑卡");
        }
        outJson(0, ['list' => $bank_list]);
    }

    /**
     * @author jxy
     * 用户登录
     */
    public function hfLogin()
    {
        //初始化信息
        $this->__init(-1, "用户汇付登录接口", "用户汇付登录接口。");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'id', '589']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $u_model = M("user");
        $u_info = $u_model->get_one(['id' => $get_data['id']]);
        if (!$u_info) {
            outJson(-1, "无效的用户ID");
        }
        $h_a_model = M("hf_api");
        if ($u_info['hf_user_id']) {
            $from_html = $h_a_model->userLogin($u_info['hf_user_id']);
            outJson(0, $from_html);
        } else {
            outJson(-1, "请先开户。");
        }
    }


    /**
     * @author jxy
     * 登陆操作，会更新登录时间
     */
    public function login()
    {
        //初始化信息
        $this->__init(-1, "用户登录接口", "登录成功修改登录时间以及上次登录时间并返回用户信息，登录失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_name' => [1, 'string', "用户登陆名", 'user_name', '13685225544'],
            'password'  => [1, 'string', "用户登录密码", 'password', 'c4ca4238a0b923820dcc509a6f75849b'],
        ];
        //输出的参数
        $this->resRule = [
            'id'            => [1, 'num', "用户编号", 'id', 4],
            'user_name'     => [1, 'string', "用户登陆名", 'user_name', '13685225544'],
            'real_name'     => [1, 'string', "用户名", 'real_name', 'aaa'],
            'reg_time'      => [0, 'string', "注册时间", 'reg_time', '2015-08-13 12:12:12'],
            'user_from'     => [0, 'string', "用户来源", 'user_from', '元立方官网'],
            'user_from_sub' => [0, 'string', "用户来源，二级联盟", 'user_from_sub', 'A100000001|q1|0000'],
            'tmp_data'      => [0, 'string', "记录临时数据，如打地鼠次数", 'tmp_data', '2'],
            'type'          => [0, 'string', "返回用户类型", 'type', '外部投资'],
        ];
        parent::selectUserLib();
    }

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
            'id'                => [0, 'num', "用户ID", 'id', 7],
            'phone'             => [0, 'string', '用户手机号', 'phone', '13685225544'],
            'co_mobile'         => [0, 'string', '企业手机号', 'co_mobile', '13685225544'],
            'promoted_code'     => [0, 'string', '推广码', 'promoted_code', 'XXXXX'],
            'user_name'         => [0, 'string', "用户登陆名", 'user_name', 'ylf20480059'],
            'open_id'           => [0, 'string', "微信用户唯一标识open_id", 'open_id', 'test']
        ];
        //输出的参数
        $this->resRule = [
            'id'                => [1, 'num', "用户编号", 'id', 7],
            'user_name'         => [1, 'string', "用户登陆名", 'user_name', 'a123456'],
            'phone'             => [1, 'string', '用户手机号', 'phone', '13685225544'],
            'co_mobile'         => [1, 'string', '企业用户的手机号', 'co_mobile', '13685225544'],
        	'type'              => [1, 'string', '用户类型', 'type', 'vip'],
            'hf_user_id'        => [1, 'string', '汇付id', 'hf_user_id', '6000060001767921'],
        	'hf_reg_time'       => [1, 'string', '实名时间', 'hf_reg_time', '2015-09-06 00:00:00'],
            'id_number'         => [1, 'string', '身份证号', 'id_number', '370000199909091234'],
            'real_name'         => [1, 'string', '真实姓名', 'real_name', '小白兔'],
            'sex'               => [1, 'string', '性别', 'sex', '男'],
            'password'          => [1, 'string', '密码', 'password', '34r34fw34sdfsdfsdgasgzsdf'],
            'reg_time'          => [1, 'string', '密码', 'reg_time', '2015-09-06 00:00:00'],
            'user_from'         => [0, 'string', "用户来源", 'user_from', '元立方官网'],
            'user_from_sub'     => [0, 'string', "用户来源，二级联盟", 'user_from_sub', 'A100000001|q1|0000'],
            'open_id'           => [0, 'string', "open_id", 'open_id', 'test'],
            'extension_code'    => [0, 'string', "extension_code", 'extension_code', 'test'],
            'promoted_code'     => [0, 'string', "promoted_code", 'promoted_code', 'test'],
            'extension_user_id' => [0, 'string', "extension_user_id", 'extension_user_id', 'test'],
            'post_code'         => [0, 'string', "post_code", 'post_code', 'test'],
            'address'           => [0, 'string', "address", 'address', 'test'],
            'deal_enabled'      => [0, 'string', "交易密码状态", 'deal_enabled', '开启'],
            'deal_pwd'          => [0, 'string', "交易密码", 'deal_pwd', 'c4ca4238a0b923820dcc509a6f75849b'],
            'email'             => [0, 'string', '邮箱', 'email', 'test'],
            'login_time'        => [0, 'string', "用户注册时间", 'login_time', '2015-03-04 04:04:04'],
            'tmp_data'          => [0, 'string', "记录临时数据，如打地鼠次数", 'tmp_data', '2'],
            'verify_times'      => [0, 'num', "实名验证错误次数", 'verify_times', 1],
            'deal_pwd_times'    => [0, 'num', "交易密码输错次数", 'deal_pwd_times', 1],
            'init_pwd'          => [0, 'num', "修改密码的次数", 'init_pwd', 0],
        ];
        parent::selectOneLib();
    }

    /**
     * @author jxy
     * 查询用户列表M
     */
    public function selectList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户信息接口", "成功返回多条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20],
            'user_from' => [0, 'string', "用户来源", 'user_from', '元立方官网']
        ];
        //输出的参数
        $this->resRule = [
            'list'  => [
                1, 'array', '用户列表', 'list',
                [
                    'id'                => [1, 'num', "用户编号", 'id', 7],
                    'user_name'         => [1, 'string', "用户登陆名", 'user_name', 'a123456'],
                    'real_name'         => [1, 'string', "用户真实姓名", 'real_name', '张三'],
                    'phone'             => [1, 'string', "用户手机号", 'phone', '13685225544'],
                    'email'             => [1, 'string', "用户邮箱", 'email', 'hk@71dai.com'],
                    'reg_time'          => [1, 'string', "用户注册时间", 'reg_time', '2015-03-04 04:04:04'],
                    'reg_ip'            => [1, 'string', "用户注册ip", 'reg_ip', '127.0.0.1'],
                    'user_from'         => [0, 'string', "用户来源", 'user_from', '元立方官网'],
                    'user_from_sub'     => [0, 'string', "用户来源，二级联盟", 'user_from_sub', 'A100000001|q1|0000'],
                    'open_id'           => [0, 'string', "微信用户唯一标识open_id", 'open_id', 'test'],
                    'extension_user_id' => [0, 'num', "推荐人用户ID", 'extension_user_id', 9188]
                ]
            ],
            'total' => [1, 'num', '查询条数', "total", 100]
        ];
        parent::selectListLib('id desc');
    }


    /**
     * @author jxy
     * 查询用户列表--元立方后台用户列表接口
     * wub 20160219
     */
    public function selectAdminList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户信息接口", "成功返回多条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'                  => [1, 'num', "页码", 'page', 0],
            'page_size'             => [1, 'num', "显示条数", 'page_size', 20],
            'id'                    => [0, 'string', "id", 'id', 1],
            'user_name'             => [0, 'string', "用户名称", 'user_name', 1],
            'real_name'             => [0, 'string', "真实姓名", 'real_name', 1],
            'phone'                 => [0, 'string', "手机号码", 'phone', 1],
            'user_from'             => [0, 'string', "用户来源", 'user_from', '元立方官网'],
            'extension_user_name'   => [0, 'string', "推荐人姓名", 'extension_user_name', '666'],
            'extension_user_phone'  => [0, 'string', "推荐人手机号", 'extension_user_phone', '15171392025'],
        ];
        //输出的参数
        $this->resRule = [
            'list'  => [
                1, 'array', '用户列表', 'list',
                [
                    'id'                => [1, 'num', "用户编号", 'id', 7],
                    'user_name'         => [1, 'string', "用户登陆名", 'user_name', 'a123456'],
                    'real_name'         => [1, 'string', "用户真实姓名", 'real_name', '张三'],
                    'phone'             => [1, 'string', "用户手机号", 'phone', '13685225544'],
                    'email'             => [1, 'string', "用户邮箱", 'email', 'hk@71dai.com'],
                    'reg_time'          => [1, 'string', "用户注册时间", 'reg_time', '2015-03-04 04:04:04'],
                    'reg_ip'            => [1, 'string', "用户注册ip", 'reg_ip', '127.0.0.1'],
                    'user_from'         => [0, 'string', "用户来源", 'user_from', '元立方官网'],
                    'user_from_sub'     => [0, 'string', "用户来源，二级联盟", 'user_from_sub', 'A100000001|q1|0000'],
                    'open_id'           => [0, 'string', "微信用户唯一标识open_id", 'open_id', 'test'],
                    'extension_user_id' => [0, 'num', "推荐人用户ID", 'extension_user_id', 9188],
                    'extension_user_phone' => [0, 'string', "推荐人用户电话", 'extension_user_phone', "15811111111"],
                    'extension_user_name' => [0, 'string', "推荐人用户姓名", 'extension_user_name', "鄄四"],
                    'last_invest_time'  => [0, 'string', "末次投资时间", 'last_invest_time', '2015-03-04 04:04:04'],
                    'sum_invest_money'  => [0, 'num', "累计投资金额", 'sum_invest_money', '10.00'],
                    'redbag_sum'        => [1, 'num', "红包发放数", 'redbag_sum', 7],
                    'redbag_used'       => [1, 'num', "红包使用数", 'redbag_used', 7],
                    'redbag_effective'  => [1, 'num', "剩余有效红包数", 'redbag_effective', 7],
                    'is_sales_user'     => [1, 'num', "推荐人是否是理财师", 'is_sales_user', "0"],
                ]
            ],
            'total' => [1, 'num', '查询条数', "total", 100]
        ];
        // parent::selectListLib('id desc');

        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $page = isset($get_data['page']) ? $get_data['page'] : 0;
        $page_size = isset($get_data['page_size']) ? $get_data['page_size'] : 0;
        unset($get_data['page']);
        unset($get_data['page_size']);

        $limit = ' LIMIT ' . intval($page) * intval($page_size) . ',' . intval($page_size);
        $where = '';
        if(isset($get_data['id']) && $get_data['id']){
            $where .= "AND u.id = {$get_data['id']} " ;
        }
        if(isset($get_data['user_name']) && $get_data['user_name']){
            $where .= "AND u.user_name like '%{$get_data['user_name']}%' " ;
        }
        if(isset($get_data['real_name']) && $get_data['real_name']){
            $where .= "AND u.real_name like '%{$get_data['real_name']}%' " ;
        }
        if(isset($get_data['phone']) && $get_data['phone']){
            $where .= "AND u.phone like '%{$get_data['phone']}%' " ;
        }


        //推广员姓名和手机
        if(isset($get_data['extension_user_name'])){
            $extension_user_name = trim($get_data['extension_user_name']);
            if($extension_user_name){
                $where .= "AND ue.real_name like '%{$extension_user_name}%' " ;
            }
            unset($get_data['extension_user_name']);
        }
        if(isset($get_data['extension_user_phone'])){
            $extension_user_mobile = trim($get_data['extension_user_phone']);

            if($extension_user_mobile){
                $where .= "AND ue.phone like '%{$extension_user_mobile}%' ";
            }
            unset($get_data['extension_user_phone']);
        }
        $where .= "AND u.type <> '企业用户' AND u.type <> '企业vip' ";

        if (substr($where, 0, 4) == 'AND '){
            $where = ' WHERE ' . substr($where, 4);
        }
        $fields = " u.*, ue.real_name as extension_user_name, ue.phone as extension_user_phone ";
        $_sql = "SELECT _fields_
                FROM y1_user as u 
                LEFT JOIN y1_user as ue on u.extension_user_id = ue.id 
                _where_ 
                ORDER BY u.id DESC ";

        $_sql = str_replace('_where_', $where, $_sql);
        $sql = str_replace('_fields_', $fields, $_sql);
        $sql .= $limit;
        $list = $_m->queryAll($sql);
        $fields = ' count(*) as count ';
        $sql = str_replace('_fields_', $fields, $_sql);
        $total = $_m->queryOne($sql)['count'];
        if ($list) {
            //查询末次投资时间与总投资金额
            $arr = array();
            foreach ($list as $value) {
                $arr[] = $value['id'];
            }
            $arr = array_unique($arr);

            $yzy_model      =   M("borrow_invest");
            $yyy_model      =   M("yyy_borrow_invest");
            $appoint_model  =   M("appoint_borrow_invest");
            $vip_model      =   M("vip_borrow_invest");
            $mb_model       =   M("mb_borrow_invest");
            $wdy_model      =   M("wdy_borrow_invest");
            $user_model     =   M("user");
            ///元政盈
            $invest_list            = $yzy_model->get_list([['', 'status', '!=', '失败'], ['and', 'invest_user_id', 'in', $arr]], '', 0, 0, "invest_user_id", 'invest_user_id,sum(money) money,max(add_time) add_time');
            ///元月盈
            $yyy_invest_list        = $yyy_model->get_list([['', 'invest_user_id', 'in', $arr]], '', 0, 0, "invest_user_id", 'invest_user_id,sum(invest_money) money,max(first_borrow_time) add_time');
            ///私人尊享
            $appoint_invest_list    = $appoint_model->get_list([['', 'user_id', 'in', $arr]], '', 0, 0, "user_id", 'user_id,sum(money) money,max(invest_time) add_time');
            ///VIP
            $vip_invest_list        = $vip_model->get_list([['', 'invest_user_id', 'in', $arr]], '', 0, 0, "invest_user_id", 'invest_user_id,sum(show_money) money,max(add_time) add_time');
            ///秒标
            $mb_invest_list         = $mb_model->get_list([['', 'user_id', 'in', $arr]], '', 0, 0, "user_id", 'user_id,sum(show_money) money,max(invest_time) add_time');
            ///薪盈计划
            $wdy_invest_list        = $wdy_model->get_list([['', 'invest_user_id', 'in', $arr]], '', 0, 0, "invest_user_id", 'invest_user_id,sum(total_money) money,max(add_time) add_time');


            foreach ($list as $key => $value) {
                if ($value["extension_user_id"] != "0") {
                    $user_info = $user_model -> get_one( array("id" => $value["extension_user_id"]) );
                    $list[$key]["extension_user_name"]  =   $user_info["real_name"];
                    $list[$key]["extension_user_phone"] =   $user_info["phone"];
                } else {
                    $list[$key]["extension_user_phone"] =   "--";
                }
                ///循环元政盈；
                $temp_sum_invest_money          =   0;
                $temp_last_invest_time          =   array();
                if ($invest_list) {
                    foreach ($invest_list as $val) {
                        if ($value['id'] == $val['invest_user_id']) {
                            $temp_last_invest_time[]        =   $val['add_time'];
                            $temp_sum_invest_money          +=  $val['money'];
                            break;
                        }
                    }
                }
                ///循环元月盈
                if ($yyy_invest_list) {
                    foreach ($yyy_invest_list as $val) {
                        if ($value['id'] == $val['invest_user_id']) {
                            $temp_last_invest_time[]        =   $val['add_time'];
                            $temp_sum_invest_money          +=  $val['money'];
                            break;
                        }
                    }
                }
                ///循环私人尊享
                if ($appoint_invest_list) {
                    foreach ($appoint_invest_list as $val) {
                        if ($value['id'] == $val['user_id']) {
                            $temp_last_invest_time[]        =   $val['add_time'];
                            $temp_sum_invest_money          +=  $val['money'];
                            break;
                        }
                    }
                }
                ///循环VIP
                if ($vip_invest_list) {
                    foreach ($vip_invest_list as $val) {
                        if ($value['id'] == $val['invest_user_id']) {
                            $temp_last_invest_time[]        =   $val['add_time'];
                            $temp_sum_invest_money          +=  $val['money'];
                            break;
                        }
                    }
                }
                ///循环秒标产品
                if ($mb_invest_list) {
                    foreach ($mb_invest_list as $val) {
                        if ($value['id'] == $val['user_id']) {
                            $temp_last_invest_time[]        =   $val['add_time'];
                            $temp_sum_invest_money          +=  $val['money'];
                            break;
                        }
                    }
                }
                ///循环薪盈计划
                if ($wdy_invest_list) {
                    foreach ($wdy_invest_list as $val) {
                        if ($value['id'] == $val['invest_user_id']) {
                            $temp_last_invest_time[]        =   $val['add_time'];
                            $temp_sum_invest_money          +=  $val['money'];
                            break;
                        }
                    }
                }

                $list[$key]['sum_invest_money'] =   $temp_sum_invest_money;
                if ($temp_last_invest_time) {
                    $list[$key]['last_invest_time'] = max($temp_last_invest_time);
                } else {
                    $list[$key]['last_invest_time'] = "0000-00-00 00:00:00";
                }
            }

            ///添加是否是在职理财师的判断；
            $sales_user_model   =   M("sales_user");
            foreach ($list as $k => $v) {
                $list[$k]["is_sales_user"]     = 0;
                if ($v["extension_user_phone"]) {
                    $sales_user_info    =   $sales_user_model->get_one(array("mobile" => $v["extension_user_phone"]));
                    if ($sales_user_info["status"] == "0" && $sales_user_info) {
                        $list[$k]["is_sales_user"] = "1";
                    }
                }
            }
            $list = res_data(['list' => $list, 'total' => $total], $this->resRule);
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }


    /**
     * @author jxy
     * 用户信息修改
     */
    public function update()
    {
        //初始化信息
        $this->__init(-1, "更新用户信息接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'id'                => [1, 'num', "用户编号", 'id', 7],
            'password'          => [0, 'string', "用户密码", 'password', 'c4ca4238a0b923820dcc509a6f75849b'],
            'phone'             => [0, 'string', "用户手机", 'phone', '13685225546'],
            'co_mobile'         => [0, 'string', "企业用户手机号", 'co_mobile', '13685225546'],
            'email'             => [0, 'string', "用户邮箱", 'email', 'hk@71dai.com'],
            'open_id'           => [0, 'string', "微应运open_id", 'open_id', 'test'],
            'deal_enabled'      => [0, 'string', "交易密码状态", 'deal_enabled', '开启'],
            'deal_pwd'          => [0, 'string', "交易密码", 'deal_pwd', 'c4ca4238a0b923820dcc509a6f75849b'],
            'tmp_data'          => [0, 'string', "记录临时数据，如打地鼠次数", 'tmp_data', '0'],
            'deal_pwd_times'    => [0, 'num', "密码输错次数", 'deal_pwd_times', 0],
            'init_pwd'          => [0, 'num', "密码修改次数", 'init_pwd', 0],
        ];
        //修改交易密码后用户修改交易密码错误次数置0
        if (!isset($this->req['deal_pwd_times'])) {
            $this->req['deal_pwd_times'] = 0;
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
        $this->__init(0, "删除用户信息接口", "返回是否成功删除用户信息");
        //需要传递的参数
        $this->postRule = [
            'id' => [0, 'num', "用户编号", 'id', 1]
        ];
        parent::deleteLib();
    }

    /**
     * @author jxy
     * 获取用户资金记录
     */
    public function selectAccountLogList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户资金记录接口", "成功返回多条用户资金记录信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id'   => [0, 'num', "用户id", 'user_id', 3],
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '用户资金记录列表', 'list',
                [
                    'id'               => [1, 'num', "资金记录id", 'id', 1],
                    'user_id'          => [1, 'num', '用户id', 'user_id', 7],
                    'in_or_out'        => [1, 'string', '转入或转出', 'in_or_out', '-1'],
                    'type'             => [1, 'string', '交易类型', 'type', 'recharge'],
                    'money'            => [1, 'num', '影响金额', 'money', 100.00],
                    'total_money'      => [1, 'num', '总金额', 'total_money', 100.00],
                    'use_money'        => [1, 'num', '可用金额', 'use_money', 100.00],
                    'recharge_money'   => [1, 'num', '充值金额', 'recharge_money', 100.00],
                    'repayment_money'  => [1, 'num', '还款金额', 'repayment_money', 100.00],
                    'frozen_money'     => [1, 'num', '冻结金额', 'frozen_money', 100.00],
                    'collection_money' => [1, 'num', '待收金额', 'collection_money', 100.00],
                    'remark'           => [1, 'string', '备注', 'remark', 'asdfa'],
                    'create_time'      => [1, 'string', '操作时间', 'create_time', '2015-03-03 03:03:03'],
                    'create_ip'        => [1, 'string', '操作人ip', 'create_ip', '192.168.1.1'],
                ]
            ]
        ];
        parent::selectUserListLib();
    }

    /**
     * 删除所有
     */
    public function deleteAll()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户资金接口", "成功返回单条用户资金信息，失败返回错误信息");
        $this->postRule = [
            'type' => [0, 'num', "用户编号", 'type', 1]
        ];
        parent::deleteLib();
    }

    /**
     * @author jxy
     * 查询单条用户资金信息
     */
    public function selectAccountOne()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户资金接口", "成功返回单条用户资金信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'user_id', 7]
        ];
        //输出的参数
        $this->resRule = [
            'user_id'          => [1, 'num', '用户id', 'user_id', 7],
            'total_money'      => [1, 'num', '用户总金额', 'total_money', 1.00],
            'use_money'        => [1, 'num', '用户可用金额', 'use_money', 2.00],
            'recharge_money'   => [1, 'num', '用户充值金额', 'recharge_money', 3.00],
            'repayment_money'  => [1, 'num', '用户还款金额', 'repayment_money', 4.00],
            'frozen_money'     => [1, 'num', '冻结', 'frozen_money', 5.00],
            'collection_money' => [1, 'num', '用户待收金额', 'collection_money', 6.00]
        ];
        parent::selectUserOneLib();
    }

    /**
    * 获取借款方
     * author:pjk
     * date:2015-07-22
     * email:peijk@yhxbank.com
    */
    public function selectBorrowList()
    {
        $where[] = ['', [
            ['', 'type', '=', '外部借款'],
            ['or', 'type', '=', '内部借款']
        ]];
        $user = M('user');
        $list = $user->get_list($where);
        if ($list) {
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /**
     * @author jxy
     * 校验用户是否注册
     */
    public function checkRegister()
    {
        //初始化信息
        $this->__init(-1, "校验用户是否注册", "成功返回TRUE，失败返回FALSE");
        //需要传递的参数
        $this->postRule = [
            'user_name' => [1, 'string', "用户ID", 'user_name', 'test']
        ];
        //输出的参数
        $this->resRule = [TRUE];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user = M('user')->get_one(['user_name' => $get_data['user_name']]);
        $phone = M('user')->get_one(['phone' => $get_data['user_name']]);
        $email = M('user')->get_one(['email' => $get_data['user_name']]);
        if ($user || $phone || $email) {
            outJson(0, 1);
        } else {
            outJson(0, 0);
        }
        // parent::selectOneLib([], $where);
    }

    /**
     * @author jxy
     * 查询单条用户信息
     */
    public function selectOneByPhone()
    {
        //初始化信息
        $this->__init(-1, "查询单条用户信息接口", "成功返回单条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'phone' => [1, 'num', "手机号码", 'phone', 13800000000]
        ];
        //输出的参数
        $this->resRule = [
            'id'            => [1, 'num', "用户编号", 'id', 7],
            'user_name'     => [1, 'string', "用户登陆名", 'user_name', 'a123456'],
            'phone'         => [1, 'string', '用户手机号', 'phone', '13685225544'],
            'real_name'     => [1, 'string', '真实姓名', 'real_name', '张三'],
            'type'          => [1, 'string', '投资类型', 'type', '外部投资'],
            'email'         => [1, 'string', '邮箱', 'email', '13800000000@163.com'],
            'id_number'     => [1, 'string', '身份证号', 'idcard', '310115190001010000'],
            'sex'           => [1, 'string', '性别', 'sex', '男'],
            'hf_user_id'    => [1, 'string', '汇付ID', 'hf_user_id', '6000060046841855'],
            'reg_time'      => [0, 'string', "注册时间", 'reg_time', '2015-08-13 12:12:12'],
            'user_from'     => [0, 'string', "用户来源", 'user_from', '元立方官网'],
            'user_from_sub' => [0, 'string', "用户来源，二级联盟", 'user_from_sub', 'A100000001|q1|0000'],
            'deal_enabled'  => [0, 'string', "交易密码状态", 'deal_enabled', '关闭'],
            'deal_pwd'      => [0, 'string', "交易密码", 'deal_pwd', 'c4ca4238a0b923820dcc509a6f75849b'],
        ];
        parent::selectOneLib();
    }

    /**
     * @author jxy
     * 查询虚拟用户列表
     */
    public function selectXNList()
    {
        //初始化信息
        $this->__init(-1, "查询多条用户信息接口", "成功返回多条用户信息，失败返回错误信息");
        //需要传递的参数
        $this->postRule = [
            'page'      => [1, 'num', "页码", 'page', 0],
            'page_size' => [1, 'num', "显示条数", 'page_size', 20]
        ];
        //输出的参数
        $this->resRule = [
            'list' => [
                1, 'array', '用户列表', 'list',
                [
                    'id'                  => [1, 'num', "用户编号", 'id', 7],
                    'user_name_use_money' => [1, 'string', "用户登陆名", 'user_name_use_money', 'ylf2554125dd_36000'],
                ]
            ]
        ];
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
        $where['type'] = "虚拟账户";
        $list = $_m->get_list($where, '', $page, $page_size, '', 'id,user_name');
        $user_ids = [];
        foreach ($list as $value) {
            $user_ids[] = $value['id'];
        }
        $account_list = M("new_account")->get_list([["", "user_id", "in", $user_ids]]);
        $user_accounts = [];
        foreach ($account_list as $value) {
            $user_accounts[$value['user_id']] = (int)$value['use_money'];
        }
        foreach ($list as $key => $value) {
            if (isset($user_accounts[$value['id']])) {
                $list[$key]['user_name_use_money'] = $value['id'] . "_" . $value['user_name'] . "_" . $user_accounts[$value['id']];
            } else {
                unset($list[$key]);
            }
        }
        if ($list) {
            $list = res_data(['list' => $list], $this->resRule);
            outJson(0, $list);
        } else {
            outJson(-1, '没有数据');
        }
    }

    /**
     * @author jxy
     * 修改地址和邮编
     */
    public function updateAddress()
    {
        //初始化信息
        $this->__init(-1, "更新用户信息接口", "返回更新结果，并更新时间");
        //需要传递的参数
        $this->postRule = [
            'id'        => [1, 'num', "用户编号", 'id', 7],
            'address'   => [0, 'string', "用户地址", 'address', '上海市浦东新区陆家嘴'],
            'post_code' => [0, 'string', "邮编", 'post_code', '250062'],
        ];
        parent::updateLib([], ['id']);
    }

    /**
     * @author jxy
     * 验证交易密码
     */
    public function checkDealPwd()
    {
        //初始化信息
        $this->__init(-1, "验证交易密码接口", "返回成功或失败");
        //需要传递的参数
        $this->postRule = [
            'id'       => [1, 'num', "用户编号", 'id', 7],
            'deal_pwd' => [1, 'string', "交易密码（MD5）", 'deal_pwd', 'c4ca4238a0b923820dcc509a6f75849b']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M($this->m);
        $one = $_m->get_one(["id" => $get_data['id']]);
        if (!$one) {
            outJson(-1, "没有此用户");
        }

        if ($one['deal_pwd'] != $get_data['deal_pwd']) {
            $sql = "update y1_user set deal_pwd_times = deal_pwd_times + 1 where id = {$get_data['id']}";
            M("user")->doExec($sql);
            $one = $_m->get_one(["id" => $get_data['id']]);
            if ($one['deal_pwd_times'] >= 4) {
                outJson(-2, 0);
            }else{
                $left_times = ceil(4 - $one['deal_pwd_times']);
                outJson(-2, $left_times);
            }
        } else {
            outJson(0, "交易密码验证成功");
        }
    }

    /**
     * @author jxy
     * 获取红包数量
     */
    public function getRedBagNumber()
    {
        //初始化信息
        $this->__init(-1, "验证交易密码接口", "返回成功或失败");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户编号", 'user_id', 7],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        //$_m = M($this->m);
        $_m = M('red_bag_log');
//        $where = $get_data;
        $user_id = $get_data['user_id'];
        $sql = "select count(*) s from y1_red_bag_log a left join y1_red_bag b on a.red_bag_id=b.id where a.user_id = " . $user_id . " UNION All
        select count(*) s from y1_red_bag_log a left join y1_red_bag b on a.red_bag_id=b.id where a.user_id = " . $user_id . " and a.use_status = '已使用' UNION All
        select count(*) s from y1_red_bag_log a left join y1_red_bag b on a.red_bag_id=b.id where a.user_id = " . $user_id . " and a.use_status = '未使用' and b.end_time > now()";
//        $count = $_m->get_total($where);
        $result = $_m->queryAll($sql);
        $count['sum'] = $result[0]['s'];
        $count['sum1'] = $result[1]['s'];
        $count['sum2'] = $result[2]['s'];
        if ($result) {
            outJson(0, $count);
        } else {
            outJson(0, 0);
        }
    }
    
    /**
     * @author jxy
     * 统计线上的所有会员数 在原有的基础上再加 5.36W 
    */
    public function totalUsers() 
    {
    	//初始化信息
    	$this->__init(-1, "统计线上的所有会员数", "返回成功或失败");
    	//需要传递的参数
    	$this->postRule = [
    			'user_id' => [0, 'num', "用户编号(无实质意义，随意传值)", 'user_id', 7],
    			];
    	//是否显示post参数或者res参数
    	_show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
    	//过滤参数，查看是否传递必须的数据 
    	$_m       = M($this->m);
    	$get_data = must_post($this->postRule, $this->req, 1);
    	$count    = $_m -> get_total() + 53600 ; //根据领导要求，在原有基础上加5.36w
    	outJson( 0 , $count );    
    }
    
    /**
     * @author jxy
     * 新手标项目：产生6位随机数，库里面检查是否已有记录；没有并入库；
     */
	public function SetRandNumAndStorage()
	{
		//初始化信息
		$this->__init(-1, "产生6位随机数，库里面检查是否已有记录；没有并入库", "返回成功或失败");
		//需要传递的参数
		$this->postRule = [
				'code' => [0, 'string', "输入的ID", 'code', ''],
				];
		//是否显示post参数或者res参数
		_show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
		///产生验证码
		$rand_num = strtoupper($this -> getRandomString( 6 ));
		///验证码检查是否可用；
		$_m = M('active_newhand_code');
		$is_has_code = $_m -> get_one( array('code' => $rand_num) );
		
		if (!empty($is_has_code)) {
			while (!empty($is_has_code)) {
				$rand_num    = strtoupper($this -> getRandomString( 6 ));
				$is_has_code = $_m -> get_one( array('code' => $rand_num) );
			}
		}
		///判断是否已经达到上线3000，写成2990，是留有空间；
// 		$has_used_count = $_m -> get_total( array('is_used' => 1) );
// 		if ( $has_used_count >= 2990 ) {
// 			outJson(-1, '识别码已达上线');
// 		}
		///对生成的随机码进行入库；
		$insert_list = array(
						'code'        => strtoupper($rand_num),
						'is_used'     => 0,
						'create_time' => date("Y-m-d H:i:s"),
						'use_time'    => '0000-00-00 00:00:00',
						'ip'          => getIp(),
						);
		
		$insert_return  = $_m -> insert( $insert_list );
		if ($insert_return !== false) {
			outJson( 0 , $rand_num );
		} else {
			outJson( -2 , '识别码入表失败');
		}
		
	}
	
	/**
     * @author jxy
	 * 新手标项目： 检查识别码是否存在和是否超标；
	 * return: -1:识别码已达上线, -2:识别码已使用, -3:识别码不正确, 0:成功
	 */
	public function checkCode(){
		//初始化信息
		$this->__init(-1, "产生6位随机数，检查值是否正确", "返回成功或失败");
		//需要传递的参数
		$this->postRule = [
				'code' => [1, 'string', "输入的ID", 'code', ''],
				];
		//是否显示post参数或者res参数
		_show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
		//过滤参数，查看是否传递必须的数据
		$get_data = must_post($this->postRule, $this->req, 1);
		
		$code = strtoupper($get_data['code']);
		$_m = M('active_newhand_code');
		
		///判断是否已经达到上线3000，写成2990，是留有空间；
		$has_used_count = $_m -> get_total( array('is_used' => 1) );
		if ( $has_used_count >= 2990 ) {
			outJson(-1, '有限的识别码已达上限！');
		}
		
		///验证码检查是否可用；
		$is_has_code = $_m -> get_one( array('code' => strtoupper($code) ) );
		$code = strtoupper($code);
		if (!empty($is_has_code)) {
			$is_used = $is_has_code['is_used'];
			if ($is_used == 1) {
				outJson(-2, '识别码已被使用！');
			} else {
				outJson(0, '成功');
			}
		} else {
			outJson(-3, '识别码错误！');
		}
	}
	
	/**
     * @author jxy
	 * 产生随机数 
	 */
	public function getRandomString($len, $chars=null)
	{
		if (is_null($chars)){
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		}
		mt_srand(10000000*(double)microtime());
		for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
			$str .= $chars[mt_rand(0, $lc)];
		}
		return $str;
	}

    /**
     * @function 判断用户是否有推广上级
     * @date     2016-08-18
     * @author zhult@yhxbank.com
     * @echo json
     */
    public function checkTG() {
        //初始化信息
        $this->__init(-1, "获取用户被推广的信息", "返回成功或失败");
        //需要传递的参数
        $this->postRule = [
            'user_id' => [1, 'num', "用户ID", 'user_id', 0],
        ];
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        $get_data = must_post($this->postRule, $this->req, 1);
        $_m = M('user');
        $return = $_m->get_one(array('id' => $get_data['user_id']), '', 'extension_user_id');
        if (isset($return['extension_user_id']) && 0 != $return['extension_user_id']) {
            outJson(0, $return['extension_user_id']);
        } else {
            outJson(-1, '没有推广员信息');
        }
        outJson(-1, '查询失败！');
    }
    
    /**
     * 获取理财师姓名
     * @date 2016-07-23
     * @author jiangxy@yhxbank.com
     * @echo json
     */
    public function getLxsName() {
        //初始化信息
        $this->__init(-1, "获取理财师姓名", "返回成功或失败");
        //需要传递的参数
        $this->postRule = [
            'sales_phone' => [1, 'num', "理财师手机", 'sales_phone', 15502115555],
        ];
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        $get_data = must_post($this->postRule, $this->req, 1);
        $sales = M('sales_user')->get_one(array('mobile' => $get_data['sales_phone']));
        if (!$sales) {
            outJson(-1, '该理财师不存在');
        } elseif ($sales['status'] == 1) {
            outJson(-1, '该理财师已离职');
        }else{
            outJson(0, $sales['u_name']);
        }
    }


    /**
     * 获取微企汇用户姓名
     * @date 2017-08-17
     * @author yangz@yhxbank.com
     * @echo json
     */
    public function getWqhName() {
        //初始化信息
        $this->__init(-1, "获取微企汇姓名", "返回成功或失败");
        //需要传递的参数
        $this->postRule = [
            'sales_phone' => [1, 'num', "微企汇手机", 'sales_phone', 15502115555],
        ];
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        $get_data = must_post($this->postRule, $this->req, 1);
        $sales = M('wqh_user')->get_one(array('mobile' => $get_data['sales_phone']));
        if (!$sales) {
            outJson(-1, '该微企汇用户不存在');
        } elseif ($sales['status'] == 1) {
            outJson(-1, '该微企汇用户已离职');
        }else{
            outJson(0, $sales['u_name']);
        }
    }


    /**
     * 是否实名接口
     */
    public function isRealName(){
        $this->__init(-1, "是否实名接口", "输入用户，完成是否实名接口。");
        $this->postRule = [
            'user_id'   => [1, 'num', '用户id', 'user_id', '2239'],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $user_id = $get_data["user_id"];
        if(M('user')->checkRealName($user_id) === true){
            outJson(0, '已实名');
        }
        outJson(-1, '请先实名！');
    }

    /**
     * @function    根据用户ID查询已经投资了的所有项目列表；
     * @author      xuwb@yhxbank.com
     * @date        2017-03-03 10:56
     */
    public function getAllInvestListByUserId(){
        $this->__init(-1, "查询用户已经投资的所有产品列表", "输入用户，查询用户已经投资的所有产品列表。");
        $this->postRule = [
            'invest_user_id'    => [1, 'num', '用户id', 'invest_user_id', '2742'],
            'page'              => [1, 'num', "页码", 'page', 0],
            'page_size'         => [1, 'num', "显示条数", 'page_size', 20]
        ];
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        $get_data       =   must_post($this->postRule, $this->req, 1);
        $user_id        =   $get_data["invest_user_id"];
        $page           =   $get_data["page"];
        $page_size      =   $get_data["page_size"];
        $borrow_model   =   M('borrow');

        $sql    =   "SELECT * FROM ( ";
        $sql    .=  "SELECT ";
        $sql    .=  "a.id as id, ";
        $sql    .=  "a.borrow_id as borrow_id, ";
        $sql    .=  "a.money as money, ";
        $sql    .=  "a.invest_user_id as invest_user_id, ";
        $sql    .=  "a.add_time as add_time, ";
        $sql    .=  "a.end_time as end_time, ";
        $sql    .=  "a.status as status, ";
        $sql    .=  "CASE ";
        $sql    .=  "WHEN b.borrow_type = '速盈' THEN '元月通' ";
        $sql    .=  "WHEN b.borrow_type = '保盈' THEN '元季融' ";
        $sql    .=  "WHEN b.borrow_type = '稳盈' THEN '元定和' ";
        $sql    .=  "WHEN b.borrow_type = '元年鑫' THEN '元年鑫' ";
        $sql    .=  "ELSE '新手标' ";
        $sql    .=  "END AS type, ";
        $sql    .=  "b.interest_period AS period ";
        $sql    .=  "FROM y1_borrow_invest AS a ";
        $sql    .=  "LEFT JOIN y1_borrow AS b ON a.borrow_id = b.id ";
        $sql    .=  "WHERE a.invest_user_id = ". $user_id ." ";
        $sql    .=  "UNION ALL ";
        $sql    .=  "SELECT ";
        $sql    .=  "c.id as id, ";
        $sql    .=  "c.borrow_id as borrow_id, ";
        $sql    .=  "c.invest_money as money, ";
        $sql    .=  "c.invest_user_id as invest_user_id, ";
        $sql    .=  "c.first_borrow_time as add_time, ";
        $sql    .=  "c.interest_end_time as end_time, ";
        $sql    .=  "CASE ";
        $sql    .=  "WHEN c.return_status = '投资中' AND c.invest_status = '首投' THEN '投资中' ";
        $sql    .=  "WHEN c.return_status = '投资中' AND c.invest_status = '复投' THEN '复投' ";
        $sql    .=  "WHEN c.return_status = '赎回中' AND c.invest_status = '首投' THEN '赎回中' ";
        $sql    .=  "WHEN c.return_status = '赎回中' AND c.invest_status = '复投' THEN '赎回中' ";
        $sql    .=  "ELSE '已还款' ";
        $sql    .=  "END AS type, ";
        $sql    .=  "'元月盈' as type, ";
        $sql    .=  "d.interest_period AS period ";
        $sql    .=  "FROM y1_yyy_borrow_invest as c ";
        $sql    .=  "LEFT JOIN y1_yyy_borrow as d ON c.borrow_id = d.id ";
        $sql    .=  "WHERE c.invest_user_id = ". $user_id ." ";
        $sql    .=  "UNION ALL ";
        $sql    .=  "SELECT ";
        $sql    .=  "e.id as id, ";
        $sql    .=  "e.borrow_id as borrow_id, ";
        $sql    .=  "e.money as money, ";
        $sql    .=  "e.user_id as invest_user_id, ";
        $sql    .=  "e.invest_time as add_time, ";
        $sql    .=  "e.interest_end_time as end_time, ";
        $sql    .=  "e.invest_status as status, ";
        $sql    .=  "'私人尊享' as type, ";
        $sql    .=  "f.invest_period AS period ";
        $sql    .=  "FROM y1_appoint_borrow_invest as e ";
        $sql    .=  "LEFT JOIN y1_appoint_borrow as f ON e.borrow_id = f.id ";
        $sql    .=  "WHERE e.user_id = ". $user_id ." ";
        $sql    .=  "UNION ALL ";
        $sql    .=  "SELECT ";
        $sql    .=  "g.id as id, ";
        $sql    .=  "g.borrow_id as borrow_id, ";
        $sql    .=  "g.money as money, ";
        $sql    .=  "g.invest_user_id as invest_user_id, ";
        $sql    .=  "g.add_time as add_time, ";
        $sql    .=  "g.invest_end_time as end_time, ";
        $sql    .=  "g.status as status, ";
        $sql    .=  "'VIP' AS type, ";
        $sql    .=  "h.interest_period AS period ";
        $sql    .=  "FROM y1_vip_borrow_invest AS g ";
        $sql    .=  "LEFT JOIN y1_borrow AS h ON g.borrow_id = h.id ";
        $sql    .=  "WHERE g.invest_user_id = ". $user_id ." ";
        $sql    .=  "UNION ALL ";
        $sql    .=  "SELECT ";
        $sql    .=  "i.id as id, ";
        $sql    .=  "i.borrow_id as borrow_id, ";
        $sql    .=  "i.total_money as money, ";
        $sql    .=  "i.invest_user_id as invest_user_id, ";
        $sql    .=  "i.add_time as add_time, ";
        $sql    .=  "i.interest_end_time as end_time, ";
        $sql    .=  "i.status as status,  ";
        $sql    .=  "'薪盈计划' AS type, ";
        $sql    .=  "CONCAT( j.interest_period_month, '月') AS period ";
        $sql    .=  "FROM y1_wdy_borrow_invest AS i ";
        $sql    .=  "LEFT JOIN y1_wdy_borrow AS j ON i.borrow_id = j.id ";
        $sql    .=  "WHERE i.invest_user_id = ". $user_id ." ";
        $sql    .=  "UNION ALL ";
        $sql    .=  "SELECT ";
        $sql    .=  "k.id as id, ";
        $sql    .=  "k.borrow_id as borrow_id, ";
        $sql    .=  "k.money as money, ";
        $sql    .=  "k.user_id as invest_user_id, ";
        $sql    .=  "k.invest_time as add_time, ";
        $sql    .=  "k.interest_end_time as end_time, ";
        $sql    .=  "k.invest_status as statu,  ";
        $sql    .=  "l.borrow_type AS type, ";
        $sql    .=  "l.interest_period AS period ";
        $sql    .=  "FROM y1_mb_borrow_invest AS k ";
        $sql    .=  "LEFT JOIN y1_mb_borrow AS l ON k.borrow_id = l.id ";
        $sql    .=  "WHERE k.user_id =  ". $user_id ." ";
        $sql    .=  "UNION ALL ";
        $sql    .=  "SELECT ";
        $sql    .=  "m.id AS id, ";
        $sql    .=  "m.borrow_id AS borrow_id, ";
        $sql    .=  "m.money AS money, ";
        $sql    .=  "m.user_id AS invest_user_id, ";
        $sql    .=  "m.invest_time AS add_time, ";
        $sql    .=  "m.interest_end_time AS end_time, ";
        $sql    .=  "m.invest_status AS STATUS, ";
        $sql    .=  "'元聚盈' AS type, ";
        $sql    .=  "n.invest_period AS period ";
        $sql    .=  "FROM ";
        $sql    .=  "y1_ygzx_borrow_invest AS m ";
        $sql    .=  "LEFT JOIN y1_ygzx_borrow AS n ON m.borrow_id = n.id ";
        $sql    .=  "WHERE ";
        $sql    .=  "m.user_id = ". $user_id ." ";
        $sql    .=  " ) as all_tables ORDER BY add_time DESC ";

        $total_result_list  =   $borrow_model -> queryAll($sql);
        $total_result       =   count($total_result_list);
        $page_sql           =   $sql . " LIMIT " . $page * $page_size . "," . $page_size;
        $result_list        =   $borrow_model -> queryAll($page_sql);
        $result["list"]     =   $result_list;
        $result["total"]    =   $total_result;
        outJson(0, $result);
    }


    /*
     * @author:yyh
     * @function:解绑实名次数 微信*/
    public function unBindRnWe(){
        $this->__init(-1, "解绑实名次数、微信", "输入用户ID，解绑类型");
        $this->postRule = [
            'user_id'   => [1, 'num', '用户id', 'user_id', '2239'],
            'type_status'=>[1,'string','解绑类型','type_status','realName']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);

        $user_id = $get_data["user_id"];
        $user_info=M('user')->get_one(['id'=>$user_id]);

        if(empty($user_info)){
            outJson(-1,'该用户不存在!');
        }
        $type_status=$get_data["type_status"];
        if($type_status=='realName'){
            if($user_info['verify_times']>0){
                $data['verify_times']=0;
                $type='实名超限';
            }else{
                outJson(-1,'该用户没有实名次数超限');
            }
        }elseif($type_status=='weChat'){
            if($user_info['open_id']!=''){
                $data['open_id']='';
                $type='微信';
            }else{
                outJson(-1,'该用户没有绑定微信');
            }
        }
        $userUnbindInfo=M($this->m)->update($data,array('id'=>$user_id));
        if(!$userUnbindInfo){
            outJson(-101,'解绑'.$type.'失败');
        }
        outJson(0,'解绑'.$type.'成功!');
    }


    /**
     * @function 查询理财师名下的一级子用户；
     * @date     2017-06-12 11:16
     * @return   array();
     */
    public function getSubUser() {
        //初始化信息
        $this->__init(-1, "查询一级子用户", "返回查询到的子用户");
        //需要传递的参数
        $this->postRule = [
            'phone'     => [1, 'string', "用户电话", 'phone', 0],
            'user_id'   => [1, 'string', "理财师ID", 'user_id', 0],
        ];
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        $get_data       =   must_post($this->postRule, $this->req, 1);
        $phone          =   $get_data["phone"];
        $user_info      =   M("user")->get_one(array("phone" => $phone));
        if ($user_info == false) {
            outJson(-2, "请输入正确的手机号");
        }
        $user_id            =   $get_data["user_id"];
        $sales_user_model   =   M("sales_user");
        $user_model         =   M("user");
        $sales_user_phone   =   $user_model->get_one(array("id" => $user_id));
        $sales_user_info    =   $sales_user_model->get_one(array("mobile" => $sales_user_phone["phone"]));
        if ($sales_user_info == false || $sales_user_info["status"] == "1") {
            outJson(-3, "您当前不是理财师，不能转让此加息券");
        }
        $son_user_info      =   M("user")->getSubUser($user_id);
        foreach ($son_user_info["all_info"] as $k => $v){
            if ($v["mobile"] == $phone) {
                $user_info                  =   array();
                $user_info["id"]            =   $v["id"];
                $user_info["real_name"]     =   $v["real_name"];
                outJson(0, $user_info);
            }
        }
        outJson(-1, "此手机号未不是您的直接好友");
    }
    
    /**
     * @function 根据用户id，判断该用户是否在职公司员工；
     * @date     2017-07-26 10:26
     * @author   zhult
     */
    public function isSalesUser() {
        //初始化信息
        $this->__init(-1, "查询用户是否公司理财师", "成功返回1,否或失败返回0");
        //需要传递的参数
        $this->postRule = [
            'user_id'   => [0, 'string', "理财师ID", 'user_id', 0],
            'phone'     => [0, 'string', "用户电话", 'phone', 0],
        ];
        _show($this->req, $this->postRule, $this->resRule, '', $this->apiExplain, $this->resExplain, $this->funRank);
        $get_data       =   must_post($this->postRule, $this->req, 1);
        ///model实例化
        $user_model         = M("user");
        $sales_user_model   = M("sales_user");
        ///
//        $phone              = $get_data["phone"];
        $user_id            = $get_data["user_id"];
        if (empty($user_id)) {
            outJson(-1, 0);
        }
//        $sales_user_info    = $sales_user_model->get_one(array('mobile' => $phone,'status' => '0'));
//        if (!empty($sales_user_info)) {
//            outJson(0, 1);
//        }
        $user_info          = $user_model->get_one(array('id' => $user_id));
        if (empty($user_info)) {
            outJson(-1, 0);
        }
        $user_mobile        = $user_info['phone'];
        $sales_user_info_T  = $sales_user_model->get_one(array('mobile' => $user_mobile,'status' => '0'));
        if (!empty($sales_user_info_T)) {
            outJson(0, 1);
        }
        outJson(-1, 0);
        
    }

}
