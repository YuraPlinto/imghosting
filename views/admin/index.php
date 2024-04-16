<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Manage images';
?>
<div class="admin-images">
    <div style="border: 2px solid black; padding: 1em; margin: 2em 0em 3em;">
        <h2>Статистика</h2>
        <b>Количество записей о файлах в БД:</b> <?= $imageInDbQuantity ?><br>
        <b>Количество загруженных файлов в папке uploads:</b> <?= $imageFilesQuantity ?>
    </div>
    <p style="margin-bottom: 3em;">
        <a
            class="btn btn-lg btn-success"
            href=<?= Url::base(true) . '/admin/clear' ?>
            title="Удалить файлы, не связанные с записью в БД, и удалить записи о несуществующих файлах из БД"
        >
            Очистка файлов и БД
        </a>
    </p>
    <h2>Записи о загруженных файлах в базе данных</h2>
    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id'
            ],
            [
                'attribute' => 'name',
                'label' => 'Название файла'
            ],
            [
                'label' => 'Изображение',
                'format' => 'html',
                'contentOptions' => ['style' => 'width: 200px;'],
                'value' => function ($data) {
                    $originalFileWithPath = \Yii::getAlias('@webroot/uploads/' . $data->name);
                    if (!file_exists($originalFileWithPath))
                        return 'Файл не существует';

                    $fileBaseName = mb_strstr($data->name, '.', true);
                    $thumbnailFileWithPath = \Yii::getAlias('@webroot/uploads/thumbnails/' . $fileBaseName . '.png');
                    if (!file_exists($thumbnailFileWithPath))
                        ;

                    $href = '/uploads/' . $data->name;
                    $previewImgTag = Html::img('@web/uploads/thumbnails/' . $fileBaseName . '.png', ['alt' => 'превью']);
                    return \sprintf("<a href='%s'>%s</a>", $href, $previewImgTag);
                }
            ],
            [
                'attribute' => 'uploaded_at',
                'label' => 'Дата и время загрузки'
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}'
            ]
        ]
    ]);
    ?>
</div>
