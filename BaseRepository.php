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
        $postData['type'] = $this->type;
        $postData['encoding'] = $this->encoding;
        $postData['image'] = base64_encode(file_get_contents($photoPath));
        $result =  $this->sendRequest($postData);

    }

    /**
     * 删除照片信息(从头像检索库中删除，通常发生在家属确认人员找到之后)
     *
     * @param integer $faceId 人脸信息的唯一标示 
     * @return bollen  是否成功保存图片
     */
    public function deleteFace($images) {

        $postData = [];
        $postData['type'] = $this->type;
        $postData['action'] = 'delete';
        foreach ($images as $key=>$image) {
           $images[$key]['dbid'] = $this->dbid; 
        }
        $postData['param'] = json_encode($images);
        $result =  $this->sendRequest($postData);

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
            throw new Exception('request failed: ' . curl_errno($curl) . ' - ' . curl_error($curl), [
                'requestMethod' => $method,
                'requestUrl' => $url,
                'requestBody' => $requestBody,
                'responseHeaders' => $headers,
                'responseBody' => $this->decodeErrorBody($body),
            ]);
        }
        else {
            #$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            return $output;
        }
    }
}
