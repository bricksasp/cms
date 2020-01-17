<?php
namespace bricksasp\cms\models;

use Yii;
use bricksasp\base\models\File;

/**
 * This is the model class for table "{{%article_category}}".
 */
class ArticleCategory extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article_category}}';
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
            [['parent_id', 'status', 'sort', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'image_id'], 'string', 'max' => 64],
            [['code'], 'string', 'max' => 32],
            [['status', 'sort'], 'default', 'value' => 1]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'name' => 'Name',
            'status' => 'Status',
            'sort' => 'Sort',
            'image_id' => 'Image ID',
            'user_id' => 'User ID',
            'code' => 'Code',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id'])->select(['file_url', 'id']);
    }

    /**
     * 级联详情
     * @param  intger $id 分类id
     * @return array
     */
    public function cascader(int $id)
    {
        $row = [];
        $model = self::findOne($id);
        if (!$model) return $row;
        
        if ($model->parent_id) {
            $row[] = $model->toArray();
            $row = array_merge($this->cascader($model->parent_id), $row);
        }else{
            $row[] = $model->toArray();
        }
        return $row;
    }
}
