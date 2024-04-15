<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Image;
use app\models\LoginForm;

class AdminController extends Controller
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
     * Показывает панель управления хостингом.
     */
    public function actionIndex()
    {
        $imageInDbQuantity = Image::find()->count();

        $uploadDirPath = \Yii::getAlias('@webroot/uploads/');
        $dir = opendir($uploadDirPath);
		$imageFilesQuantity = 0;
		while($file = readdir($dir)){
		    if(is_dir($uploadDirPath . $file)){
		        continue;
		    }
		    $imageFilesQuantity++;
		}

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

        return $this->render('index', [
            'imageInDbQuantity' => $imageInDbQuantity,
            'imageFilesQuantity' => $imageFilesQuantity,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionDelete()
    {
        ;
    }
}