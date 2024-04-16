<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Image;
use app\models\UploadForm;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFiles = UploadedFile::getInstances($model, 'imageFiles');
            if ($model->upload()) {
                // file is uploaded successfully
                return $this->render('index', ['imageUrls' => $model->uploadedUrls]);
            } else {
                $errorMessage = 'Ошибка!';
                if (\count($model->uploadedUrls) >= 1) {
                    $errorMessage .= ' Удалось загрузить не все файлы';
                    return $this->render('index', [
                        'errorMessage' => $errorMessage,
                        'imageUrls' => $model->uploadedUrls
                    ]);
                } else {
                    $errorMessage .= ' Файлы загрузить не удалось';
                    return $this->render('index', ['errorMessage' => $errorMessage]);
                }
            }
        }

        return $this->render('index', ['model' => $model]);
    }

    /**
     * Displays list of all images on server.
     */
    public function actionSee()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Image::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'name',
                    'uploaded_at'
                ]
            ]
        ]);

        return $this->render('images', [
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Проверяет, существует ли файл с заархивированным изображением.
     * Если архив уже хранится на сервере, он отдаётся клиенту.
     * Если архив не существует, то он создаётся.
     *
     * TODO: Нужна обработка ошибки, которая возникает, когда файла с именем
     * $fileName не существует.
     *
     * @param  $string $fileName
     * @return Response
     */
    public function actionDownload($fileName) {
        $fileBaseName = mb_strstr($fileName, '.', true);
        $fileWithPath = \Yii::getAlias('@webroot/uploads/' . $fileName);
        $archiveFileWithPath = \Yii::getAlias('@webroot/uploads/archive/' . $fileBaseName . '.zip');

        // Если файл с архивированным изображением не существует - создаём его
        if (!file_exists($archiveFileWithPath)) {
            $zip = new \ZipArchive();
            $zip->open($archiveFileWithPath, \ZIPARCHIVE::CREATE);
            $zip->addFromString($fileName, file_get_contents($fileWithPath));
            $zip->close();
        }

        if (file_exists($archiveFileWithPath))
            \Yii::$app->response->sendFile($archiveFileWithPath);
    }
}
