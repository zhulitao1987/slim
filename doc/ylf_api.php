<?php
date_default_timezone_set('PRC');
class ylf_api {
    /**
     * 提交数据，只能用post
     * $param string $url
     * $param array $post
     * $param string $token
     * $return string
     */
    public function apiCurl($url, $post, $token) {
        if (empty($url) || empty($post) || empty($token)) {
            return "传递的参数不可为空";
        }
        foreach ($post as $key => $val){
            if (substr($key, 0, 1) != '_' && substr($key, -1) != '_') {
                $post[$key] = ase::encrypt(rtrim($val));
            }
        }
        $this->mkChkSign($post, $token);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $rtn = curl_exec($ch);
        curl_close($ch);
        $get_result = json_decode($rtn, true);
        if (!is_array($get_result)) {
            $get_result = [
                'time' => date('Y-m-d H:i:s'), 'error_id' => '-1',
                'error' => '系统错误，请联系管理员', "msg" => '系统错误，请联系管理员'
            ];
        }
        return $get_result;
    }

    /**
     * 生产验证字符串
     * @param array $array
     * @param string $token
     * @return mixed
     */
    protected function mkChkSign(&$array, $token) {

        if (array_key_exists('_sign_', $array)) {
            unset($array['_sign_']);
        }
        $array['_time_'] = time();
        ksort($array);
        $array['_sign_'] = md5(http_build_query($array) . $token);
    }

}