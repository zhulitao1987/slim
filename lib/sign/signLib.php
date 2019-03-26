<?php

/**
 * 签章API
 *
 * @author zhult
 */
class signLib extends Lib {
    
    /**
     * @todo 初始化和登录
     * @author zhult@ylfcf.com
     * @return JSON
     * @time 2017-10-18 09:28
     */
    public function init() {
        //初始化信息
        $this->__init(-1, "请求init接口", "成功返回成功信息，失败返回失败信息");
        //需要传递的参数
        $this->postRule = [
            'is_request'            => [1, 'num', "是否请求API", 'is_request', 1]
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data = must_post($this->postRule, $this->req, 1);
        $is_request = isset($get_data['is_request']) ? $get_data['is_request'] : 0;
        if (empty($is_request)) {
            outJson(-1, "请求非法");
        }
        //model
        $signModel = M('sign');
        //
        $ret = $signModel->init();
        outJson(0, $ret);
    }
    
    /**
     * @todo 添加个人用户
     * @author zhult@ylfcf.com
     * @return JSON
     * @time 2017-10-18 09:28
     */
    public function addPerson(){
        //初始化信息
        $this->__init(-1, "请求添加个人用户接口", "成功返回成功信息，失败返回失败信息");
        //需要传递的参数
        $this->postRule = [
            'mobile'          => [1, 'string', "手机号码", 'mobile', ''],
            'name'            => [1, 'string', "姓名", 'name', ''],
            'idNo'            => [1, 'string', "身份证号/护照号", 'idNo', ''],
            'personarea'      => [0, 'string', "归属地", 'personarea', ''],
            'email'           => [0, 'string', "邮箱地址", 'email', ''],
            'organ'           => [0, 'string', "所属公司", 'organ', ''],
            'title'           => [0, 'string', "职位", 'title', ''],
            'address'         => [0, 'string', "常用地址", 'address', '']
            
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data   = must_post($this->postRule, $this->req, 1);
        $mobile     = isset($get_data['mobile'])     ? $get_data['mobile']     : '';
        $name       = isset($get_data['name'])       ? $get_data['name']       : '';
        $idNo       = isset($get_data['idNo'])       ? $get_data['idNo']       : '';
        $personarea = isset($get_data['personarea']) ? $get_data['personarea'] : '0';
        $email      = isset($get_data['email'])      ? $get_data['email']      : '';
        $organ      = isset($get_data['organ'])      ? $get_data['organ']      : '';
        $title      = isset($get_data['title'])      ? $get_data['title']      : '';
        $address    = isset($get_data['address'])    ? $get_data['address']    : '';
        //model
        $signModel = M('sign');
        //
        $ret = $signModel->addPerson(
                $mobile, $name, $idNo, $personarea, $email, $organ, $title, $address
        );
        outJson(0, $ret);
    }
    
    /**
     * @todo 添加企业用户
     * @author zhult@ylfcf.com
     * @return JSON
     * @time 2017-10-18 09:28
     */
    public function addOrganize(){
        //初始化信息
        $this->__init(-1, "请求添加企业用户接口", "成功返回成功信息，失败返回失败信息");
        //需要传递的参数
        $this->postRule = [
            'mobile'          => [0, 'string', "手机号码", 'mobile', ''],
            'name'            => [1, 'string', "机构名称", 'name', ''],
            'organCode'       => [1, 'string', "组织机构代码号或社会信用代码号, 工商注册号", 'organCode', ''],
            'regType'         => [1, 'string', "企业注册类型。含组织机构代码号、多证合一或工商注册码，默认组织机构代码号。", 'regType', ''],
            'email'           => [0, 'string', "邮箱地址", 'email', ''],
            'organType'       => [0, 'string', "单位类型，0-普通企业，1-社会团体，2-事业单位，3-民办非企业单位，4-党政及国家机构，默认0", 'organType', ''],
            'legalArea'       => [0, 'string', "法定代表人归属地, 默认大陆", 'legalArea', ''],
            'userType'        => [0, 'string', "注册类型", 'userType', ''],
            'agentName'       => [0, 'string', "代理人姓名，当userType为1时必填", 'agentName', ''],
            'agentIdNo'       => [0, 'string', "代理人身份证号，当userType为1时必填", 'agentIdNo', ''],
            'legalName'       => [0, 'string', "法定代表姓名，当userType为2时必填", 'legalName', ''],
            'legalIdNo'       => [0, 'string', "法定代表身份证号/护照号，当userType为2时必填", 'legalIdNo', ''],
            'address'         => [0, 'string', "公司地址", 'address', ''],
            'scope'           => [0, 'string', "经营范围", 'scope', '']
            
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data   = must_post($this->postRule, $this->req, 1);
        $mobile     = isset($get_data['mobile'])     ? $get_data['mobile']     : '';
        $name       = isset($get_data['name'])       ? $get_data['name']       : '';
        $organCode  = isset($get_data['organCode'])  ? $get_data['organCode']  : '';
        $regType    = isset($get_data['regType'])    ? $get_data['regType']    : '';
        $email      = isset($get_data['email'])      ? $get_data['email']      : '';
        $organType  = isset($get_data['organType'])  ? $get_data['organType']  : '0';
        $legalArea  = isset($get_data['legalArea'])  ? $get_data['legalArea']  : '0';
        $userType   = isset($get_data['userType'])   ? $get_data['userType']   : '';
        $agentName  = isset($get_data['agentName'])  ? $get_data['agentName']  : '';
        $agentIdNo  = isset($get_data['agentIdNo'])  ? $get_data['agentIdNo']  : '';
        $legalName  = isset($get_data['legalName'])  ? $get_data['legalName']  : '';
        $legalIdNo  = isset($get_data['legalIdNo'])  ? $get_data['legalIdNo']  : '';
        $address    = isset($get_data['address'])    ? $get_data['address']    : '';
        $scope      = isset($get_data['scope'])      ? $get_data['scope']      : '';
        //model
        $signModel = M('sign');
        //
        $ret = $signModel->addOrganize(
                $mobile, $name, $organCode, $regType, $email, $organType, $legalArea, $userType, $agentName, $agentIdNo, $legalName, $legalIdNo, $address, $scope
        );
        outJson(0, $ret);
    }
    
    /**
     * @todo 新建模版印章
     * @author zhult@ylfcf.com
     * @return JSON
     * @time 2017-10-18 09:28
     */
    public function addTemplateSeal() {
        //初始化信息
        $this->__init(-1, "请求新建模版印章接口", "成功返回成功信息，失败返回失败信息");
        //需要传递的参数
        $this->postRule = [
            'accountId'       => [1, 'string', "待创建印章的账户标识", 'accountId', ''],
            'templateType'    => [1, 'string', "个人印章模板类型，类型值：参考 PersonTemplateType 中的定义", 'templateType', ''],
            'color'           => [1, 'string', "印章颜色，颜色值：参考SealColor中的定义", 'color', ''],
            'hText'           => [0, 'string', "生成印章中的横向文内容（如果是条形章，此项必填；显示为条形章的第二行公司英文名称等附加信息）；", 'hText', ''],
            'qText'           => [0, 'string', "生成印章中的下弦文内容", 'qText', '']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data      = must_post($this->postRule, $this->req, 1);
        $accountId     = isset($get_data['accountId'])   ? $get_data['accountId']     : '';
        $templateType  = isset($get_data['templateType'])? $get_data['templateType']  : '';
        $color         = isset($get_data['color'])       ? $get_data['color']         : '';
        $hText         = isset($get_data['hText'])       ? $get_data['hText']         : '';
        $qText         = isset($get_data['qText'])       ? $get_data['qText']         : '';
        //model
        $signModel = M('sign');
        //
        $ret = $signModel->addTemplateSeal(
                $accountId, $templateType, $color, $hText, $qText
        );
        outJson(0, $ret);
    }
    
    /**
     * @todo 添加手绘印章
     * @author zhult@ylfcf.com
     * @return JSON
     * @time 2017-10-18 09:28
     */
    public function addFileSeal() {
        //初始化信息
        $this->__init(-1, "请求新建模版印章接口", "成功返回成功信息，失败返回失败信息");
        //需要传递的参数
        $this->postRule = [
            'sealData'       => [1, 'string', "个人用户手机号码", 'sealData', '']
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data      = must_post($this->postRule, $this->req, 1);
        $sealData      = isset($get_data['sealData'])   ? $get_data['sealData']     : '';
        //model
        $signModel = M('sign');
        //
        $ret = $signModel->addFileSeal($sealData);
        outJson(0, $ret);
    }
    
    /**
     * @todo 平台用户签署
     * @author zhult@ylfcf.com
     * @return array
     * @time 2017-10-18 09:28
     */
    public function userSignPDF(){
        //初始化信息
        $this->__init(-1, "请求平台用户签署接口", "成功返回成功信息，失败返回失败信息");
        //需要传递的参数
        $this->postRule = [
            'accountId'       => [1, 'string', "签署账户标识。需要签署的账号在e签宝平台中的唯一标识，以此获取账户的证书进行签署", 'accountId', ''],
            'signType'        => [0, 'string', "签章类型。 值： 参考 SignType签章类型 中的定义。", 'signType', ''],
            'posPage'         => [0, 'string', "签署页码，若为多页签章，支持页码格式“1-3,5,8“，若为坐标定位时，不可空", 'posPage', ''],
            'posX'            => [0, 'string', "签署位置X坐标，，若为关键字定位，相对于关键字的X坐标偏移量，默认0", 'posX', ''],
            'posY'            => [0, 'string', "签署位置Y坐标，，若为关键字定位，相对于关键字的Y坐标偏移量，默认0", 'posY', ''],
            'key'             => [0, 'string', "关键字，仅限关键字签章时有效，若为关键字定位时，不可空", 'key', ''],
            'isQrcodeSign'    => [0, 'string', "签署印章带二维码，可以扫描二维码查看签署详情。True：二维码签署； Fasle：普通签署；默认false", 'isQrcodeSign', ''],
            'srcFile'         => [1, 'string', "待签署PDF文档本地路径，含文档名", 'srcFile', ''],
            'dstFile'         => [1, 'string', "签署后PDF文档本地路径，含文档名", 'dstFile', ''],
            'fileName'        => [0, 'string', "文档名称，e签宝签署日志对应的文档名，若为空则取文档路径中的名称", 'fileName', ''],
            'sealData'        => [0, 'string', "印章图片数据。印章图片文件的base64字符串。可以是任意的图片，也可以通过创建印章模板接口获取。如果为空，签署位置不显示印章，但签署任然有效。", 'sealData', ''],
            'stream'          => [0, 'string', "是否文件流签署", 'stream', false],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data      = must_post($this->postRule, $this->req, 1);
        $accountId     = isset($get_data['accountId'])   ? $get_data['accountId']     : '';
        $signType      = isset($get_data['signType'])    ? $get_data['signType']      : '';
        $posPage       = isset($get_data['posPage'])     ? $get_data['posPage']       : '';
        $posX          = isset($get_data['posX'])        ? $get_data['posX']          : '';  
        $posY          = isset($get_data['posY'])        ? $get_data['posY']          : '';
        $key           = isset($get_data['key'])         ? $get_data['key']           : '';
        $isQrcodeSign  = isset($get_data['isQrcodeSign'])? $get_data['isQrcodeSign']  : 'false';
        $srcFile       = isset($get_data['srcFile'])     ? $get_data['srcFile']       : '';
        $dstFile       = isset($get_data['dstFile'])     ? $get_data['dstFile']       : '';
        $fileName      = isset($get_data['fileName'])    ? $get_data['fileName']      : '';
        $sealData      = isset($get_data['sealData'])    ? $get_data['sealData']      : '';
        $stream        = isset($get_data['stream'])      ? $get_data['stream']        : false;
        //model
        $signModel = M('sign');
        //
        $ret = $signModel->userSignPDF(
                $accountId, $signType, $posPage, $posX, $posY, $key, $isQrcodeSign, $srcFile, $dstFile, $fileName, $sealData, $stream
        );
        outJson(0, $ret);
    }
    
    /**
     * @todo 平台自身签署
     * @author zhult@ylfcf.com
     * @return JSON
     * @time 2017-10-18 09:28
     */
    public function selfSignPDF(){
        //初始化信息
        $this->__init(-1, "请求平台自身签署接口", "成功返回成功信息，失败返回失败信息");
        //需要传递的参数
        $this->postRule = [
            'sealId'          => [0, 'string', "签署印章的标识，为0表示用默认印章签署，默认0", 'sealId', ''],
            'signType'        => [0, 'string', "签章类型。 值： 参考 SignType签章类型 中的定义。", 'signType', ''],
            'posPage'         => [0, 'string', "签署页码，若为多页签章，支持页码格式“1-3,5,8“，若为坐标定位时，不可空", 'posPage', ''],
            'posX'            => [0, 'string', "签署位置X坐标，，若为关键字定位，相对于关键字的X坐标偏移量，默认0", 'posX', ''],
            'posY'            => [0, 'string', "签署位置Y坐标，，若为关键字定位，相对于关键字的Y坐标偏移量，默认0", 'posY', ''],
            'key'             => [0, 'string', "关键字，仅限关键字签章时有效，若为关键字定位时，不可空", 'key', ''],
            'isQrcodeSign'    => [0, 'string', "签署印章带二维码，可以扫描二维码查看签署详情。True：二维码签署； Fasle：普通签署；默认false", 'isQrcodeSign', ''],
            'srcFile'         => [1, 'string', "待签署PDF文档本地路径，含文档名", 'srcFile', ''],
            'dstFile'         => [1, 'string', "签署后PDF文档本地路径，含文档名", 'dstFile', ''],
            'fileName'        => [0, 'string', "文档名称，e签宝签署日志对应的文档名，若为空则取文档路径中的名称", 'fileName', ''],
            'stream'          => [0, 'string', "是否文件流签署", 'stream', false],
        ];
        //是否显示post参数或者res参数
        _show($this->req, $this->postRule, $this->resRule, $this->cacheName, $this->apiExplain, $this->resExplain, $this->funRank);
        //过滤参数，查看是否传递必须的数据
        $get_data      = must_post($this->postRule, $this->req, 1);
        $sealId        = isset($get_data['sealId'])      ? $get_data['sealId']        : '';
        $signType      = isset($get_data['signType'])    ? $get_data['signType']      : '';
        $posPage       = isset($get_data['posPage'])     ? $get_data['posPage']       : '';
        $posX          = isset($get_data['posX'])        ? $get_data['posX']          : '';  
        $posY          = isset($get_data['posY'])        ? $get_data['posY']          : '';
        $key           = isset($get_data['key'])         ? $get_data['key']           : '';
        $isQrcodeSign  = isset($get_data['isQrcodeSign'])? $get_data['isQrcodeSign']  : 'false';
        $srcFile       = isset($get_data['srcFile'])     ? $get_data['srcFile']       : '';
        $dstFile       = isset($get_data['dstFile'])     ? $get_data['dstFile']       : '';
        $fileName      = isset($get_data['fileName'])    ? $get_data['fileName']      : '';
        $stream        = isset($get_data['stream'])      ? $get_data['stream']        : false;
        //model
        $signModel = M('sign');
        //
        $ret = $signModel->selfSignPDF(
                $sealId, $signType, $posPage, $posX, $posY, $key, $isQrcodeSign, $srcFile, $dstFile, $fileName, $stream
        );
        outJson(0, $ret);
    }
    
}
