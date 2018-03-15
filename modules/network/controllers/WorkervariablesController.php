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
use app\models\JobGlobalVariable;
use app\models\search\JobGlobalVariableSearch;


/**
 * @package app\modules\network\controllers
 */
class WorkervariablesController extends Controller
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
                ]
            ]
        ];
    }


    /**
     * Render list of global worker variables
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new JobGlobalVariableSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'      => $searchModel,
            'dataProvider'     => $dataProvider,
        ]);
    }

    /**
     * Add new global variable
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new JobGlobalVariable();

        if (isset($_POST['JobGlobalVariable'])) {

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
            'model' => $model
        ]);

    }


    /**
     * Edit global variable
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {

        $model = $this->findModel($id);

        /** Prevent user from editing protected variables */
        if ($model->protected == 1) {
            \Y::flashAndRedirect('warning',
                Yii::t('network', 'Permanent system variables can not be edited'), '/network/workervariables/list'
            );
        }

        if (isset($_POST['JobGlobalVariable'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->var_name));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->var_name));
                    }

                    return $this->redirect(['list']);
                }
            }
        }

        return $this->render('_form', [
            'model' => $model
        ]);

    }


    /**
     * Delete global variable via POST
     *
     * @param  int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        /** Prevent user from deleting protected variables */
        if ($model->protected == 1) {
            \Y::flash('warning', Yii::t('network', 'Permanent system variables can not be deleted'));
            return $this->redirect(['/network/workervariables/list']);
        }

        try {
            if ($model->delete()) {
                $class   = 'success';
                $message = Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->var_name);
            } else {
                $class   = 'error';
                $message = Yii::t('network',
                    'Worker jobs: </br><b>{0}</b></br> depends on job variable <b>{1}</b>. Remove all dependencies first.',
                    [implode('</br>', $model->var_dependencies), $model->var_name]
                );
            }
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->var_name);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['list']);

    }


    /**
     * Delete global variable via Ajax
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($id)
    {

        $model = $this->findModel($id);

        /** Prevent user from deleting protected variables */
        if ($model->protected == 1) {
            return Json::encode([
                'status' => 'warning',
                'msg'    => Yii::t('network', 'Permanent system variables can not be deleted')
            ]);
        }

        try {
            if ($model->delete()) {
                $response = [
                    'status' => 'success',
                    'msg'    => Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->var_name)
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'msg'    => Yii::t('network',
                        'Worker jobs: </br><b>{0}</b></br> depends on job variable <b>{1}</b>. Remove all dependencies first.',
                        [implode('</br>', $model->var_dependencies), $model->var_name]
                    )
                ];
            }
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->var_name)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Finds the JobGlobalVariable model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return JobGlobalVariable the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = JobGlobalVariable::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
