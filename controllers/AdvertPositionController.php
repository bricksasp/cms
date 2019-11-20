<?php
namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\cms\models\AdvertPosition;
use yii\data\ActiveDataProvider;
use bricksasp\base\BaseController;
use yii\web\HttpException;

/**
 * AdvertPositionController implements the CRUD actions for AdvertPosition model.
 */
class AdvertPositionController extends BaseController
{

    /**
     * 免登录可访问
     * @return array
     */
    public function allowNoLoginAction()
    {
        return [
            'detail',
            'index'
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => AdvertPosition::find($this->dataOwnerUid()),
        ]);

        return $this->pageFormat($dataProvider);
    }
    /**
     * Displays a single AdvertPosition model.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $advert = $model->advertItems ? AdvertPosition::sortItem([$model->advertItems, 'id'],[$model->advertRelation, 'sort', 'advert_id']):[];
        $data['items'] = array_column($advert,'id');
        return $this->success($data);
    }

    /**
     * Creates a new AdvertPosition model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AdvertPosition();
        if ($model->saveAdvert(Yii::$app->request->post())) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Updates an existing AdvertPosition model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));

        if ($model->saveAdvert(Yii::$app->request->post())) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Deletes an existing AdvertPosition model.
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
     * @OA\Get(path="/advert-position/detail",
     *   summary="广告位详情",
     *   tags={"cms模块"},
     *   @OA\Parameter(
     *     description="广告位调用代码",
     *     name="code",
     *     in="query",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       default="home_banner"
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="响应数据",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/AdvertPositionDetail"),
     *     ),
     *   ),
     * )
     *
     */
    public function actionDetail()
    {
        $model = new AdvertPosition();
        $data = $model->detail(Yii::$app->request->get('code'));
        if ($data === false) {
            throw new HttpException(200,Yii::t('base',40001));
        }
        return $this->success($data);
    }

    /**
     * Finds the AdvertPosition model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AdvertPosition the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AdvertPosition::findOne($id)) !== null) {
            return $model;
        }

        throw new HttpException(200,Yii::t('base',40001));
    }
}
