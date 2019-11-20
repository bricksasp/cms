<?php
namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\cms\models\ArticleCategory;
use yii\data\ActiveDataProvider;
use bricksasp\base\BaseController;
use yii\web\HttpException;
use bricksasp\helpers\Tools;

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
     * Lists all Type models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ArticleCategory::find($this->dataOwnerUid())->with(['image']),
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
     */
    public function actionTree($id=null)
    {
        $map = [];
        if ($id) {
            $map = ['!=', 'id', (int)$id];
        }
        $data = ArticleCategory::find($this->dataOwnerUid())->select(['id', 'parent_id', 'name'])
            ->andWhere($map)
            ->all();
        $data = array_map(function ($item)
        {
            return $item->toArray();
        }, $data);
        $tree = Tools::build_tree($data, $root_id = 0);
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
        return $this->findModel(Yii::$app->request->post('id'))->delete() !== false ? $this->success() : $this->fail();
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
}
