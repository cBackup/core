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

namespace app\modules\mail\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use app\models\MailerEvents;
use app\models\search\MailerEventsSearch;
use app\models\search\MailerEventsTasksSearch;
use app\models\MailerEventsTasks;
use app\models\MailerEventsTasksStatuses;
use app\mailer\MailerMethods;
use app\mailer\CustomMailer;

/**
 * @package app\modules\mail\controllers
 */
class EventsController extends Controller
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
            'ajax' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-delete-event',
                    'ajax-event-task-delete',
                    'ajax-send-mail',
                    'ajax-preview-message'
                ]
            ],
        ];
    }


    /**
     * Render list of mail events
     *
     * @return string
     */
    public function actionList()
    {

        $searchModel  = new MailerEventsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $mailDisabled = false;

        if(\Y::param('mailer') == 0) {
            /** @noinspection HtmlUnknownTarget */
            \Y::flash('warning', Yii::t('app', 'Mailer is disabled. To use mailer please enable it in <a href="{url}">System settings</a>', ['url' => Url::to(['/config'])]));
            $mailDisabled = true;
        }

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'mailDisabled' => $mailDisabled
        ]);

    }


    /**
     * Render list of sent messages
     *
     * @return string
     */
    public function actionMessages()
    {
        $searchModel  = new MailerEventsTasksSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('messages', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'events_list'  => MailerEvents::find()->select('name')->indexBy('name')->asArray()->column(),
            'statuses'     => MailerEventsTasksStatuses::getStatusesList()
        ]);
    }


    /**
     * Add new mail event
     *
     * @return string|\yii\web\Response
     */
    public function actionAddEvent()
    {

        $model = new MailerEvents();

        if (isset($_POST['MailerEvents'])) {

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

        return $this->render('_event_form', [
            'model' => $model
        ]);

    }


    /**
     * Edit event
     *
     * @param  string $name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditEvent($name)
    {

        $model = $this->findModel($name);

        if (isset($_POST['MailerEvents'])) {

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

        return $this->render('_event_form', [
            'model' => $model
        ]);

    }


    /**
     * Edit event recipients
     *
     * @param  string $name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditEventRecipients($name)
    {

        $model = $this->findModel($name);

        if (isset($_POST['MailerEvents'])) {

            if ($model->load(Yii::$app->request->post())) {

                $model->scenario = 'set-recipients';

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

        return $this->render('_event_recipients', [
            'model' => $model
        ]);

    }


    /**
     * Edit event template
     *
     * @param  string $name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditEventTemplate($name)
    {

        $model = $this->findModel($name);

        if (isset($_POST['MailerEvents'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {

                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->name));

                        if( isset($_POST['saveandclose']) ) {
                            return $this->redirect(['list']);
                        }

                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->name));
                    }

                }
            }
        }

        return $this->render('_event_template', [
            'model'      => $model,
            'templ_vars' => MailerMethods::$template_variables
        ]);

    }


    /**
     * Delete event via POST
     *
     * @param  string $name
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteEvent($name)
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
     * Delete event via Ajax
     *
     * @param  string $name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDeleteEvent($name)
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
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->name)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Delete event task via Ajax
     *
     * @param  int $id
     * @return string
     */
    public function actionAjaxEventTaskDelete($id)
    {

        try {
            $model = MailerEventsTasks::findOne($id);
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
                'msg'    => Yii::t('app', 'An error occurred while deleting record.</br>Exception:</br> {0}', $e->getMessage())
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Start event distribution in background
     *
     * @param  string $task_name
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionAjaxSendMail($task_name)
    {

        $status  = 'error';
        $message = Yii::t('app', 'An error occurred while processing your request');

        if (Yii::$app->request->isAjax) {
            (new CustomMailer())->sendMail($task_name, true);
            $status  = 'success';
            $message = Yii::t('app', 'Event <b>{0}</b> distribution successfully started. For more detailed info view mailer log', $task_name);
        }

        return Json::encode(['status' => $status, 'msg' => $message]);

    }


    /**
     * Preview message without sending it
     *
     * @param  string $name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxPreviewMessage($name)
    {
        $model = $this->findModel($name);

        return $this->renderPartial('_body_preview', [
            'body' => (new CustomMailer())->generateMailBody($model->template)
        ]);
    }


    /**
     * Finds the MailerEvents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $name
     * @return MailerEvents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = MailerEvents::findOne($name)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
