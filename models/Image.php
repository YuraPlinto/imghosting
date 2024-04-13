<?php

namespace app\models;

use yii\db\ActiveRecord;

class Image extends ActiveRecord
{
    public function rules()
    {
        return [
            ['name', 'unique'],
        ];
    }
}