<?php
namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\cms\models\Article;
use bricksasp\cms\models\ArticleSearch;
use bricksasp\cms\models\ArticleLabel;
use bricksasp\cms\models\ArticleCategory;
use bricksasp\base\BaseController;
use bricksasp\base\Config;
use yii\web\HttpException;
use bricksasp\helpers\Tools;
use bricksasp\rbac\models\redis\Token;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends BaseController
{
    /**
     * Lists all Article models.
     * @OA\Get(path="/cms/article/index",
     *   summary="文章列表",
     *   tags={"cms模块"},
     *   @OA\Parameter(
     *     description="开启平台功能后，访问商户对应的数据标识，未开启忽略此参数",
     *     name="access-token",
     *     in="query",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     description="分类调用代码",
     *     name="code",
     *     in="query",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     description="当前叶数",
     *     name="page",
     *     in="query",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Parameter(
     *     description="每页行数",
     *     name="pageSize",
     *     in="query",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *         schema="articleList",
     *         description="文章详情结构",
     *         ref="#/components/schemas/articleDetail"),
     *     ),
     *   ),
     * )
     */
    public function actionIndex()
    {
        $query = ArticleCategory::find($this->dataOwnerUid())->with(['image']);
        if ($this->request_entrance == Token::TOKEN_TYPE_BACKEND) {
            $fields = [];
        }else{
            $fields = ['id', 'title'];
        }

        $searchModel = new ArticleSearch();
        $dataProvider = $searchModel->search($this->queryFilters(),$fields);

        return $this->pageFormat($dataProvider,['labelItems'=>false,'labels'=>false, 'image'=>[
            ['file_url'=>['implode',['',[Config::instance()->web_url,'###']],'array']]
        ]]);
    }

    /**
     * @OA\Get(path="/cms/article/view",
     *   summary="文章详情",
     *   tags={"cms模块"},
     *   @OA\Parameter(
     *     description="文章id",
     *     name="id",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/articleDetail"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="articleDetail",
     *   description="文章详情结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="title", type="string", description="标题"),
     *       @OA\Property(property="reprint_info", type="string", description="转载说明"),
     *       @OA\Property(property="brief", type="string", description="简介"),
     *       @OA\Property(property="content", type="string", description="详情"),
     *       @OA\Property(property="comments_count", type="integer", description="评论数"),
     *       @OA\Property(property="view_count", type="integer", description="浏览数"),
     *       @OA\Property(property="like_count", type="integer", description="关注数"),
     *       @OA\Property(property="is_comment", type="integer", description="评论 1允许 2不允许"),
     *       @OA\Property(property="is_recommend", type="integer", description="推荐 1是 2否"),
     *       @OA\Property(property="imageItem", description="封面", ref="#/components/schemas/file"),
     *       @OA\Property(property="labelItems", type="array", description="封面", @OA\Items(ref="#/components/schemas/label")),
     *     )
     *   }
     * )
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $data = $model->toArray();
        $data['imageItem'] = $model->image ? Tools::format_array($model->image,['file_url'=>['implode',['',[Config::instance()->web_url,'###']],'array']]) : (object)[];
        $amodel = new ArticleCategory();
        $cascader = $amodel->cascader($model->cat_id);
        $data['cat_id'] = array_column($cascader, 'id');
        return $this->success($data);
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Article();
        $data = Yii::$app->request->post();
        if ($data['release_at']) {
            $data['release_at'] = $data['release_at'] / 1000;
        }
        $data['owner_id'] = $this->ownerId;
        if ($model->load($data) && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));
        $data = Yii::$app->request->post();
        if ($data['release_at']) {
            $data['release_at'] = $data['release_at'] / 1000;
        }
        
        if ($model->load($data) && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Deletes an existing Article model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        $transaction = Article::getDb()->beginTransaction();
        if (is_array($id)) $n = Article::deleteAll(['id'=>$id, 'user_id'=>$this->uid]); else $item = $this->findModel($id);
            
        try {
            ArticleLabel::deleteAll(['Article_id'=>$id]);
            if (is_array($id)) {
                if ($n != count($id)) {
                    $transaction->rollBack();
                    Tools::exceptionBreak(Yii::t('base',40003));
                }
            } else $item->delete();
            $transaction->commit();
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->success($n);
    }

    public function actionSetlabel()
    {
        $article_id = Yii::$app->request->get('id');
        $data = Yii::$app->request->post();
        $inster = [];
        foreach ($data as $k => $item) {
            $row['article_id'] = $article_id;
            $row['lable_id'] = $item['id'];
            $row['sort'] = $k + 1;
            $inster[] = $row;
        }
        ArticleLabel::deleteAll(['article_id' => $article_id]);
        $a = ArticleLabel::getDb()->createCommand()
            ->batchInsert(ArticleLabel::tableName(),['article_id','lable_id','sort'],$inster)
            ->execute();
        return $this->success($a);
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Article the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        }

        throw new HttpException(200,Yii::t('base',40001));
    }
}
