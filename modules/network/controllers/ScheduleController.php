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
use app\components\NetSsh;
use app\models\search\ScheduleSearch;
use app\models\search\ScheduleMailSearch;
use app\models\Schedule;
use app\models\ScheduleMail;
use app\models\MailerEvents;
use app\models\Task;


/**
 * @package app\modules\network\controllers
 */
class ScheduleController extends Controller
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
                    'delete'               => ['post'],
                    'delete-mail-schedule' => ['post']
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-delete',
                    'ajax-delete-mail-schedule',
                    'ajax-scheduler',
                    'ajax-scheduler-run-task'
                ]
            ]
        ];
    }


    /**
     * Render list of scheduled tasks
     *
     * @return string
     */
    public function actionList()
    {

        /** Tasks schedule data provider  */
        $searchModel  = new ScheduleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        /** Mailer schedule data provider  */
        $mailSearchModel  = new ScheduleMailSearch();
        $mailDataProvider = $mailSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'      => $searchModel,
            'dataProvider'     => $dataProvider,
            'mailSearchModel'  => $mailSearchModel,
            'mailDataProvider' => $mailDataProvider
        ]);
    }


    /**
     * Add new schedule
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new Schedule();

        if (isset($_POST['Schedule'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                    }

                    return $this->redirect(['/network/schedule/edit', 'id' => $model->id]);

                }
            }
        }

        return $this->render('_form', [
            'model'      => $model,
            'tasks_list' => Task::getTasksList()
        ]);

    }


    /**
     * Edit schedule
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {

        $model = $this->findModel($id);

        if (isset($_POST['Schedule'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->task_name));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->task_name));
                    }

                    return $this->redirect(['/network/schedule/edit', 'id' => $model->id]);

                }
            }
        }

        return $this->render('_form', [
            'model'      => $model,
            'tasks_list' => Task::getTasksList()
        ]);

    }


    /**
     * Delete schedule via POST
     *
     * @param  int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->task_name);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->task_name);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/network/schedule/list']);

    }


    /**
     * Delete schedule via Ajax
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($id)
    {

        $model = $this->findModel($id);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->task_name)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->task_name)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Add new mail schedule
     *
     * @return string|\yii\web\Response
     */
    public function actionAddMailSchedule()
    {

        $model = new ScheduleMail();

        if (isset($_POST['ScheduleMail'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                    }

                    return $this->redirect(['/network/schedule/edit-mail-schedule', 'id' => $model->id]);

                }
            }
        }

        return $this->render('_form_mail', [
            'model'      => $model,
            'events_list' => MailerEvents::find()->select('name')->indexBy('name')->asArray()->column()
        ]);

    }


    /**
     * Edit mail schedule
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditMailSchedule($id)
    {

        $model = $this->findMailModel($id);

        if (isset($_POST['ScheduleMail'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->event_name));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->event_name));
                    }

                    return $this->redirect(['/network/schedule/edit-mail-schedule', 'id' => $model->id]);

                }
            }
        }

        return $this->render('_form_mail', [
            'model'       => $model,
            'events_list' => MailerEvents::find()->select('name')->indexBy('name')->asArray()->column()
        ]);

    }


    /**
     * Delete mail schedule via POST
     *
     * @param  int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteMailSchedule($id)
    {

        $model = $this->findMailModel($id);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->event_name);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->event_name);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/network/schedule/list']);

    }


    /**
     * Delete mail schedule via Ajax
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDeleteMailSchedule($id)
    {

        $model = $this->findMailModel($id);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->event_name)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->event_name)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Set scheduler status via Ajax
     *
     * @param  int $mode
     * @return string
     */
    public function actionAjaxScheduler($mode)
    {

        try {

            switch ((int)$mode) {
                case 0: $action = 'start';   break;
                case 1: $action = 'stop';    break;
                case 2: $action = 'restart'; break;
                default: throw new \Exception(Yii::t('network', 'Unknown Java scheduler mode specified'));
            }

            $command  = (new NetSsh())->init()->schedulerExec("cbackup {$action} -json");
            $response = ['status' => 'success', 'msg' => ''];

            /** Throw exception if error occurs */
            if (!$command['success']) {
                throw new \Exception($command['exception']);
            }

            /** Show warning if something went wrong */
            if ($command['success'] && !$command['object']) {
                $response = ['status' => 'warning', 'msg' => Yii::t('network', 'Something went wrong. Java response: {0}', $command['message'])];
            }

        } catch (\Exception $e) {
            $response = ['status' => 'danger', 'msg' => $e->getMessage()];
        }

        return Json::encode($response);

    }


    /**
     * Start specific task by task name via Ajax
     *
     * @param  string $task_name
     * @return string
     */
    public function actionAjaxSchedulerRunTask($task_name)
    {

        try {

            $command  = (new NetSsh())->init()->schedulerExec("cbackup runtask {$task_name} -json");
            $response = ['status' => 'success', 'msg' => Yii::t('network', 'Task <b>{0}</b> successfully started', $task_name)];

            /** Throw exception if error occurs */
            if (!$command['success']) {
                throw new \Exception($command['exception']);
            }

            /** Show warning if something went wrong */
            if ($command['success'] && !$command['object']) {
                $response = ['status' => 'warning', 'msg' => Yii::t('network', 'Something went wrong. Java response: {0}', $command['message'])];
            }

        } catch (\Exception $e) {
            $response = ['status' => 'error', 'msg' => Yii::t('app', 'Error: {0}', $e->getMessage())];
        }

        return Json::encode($response);

    }


    /**
     * Finds the Schedule model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return Schedule the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Schedule::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    /**
     * Finds the ScheduleMail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return ScheduleMail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findMailModel($id)
    {
        if (($model = ScheduleMail::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
