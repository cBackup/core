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
use yii\helpers\Inflector;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\filters\AjaxFilter;
use app\models\search\TaskSearch;
use app\models\Task;
use app\models\TaskDestination;
use app\models\TaskType;

/**
 * @package app\modules\network\controllers
 */
class TaskController extends Controller
{

    /**
     * @var string
     */
    public $defaultAction = 'list';

    /**
     * List of destinations
     *
     * @var array
     */
    public $destinations = [];

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
                    'ajax-create-table',
                    'ajax-edit-table',
                    'ajax-delete-table'
                ]
            ]
        ];
    }


    /**
     * @inheritdoc
     */
    public function beforeAction($event)
    {
        $actions = ['add', 'edit'];

        if (in_array($event->id, $actions)) {
            $this->destinations = ArrayHelper::map(TaskDestination::find()->all(), 'name', function ($data) {
                /** @var $data \app\models\TaskDestination */
                return Yii::t('network', $data->description);
            });
        }

        return parent::beforeAction($event);
    }


    /**
     * Render list of tasks
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new TaskSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        /** Create types list array for dropdownlist */
        $types_list = TaskType::find()->select(['name'])->asArray()->all();
        $task_types = ArrayHelper::map($types_list, 'name', function ($data) {
            return Yii::t('network', Inflector::humanize($data['name']));
        });

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'task_types'   => $task_types
        ]);
    }


    /**
     * Add new task
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new Task();

        /** Set model attributes after Pjax reload */
        if (Yii::$app->request->isAjax) {
            $model->attributes = Yii::$app->request->post('params');
        }

        if (isset($_POST['Task'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->out_table_exists == true) {

                        /** Create table name based task name */
                        $model->table = "out_{$model->name}";

                        if ($model->save()) {
                            \Y::flash('success', Yii::t('network', 'New task was successfully added.'));
                        } else {
                            \Y::flash('danger', Yii::t('network', 'An error occurred while adding new task.'));
                        }

                        return $this->redirect(['/network/task/edit', 'name' => $model->name]);
                    }
                }
            }
        }

        return $this->render('_form', [
            'model'        => $model,
            'destinations' => $this->destinations,
            'table_fields' => []
        ]);

    }


    /**
     * Edit task
     *
     * @param  string $name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($name)
    {

        $model = $this->findModel($name);

        /** Set model attributes after Pjax reload */
        if (Yii::$app->request->isAjax) {
            $model->attributes = Yii::$app->request->post('params');
        }

        if (isset($_POST['Task'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->out_table_exists == true) {
                        if ($model->save()) {

                            $status  = 'success';
                            $message = Yii::t('network', 'Task <b>{0}</b> was successfully edited.', $model->name);

                            if ($model->clean_up['show_msg']) {
                                if ($model->clean_up['table'] && $model->clean_up['files']) {
                                    $message .= Yii::t('network', '<br>Table clean up: successfully <br>Files clean up: successfully');
                                    $message .= ($model->clean_up['disable_git']) ? '<br>' . Yii::t('network', 'Disable GIT: successfully') : '';
                                } else {
                                    $status  = 'warning';
                                    $message.= Yii::t('network', '<br>An error occurred while cleaning up task related data. View system logs for more info.');
                                }
                            }

                            \Y::flash($status, $message);

                        } else {
                            \Y::flash('danger', Yii::t('network', 'An error occurred while editing task <b>{0}</b>.', $model->name));
                        }

                        return $this->redirect(['/network/task/edit', 'name' => $model->name]);
                    }

                }
            }
        }

        return $this->render('_form', [
            'model'        => $model,
            'destinations' => $this->destinations,
            'table_fields' => $model::getTaskOutTableFields($name)
        ]);

    }


    /**
     * Delete task via POST
     *
     * @param  string $name
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($name)
    {

        $model = $this->findModel($name);

        /** Check if task is attached to one or more objects */
        if ($model->isTaskAttached()) {
            \Y::flash('danger', Yii::t('network', 'Task <b>{0}</b> is attached to one or more objects.', $model->name));
            return $this->redirect(['/network/task/edit', 'name' => $model->name]);
        }

        try {

            /** Delete task */
            $model->delete();

            /** Drop table after task delete if table exists */
            $drop_msg = '';
            if ($model::outTableExists($name)) {
                $model::deleteTable($name);
                $drop_msg = Yii::t('network', 'Table <b>out_{0}</b> was successfully deleted.', $model->name);
            }

            /** Delete all task related files */
            $files_msg = '';
            if ($model->put == 'file') {
                $model::deleteFiles($name);
                $files_msg = Yii::t('network', 'Files related to task <b>{0}</b> successfully deleted.', $model->name);
            }

            $class   = 'success';
            $message = Yii::t('network', 'Task <b>{0}</b> was successfully deleted.', $model->name);
            $message.= "<br>{$drop_msg}<br>$files_msg";
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('network', 'An error occurred while deleting task <b>{0}</b>.', $model->name);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/network/task/list']);

    }


    /**
     * Delete task via AJAX
     *
     * @param  int $name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($name)
    {

        $model = $this->findModel($name);

        /** Check if task is attached to one or more objects */
        if ($model->isTaskAttached()) {
            return Json::encode([
                'status' => 'warning',
                'msg'    => Yii::t('network', 'Task <b>{0}</b> is attached to one or more objects.', $model->name)
            ]);
        }

        try {

            /** Delete task */
            $model->delete();

            /** Drop table after task delete if table exists */
            $drop_msg = '';
            if ($model::outTableExists($name)) {
                $model::deleteTable($name);
                $drop_msg = Yii::t('network', 'Table <b>out_{0}</b> was successfully deleted.', $model->name);
            }

            /** Delete all task related files */
            $files_msg = '';
            if ($model->put == 'file') {
                $model::deleteFiles($name);
                $files_msg = Yii::t('network', 'Files related to task <b>{0}</b> successfully deleted.', $model->name);
            }

            $class   = 'success';
            $message = Yii::t('network', 'Task <b>{0}</b> was successfully deleted.', $model->name);
            $message.= "<br>{$drop_msg}<br>$files_msg";

        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {

            $class   = 'error';
            $message = Yii::t('network', 'An error occurred while deleting task <b>{0}</b>.', $model->name);
            $message.= '<br>'.$e->getMessage();

        }

        return Json::encode(['status' => $class, 'msg' => $message]);


    }


    /**
     * Create out table via Ajax
     *
     * @param  string $task_name
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionAjaxCreateTable($task_name)
    {

        $fields = ['field_1' => ''];

        if (Yii::$app->request->post()) {

            $model     = new Task();
            $fields    = array_filter(Yii::$app->request->post('fields'));
            $validator = $model->formValidator($fields);

            if ($validator->validate()) {

                if ($model->createTable($task_name, array_filter($validator->attributes))) {
                    $status = Json::encode([
                        'status' => 'success',
                        'msg'    => Yii::t('network', 'New table was successfully created. Do not forget to save task.')
                    ]);
                }
                else {
                    $status = Json::encode([
                        'status' => 'error',
                        'msg'    => Yii::t('network', 'An error occurred while creating new table.')
                    ]);
                }

                return $status;

            }
            else {
                return Json::encode(['status' => 'validation_failed', 'error' => $validator->errors]);
            }
        }

        return $this->renderPartial('_create_table_modal', [
            'fields'       => $fields,
            'table_exists' => false,
            'protected'    => 0
        ]);

    }


    /**
     * Edit out table via Ajax
     *
     * @param  string $task_name
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionAjaxEditTable($task_name)
    {

        $model        = new Task();
        $fields       = ['field_1' => ''];
        $table_fields = $model::getTaskOutTableFields($task_name);

        /** Create array of table fields based on table columns */
        if (!empty($table_fields['custom_fields'])) {
            $i = 1;
            foreach ($table_fields['custom_fields'] as $field) {
                $fields['field_' . $i] = $field->name;
                $i++;
            }
        }

        /** Process posted data */
        if (Yii::$app->request->post()) {

            $fields    = array_filter(Yii::$app->request->post('fields'));
            $validator = $model->formValidator($fields);

            if ($validator->validate()) {

                /** Check if form was changed  */
                if (ArrayHelper::getColumn($table_fields['custom_fields'], 'name') == array_values($fields)) {
                    return Yii::t('network', 'Form has not been changed.');
                }

                if ($model->createTable($task_name, array_filter($validator->attributes))) {
                    $status = Json::encode([
                        'status' => 'success',
                        'msg'    => Yii::t('network', 'Table <b>out_{0}</b> was successfully edited.', $task_name)
                    ]);
                } else {
                    $status = Json::encode([
                        'status' => 'error',
                        'msg'    => Yii::t('network', 'An error occurred while editing table <b>out_{0}</b>.', $task_name)
                    ]);
                }

                return $status;

            } else {
                return Json::encode(['status' => 'validation_failed', 'error' => $validator->errors]);
            }
        }

        return $this->renderPartial('_create_table_modal', [
            'fields'       => $fields,
            'table_exists' => $model::outTableExists($task_name),
            'protected'    => $this->findModel($task_name)->protected
        ]);

    }


    /**
     * Delete out table via Ajax
     *
     * @param  string $task_name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDeleteTable($task_name)
    {

        $model = $this->findModel($task_name);

        /** Protection from messing around with link attributes */
        if ($model->protected == 1 || !Task::outTableExists($task_name)) {
            return Json::encode([
                'status' => 'warning',
                'msg'    => Yii::t('network', '<b>STOP messing around with link attributes and go play somewhere else!</b>')
            ]);
        }

        /** Check if table is attached to current task */
        if (!is_null($model->put)) {
            return Json::encode([
                'status' => 'warning',
                'msg'    => Yii::t('network', 'Table is set as destination to current task. Please first deselect task destination and then delete table.')
            ]);
        }

        try {

            $model::deleteTable($task_name);
            $class   = 'success';
            $message = Yii::t('network', 'Table <b>out_{0}</b> was successfully deleted.', $model->name);

        }
        catch (\Exception $e) {

            $class   = 'error';
            $message = Yii::t('network', 'An error occurred while deleting table <b>out_{0}</b>.', $model->name);
            $message.= '<br>'.$e->getMessage();

        }

        return Json::encode(['status' => $class, 'msg' => $message]);

    }


    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $name
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = Task::findOne($name)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
