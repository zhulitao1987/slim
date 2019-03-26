<?php
set_time_limit(0);
header("Content-Type: text/html; charset=UTF-8");
include_once "config.php";
include_once "function.php";

class hd_llb
{
    public function sent_llb($smobile, $llb_num)
    {
        if (!$smobile || !$llb_num) {
            return false;
        }
        /**
         * 网关地址
         */
        $gwUrl = 'http://123.57.210.65:80/outerservice/request';
//        $smobile = '13651502815';
        //移动段号
        $cmccList = [134, 135, 136, 137, 138, 139, 150, 151, 152, 154, 157, 158, 159, 187, 188, 147];
        //联通段号
        $cuccList = [130, 131, 132, 155, 156, 185, 186];
        //电信段号
        $ctccList = [133, 153, 189, 180];
        //流量包数据
        //$countList = [100 => 2000, 200 => 1500, 500 => 1000, 1000 => 500];
        $countList = [100  => ['ctcc' => 5, 'cmcc' => [12, 13], 'cucc' => 25],
                      200  => ['ctcc' => 6, 'cmcc' => [12, 12, 13, 13], 'cucc' => [25, 25]],
                      500  => ['ctcc' => 7, 'cmcc' => 15, 'cucc' => 26],
                      1000 => ['ctcc' => 7, 'cmcc' => 15, 'cucc' => 26]];
        $appID = '3b2a562a-bc1b-485a-b44d-a69969db5f45';  //APPID用于识别用户，该APPID由亿美提供，可从亿美商务人员那里获取
        $sTOKEN = '440aa647385741e8';  //TOKEN用于加密，该TOKEN由亿美提供，可从亿美商务人员那里获取
        $taskNo = getOrderId();
        $sctcc = '0';            //电信套餐编号
        $scucc = '0';            //联通套餐编号
        $scmcc = '0';           //移动套餐编号
        $setype = '0';           //生效类型，0:立即生效，1:下月生效
        //手机号段类型 cmcc 移动 cucc 联通 ctcc 电信
        $is_type = 0;
        $sList = [];
        $sub_phone = substr($smobile, 0, 3);
        //判断类型
        if (in_array($sub_phone, $cmccList)) {
            $is_type = "cmcc";
        }
        if (in_array($sub_phone, $cuccList)) {
            $is_type = "cucc";
        }
        if (in_array($sub_phone, $ctccList)) {
            $is_type = "ctcc";
        }
        //获取数据
        if (isset($countList[$llb_num])) {
            $sList = $countList[$llb_num][$is_type];
        }
        if ($is_type && $sList) {
            //数组循环送
            if (is_array($sList)) {
                foreach ($sList as $value) {
                    if ($is_type == "cmcc") {
                        $scmcc = $value;
                    } elseif ($is_type == "cucc") {
                        $scucc = $value;
                    } else {
                        $sctcc = $value;
                    }
                    $newTaskNo = $taskNo . $value;
                    $ID_key = 'mobiles=' . $smobile . '&taskNo=' . $newTaskNo . '&ctcc=' . $sctcc . '&cucc=' . $scucc . '&cmcc=' . $scmcc . '&etype=' . $setype;
                    $ID_value = 'mobiles=' . $smobile . '&taskNo=' . $newTaskNo . '&ctcc=' . $sctcc . '&cucc=' . $scucc . '&cmcc=' . $scmcc . '&etype=' . $setype;
                    $md5_key = md5($ID_key);      //MD5加密
                    $base64_val = $this->encrypt($ID_value, $sTOKEN);  //AES,Base64加密
                    $base64_val = urlencode($base64_val);
                    $postStr1 = 'key=' . $md5_key . '&value=' . $base64_val . '&appId=' . $appID;   //POST的内容
                    echo $this->post($gwUrl, $postStr1);
                }
            } else {
                if ($is_type == "cmcc") {
                    $scmcc = $sList;
                } elseif ($is_type == "cucc") {
                    $scucc = $sList;
                } else {
                    $sctcc = $sList;
                }
                $newTaskNo = $taskNo . $sList;
                $ID_key = 'mobiles=' . $smobile . '&taskNo=' . $newTaskNo . '&ctcc=' . $sctcc . '&cucc=' . $scucc . '&cmcc=' . $scmcc . '&etype=' . $setype;
                $ID_value = 'mobiles=' . $smobile . '&taskNo=' . $newTaskNo . '&ctcc=' . $sctcc . '&cucc=' . $scucc . '&cmcc=' . $scmcc . '&etype=' . $setype;
                $md5_key = md5($ID_key);      //MD5加密
                $base64_val = $this->encrypt($ID_value, $sTOKEN);  //AES,Base64加密
                $base64_val = urlencode($base64_val);
                $postStr1 = 'key=' . $md5_key . '&value=' . $base64_val . '&appId=' . $appID;   //POST的内容
                echo $this->post($gwUrl, $postStr1);
            }
            return true;
        } else {
            return false;
        }
    }

    private function post($url, $post_data = '', $timeout = 5)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($post_data != '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;
    }

    private function encrypt($input, $key)
    {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = $this->pkcs5_pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    private function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
}

$llb = new hd_llb();
$llb->sent_llb("13651502815", 100);

