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
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\filters\AjaxFilter;
use app\models\TasksHasDevices;
use app\models\TasksHasNodes;
use app\models\search\TasksHasDevicesSearch;
use app\models\search\TasksHasNodesSearch;
use app\models\Device;
use app\models\Worker;
use app\models\Task;
use app\models\search\CustomNodeSearch;
use app\models\search\CustomDeviceSearch;
use app\models\Network;
use app\models\Vendor;


/**
 * @package app\modules\network\controllers
 */
class AssigntaskController extends Controller
{

    /**
     * @var string
     */
    public $defaultAction = 'list';

    /**
     * @var array
     */
    protected $devices_list = [];

    /**
     * @var array
     */
    protected $tasks_list = [];

    /**
     * @var array
     */
    protected $nodes_list = [];


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
                    'delete-device-task' => ['post'],
                    'delete-node-task'   => ['post'],
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-get-task-workers',
                    'ajax-get-nodes',
                    'ajax-update-workers',
                    'ajax-delete-device-task',
                    'ajax-assign-nodes',
                    'ajax-assign-devices'
                ]
            ]
        ];
    }


    /**
     * @inheritdoc
     */
    public function beforeAction($event)
    {
        $actions = ['list', 'assign-device-task', 'edit-device-task', 'assign-node-task', 'edit-node-task', 'adv-node-assign', 'adv-device-assign'];

        if (in_array($event->id, $actions)) {
            $tasks = Task::find()->select('name')->indexBy('name')->where(['!=', 'task_type', 'yii_console_task'])->asArray()->column();
            $this->devices_list = ArrayHelper::map(Device::find()->all(), 'id', 'model', 'vendor');
            $this->tasks_list   = array_diff($tasks, \Y::param('forbidden_tasks_list'));
        }

        return parent::beforeAction($event);
    }


    /**
     * Render task assign view
     *
     * @return string
     */
    public function actionList()
    {

        /** Devices data provider */
        $deviceSearchModel  = new TasksHasDevicesSearch();
        $deviceDataProvider = $deviceSearchModel->search(Yii::$app->request->queryParams);

        /** Nodes data provider */
        $nodeSearchModel  = new TasksHasNodesSearch();
        $nodeDataProvider = $nodeSearchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'deviceSearchModel'  => $deviceSearchModel,
            'deviceDataProvider' => $deviceDataProvider,
            'nodeSearchModel'    => $nodeSearchModel,
            'nodeDataProvider'   => $nodeDataProvider,
            'tasks_list'         => $this->tasks_list
        ]);
    }


    /**
     * Assign task to device
     *
     * @return string|\yii\web\Response
     */
    public function actionAssignDeviceTask()
    {

        $model = new TasksHasDevices();

        if (isset($_POST['TasksHasDevices'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                    }

                    return $this->redirect(['/network/assigntask/list']);

                }
            }
        }

        return $this->render('_assign_device_form', [
            'model'        => $model,
            'devices_list' => $this->devices_list,
            'tasks_list'   => $this->tasks_list,
        ]);

    }


    /**
     * Edit device task assignment
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditDeviceTask($id)
    {

        $model = $this->findDeviceTaskModel($id);

        if (isset($_POST['TasksHasDevices'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0} - {1}</b> edited successfully.', [$model->device->vendor, $model->device->model]));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0} - {1}</b>.', [$model->device->vendor, $model->device->model]));
                    }

                    return $this->redirect(['/network/assigntask/list']);

                }
            }
        }

        return $this->render('_assign_device_form', [
            'model'        => $model,
            'devices_list' => $this->devices_list,
            'tasks_list'   => $this->tasks_list,
        ]);

    }


    /**
     * Delete device assignment via POST
     *
     * @param  int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteDeviceTask($id)
    {

        $model = $this->findDeviceTaskModel($id);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('app', 'Record <b>{0} - {1}</b> has been successfully deleted.', [$model->device->vendor, $model->device->model]);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0} - {1}</b>.', [$model->device->vendor, $model->device->model]);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/network/assigntask/list']);

    }


    /**
     * Delete device assignment via Ajax
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDeleteDeviceTask($id)
    {

        $model = $this->findDeviceTaskModel($id);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('app', 'Record <b>{0} - {1}</b> has been successfully deleted.', [$model->device->vendor, $model->device->model])
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('app', 'An error occurred while deleting record <b>{0} - {1}</b>.', [$model->device->vendor, $model->device->model])
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Assign task to node
     *
     * @param null $id
     * @return string|\yii\web\Response
     */
    public function actionAssignNodeTask($id = null)
    {

        $model = new TasksHasNodes();

        /** Set selected node if node id were passed */
        if (!is_null($id)) {
            $model->selected_node = $model::getNodeById($id);
        }

        if (isset($_POST['TasksHasNodes'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                    }

                    return $this->redirect(['/network/assigntask/list']);

                }
            }
        }

        return $this->render('_assign_node_form', [
            'model'        => $model,
            'tasks_list'   => $this->tasks_list,
        ]);

    }


    /**
     * Edit node task assignment
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditNodeTask($id)
    {

        $model = $this->findNodeTaskModel($id);


        if (isset($_POST['TasksHasNodes'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0} - {1}</b> edited successfully.', [$model->node->hostname, $model->node->ip]));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0} - {1}</b>.', [$model->node->hostname, $model->node->ip]));
                    }

                    return $this->redirect(['/network/assigntask/list']);

                }
            }
        }

        return $this->render('_assign_node_form', [
            'model'        => $model,
            'tasks_list'   => $this->tasks_list,
        ]);

    }


    /**
     * Delete node assignment via POST
     *
     * @param  int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteNodeTask($id)
    {

        $model = $this->findNodeTaskModel($id);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('app', 'Record <b>{0} - {1}</b> has been successfully deleted.', [$model->node->hostname, $model->node->ip]);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0} - {1}</b>.', [$model->node->hostname, $model->node->ip]);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/network/assigntask/list']);

    }


    /**
     * Delete node assignment via Ajax
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDeleteNodeTask($id)
    {

        $model = $this->findNodeTaskModel($id);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('app', 'Record <b>{0} - {1}</b> has been successfully deleted.', [$model->node->hostname, $model->node->ip])
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('app', 'An error occurred while deleting record <b>{0} - {1}</b>.', [$model->node->hostname, $model->node->ip])
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Advanced node assignment view
     *
     * @param  string $task_name
     * @return string
     */
    public function actionAdvNodeAssign($task_name)
    {

        $searchModel  = new CustomNodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $searchModel->task_name = $task_name;

        return $this->render('adv_node_assign', [
            'searchModel'   => $searchModel,
            'dataProvider'  => $dataProvider,
            'data'          => $dataProvider->getModels(),
            'networks_list' => Network::find()->select('network')->indexBy('id')->asArray()->column(),
            'devices_list'  => $this->devices_list,
            'tasks_list'    => $this->tasks_list
        ]);

    }


    /**
     * Advanced device assignment view
     *
     * @return string
     */
    public function actionAdvDeviceAssign()
    {

        $searchModel  = new CustomDeviceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('adv_device_assign',[
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'data'         => ArrayHelper::index($dataProvider->getModels(), null, 'vendor'),
            'tasks_list'   => $this->tasks_list,
            'vendors_list' => Vendor::find()->select('name')->indexBy('name')->asArray()->column()
        ]);

    }


    /**
     * Assign nodes to task via Ajax
     *
     * @return string
     */
    public function actionAjaxAssignNodes()
    {

        $msg_status = 'error';
        $msg_text   = Yii::t('app', 'An error occurred while processing your request');

        $save_status   = [];
        $delete_status = [];

        if (Yii::$app->request->isAjax && isset($_POST['NodeTasks'])) {

            $_post = $_POST['NodeTasks'];

            foreach ($_post as $node_id => $data) {

                /** Find record in TasksHasNodes */
                $record = TasksHasNodes::find()->where(['node_id' => $node_id, 'task_name' => $data['task_name']]);

                /** Add new record if it doesn't exists and set_node is set 1 */
                if (!$record->exists() && $data['set_node'] == '1') {
                    $model            = new TasksHasNodes();
                    $model->node_id   = $node_id;
                    $model->task_name = $data['task_name'];
                    $save_status[]    = ($model->save()) ? true : false;
                }
                else {
                    $save_status[] = true;
                }

                /** Remove record if it exists and set node is set to 0 */
                if ($record->exists() && $data['set_node'] == '0') {
                    try {
                        $record->one()->delete();
                        $delete_status[] = true;
                    }
                    /** @noinspection PhpUndefinedClassInspection */
                    catch (\Throwable $e) {
                        $delete_status[] = false;
                    }
                }
                else {
                    $delete_status[] = true;
                }

            }

            /** Check if all save and remove requests return true if at least one return false show error */
            if ((!empty($save_status) && in_array(false, $save_status, true) === false) &&
                (!empty($delete_status) && in_array(false, $delete_status, true) === false)) {
                $msg_status = 'success';
                $msg_text   = Yii::t('app', 'Action successfully finished');
            }

        }

        return Json::encode(['status' => $msg_status, 'msg' => $msg_text]);

    }


    /**
     * Assign devices to task via Ajax
     *
     * @return string
     */
    public function actionAjaxAssignDevices()
    {

        $msg_status = 'error';
        $msg_text   = Yii::t('app', 'An error occurred while processing your request');

        $save_status   = [];
        $delete_status = [];
        $errors        = [];

        if (Yii::$app->request->isAjax && isset($_POST['DeviceTasks'])) {

            $_post = $_POST['DeviceTasks'];

            foreach ($_post as $device_id => $data) {

                /** Find record in TasksHasDevices */
                $record = TasksHasDevices::find()->where([
                    'device_id' => $device_id, 'task_name' => $data['task_name'], 'worker_id' => $data['worker_id']
                ]);

                /** Add new record if it doesn't exists and set_node is set 1 */
                if (!$record->exists() && $data['set_device'] == '1') {
                    $model            = new TasksHasDevices();
                    $model->device_id = $device_id;
                    $model->task_name = $data['task_name'];
                    $model->worker_id = $data['worker_id'];
                    if ($model->validate()) {
                        $save_status[] = ($model->save()) ? true : false;
                    }
                    else {
                        $errors = $model->errors;
                    }
                }
                else {
                    $save_status[] = true;
                }

                /** Remove record if it exists and set node is set to 0 */
                if ($record->exists() && $data['set_device'] == '0') {
                    try {
                        $record->one()->delete();
                        $delete_status[] = true;
                    }
                    /** @noinspection PhpUndefinedClassInspection */
                    catch (\Throwable $e) {
                        $delete_status[] = false;
                    }
                }
                else {
                    $delete_status[] = true;
                }

            }

            /** Check if all save and remove requests return true if at least one return false show error */
            if ((!empty($save_status) && in_array(false, $save_status, true) === false) &&
                (!empty($delete_status) && in_array(false, $delete_status, true) === false)) {
                $msg_status = 'success';
                $msg_text   = Yii::t('app', 'Action successfully finished');
            }

            /** Show validation errors */
            if (!empty($errors)) {
                $msg_status = 'warning';
                $msg_text  .= Yii::t('network', '<br>But some errors occurred:<br>');
                foreach ($errors as $message) {
                    $msg_text .= "&#9675; {$message[0]}<br>";
                }
            }

        }

        return Json::encode(['status' => $msg_status, 'msg' => $msg_text]);

    }


    /**
     * Get task workers for dependent select2 via Ajax
     *
     * @return string
     */
    public function actionAjaxGetTaskWorkers()
    {

        $result = ['output'   => '', 'selected' => ''];

        if (isset($_POST['depdrop_all_params'])) {
            $_post     = $_POST['depdrop_all_params'];
            $task_name = $_post['task_name'];

            if (!empty($task_name)) {
                $workers = Worker::find()->select('id, name')->where(['task_name' => $task_name])->asArray()->all();
                $result  = (isset($_post['worker_id'])) ? ['output' => $workers, 'selected' => $_post['worker_id']] : ['output' => $workers];
            }

        }

        return Json::encode($result);

    }


    /**
     * Get nodes via Ajax
     */
    public function actionAjaxGetNodes()
    {
        $result = '';

        if (isset($_GET['value'])) {
            $value  = $_GET['value'];
            $result = TasksHasNodes::searchNode($value);
        }

        return Json::encode($result);

    }


    /**
     * Get workers for select2 via Ajax
     *
     * @param  string $task_name
     * @return string
     */
    public function actionAjaxUpdateWorkers($task_name)
    {
        $workers = Worker::find()->select('id, name as text')->where(['task_name' => $task_name])->asArray()->all();
        return Json::encode($workers);
    }


    /**
     * Finds the TasksHasDevices model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return TasksHasDevices the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findDeviceTaskModel($id)
    {
        if (($model = TasksHasDevices::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    /**
     * Finds the TasksHasNodes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return TasksHasNodes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findNodeTaskModel($id)
    {
        if (($model = TasksHasNodes::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
