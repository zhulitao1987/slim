<?php

require_once API_DIR . '/sign/eSignOpenAPI.php';

/**
 * 签章类Model
 *
 * @author zhult
 */
class signModel extends Model {
    
    /**
     * @todo 初始化和登录
     * @author zhult@ylfcf.com
     * @return array
     * @time 2017-10-18 09:28
     */
    public function init() {
        $sign = new eSign();
        $iRet = $sign->init();
        if (0 == $iRet) {
            return array(
                "errCode" => 0,
                "msg" => "初始化成功",
                "errShow" => true
            );
        }
        return array();
    }
    
    /**
     * @todo 添加个人用户
     * @author zhult@ylfcf.com
     * @return array
     * @time 2017-10-18 09:28
     */
    public function addPerson(
                              $mobile, 
                              $name, 
                              $idNo, 
                              $personarea, 
                              $email, 
                              $organ, 
                              $title, 
                              $address
        ) {
        $sign = new eSign();
        $ret = $sign->addPersonAccount(
                $mobile, $name, $idNo, $personarea, $email, $organ, $title, $address
        );
        return $ret;
    }

    /**
     * @todo 添加企业用户
     * @author zhult@ylfcf.com
     * @return array
     * @time 2017-10-18 09:28
     */
    public function addOrganize(
                                $mobile, 
                                $name, 
                                $organCode, 
                                $regType, 
                                $email, 
                                $organType, 
                                $legalArea, 
                                $userType, 
                                $agentName, 
                                $agentIdNo, 
                                $legalName, 
                                $legalIdNo, 
                                $address, 
                                $scope
        ) {
        $sign = new eSign();
        $ret = $sign->addOrganizeAccount(
                $mobile, $name, $organCode, $regType, $email, $organType, $legalArea, $userType, $agentName, $agentIdNo, $legalName, $legalIdNo, $address, $scope
        );
        return $ret;
    }
    
    /**
     * @todo 新建模版印章
     * @author zhult@ylfcf.com
     * @return array
     * @time 2017-10-18 09:28
     */
    public function addTemplateSeal(
                                    $accountId, 
                                    $templateType, 
                                    $color, 
                                    $hText, 
                                    $qText
        ) {
        $sign = new eSign();
        $ret = $sign->addTemplateSeal(
                $accountId, $templateType, $color, $hText, $qText
        );
        return $ret;
    }
    
    /**
     * @todo 添加手绘印章
     * @author zhult@ylfcf.com
     * @return array
     * @time 2017-10-18 09:28
     */
    public function addFileSeal($sealData) {
        $imgB64 = substr($sealData, strpos($sealData, ',') + 1);
        $ret['errCode'] = 0;
        $ret['sealData'] = $imgB64;
        return $ret;
    }
    
    /**
     * @todo 平台用户签署
     * @author zhult@ylfcf.com
     * @return array
     * @time 2017-10-18 09:28
     */
    public function userSignPDF (
                                $accountId,
                                $signType,
                                $posPage,
                                $posX,
                                $posY,
                                $key,
                                $isQrcodeSign,
                                $srcFile,
                                $dstFile,
                                $fileName,
                                $sealData
        ) {
        $sign = new eSign();
        $signPos = array(
            'posPage'       => !empty($posPage) ? $posPage : 1,
            'posX'          =>  $posX,
            'posY'          => $posY,
            'key'           =>  $key,
            'width'         => '',
            'isQrcodeSign'  => isset($isQrcodeSign) && $isQrcodeSign == "false" ? false : true
        );
        $signFile = array(
            'srcPdfFile'    => $srcFile,
            'dstPdfFile'    => $dstFile,
            'fileName'      => $fileName,
            'ownerPassword' => ''
        );

        $ret = $sign->userSignPDF(
                $accountId, $signFile, $signPos, $signType, $sealData, false
        );
        return $ret;
    }
    
    /**
     * @todo 平台自身签署
     * @author zhult@ylfcf.com
     * @return array
     * @time 2017-10-18 09:28
     */
    public function selfSignPDF (
                                $sealId,
                                $signType,
                                $posPage,
                                $posX,
                                $posY,
                                $key,
                                $isQrcodeSign,
                                $srcFile,
                                $dstFile,
                                $fileName
        ){
        $sign = new eSign();
        $signPos = array(
            'posPage'       => $posPage,
            'posX'          =>  $posX,
            'posY'          => $posY,
            'key'           =>  $key,
            'width'         => '',
            'isQrcodeSign'  => isset($isQrcodeSign) && $isQrcodeSign == "false" ? false : true
        );
        $signFile = array(
            'srcPdfFile'    => $srcFile,
            'dstPdfFile'    => $dstFile,
            'fileName'      => $fileName,
            'ownerPassword' => ''
        );
        $ret = $sign->selfSignPDF(
                $signFile, $signPos, $sealId, $signType, false
        );
        return $ret;
    }

}
    