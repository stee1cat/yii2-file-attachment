<?php

namespace stee1cat\FileAttachment;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%files}}".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $status
 * @property string $name
 * @property string $extension
 * @property string $hash
 * @property string $path
 * @property string $description
 * @property integer $size
 *
 * @property string $filename
 */
class File extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'extension', 'hash', 'path', 'size'], 'required'],
            [['created_at', 'updated_at', 'status', 'size'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['extension'], 'string', 'max' => 10],
            [['hash'], 'string', 'max' => 32],
            [['path'], 'string', 'max' => 1024],
            [['hash'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_at' => Yii::t('app', 'Дата создания'),
            'updated_at' => Yii::t('app', 'Дата изменения'),
            'status' => Yii::t('app', 'Статус'),
            'name' => Yii::t('app', 'Имя'),
            'extension' => Yii::t('app', 'Тип'),
            'size' => Yii::t('app', 'Размер'),
            'path' => Yii::t('app', 'Путь'),
            'hash' => Yii::t('app', 'Хеш'),
            'description' => Yii::t('app', 'Описание'),
        ];
    }

    public function getFilename()
    {
        return $this->name . '.' . $this->extension;
    }

    /**
     * Создаёт модель для указанного файла
     *
     * @param $path
     * @return bool|File
     */
    public static function create($path)
    {
        $result = false;
        if (file_exists($path)) {
            $info = pathinfo($path);
            $result = new File([
                'name' => $info['filename'],
                'extension' => $info['extension'],
                'hash' => md5_file($path),
                'path' => str_ireplace($_SERVER['DOCUMENT_ROOT'], '', $path),
                'size' => filesize($path),
            ]);
        }
        return $result;
    }

    /**
     * @inheritdoc
     * @return File\Query
     */
    public static function find()
    {
        return new File\Query(get_called_class());
    }

}
