<?php
namespace bricksasp\cms\models;

use Yii;

/**
 * This is the model class for table "{{%article_label}}".
 */
class ArticleLabel extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_label}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['article_id', 'lable_id', 'sort'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'article_id' => 'Article ID',
            'lable_id' => 'Lable ID',
            'sort' => 'Sort',
        ];
    }
}
