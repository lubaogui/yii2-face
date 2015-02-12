<?php

namespace lubaogui\face;

/**
 * This is just an example.
 */
class BaseRepository extends \yii\base\Object;
{
    //头像库url地址
    public $faceReposUrl = '';

    //图片的最大大小限制,0表示无限制
    public $maxFileSize = 0;

    //请求动作
    public $action = '';

    //图片编码方式
    public $encoding = 1;

    //检索库id
    public $dbid = 0;

    //错误信息编码
    private $_errorNo = 0;

    //错误信息内容
    private $_errorMsg = '';
    

    /**
     * 保存上传的头像照片信息
     *
     * @param string $type 请求类型.
     * @param array $params   一组图片信息
     * @return bollen  是否成功保存图片
     */
    public function saveImage($type, $images) {

        $postData = [];
        $postData['action'] = 'add';
        $postData['type'] = $type;
        //添加dbid参数
        foreach ($images as $key=>$image) {
            $images[$key]['dbid'] = $this->dbid;
            //参数相同则覆盖原图片
            $images[$key]['overwrite'] = 1;
        }
        $postData['param'] = json_encode($images);

        return $this->sendRequest($postData);
    }

    /**
     * 根据提交的图片查找对应检索库中相似的图片信息 
     *
     * @param string $reposType 检索库的类型.
     * @param string $photoPath 提交照片的路径.
     * @param reference of array  &$result  保存返回结果的对象指针 
     * @return bollen  是否检索成功
     */
    public function searchByImage($reposType, $photoPath, &$result) {

        $postData = [];
        $postData['type'] = $reposType;
        $postData['encoding'] = $this->encoding;
        $postData['image'] = base64_encode(file_get_contents($photoPath));
        $result =  $this->sendRequest($postData);

    }

    /**
     * 删除照片信息(从头像检索库中删除，通常发生在家属确认人员找到之后)
     *
     * @param array $images 人脸信息的唯一标示,支持批量数组提交 
     * @return bollen  是否成功保存图片
     */
    public function deleteImage($images) {

        $postData = [];
        $postData['type'] = $this->type;
        $postData['action'] = 'delete';
        foreach ($images as $key=>$image) {
           $images[$key]['dbid'] = $this->dbid; 
        }
        $postData['param'] = json_encode($images);
        $result = $this->sendRequest($postData);
        return $result;

    }

}    /**
     * 向后端发送请求 
     *
     * @param array reference $postData post数组 
     * @return bollen  是否成功保存图片
     */
    protected function sendRequest(&$postData) {

        $postStr = '';
        foreach ($postData as $key=>$value) {
            $postStr .= "{$key}=".urlencode($value)."&";
        }

        //请求的选项，固定格式
        $options = [
            'CURLOPT_POST'=>1,
            'CURLOPT_RETURNTRANSFER'=>1,
            'CURLOPT_POSTFIELDS'=>$postStr
            ];

        //使用curl提交请求
        $curl = curl_init($url);
        curl_setopt_array($curl, $options);
        $output = curl_exec($curl);
        if ($output === false) {
            /*
            throw new Exception('request failed: ' . curl_errno($curl) . ' - ' . curl_error($curl), [
                'requestMethod' => $method,
                'requestUrl' => $url,
                'requestBody' => $requestBody,
                'responseHeaders' => $headers,
                'responseBody' => $this->decodeErrorBody($body),
            ]);
             */
            $this->setError(curl_errorno($curl), curl_error($curl)) ;
            return false;
        }
        else {
            #$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            $output = json_decode($output);

            //json解析失败，则设置错误并返回
            if (empty($output)) {
                $this->setError(100001, 'json_decode of the return output failed!');
                return false;
            }

            if ($output['errno']) {
                $this->setError($output['errno'], $output['errmas']);
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
     * @param reference of array  &$result  保存返回结果的对象指针 
     */
    protected function setError($errorNo, $errorMsg) {
        $this->_errorNo = $errorNo;
        $this->_errorMsg = $errorMsg;
    }

    /**
     * 获取错误编码信息 
     *
     * @return int 错误编码
     */
    public function getErrorNo() {
        return $this->_errorNo;
    }

    /**
     * 获取错误编码信息 
     *
     * @return string  错误信息
     */
    public function getErrorMsg() {
        return $this->_errorMsg;
    }

}   
