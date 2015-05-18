<?php

/*
 * 寻人项目后端和IDL图片库交互类基类, 该类不允许直接被实例化，通过子类实例化
 * 
 * Author: 吕宝贵 
 *
 */


namespace lubaogui\face;

use Yii;

/**
 * BaseRepository类是和IDL后台服务交互的基类，主要完成图片请求的入库，检索和删除请求的底层实现 
 *
 */
abstract class BaseRepository extends \yii\base\Object
{

    //错误代码
    const PARAM_MISSING = 1001;
    const PARAM_ILLEGAL = 1002;
    const SERVICE_REQUEST_FAILED = 2001;
    const APPLICATION_ERROR = 3001;

    //动作常量
    const ACTION_ADD = 'add';
    const ACTION_DELETE = 'delete';

    //action type常量
    const ACTION_TYPE_ADD = 'st_ruku_indexgen';
    const ACTION_TYPE_DELETE = 'st_ruku_indexgen';
    const ACTION_TYPE_SEARCH = 'st_visplat_search_';

    //编码方式
    const ENCODING_TYPE_BASE64 = 1;

    /**
     * @var string 图片库url地址, 在配置文件中配置加载
     */
    public $reposUrl;

    /**
     * @var number 图片文件的大小上限
     */
    public $maxFileSize = 0;

    /**
     * @var int 图片编码方式,默认为base64编码方式
     */
    public $encoding = self::ENCODING_TYPE_BASE64;

    /**
     * @var int 检索库id 
     */
    public $dbid;

    /**
     * @var int 图片覆盖策略，1表示对于已存在的url图片会采取覆盖策略 
     */
    public $overwrite = 1;

    /**
     * @var int 错误代码 
     */
    private $errorNo = 0;

    /**
     * @var string 错误信息 
     */
    private $errorMsg = '';

    /**
     * 入库保存上传的图片
     *
     * @param string $type 请求类型.该值由IDL提供,具体咨询IDL接口人
     * @param array $imageUrls  可抓取的图片url数组,键为应用中图片唯一标识id, 值为可抓取的图片url地址
     * @return boolen  是否成功保存
     */
    public function saveImages($imageUrls) {

        $postData = [];
        $postData['action'] = self::ACTION_ADD;
        $postData['type'] = self::ACTION_TYPE_ADD;

        //构造idl库需要的图片组织形式
        $images = [];
        foreach ($imageUrls as $imageId => $imageUrl) {
            $images[$imageUrl]['unique_id'] = $imageId;
            $images[$imageUrl]['dbid'] = $this->dbid;
            //相同图片采用覆盖策略
            $images[$imageUrl]['overwrite'] = $this->overwrite;
            $images[$imageUrl]['callback'] = '';
        }

        $postData['param'] = json_encode($images);
        $result = $this->sendRequest($postData);
        if ($result === false) {
            $this->setError('idl return error! return is:' . $result, self::PARAM_ILLEGAL );
            return false;
        }
        else {
            return true;
        }

    }

    /**
     * 根据提交的图片检索图片库中存在的相似图片 
     *
     * @param string $reposType 检索库的类型,参数由IDL服务提供.
     * @param string $photoPath 提交照片的本地路径.
     * @param reference of array  &$result  保存返回结果的对象指针 
     * @return boolen  是否检索成功
     */
    public function searchByImage($imagePath, &$result) {

        $postData = [];
        $postData['type'] = self::ACTION_TYPE_SEARCH . $this->dbid;
        $postData['encoding'] = $this->encoding;
        if (file_exists($imagePath)) {
            $postData['image'] = base64_encode(file_get_contents($imagePath));
        }
        else {
            $this->setError('file does not exist!', self::PARAM_ILLEGAL );
            return false;
        }
        $idlReturnResult = $this->sendRequest($postData);
        if ($idlReturnResult === false) {
            $this->setError('idl return error! return is:' . $result, self::PARAM_ILLEGAL );
            return false;
        }
        else {
            $result = $idlReturnResult['ret'];
        }
        return true;

    }

    /**
     * 删除照片信息
     *
     * @param string $type  由IDL服务提供的类型参数，固定且必须 
     * @param array $imageUrls 图片信息的唯一标识,支持批量数组提交 ,每个元素的键为图片地址
     * @return boolen  是否成功从图片库中删除
     */
    public function deleteImages($imageUrls) {

        $postData = [];
        $postData['action'] = self::ACTION_DELETE;
        $postData['type'] = self::ACTION_TYPE_DELETE;

        $images = [];
        foreach ($imageUrls as $imageId => $imageUrl) {
            $images[$imageUrl]['dbid'] = $this->dbid; 
            $images[$imageUrl]['unique_id'] = $imageId; 
        }

        $postData['param'] = json_encode($images);
        $result = $this->sendRequest($postData);
        if ($result === false) {
            return false;
        }
        else {
            return true;
        }

    }

    /**
     * 向后端发送图片入库或者检索请求 
     *
     * @param array $postData 请求信息数组 
     * @return boolen | array 请求是否成功,不成功返回false,成功返回结果数组
     */
    private function sendRequest($postData) {

        $postStr = '';
        foreach ($postData as $key => $value) {
            if ($key == 'image') {
                $postStr .= "{$key}=" . $value . "&";
            }
            else {
                $postStr .= "{$key}=" . urlencode($value) . "&";
            }
        }

        //请求的选项数组，固定格式
        $options = [
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $postStr,
            ];

        //使用curl提交请求
        $curl = curl_init($this->reposUrl);
        curl_setopt_array($curl, $options);
        $output = curl_exec($curl);
        curl_close($curl);

        $result = [];
        if ($output === false) {
            $this->setError(curl_errno($curl), curl_error($curl)) ;
            return false;
        }
        else {
            if (empty($output)) {
                $result['errno'] = 0;
                $result['errmas'] = '';
                $result['ret'] = [];
            }
            else {
                $result = json_decode($output, true);
            }

            //json解析失败，则设置错误并返回
            if (empty($result)) {
                $this->setError('json_decode of the return output failed!', self::SERVICE_REQUEST_FAILED);
                return false;
            }

            //第三方服务返回错误信息
            if ($result['errno']) {
                $this->setError($result['errno'] . ':' . $result['errmas'], self::SERVICE_REQUEST_FAILED);
                return false;
            }
            return $result;
        }
    }

    /**
     * 设置错误信息 
     *
     * @param int $errorNo 错误编号.
     * @param string $errorMsg 错误信息.
     * @return void无返回信息
     */
    protected function setError($errorMsg, $errorNo) {
        $this->errorNo = $errorNo;
        $this->errorMsg = $errorMsg;
    }

    /**
     * 获取错误编码信息, 当返回错误时，可以通过getErrorNo和getErrorMsg组合查出具体的错误信息 
     *
     * @return int 错误编码
     */
    public function getErrorNo() {
        return $this->errorNo;
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
