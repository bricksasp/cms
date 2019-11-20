<?php
namespace bricksasp\cms\models;

use Yii;
use bricksasp\base\models\File;

/**
 * This is the model class for table "bricksasp_advert".
 *
 */
class Advert extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advert}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
            [
                'class' => \bricksasp\helpers\behaviors\UidBehavior::className(),
                'createdAtAttribute' => 'user_id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['name', 'image_id'], 'string', 'max' => 64],
            [['content'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'image_id' => 'Image ID',
            'content' => 'Content',
            'type' => 'Type',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id'])->select(['file_url', 'id']);
    }
}
