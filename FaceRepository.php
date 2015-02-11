<?php

namespace lubaogui\face;

/**
 * This is just an example.
 */
class FaceRepository extends BaseRepository;
{
    //头像库url地址
    public $faceReposUrl = '';

    //图片的最大大小限制,0表示无限制
    public $maxFileSize = 0;

    /**
     * Initializes the application component.
     */
    public function init()
    {
        parent::init();

        $this->dbid = 2021;
    }

    /**
     * 保存上传的头像照片信息
     *
     * @param array $images 本地路径或者需要上传到人脸信息库的图片绝对路径.
     * @return bollen  是否成功保存图片
     */
    public function saveFace($images) {
        $this->type = 'st_ruku_indexgen';
        $this->saveImage();
    }

    /**
     * 根据提交的照片返回相似的照片 
     *
     * @param array $photo    图片的绝对路径 
     * @param reference of array &$result  返回的相似图片组 
     * @return bollen  是否成功保存图片
     */
    public function searchByPhoto($photo, &$result) {
        $this->type = 'st_visplat_search_'.$this->dbid;
    }

    /**
     * 删除照片信息(从头像检索库中删除，通常发生在家属确认人员找到之后)
     *
     * @param integer $faceId 人脸信息的唯一标示 
     * @return bollen  是否成功保存图片
     */
    public function deleteFace($image) {




    }
}
