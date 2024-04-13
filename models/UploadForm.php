<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use app\models\Image;
use yii\imagine\Image as Imagine;
use yii\helpers\Url;

class UploadForm extends Model
{
    /**
     * @var UploadedFile[]
     */
    public $imageFiles;

    /**
     * @var string[]
     */
    public $uploadedUrls;

    public function rules()
    {
        return [
            [['imageFiles'], 'file', 'skipOnEmpty' => false, 'extensions' => 'bmp, png, jpg, jpeg, tiff, gif, svg', 'maxFiles' => 5]
        ];
    }

    /**
     * Приводит имя файла к такому виду, чтобы оно соответствовало правилам хостинга.
     * Название файла приводится к нижнему регистру и транслитерируется на английский
     * язык; все пробельные символы заменяются на "-".
     *
     * @param  string $value
     * @return string
     */
    public function normalizeFileName(string $value): string {
        $value = mb_strtolower($value);
        $value = preg_replace("/\s+/", "-", $value);
        $value = \Transliterator::create('Any-Latin; Latin-ASCII')->transliterate($value);
        return $value;
    }

    /**
     * Проводит валидацию загружаемых пользователем файлов, преобразует имена файлов и сохраняет
     * файлы в папку на сервере. Если сохранение успешно, информация о файле сохраняется в базе
     * данных.
     * Метод возвращет true, если все файлы были сохранены успешно. Если удалось сохранить
     * только часть файлов, то возвращается false. Эта ситуация отличается от случая, когда не удалось
     * сохранить ни одного файла, тем, что в $this->uploadedUrls будут записаны url файлов, которые
     * удалось сохранить.
     *
     * @return bool
     */
    public function upload(): bool
    {
        if ($this->validate()) {
            $saveError = false;
            foreach ($this->imageFiles as $file) {
                $image = new Image();
                $image->uploaded_at = (new \DateTime())->format('Y-m-d H:i:s');
                $fileBaseName = $this->normalizeFileName($file->baseName);
                $image->name = \sprintf('%s.%s', $fileBaseName, $file->extension);
                // Валидация того, что имя файла (вместе с расширением) уникально
                if (!$image->validate()) {
                    $fileBaseName = \md5($fileBaseName . (string)\time());
                    $image->name = \sprintf('%s.%s', $fileBaseName, $file->extension);
                }

                // Файловая система - это внешняя система, которую приложение не контролирует полностью.
                // Поэтому необходимо всегда учитывать возможность неудачи при сохранении файла.
                if (!$file->saveAs('uploads/' . $image->name)) {
                    $saveError = true;
                    continue;
                }

                $this->uploadedUrls[] = $image->name;

                $image->save();

                $thumbnailFile = Yii::$app->basePath . '/web/uploads/thumbnails/' . $fileBaseName . '.png';
                Imagine::thumbnail(Yii::$app->basePath . '/web/uploads/' . $image->name, 200, 200)
                    ->save($thumbnailFile, ['quality' => 80]);
            }
            if ($saveError)
                return false;
            else
                return true;
        } else {
            return false;
        }
    }
}