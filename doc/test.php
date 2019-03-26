<?php

class Test
{
    public $postRule = []; //需要传递的参数
    public $m = '';
    public $f = '';
    public $is_show = 0;
    protected $token = 'test';

    public function run_test()
    {
        $url = SITE_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . '/' . $this->m . $this->f;
        $par = $this->postRule;
        $this->mkChkSign($par, $this->token);
        $res = $this->curl($url, $par);
        $apiData = json_decode($res, true);
        if (!isset($apiData['error_id'])) {
            echo "<pre>";
            echo "<font color='red'><b>[错误]</b></font>" . $this->m . $this->f;
            echo "</pre>";
        }
        echo "<pre>";
        if ($apiData['error_id'] === 0) {
            echo "<font color='blue'><b>[成功]</b></font>" . $this->m . $this->f;
        } else {
            echo "<font color='orange'><b>[失败]</b></font>" . $this->m . $this->f . " :: " . $apiData['error'];
        }
        echo "</pre>";
    }

    public function run_look()
    {
        $url = SITE_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . '/' . $this->m . $this->f;
        $par = $this->postRule;
        $this->mkChkSign($par, $this->token);
        echo "提交数据->";
        var_dump($par);
        echo "返回数据->";
        $res = $this->curl($url, $par);
        $apiData = json_decode($res, true);
        echo "<pre>";
        print_r($apiData);
        echo "</pre>";
    }

    public function run_get()
    {
        $url =SITE_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . '/' . $this->m . $this->f;
        $par = $this->postRule;
        $par['_POS_'] = 1;
        $par['_RES_'] = 1;
        $this->mkChkSign($par, $this->token);
        $res = $this->curl($url, $par);
        $apiData = json_decode($res, true);
        return $apiData;
    }

    public function run()
    {
        $url = SITE_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . $this->m . $this->f;
        $par = $this->postRule;
        $par['_POS_'] = 1;
        $par['_RES_'] = 1;
        $this->mkChkSign($par, $this->token);
        $res = $this->curl($url, $par);
        $apiData = json_decode($res, true);
        return $apiData;
    }

    /**
     * 提交数据，现只能用post
     */
    private function curl($url, $post)
    {
        if (empty($post)) {
            echo "请POST提交数据";
            return false;
        }
        foreach ($post as $key => $val){
            if (substr($key, 0, 1) != '_' && substr($key, -1) != '_') {
                $post[$key] = ase::encrypt(rtrim($val));
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $rtn = curl_exec($ch);
        curl_close($ch);
        if($this->is_show){
            print_r($rtn);
        }
        return $rtn;
    }

    private function mkChkSign(&$array, $token)
    {
        if (array_key_exists('_sign_', $array)) {
            unset($array['_sign_']);
        }
        $array['_time_'] = time();
        ksort($array);
        $array['_sign_'] = md5(http_build_query($array) . $token);
    }

}
