<?php
namespace bricksasp\cms\models;

use Yii;

/**
 * This is the model class for table "{{%advert_relation}}".
 *
 */
class AdvertRelation extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advert_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['position_id', 'advert_id', 'sort'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'position_id' => 'Position ID',
            'advert_id' => 'Advert ID',
            'sort' => 'Sort',
        ];
    }
}
