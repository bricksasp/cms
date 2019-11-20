<?php
namespace bricksasp\cms\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use bricksasp\cms\models\Article;

/**
 * ArticleSearch represents the model behind the search form of `bricksasp\cms\models\Article`.
 */
class ArticleSearch extends Article
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'owner_id', 'cat_id', 'parent_id', 'type', 'is_comment', 'is_top', 'is_recommend', 'release_at', 'updated_at'], 'integer'],
            [['keywords', 'reprint_info', 'title', 'image_id', 'brief', 'content'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params,$fields=[])
    {
        $params['status'] = 1;

        $query = Article::find()->with(['image', 'labelItems'])->select($fields);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $params['pageSize'] ?? 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user_id' => $this->user_id,
            'owner_id' => $this->owner_id,
            'cat_id' => $this->cat_id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'is_comment' => $this->is_comment,
            'is_top' => $this->is_top,
            'is_recommend' => $this->is_recommend,
            'release_at' => $this->release_at,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'keywords', $this->keywords])
            ->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
