<?php

/*
 * 寻人项目后端和IDL图片库交互类基类, 该类不允许直接被实例化，通过子类实例化
 * 
 * Author: 吕宝贵 
 *
 */


namespace lubaogui\face;

/**
 * BaseRepository类是和IDL后台服务交互的基类，主要完成图片请求的入库，检索和删除请求的底层实现 
 *
 */
abstract class BaseRepository extends \yii\base\Object
{

    //错误代码设置
    const PARAM_MISSING = 1001;
    const PARAM_ILEGAL = 1002;
    const SERVICE_REQUEST_FAILED = 2001;
    const APPLICATION_ERROR = 3001;

    //动作常量设置
    const ACTION_ADD = 'add';
    const ACTION_DELETE = 'delete';

    //图片库url地址
    public $reposUrl;

    //图片的最大大小限制,0表示无限制
    public $maxFileSize = 0;

    //请求动作 
    public $action;

    //图片编码方式,默认为1，base64编码方式
    public $encoding = 1;

    //检索库id
    public $dbid;

    //相同图片是否采用覆盖策略, 默认覆盖
    public $overwrite = 1;

    //错误信息编码,0为默认值代表无错误
    private $_errorNo = 0;

    //错误信息内容
    private $_errorMsg = '';


    /**
     * 入库保存上传的图片
     *
     * @param string $type 请求类型.该值由IDL提供,具体咨询IDL接口人
     * @param array $imageUrls  可抓取的图片url数组,键为应用中图片唯一标识id, 值为可抓取的图片url地址
     * @return bollen  是否成功保存
     */
    public function saveImage($type, $imageUrls) {

        $postData = [];
        $postData['action'] = self::ACTION_ADD;
        $postData['type'] = $type;

        //构造idl库需要的图片组织形式
        $images = [];
        foreach ($imageUrls as $imageId=>$imageUrl) {
            $images[$imageUrl]['unique_id'] = $imageId;
            $images[$imageUrl]['dbid'] = $this->dbid;
            //相同图片采用覆盖策略
            $images[$imageUrl]['overwrite'] = $this->overwrite;
            $images[$imageUrl]['callback'] = '';
        }

        $postData['param'] = json_encode($images);
        return $this->sendRequest($postData);
    }

    /**
     * 根据提交的图片检索图片库中存在的相似图片 
     *
     * @param string $reposType 检索库的类型,参数由IDL服务提供.
     * @param string $photoPath 提交照片的本地路径.
     * @param reference of array  &$result  保存返回结果的对象指针 
     * @return bollen  是否检索成功
     */
    public function searchByImage($reposType, $photoPath, &$result) {

        $postData = [];
        $postData['type'] = $reposType;
        $postData['encoding'] = $this->encoding;
        if (file_exists($photoPath)) {
            $postData['image'] = base64_encode(file_get_contents($photoPath));
        }
        else {
            $this->setError('file does not exist!', self::PARAM_ILEGAL );
            return false;
        }
        $result = $this->sendRequest($postData);
        $resultArray = json_decode($result, true);
        if (!$resultArray || $resultArray['errno']!=0) {
            $this->setError('idl return error!', self::PARAM_ILEGAL );
            return false;
        }
        else {
            $result = $resultArray['ret'];
        }
        return true;

    }

    /**
     * 删除照片信息(从头像检索库中删除，通常发生在家属确认人员找到之后)
     *
     * @param string $type  由IDL服务提供的类型参数，固定且必须 
     * @param array $imageUrls 人脸信息的唯一标识,支持批量数组提交  [['unique_id'=>$url]]
     * @return bollen  是否成功保存图片
     */
    public function deleteImage($type, $imageUrls) {

        $postData = [];
        $postData['type'] = $type;
        $postData['action'] = self::ACTION_DELETE;

        $images = [];
        foreach ($imageUrls as $imageId => $imageUrl) {
            $images[$imageUrl]['dbid'] = $this->dbid; 
            $images[$imageUrl]['unique_id'] = $imageId; 
        }

        $postData['param'] = json_encode($images);
        $result = $this->sendRequest($postData);
        return $result;

    }

    /**
     * 向后端发送图片入库或者检索请求 
     *
     * @param array reference $postData 请求信息数组 
     * @return bollen 请求是否成功 
     */
    private function sendRequest(&$postData) {

        $postStr = '';
        foreach ($postData as $key=>$value) {
            if ($key == 'image') {
                $postStr .= "{$key}=".$value."&";
            }
            else {
                $postStr .= "{$key}=".urlencode($value)."&";
            }
        }

        //请求的选项数组，固定格式
        $options = [
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $postStr
            ];

        //使用curl提交请求
        $curl = curl_init($this->reposUrl);
        curl_setopt_array($curl, $options);
        $output = curl_exec($curl);
        if ($output === false) {
            $this->setError(curl_errno($curl), curl_error($curl)) ;
            return false;
        }
        else {
            curl_close($curl);
            if (empty($output)) {
                $output['errno'] = 0;
                $output['errmas'] = '';
                $output['ret'] = [];
            }
            else {
                $output = json_decode($output, true);
            }

            //json解析失败，则设置错误并返回
            if (empty($output)) {
                $this->setError(self::SERVICE_REQUEST_FAILED, 'json_decode of the return output failed!');
                return false;
            }

            //第三方服务返回错误信息
            if ($output['errno']) {
                $this->setError(self::SERVICE_REQUEST_FAILED, $output['errno'].':'.$output['errmas']);
                return false;
            }
            return $output;
        }
    }

    /**
     * 设置错误信息 
     *
     * @param int $errorNo 错误编号.
     * @param string $errorMsg 错误信息.
     * @return 无返回信息
     */
    protected function setError($errorNo, $errorMsg) {
        $this->_errorNo = $errorNo;
        $this->_errorMsg = $errorMsg;
    }

    /**
     * 获取错误编码信息, 当返回错误时，可以通过getErrorNo和getErrorMsg组合查出具体的错误信息 
     *
     * @return int 错误编码
     */
    public function getErrorNo() {
        return $this->_errorNo;
    }

    /**
     * 获取错误详细信息 
     *
     * @return string  错误信息
     */
    public function getErrorMsg() {
        return $this->_errorMsg;
    }

}   
