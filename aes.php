<?php


class ase{
    private static $privateKey='111111';
    private static $iv='1111111';
    //加密
    public static function encrypt($data){
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,ase::$privateKey, $data, MCRYPT_MODE_CBC, ase::$iv);
        return (base64_encode($encrypted));
    }

    public static function decrypt($data){
        $encryptedData = base64_decode($data);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, ase::$privateKey, $encryptedData, MCRYPT_MODE_CBC,ase::$iv);
        return ($decrypted);
    }

}
?>
