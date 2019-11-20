<?php
namespace bricksasp\cms\models;

use Yii;
use bricksasp\base\models\Label;
use bricksasp\base\models\File;

/**
 * This is the model class for table "{{%article}}".
 *
 */
class Article extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%article}}';
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
            [['id', 'user_id', 'owner_id', 'cat_id', 'parent_id', 'type', 'comments_count', 'view_count', 'like_count', 'is_comment', 'is_top', 'is_recommend', 'release_at', 'status', 'created_at', 'updated_at'], 'integer'],
            [['brief', 'content'], 'string'],
            [['keywords', 'title'], 'string', 'max' => 255],
            [['reprint_info'], 'string', 'max' => 150],
            [['image_id'], 'string', 'max' => 64],
            [['status', 'is_comment', 'type'], 'default', 'value'=>1]
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
            'cat_id' => 'Cat ID',
            'keywords' => 'Keywords',
            'reprint_info' => 'Reprint Info',
            'title' => 'Title',
            'image_id' => 'Image ID',
            'brief' => 'brief',
            'content' => 'Content',
            'parent_id' => 'Parent ID',
            'type' => 'Type',
            'comments_count' => 'Comments Count',
            'view_count' => 'View Count',
            'like_count' => 'Like Count',
            'is_comment' => 'Is Comment',
            'is_top' => 'Is Top',
            'is_recommend' => 'Is Recommend',
            'release_at' => 'Release At',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public function getImage()
    {
        return $this->hasOne(File::className(), ['id' => 'image_id'])->select(['file_url', 'id']);
    }

    public function getLabels()
    {
        return $this->hasMany(ArticleLabel::className(), ['article_id' => 'id']);
    }

    public function getLabelItems()
    {
        return $this->hasMany(Label::className(), ['id' => 'lable_id'])->via('labels')->select(['id', 'name', 'style']);
    }

    public function getCommentItems()
    {
        return $this->hasMany(ArticleComment::className(), ['article_id' => 'id'])/*->onCondition(['cat_id' => 1])*/;
    }
}
