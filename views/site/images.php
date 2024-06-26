<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Images';
?>
<div class="site-images">
    <p>Чтобы открыть картинку, кликните по превью.</p>
    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'label' => 'Изображение',
                'format' => 'html',
                'contentOptions' => ['style' => 'width: 200px;'],
                'value' => function ($data) {
                    $fileBaseName = mb_strstr($data->name, '.', true);
                    $href = '/uploads/' . $data->name;
                    $previewImgTag = Html::img('@web/uploads/thumbnails/' . $fileBaseName . '.png', ['alt' => 'превью']);
                    return \sprintf("<a href='%s'>%s</a>", $href, $previewImgTag);
                }
            ],
            [
                'attribute' => 'name',
                'label' => 'Название файла',
                'format' => 'html',
                'value' => function ($data) {
                    return $data->name . "<br><a href='download/" . $data->name . "'>Скачать архив</a>";
                }
            ],
            [
                'attribute' => 'uploaded_at',
                'label' => 'Дата и время загрузки'
            ],
        ]
    ]);
    ?>
</div>
