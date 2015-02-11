<?php

namespace lubaogui\face;

/**
 * This is just an example.
 */
class FaceRepository extends BaseRepository;
{

    //头像库url地址
    public $reposUrl = '';

    //图片的最大大小限制,0表示无限制
    public $maxFileSize = 0;

    /**
     * 保存上传的头像照片信息
     *
     * @param string $filePath 本地路径或者需要上传到人脸信息库的图片绝对路径.
     * @param array $params   保存时所带的参数信息
     * @return bollen  是否成功保存图片
     */
    public function saveFace($filePath, $params) {


    }

    /**
     * 保存上传的头像照片信息
     *
     * @param string $filePath 本地路径或者需要上传到人脸信息库的图片绝对路径.
     * @param array $params   保存时所带的参数信息
     * @return bollen  是否成功保存图片
     */
    public function searchFaceByPhoto($type, $photo, &$result) {


    }

    /**
     * 删除照片信息(从头像检索库中删除，通常发生在家属确认人员找到之后)
     *
     * @param integer $faceId 人脸信息的唯一标示 
     * @return bollen  是否成功保存图片
     */
    public function deleteFace($faceId) {




    }

}
