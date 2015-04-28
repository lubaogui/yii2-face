<?php

namespace lubaogui\face;

use Yii;

/**
 * 拍照图片索引库接口类 
 */
class PhotoRepository extends BaseRepository
{

    /**
     * 初始化应用组件和变量.
     */
    public function init()
    {
        parent::init();
        $this->dbid = Yii::$app->params['photoRepos']['dbid'];
        $this->reposUrl = Yii::$app->params['photoRepos']['reposUrl'];
    }

}
