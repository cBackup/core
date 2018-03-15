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
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\filters\AjaxFilter;
use app\models\DeviceAuthTemplate;
use app\models\JobGlobalVariable;
use app\models\search\DeviceAuthTemplateSearch;


/**
 * @package app\modules\network\controllers
 */
class AuthtemplateController extends Controller
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
                    'delete' => ['post'],
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-delete',
                    'ajax-add-template'
                ]
            ]
        ];
    }

    /**
     * Render list of device auth templates
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new DeviceAuthTemplateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'      => $searchModel,
            'dataProvider'     => $dataProvider,
        ]);
    }


    /**
     * Add new auth template
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new DeviceAuthTemplate();

        if (isset($_POST['DeviceAuthTemplate'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                    }

                    return $this->redirect(['list']);
                }
            }
        }

        return $this->render('_form', [
            'model' => $model,
            'vars'  => JobGlobalVariable::find()->select(['description'])->where(['like', 'var_name', '%%SEQ'])->indexBy('var_name')->asArray()->column()
        ]);

    }


    /**
     * Edit auth template
     *
     * @param  string $name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($name)
    {

        $model = $this->findModel($name);

        if (isset($_POST['DeviceAuthTemplate'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->name));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->name));
                    }

                    return $this->redirect(['list']);
                }
            }
        }

        return $this->render('_form', [
            'model' => $model,
            'vars'  => JobGlobalVariable::find()->select(['description'])->where(['like', 'var_name', '%%SEQ'])->indexBy('var_name')->asArray()->column()
        ]);

    }


    /**
     * Delete auth template via POST
     *
     * @param  string $name
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($name)
    {

        $model = $this->findModel($name);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->name);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->name);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['list']);

    }


    /**
     * Delete auth template via Ajax
     *
     * @param  string $name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($name)
    {

        $model = $this->findModel($name);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->name)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $response = [
                'status' => 'error',
                'msg'    => Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->name)
            ];
        }

        return Json::encode($response);

    }


    /**
     * Add new template via Ajax
     *
     * @return string
     */
    public function actionAjaxAddTemplate()
    {

        $model = new DeviceAuthTemplate();

        if (isset($_POST['DeviceAuthTemplate'])) {

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

        return $this->renderPartial('_auth_form_modal', [
            'model' => $model,
            'vars'  => JobGlobalVariable::find()->select(['description'])->where(['like', 'var_name', '%%SEQ'])->indexBy('var_name')->asArray()->column()
        ]);

    }


    /**
     * Finds the DeviceAuthTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $name
     * @return DeviceAuthTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = DeviceAuthTemplate::findOne($name)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
