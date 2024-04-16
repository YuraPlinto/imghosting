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
                'only' => ['index', 'logout', 'delete', 'clear'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'delete', 'clear'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'delete' => ['post']
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
                'class' => 'yii\web\ErrorAction'
            ]
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

    public function actionDelete($id)
    {
        $image = Image::findOne($id);
        $image->delete();

        return $this->redirect('/admin/index', 303);
    }

    public function actionClear()
    {
        $originalFiles = [];
        $originalFilesWithoutExt = [];

        $uploadsPath = Yii::$app->basePath . '\web\uploads\\';
        $pos = mb_strlen($uploadsPath);
        foreach (glob(Yii::$app->basePath . '\web\uploads\*') as $fileNameWithPath) {
            if (\is_file($fileNameWithPath)) {
                $fileNameWithoutPath = mb_substr($fileNameWithPath, $pos);
                $fileNameWithoutPathAndExt = mb_strstr($fileNameWithoutPath, '.', true);
                $originalFilesWithoutExt[] = $fileNameWithoutPathAndExt;
                $originalFiles[] = $fileNameWithoutPath;
            }
        }

        // Удаляем из папки uploads файлы, о которых нет информации в БД
        $imagesInDb = Image::find()->all();
        $imagesInDbNames = [];
        foreach($imagesInDb as $imageInDb) {
            $imagesInDbNames[] = $imageInDb->name;
        }
        $filesNotInDb = \array_diff($originalFiles, $imagesInDbNames);
        foreach($filesNotInDb as $file) {
            unlink($uploadsPath . $file);
        }

        // Удаляем превью несуществующих изображений (файла с оригиналом изображения нет в папке uploads)
        $thumbnailsPath = $uploadsPath . 'thumbnails\\';
        $pos = mb_strlen($thumbnailsPath);
        $thumbnailFiles = [];
        foreach(glob(Yii::$app->basePath . '\web\uploads\thumbnails\*') as $fileNameWithPath) {
            if (\is_file($fileNameWithPath)) {
                $fileNameWithoutPath = mb_substr($fileNameWithPath, $pos);
                $fileNameWithoutPathAndExt = mb_strstr($fileNameWithoutPath, '.', true);
                $thumbnailFiles[] = $fileNameWithoutPathAndExt;
            }
        }
        $thumbnailFilesWithoutOriginal = \array_diff($thumbnailFiles, $originalFilesWithoutExt);
        foreach($thumbnailFilesWithoutOriginal as $file) {
            unlink($thumbnailsPath . $file . '.png');
        }

        // Удаляем архивы несуществующих файлов (оригинала файла нет в папке uploads)
        $archiveFiles = [];
        $archivePath = $uploadsPath . 'archive\\';
        $pos = mb_strlen($archivePath);
        foreach(glob(Yii::$app->basePath . '\web\uploads\archive\*') as $fileNameWithPath) {
            if (\is_file($fileNameWithPath)) {
                $fileNameWithoutPath = mb_substr($fileNameWithPath, $pos);
                $fileNameWithoutPathAndExt = mb_strstr($fileNameWithoutPath, '.', true);
                $archiveFiles[] = $fileNameWithoutPathAndExt;
            }
        }
        $archiveFilesWithoutOriginal = \array_diff($archiveFiles, $originalFilesWithoutExt);
        foreach($archiveFilesWithoutOriginal as $file) {
            unlink($archivePath . $file . '.zip');
        }

        // Очистка БД от записей о несуществующих файлах
        $filenamesInDbWithoutFiles = \array_diff($imagesInDbNames, $originalFiles);
        foreach($filenamesInDbWithoutFiles as $filename) {
            $badImage = Image::find()->where(['name' => $filename])->one();
            $badImage->delete();
        }

        return $this->redirect('/admin/index', 303);
    }
}