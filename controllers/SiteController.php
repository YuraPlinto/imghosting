<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
//use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Image;
use app\models\LoginForm;
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
                // TODO: произошла ошибка при загрузке картинок. Нужно вывести сообщение об этом.
                // При этом нужно различать ситуации, когда не удалось загрузить ни одной картинки,
                // и когда удалось загрузить часть изображений. Во втором случае нужно вывести
                // URL картинок, которые удалось сохранить на сервере.
            }
        }

        return $this->render('index', ['model' => $model]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays list of all images on server.
     *
     *
     */
    public function actionImages()
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
}
