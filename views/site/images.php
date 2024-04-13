<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Images';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-images">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'label' => 'Превью',
                'format' => 'html',
                'contentOptions' => ['style' => 'width: 200px;'],
                'value' => function ($data) {
                    $fileBaseName = mb_strstr($data->name, '.', true);
                    return Html::img('@web/uploads/thumbnails/' . $fileBaseName . '.png', ['alt' => 'превью']);
                }
            ],
            [
                'attribute' => 'name',
                'label' => 'Название файла'
            ],
            [
                'attribute' => 'uploaded_at',
                'label' => 'Дата и время загрузки'
            ],
        ]
    ]);
    ?>
</div>
