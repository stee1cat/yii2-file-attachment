<?php

use yii\db\Migration;

class m150924_095601_create_files extends Migration
{

    private $tableName = '{{%files}}';

    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey() . " COMMENT 'ID'",
            'created_at' => $this->integer() . " UNSIGNED NOT NULL COMMENT 'Дата создания'",
            'updated_at' => $this->integer() . " UNSIGNED NOT NULL COMMENT 'Дата изменения'",
            'status' => $this->integer()->notNull()->defaultValue(0) . " COMMENT 'Статус'",
            'name' => $this->string(255)->notNull() . " COMMENT 'Имя'",
            'extension' => $this->string(10)->notNull() . " COMMENT 'Тип'",
            'size' => $this->integer()->notNull() . " COMMENT 'Размер'",
            'path' => $this->string(1024)->notNull() . " COMMENT 'Путь'",
            'hash' => $this->string(32)->notNull() . " COMMENT 'Хеш'",
            'description' => $this->string(255)->defaultValue('') . " COMMENT 'Описание'",
        ]);
        $this->createIndex('hash_idx', $this->tableName, 'hash', true);
    }

    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

}
