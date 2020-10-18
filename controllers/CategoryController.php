<?php
namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\cms\models\ArticleCategory;
use yii\data\ActiveDataProvider;
use bricksasp\base\BaseController;
use yii\web\HttpException;
use bricksasp\helpers\Tools;
use bricksasp\rbac\models\redis\Token;

/**
 * CategoryController implements the CRUD actions for ArticleCategory model.
 */
class CategoryController extends BaseController
{
    /**
     * 免登录可访问
     * @return array
     */
    public function allowNoLoginAction()
    {
        return [
            'view',
            'index',
            'tree',
            'view',
        ];
    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $map = [];
        $query = ArticleCategory::find($this->dataOwnerUid())->with(['image']);
        if ($this->request_entrance == Token::TOKEN_TYPE_BACKEND) {
            $query = ArticleCategory::find($this->dataOwnerUid())->with(['image']);
        }else{
            $query = ArticleCategory::find($this->dataOwnerUid())->with(['image'])->where(['status' => 1])->orderBy(['sort' => SORT_ASC]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->pageFormat($dataProvider,['image'=>[['file_url'=>['implode',['',[\bricksasp\base\Config::instance()->web_url,'###']],'array']]]]);
    }

    /**
     * Displays a single ArticleCategory model.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $data['image'] = $model['image'] ? Tools::format_array($model['image'],['file_url'=>['implode',['',[\bricksasp\base\Config::instance()->web_url,'###']],'array']]) : (object)[];
        $data['parent_id'] = $data['parent_id'] ? [(string)$data['parent_id']] : [];
        return $this->success($data);
    }

    /**
     * 获取分类树
     * @OA\Get(path="/cms/category/tree",
     *   summary="获取分类树",
     *   tags={"cms模块"},
     *   @OA\Parameter(
     *     description="用户请求token",
     *     name="X-Token",
     *     in="header",
     *     @OA\Schema(
     *       type="string"
     *     )
     *   ),
     *   @OA\Parameter(
     *     description="分类id,返回对应id子树",
     *     name="id",
     *     in="query",
     *     @OA\Schema(
     *       type="integer"
     *     )
     *   ),

     *   @OA\Response(
     *     response=200,
     *     description="返回数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/cmsCategoryList"),
     *     ),
     *   ),
     * )
     *
     * @OA\Schema(
     *   schema="cmsCategoryList",
     *   description="收货地址结构",
     *   allOf={
     *     @OA\Schema(
     *       @OA\Property(property="id", type="integer", description="分类id"),
     *       @OA\Property(property="name", type="string", description="分类名称"),
     *       @OA\Property(property="parent_id", type="integer", description="父id"),
     *       @OA\Property( property="children", type="array", description="子集", @OA\Items(
     *            @OA\Property(property="id", type="integer", description="分类id"),
     *            @OA\Property( property="name", type="string", description="名称"),
     *            @OA\Property(property="parent_id", type="integer", description="父id"),
     *         ),
     *       ),
     *     )
     *   }
     * )
     * 
     * @return mixed
     */
    public function actionTree($id=null)
    {
        $map = [];
        if ($id) {
            $map = ['!=', 'id', (int)$id];
        }

        $query = ArticleCategory::find($this->dataOwnerUid())->with(['image']);
        if ($this->request_entrance == Token::TOKEN_TYPE_BACKEND) {
            $data = ArticleCategory::find($this->dataOwnerUid())->select(['id', 'parent_id', 'name'])
                ->andWhere($map)
                ->all();
            $data = array_map(function ($item)
            {
                return $item->toArray();
            }, $data);
        }else{
            $data = ArticleCategory::find($this->dataOwnerUid())
                ->select(['id', 'parent_id', 'name','image_id'])
                ->with(['image'])
                ->andWhere($map)
                ->all();
            foreach ($data as $k => $item) {
                $row = $item->toArray();
                $row['image'] = $item->image ? Tools::format_array($item->image,['file_url'=>['implode',['',[\bricksasp\base\Config::instance()->web_url,'###']],'array']]) : (object)[];
                $data[$k] = $row;
            }
        }
        
        $tree = Tools::build_tree($data);
        return $this->success($tree);
    }

    /**
     * Creates a new ArticleCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ArticleCategory();

        $data = Yii::$app->request->post();
        if (!empty($data['parent_id']) && is_array($data['parent_id'])) {
            $data['parent_id'] = end($data['parent_id']);
        }
        if ($model->load($data,'') && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Updates an existing ArticleCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));

        $data = Yii::$app->request->post();
        if (!empty($data['parent_id']) && is_array($data['parent_id'])) {
            $data['parent_id'] = end($data['parent_id']);
        }
        if ($model->load($data,'') && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Deletes an existing ArticleCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));
        $this->deleteChildren($model->id);
        return $model->delete() !== false ? $this->success() : $this->fail();
    }

    /**
     * Finds the ArticleCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ArticleCategory the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ArticleCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new HttpException(200,Yii::t('base',40001));
    }

    public function deleteChildren($id)
    {
        $children = ArticleCategory::find()->where(['parent_id'=>$id])->asArray()->all();
        if ($children) {
            $ids = array_column($children,'id');
            ArticleCategory::deleteAll(['id'=>$ids]);
            $this->deleteChildren($ids);
        }
        return true;
    }
}
