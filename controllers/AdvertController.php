<?php
namespace bricksasp\cms\controllers;

use Yii;
use bricksasp\cms\models\Advert;
use yii\data\ActiveDataProvider;
use bricksasp\base\BaseController;
use yii\web\HttpException;
use bricksasp\helpers\Tools;

/**
 * AdvertController implements the CRUD actions for Advert model.
 */
class AdvertController extends BaseController
{
    /**
     * 免登录可访问
     * @return array
     */
    public function allowNoLoginAction()
    {
        return [
            'index',
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Advert::find($this->dataOwnerUid())->with(['image']),
        ]);

        return $this->pageFormat($dataProvider,['image'=>[['file_url'=>['implode',['',[\bricksasp\base\Config::instance()->web_url,'###']],'array']]]]);
    }
    /**
     * Displays a single Advert model.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionView()
    {
        $model = $this->findModel(Yii::$app->request->get('id'));
        $data = $model->toArray();
        $data['image'] = $model['image'] ? Tools::format_array($model['image'],['file_url'=>['implode',['',[\bricksasp\base\Config::instance()->web_url,'###']],'array']]) : (object)[];
        return $this->success($data);
    }

    /**
     * Creates a new Advert model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Advert();
        $data = Yii::$app->request->post();
        if (!empty($data['start_at'])) {
            $data['start_at'] = $data['start_at'] / 1000;
        }
        if (!empty($data['end_at'])) {
            $data['end_at'] = $data['end_at'] / 1000;
        }
        if ($model->load($data,'') && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Updates an existing Advert model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException if the model cannot be found
     */
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));

        $data = Yii::$app->request->post();
        if (!empty($data['start_at'])) {
            $data['start_at'] = $data['start_at'] / 1000;
        }
        if (!empty($data['end_at'])) {
            $data['end_at'] = $data['end_at'] / 1000;
        }
        if ($model->load($data,'') && $model->save()) {
            return $this->success();
        }

        return $this->fail($model->errors);
    }

    /**
     * Deletes an existing Advert model.
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
     * Finds the Advert model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Advert the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Advert::findOne($id)) !== null) {
            return $model;
        }

        throw new HttpException(200,Yii::t('base',40001));
    }
}
