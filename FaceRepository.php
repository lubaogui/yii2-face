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

}
