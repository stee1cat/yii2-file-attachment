<?php

namespace stee1cat\FileAttachment;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Transaction;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Class FileBehavior
 * @package stee1cat\FileAttachment
 */
class FileBehavior extends Behavior
{

    public $upload;

    /**
     * @var ActiveRecord
     */
    public $owner;

    /**
     * Имя атрибута с ID файлом
     *
     * @var
     */
    public $attribute = 'file_id';

    /**
     * Директория загрузки
     *
     * @var string
     */
    public $directory = '@webroot/uploads/';

    /**
     * URL к директории загрузки
     *
     * @var string
     */
    public $url = '@web/uploads/';

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * ID загруженных файлов
     *
     * @var array
     */
    private $items = [];

    /**
     * Запрещает загрузку
     *
     * @var bool
     */
    private $disable = false;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'insertFile',
            ActiveRecord::EVENT_AFTER_UPDATE => 'updateFile',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteFile'
        ];
    }

    public function insertFile()
    {
        $result = true;
        $files = [UploadedFile::getInstance($this->owner, 'upload')];
        if ($files && !$this->disable) {
            $this->beginTransaction();
            // Сохраняем изображения
            $result = $result && $this->move($files);
            $this->endTransaction($result);
        }
        return !$this->disable && $files && $result;
    }

    public function updateFile()
    {
        return $this->insertFile();
    }

    public function deleteFile()
    {
        /** @var File|null $file */
        $file = File::findOne($this->owner->{$this->attribute});
        if ($file) {
            $file->delete();
        }
    }

    /**
     * @return File\Query
     */
    public function getFile()
    {
        return $this->owner->hasOne(File::className(), ['id' => $this->attribute]);
    }

    /**
     * Запрещает загрузку
     *
     * @param bool|true $disable
     */
    public function disableAttachment($disable = true)
    {
        $this->disable = $disable;
    }

    /**
     * @param UploadedFile[] $items
     * @return bool
     */
    private function move($items)
    {
        $result = true;
        foreach ($items as $item) {
            if ($filePath = $this->moveUploadedFile($item, $this->directory)) {
                $file = File::create($filePath);
                $file->name = $item->baseName;
                $file->path = Yii::getAlias($this->url) . basename($filePath);
                $result = $result && $file->save();
                if ($result) {
                    $this->updateOwner($file->primaryKey);
                    $this->items[] = $file->primaryKey;
                }
            }
        }
        return $result;
    }

    /**
     * Перемещает и переименовывает загруженный файл в директорию
     *
     * @param UploadedFile $file
     * @param $directory
     * @return bool|string
     */
    protected function moveUploadedFile(UploadedFile $file, $directory)
    {
        $result = false;
        $directory = Yii::getAlias($directory);
        if ($directory) {
            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory, 0755);
            }
            $fileName = $this->generateFileName($file->tempName, $file->name);
            if ($file->saveAs($directory . $fileName)) {
                $result = $directory . $fileName;
            }
        }
        return $result;
    }

    /**
     * Генерирует имя файла
     *
     * @param string $path Путь к файлу
     * @param string $fileName Оригинальное имя файла
     * @return string
     */
    protected function generateFileName($path, $fileName = '')
    {
        $name = '';
        if (file_exists($path)) {
            $name = time() . '-' . md5_file($path);
            $pathInfo = ($fileName)? pathinfo($fileName): pathinfo($path);
            if (isset($pathInfo['extension']) && $pathInfo['extension']) {
                $name .= '.' . $pathInfo['extension'];
            }
        }
        return $name;
    }

    /**
     * @param integer $fileId
     * @return int
     * @throws Exception
     */
    protected function updateOwner($fileId)
    {
        $this->owner->updateAttributes([
            $this->attribute => $fileId,
        ]);
        $sql = "
            UPDATE " . $this->owner->tableName() . "
            SET " . $this->attribute .  " = :file
            WHERE id = :id
        ";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':file', $fileId)
            ->bindValue(':id', $this->owner->primaryKey)
            ->execute();
    }

    protected function beginTransaction()
    {
        $db = $this->owner->getDb();
        if ($db->getTransaction() === null) {
            $this->transaction = $db->beginTransaction();
        }
    }

    protected function endTransaction($result)
    {
        if ($this->transaction) {
            if ($result) {
                $this->transaction->commit();
            }
            else {
                $this->transaction->rollBack();
            }
        }
    }

}