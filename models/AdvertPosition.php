<?php
namespace bricksasp\cms\models;

use Yii;
use bricksasp\helpers\Tools;

/**
 * This is the model class for table "{{%advert_position}}".
 *
 */
class AdvertPosition extends \bricksasp\base\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%advert_position}}';
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
            [['user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['code'], 'string', 'max' => 32],
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
            'code' => 'Code',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAdvertRelation()
    {
        return $this->hasMany(AdvertRelation::className(), ['position_id' => 'id']);
    }

    public function getAdvertItems()
    {
        return $this->hasMany(Advert::className(), ['id' => 'advert_id'])->via('advertRelation');
    }

    public function saveAdvert($data)
    {
        $this->load($data,'');

        $transaction = self::getDb()->beginTransaction();
        try {
            // 保存广告位
            if ($this->save() === false) {
                $transaction->rollBack();
                return false;
            }

            $adverts = [];
            foreach ($data['items'] as $k => $v) {
                $advert['position_id'] = $this->id;
                $advert['advert_id'] = $v;
                $advert['sort'] = $k + 1;
                $adverts[] = $advert;
            }

            AdvertRelation::deleteAll(['position_id'=>$this->id]);
            AdvertRelation::getDb()->createCommand()
            ->batchInsert(AdvertRelation::tableName(),['position_id','advert_id','sort'],$adverts)
            ->execute();

            $transaction->commit();
            return true;
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return false;
    }

    /**
     * 
     * 广告位详情
     * @OA\Schema(
     *   schema="AdvertPositionDetail",
     *   description="广告位模型",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="name", type="string", description="广告位名称"),
     *       @OA\Property(property="content", type="string", description="内容"),
     *       @OA\Property(property="type", type="integer", description="1:url 2:商品id 3:文章id"),
     *       @OA\Property(property="image", ref="#/components/schemas/file", description="图片")
     *     )
     *   }
     * )
     */
    public function detail($code)
    {
        $model = $this::find()->select(['id', 'name'])->where(['code' => $code])->one();
        if (!$model) return false;
        $sort = AdvertRelation::find()->where(['position_id' => $model->id])->all();

        $t = strtotime(date('Y-m-d'));
        $items = Advert::find()->with(['image'])
            ->select(['id', 'image_id', 'name', 'content', 'type'])
            ->where(['id'=>array_column($sort, 'advert_id')])
            ->andWhere(['<=', 'start_at', $t])
            ->andWhere(['>=', 'end_at', $t])
            ->all();

        $advert = array_map(function ($item)
        {
            $v = $item->toArray();
            $v['image'] = $item->image ? Tools::format_array($item->image, ['file_url'=>['implode',['',[\bricksasp\base\Config::instance()->web_url,'###']],'array']]) : (object)[];
            return $v; 
        },$items);

        $advert = self::sortItem([$advert, 'id'],[$sort, 'sort', 'advert_id']);

        $data = $model->toArray();
        $data['items'] = $advert;
        return $data;
    }
}
