<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\modules\network\controllers;

use Yii;
use app\models\Device;
use app\models\search\DeviceSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use app\models\Vendor;
use app\models\search\DeviceAttributesUnknownSearch;
use app\models\DeviceAttributesUnknown;
use app\models\DeviceAttributes;
use app\models\DeviceAuthTemplate;


/**
 * DeviceController implements the CRUD actions for Device model.
 * 
 * @package app\modules\network\controllers
 */
class DeviceController extends Controller
{

    /**
     * @var string
     */
    public $defaultAction = 'list';


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-update-devices',
                    'ajax-delete-unknown',
                    'ajax-update-templates',
                    'ajax-auth-template-preview',
                    'ajax-get-device-attributes',
                    'ajax-add-device'
                ]
            ]
        ];
    }

    /**
     * Lists all Device models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new DeviceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'vendors'      => Vendor::find()->asArray()->all(),
            'unkn_count'   => DeviceAttributesUnknown::find()->count()
        ]);
    }


    /**
     * Creates a new Device model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAdd()
    {

        $model = new Device();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect([$this->defaultAction]);
        }
        else {
            return $this->render('_form', [
                'model'     => $model,
                'vendors'   => Vendor::find()->select('name')->indexBy('name')->asArray()->column(),
                'templates' => DeviceAuthTemplate::find()->select('name')->indexBy('name')->asArray()->column()
            ]);
        }

    }

    /**
     * Updates an existing Device model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect([$this->defaultAction]);
        }
        else {
            return $this->render('_form', [
                'model'     => $model,
                'vendors'   => Vendor::find()->select('name')->indexBy('name')->asArray()->column(),
                'templates' => DeviceAuthTemplate::find()->select('name')->indexBy('name')->asArray()->column()
            ]);
        }

    }

    /**
     * Deletes an existing Device model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        try {
            $model->delete();
        }
            /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->model);
            if( $e->getCode() == 23000 ) {
                $message.= '<br>'.Yii::t('network', 'This device is assigned to already existing nodes, please remove nodes first.');
            }
            else {
                $message.= '<br>'.$e->getMessage();
            }
            \Y::flash('danger', $message);
        }

        return $this->redirect([$this->defaultAction]);

    }


    /**
     * Render unknown device list
     *
     * @return string
     */
    public function actionUnknownList()
    {
        $searchModel = new DeviceAttributesUnknownSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('unknown_list', [
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Add unknown device
     *
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionAddUnknownDevice()
    {

        $model = new DeviceAttributes();
        $data  = Yii::$app->request->post('data');

        /** Show warning message if something went wrong */
        if (is_null($data) && !isset($_POST['DeviceAttributes'])) {
            \Y::flashAndRedirect(
                'warning', Yii::t('network', 'Error while retrieving necessary parameters or form was refreshed.'), '/network/device/unknown-list'
            );
        }

        /** Preload form with necessary data */
        $model->attributes = $data;

        if (isset($_POST['DeviceAttributes'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();

                    try {

                        if ($model->save()) {
                            DeviceAttributesUnknown::findOne($model->unkn_id)->delete();
                            \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                        }

                        return $this->redirect(['device/unknown-list']);

                    }
                    /** @noinspection PhpUndefinedClassInspection */
                    catch (\Throwable $e) {
                        $transaction->rollBack();
                        \Y::flashAndRedirect('warning', $e->getMessage(), 'device/unknown-list');
                    }

                }
            }
        }

        return $this->render('_form_unknown', [
            'model'   => $model,
            'devices' => ArrayHelper::map(Device::find()->all(), 'id', 'model', 'vendor')
        ]);
    }


    /**
     * Change device
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionChangeDevice($id)
    {

        $model = $this->findDeviceAttributesModel($id);

        if (isset($_POST['DeviceAttributes'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->sysobject_id));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->sysobject_id));
                    }

                    return $this->redirect([$this->defaultAction]);

                }
            }
        }

        return $this->render('_form_unknown', [
            'model'   => $model,
            'devices' => ArrayHelper::map(Device::find()->all(), 'id', 'model', 'vendor')
        ]);

    }


    /**
     * Delete device attributes
     *
     * @param  int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteAttributes($id)
    {

        $model = $this->findDeviceAttributesModel($id);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->sysobject_id);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->sysobject_id);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect([$this->defaultAction]);

    }


    /**
     * Delete unknown device attributes via Ajax
     *
     * @param  int $id
     * @return string
     */
    public function actionAjaxDeleteUnknown($id)
    {

        $model = DeviceAttributesUnknown::findOne($id);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->sys_description)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->sys_description)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Get devices for select2 via Ajax
     *
     * @return string
     */
    public function actionAjaxUpdateDevices()
    {
        $devices = ArrayHelper::index(Device::find()->select('id, model as text, vendor')->orderBy('id')->asArray()->all(), null, 'vendor');
        $result  = [];
        /** Create acceptable data array for select2 */
        foreach ($devices as $vendor => $models) {
            $result[] = ['text' => $vendor, 'children' => $models];
        }
        return Json::encode($result);
    }


    /**
     * Get templates for select2 via Ajax
     *
     * @return string
     */
    public function actionAjaxUpdateTemplates()
    {
        $result  = [];
        $templates = DeviceAuthTemplate::find()->select('name')->indexBy('name')->asArray()->column();

        /** Create acceptable data array for select2 */
        foreach ($templates as $id => $name) {
            $result[] = ['id' => $id, 'text' => $name];
        }

        return Json::encode($result);
    }


    /**
     * Get auth sequence for preview via Ajax
     *
     * @param  string $name
     * @return string
     */
    public function actionAjaxAuthTemplatePreview($name)
    {
        $template = DeviceAuthTemplate::find()->select('auth_sequence')->where(['name' => $name])->scalar();
        return Json::encode($template);
    }


    /**
     * Render device attributes list
     *
     * @param  int $device_id
     * @return string
     */
    public function actionAjaxGetDeviceAttributes($device_id)
    {
        return $this->renderPartial('_view_attributes', [
            'data' => DeviceAttributes::find()->where(['device_id' => $device_id])->orderBy('hw')->all()
        ]);
    }


    /**
     * Add new device via Ajax
     *
     * @return string
     */
    public function actionAjaxAddDevice()
    {

        $model = new Device();

        if (isset($_POST['Device'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        $status = Json::encode([
                            'status' => 'success',
                            'msg'    => Yii::t('app', 'Record added successfully.')
                        ]);

                    } else {
                        $status = Json::encode([
                            'status' => 'error',
                            'msg'    => Yii::t('app', 'An error occurred while adding record.')
                        ]);
                    }

                    return $status;

                } else {
                    return Json::encode(['status' => 'validation_failed', 'error' => $model->errors]);
                }

            }
        }

        return $this->renderPartial('_device_form_modal', [
            'model'     => $model,
            'vendors'   => Vendor::find()->select('name')->indexBy('name')->asArray()->column(),
            'templates' => DeviceAuthTemplate::find()->select('name')->indexBy('name')->asArray()->column()
        ]);

    }


    /**
     * Finds the Device model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Device the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Device::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    /**
     * Finds the DeviceAttributes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DeviceAttributes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findDeviceAttributesModel($id)
    {
        if (($model = DeviceAttributes::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
