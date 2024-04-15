<?php

/** @var yii\web\View $this */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Image hosting';
?>
<div class="site-index">
<style>
    .result-urls-wrapper {
        margin: 0 auto;
        width:  60%;
        border: 1px solid black;
        border-radius: 20px;
    }
    .result-urls-wrapper h2 {
        text-align: center;
    }
    .result-urls-wrapper h3 {
        text-align: center;
    }
    .result-urls-wrapper p {
        font-size:  large;
        text-align: center;
    }
    .form-wrapper {
        margin:        0 auto;
        padding:       3em;
        width:         40%;
        border:        1px solid black;
        border-radius: 20px;
    }
    .form-wrapper h2 {
        text-align: center;
    }
    .btn-wrapper {
        text-align: center;
    }
</style>
    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Загружайте и делитесь изображениями!</h1>

        <p class="lead">
            Загружайте изображения анонимно без регистрации.
        </p>
    </div>

    <div class="body-content">
            <?php if(isset($imageUrls)): ?>
                <div class="result-urls-wrapper">
                    <?php
                    $urlQuantity = count($imageUrls);
                    if (!isset($errorMessage)):
                        if($urlQuantity > 1):
                        ?>
                            <h2>Изображения успешно загружены!</h2>
                            <h3>Их URL:</h3>
                            <ul>
                                <?php foreach($imageUrls as $imageUrl): ?>
                                    <?= '<li>' . Url::base(true) . '/uploads/' . $imageUrl . '</li>' ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif ($urlQuantity == 1): ?>
                            <h2>Изображение успешно загружено!</h2>
                            <p><b>Его URL:</b> <?= Url::base(true) . '/uploads/' . $imageUrls[0] ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <h2><?= $errorMessage ?></h2>
                        <?php
                        if($urlQuantity > 1):
                        ?>
                            <h3>Но эти изображения удалось успешно загрузить:</h3>
                            <ul>
                                <?php foreach($imageUrls as $imageUrl): ?>
                                    <?= '<li>' . Url::base(true) . '/uploads/' . $imageUrl . '</li>' ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif ($urlQuantity == 1): ?>
                            <p>
                                <b>Но это изображение удалось успешно загрузить:</b><br>
                                <?= Url::base(true) . '/uploads/' . $imageUrls[0] ?>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <p><a class="btn btn-lg btn-success" href=<?= Url::base(true) ?>>Загрузить ещё</a></p>
                </div>
            <?php endif; ?>

            <?php if(isset($model)): ?>
                <div class="form-wrapper">
                    <?php $form = ActiveForm::begin(['id' => 'img-form']); ?>
                        <?= $form->field($model, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*'])->label('Выберите до 5 файлов') ?>

                        <div class="form-group btn-wrapper">
                            <?= Html::submitButton('Загрузить', ['class' => 'btn btn-primary', 'name' => 'upload-img-button']) ?>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>
            <?php endif; ?>
    </div>
</div>
