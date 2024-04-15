<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Image extends ActiveRecord
{
    public function rules()
    {
        return [
            ['name', 'unique'],
        ];
    }

    public function afterDelete () {
        parent::afterDelete();

        $originalFile = Yii::$app->basePath . '/web/uploads/' . $this->name;
        if (file_exists($originalFile))
            unlink($originalFile);

        $fileBaseName = mb_strstr($this->name, '.', true);
        $thumbnailFile = Yii::$app->basePath . '/web/uploads/thumbnails/' . $fileBaseName . '.png';
        if (file_exists($thumbnailFile))
            unlink($thumbnailFile);

        $archiveFile = Yii::$app->basePath . '/web/uploads/archive/' . $fileBaseName . '.zip';
        if (file_exists($archiveFile))
            unlink($archiveFile);
    }
}