<?php

namespace lubaogui\face;

use Yii;

/**
 * 人脸信息库操作类，完成人脸信息库的图片添加，检索和删除 
 */
class FaceRepository extends BaseRepository
{

    /**
     * 初始化应用组件和变量.
     */
    public function init()
    {
        parent::init();
        $this->dbid = Yii::$app->params['faceRepos']['dbid'];
        $this->reposUrl = Yii::$app->params['faceRepos']['reposUrl'];
    }

    /**
     * 保存人脸图片到人脸信息库
     *
     * @param array $images 本地路径或者需要上传到人脸信息库的图片url地址. ['unique_id'=>$url, 'item02'=>$url2]
     * @return bollen  是否成功保存图片
     */
    public function saveFaces($images) {
        $type = 'st_ruku_indexgen';
        return $this->saveImage($type, $images);
    }

    /**
     * 根据提交的照片返回检索库中的相似照片 
     *
     * @param array $photo   图片的绝对路径 
     * @param reference of array &$result  返回的相似图片组 
     * @return bollen 检索是否成功 
     */
    public function searchByPhoto($photo, &$result) {
        //类型数据由IDL提供,可咨询接口人员获取
        $type = 'st_visplat_search_' . $this->dbid;
        //基类进行$photo文件的存在性判断并检索图片库中是否存在相似人脸
        if ($this->searchByImage($type, $photo, $result)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 删除照片信息(从头像检索库中删除，通常发生在家属确认人员找到之后)
     *
     * @param array $images 标识人脸的信息数组 [['unique_id'=>http://xxxx.com/abc.jpg']]
     * @return bollen  是否成功删除
     */
    public function deleteFace($images) {
        $type = 'st_ruku_indexgen';
        return $this->deleteImage($type, $images);
    }
}
