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
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use app\models\search\WorkerSearch;
use app\models\Worker;
use app\models\Job;
use app\models\JobSnmpTypes;
use app\models\JobSnmpRequestTypes;
use app\models\WorkerProtocol;
use app\models\Task;
use app\models\JobGlobalVariable;


/**
 * @package app\modules\network\controllers
 */
class WorkerController extends Controller
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
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-add-worker',
                    'ajax-edit-worker',
                    'ajax-delete-worker',
                    'ajax-add-job',
                    'ajax-edit-job',
                    'ajax-delete-job',
                    'ajax-save-order',
                    'ajax-switch-status',
                    'ajax-view-worker',
                    'ajax-get-job-description',
                    'ajax-view-job'
                ]
            ]
        ];
    }


    /**
     * Render tree of workers and jobs
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new WorkerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'   => $searchModel,
            'dataProvider'  => $dataProvider,
            'data'          => ArrayHelper::index($dataProvider->getModels(), null, 'task_name'),
            'protocols'     => WorkerProtocol::find()->select('name')->indexBy('name')->asArray()->column(),
            'tasks'         => array_diff(Task::find()->select('name')->indexBy('name')->asArray()->column(), \Y::param('forbidden_tasks_list')),
            'table_fields'  => array_filter(Job::find()->select('table_field')->indexBy('table_field')->asArray()->column()),
            'check_jobs'    => Job::checkJobsIntegrity()
        ]);
    }


    /**
     * Add new worker via Ajax
     *
     * @param  string $task_name
     * @return string
     */
    public function actionAjaxAddWorker($task_name)
    {

        $model            = new Worker();
        $model->task_name = $task_name;
        $model->loadDefaultValues();

        if (isset($_POST['Worker'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        $status = Json::encode([
                            'status' => 'success',
                            'msg'    => Yii::t('network', 'New worker was successfully added.')
                        ]);

                    } else {
                        $status = Json::encode([
                            'status' => 'error',
                            'msg'    => Yii::t('network', 'An error occurred while adding new worker.')
                        ]);
                    }

                    return $status;

                } else {
                    return Json::encode(['status' => 'validation_failed', 'error' => $model->errors]);
                }

            }
        }

        return $this->renderPartial('_worker_form_modal', [
            'model'     => $model,
            'protocols' => WorkerProtocol::find()->select('name')->indexBy('name')->asArray()->column(),
            'tasks'     => array_diff(Task::find()->select('name')->indexBy('name')->asArray()->column(), \Y::param('forbidden_tasks_list'))
        ]);

    }


    /**
     * Edit worker via Ajax
     *
     * @param  int $worker_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxEditWorker($worker_id)
    {

        $model = $this->findWorkerModel($worker_id);

        if (isset($_POST['Worker'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {

                        $alert_status = 'success';
                        $msg          = Yii::t('network', 'Worker <b>{0}</b> was successfully edited.', $model->name);

                        if (!$model->job_delete_status) {
                            $alert_status = 'warning';
                            $msg         .= Yii::t('network', '<br>But an error occurred while deleting worker <b>{0}</b> jobs.', $model->name);
                        }

                        $status = Json::encode([
                            'status' => $alert_status,
                            'msg'    => $msg
                        ]);

                    } else {
                        $status = Json::encode([
                            'status' => 'error',
                            'msg'    => Yii::t('network', 'An error occurred while editing worker <b>{0}</b>.', $model->name)
                        ]);
                    }

                    return $status;

                } else {
                    return Json::encode(['status' => 'validation_failed', 'error' => $model->errors]);
                }

            }
        }

        return $this->renderPartial('_worker_form_modal', [
            'model'     => $model,
            'protocols' => WorkerProtocol::find()->select('name')->indexBy('name')->asArray()->column(),
            'tasks'     => []
        ]);

    }


    /**
     * Delete worker via Ajax
     *
     * @param  int $worker_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDeleteWorker($worker_id)
    {

        $model = $this->findWorkerModel($worker_id);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('network', 'Worker <b>{0}</b> was successfully deleted.', $model->name)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('network', 'An error occurred while deleting worker <b>{0}</b>.', $model->name)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Add new job via Ajax
     *
     * @param  int $worker_id
     * @param  string $task_name
     * @return string
     */
    public function actionAjaxAddJob($worker_id, $task_name)
    {

        $model                    = new Job();
        $model->worker_id         = $worker_id;
        $model->snmp_request_type = 'get';
        $model->loadDefaultValues();

        if (isset($_POST['Job'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        $status = Json::encode([
                            'status' => 'success',
                            'msg'    => Yii::t('network', 'New job was successfully added.')
                        ]);

                    } else {
                        $status = Json::encode([
                            'status' => 'error',
                            'msg'    => Yii::t('network', 'An error occurred while adding new job.')
                        ]);
                    }

                    return $status;

                } else {
                    return Json::encode(['status' => 'validation_failed', 'error' => $model->errors]);
                }

            }
        }

        return $this->renderPartial('_job_form_modal', [
            'model'         => $model,
            'jobs'          => $model->getWorkerJobs(),
            'table_fields'  => $model::getTaskTableFields($task_name),
            'snmp_types'    => JobSnmpTypes::find()->select('description')->indexBy('name')->asArray()->column(),
            'request_types' => JobSnmpRequestTypes::find()->select('name')->indexBy('name')->asArray()->column(),
            'worker_var'    => $model->getWorkerVariables(),
            'static_var'    => JobGlobalVariable::find()->select('var_name')->orderBy('id')->column()
        ]);
    }


    /**
     * Edit job via Ajax
     *
     * @param  int $job_id
     * @param  string $task_name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxEditJob($job_id, $task_name)
    {

        $model = $this->findJobModel($job_id);

        if (isset($_POST['Job'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        $status = Json::encode([
                            'status' => 'success',
                            'msg'    => Yii::t('network', 'Job <b>{0}</b> was successfully edited.', $model->name)
                        ]);

                    } else {
                        $status = Json::encode([
                            'status' => 'error',
                            'msg'    => Yii::t('network', 'An error occurred while editing job <b>{0}</b>.', $model->name)
                        ]);
                    }

                    return $status;

                } else {
                    return Json::encode(['status' => 'validation_failed', 'error' => $model->errors]);
                }

            }
        }

        return $this->renderPartial('_job_form_modal', [
            'model'         => $model,
            'jobs'          => $model->getWorkerJobs(),
            'table_fields'  => $model::getTaskTableFields($task_name),
            'snmp_types'    => JobSnmpTypes::find()->select('description')->indexBy('name')->asArray()->column(),
            'request_types' => JobSnmpRequestTypes::find()->select('name')->indexBy('name')->asArray()->column(),
            'worker_var'    => $model->getWorkerVariables(),
            'static_var'    => JobGlobalVariable::find()->select('var_name')->orderBy('id')->column(),
        ]);
    }


    /**
     * Delete job via Ajax
     *
     * @param  int $job_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDeleteJob($job_id)
    {

        $model = $this->findJobModel($job_id);

        try {
            if ($model->delete()) {
                $response = [
                    'status' => 'success',
                    'msg'    => Yii::t('network', 'Job <b>{0}</b> was successfully deleted.', $model->name)
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'msg'    => Yii::t('network',
                        'Worker jobs: </br><b>{0}</b></br> depends on job <b>{1}</b>.</br> Remove all dependencies first.',
                        [implode('</br>', $model->job_dependencies), $model->name]
                    )
                ];
            }
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('network', 'An error occurred while deleting job <b>{0}</b>.', $model->name)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Save job order via Ajax
     *
     * @return string
     */
    public function actionAjaxSaveOrder()
    {

        $request  = Yii::$app->request;
        $response = ['status' => 'error', 'msg' => Yii::t('user', 'Something went wrong. <br> View system logs for more info.')];

        if ($request->isAjax && !empty($request->post())) {

            $_post = $request->post();

            /** Get list of jobs based on _POST jobs */
            $jobs_list = Job::find()->where(['worker_id' => $_post['worker_id'], 'id' => $_post['job']])->orderBy('sequence_id')->asArray()->all();

            /** Check job sequence */
            foreach ($jobs_list as $value) {
                if (preg_match_all('/%%\w+%%/', $value['command_value'], $matches)) {
                    $query = Job::find()->where(['worker_id' => $_post['worker_id'], 'command_var' => $matches[0]])->orderBy('sequence_id')->asArray()->all();
                    foreach ($query as $item) {
                        $sequence = array_flip($_post['job'])[$value['id']];
                        if (($sequence + 1) <= $item['sequence_id']) {
                            return Json::encode([
                                'status' => 'error',
                                'msg'    => Yii::t('network', 'Incorrect job sequence.<br> Job <b>{0}</b> must go after job <b>{1}</b>', [$value['name'], $item['name']])
                            ]);
                        }
                    }
                }
            }

            try {

                /** Update job process sequence*/
                $sequence = 1;
                foreach ($_post['job'] as $job) {
                    Job::updateAll(['sequence_id' => $sequence], ['id' => $job]);
                    $sequence++;
                }

                $response = [
                    'status' => 'success',
                    'msg'    => Yii::t('network', 'Sequence successfully updated')
                ];

            } catch (\Exception $e) {
                $response = [
                    'status' => 'error',
                    'msg'    => Yii::t('network', 'An error occurred while saving sequence')
                ];
            }

        }

        return Json::encode($response);

    }


    /**
     * Change job status
     *
     * @param  int $job_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxSwitchStatus($job_id)
    {

        $request  = Yii::$app->request;
        $response = Yii::t('user', 'Something went wrong. <br> View system logs for more info.');

        if ($request->isAjax && !empty($request->post())) {

            $_post          = $request->post();
            $model          = $this->findJobModel($job_id);
            $model->enabled = intval($_post['status']);

            if ($model->save()) {
                $response = [
                    'status' => 'success',
                    'msg'    => Yii::t('network', 'Job <b>{0}</b> status was successfully changed', $model->name)
                ];
            }
            else {
                $response = [
                    'status' => 'error',
                    'msg'    => Yii::t('network', 'An error occurred while changing job <b>{0}</b> status', $model->name)
                ];
            }

        }

        return Json::encode($response);

    }


    /**
     * Render job information via Ajax
     *
     * @param  int $job_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxViewJob($job_id)
    {
        return $this->renderPartial('_job_info', [
            'data' => $this->findJobModel($job_id)
        ]);
    }


    /**
     * Render job information via Ajax
     *
     * @param  int $worker_id
     * @return string
     */
    public function actionAjaxViewWorker($worker_id)
    {
        return $this->renderPartial('_worker_info', [
            'data' => Worker::find()->joinWith(['sortedJobs'])->where(['worker.id' => intval($worker_id)])->one()
        ]);
    }


    /**
     * @param $job_id
     * @return string
     */
    public function actionAjaxGetJobDescription($job_id)
    {
        return json_encode(Job::find()->where(['id' => intval($job_id)])->asArray()->one());
    }


    /**
     * Finds the Worker model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return Worker the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findWorkerModel($id)
    {
        if (($model = Worker::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    /**
     * Finds the Job model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return Job the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findJobModel($id)
    {
        if (($model = Job::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
