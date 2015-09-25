<?php

namespace stee1cat\FileAttachment\File;

use stee1cat\FileAttachment\File;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[File]].
 *
 * @see File
 */
class Query extends ActiveQuery
{

    /**
     * @inheritdoc
     * @return File[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return File|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

}