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

namespace app\controllers;

use yii\filters\AjaxFilter;
use app\models\Config;
use app\models\Exclusion;
use app\models\NodeAltInterfaceActions;
use app\models\Plugin;
use dautkom\ipv4\IPv4;
use dautkom\netsnmp\NetSNMP;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Sort;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\models\search\NodeSearch;
use app\models\Node;
use app\models\Credential;
use app\models\Device;
use app\models\Network;
use app\models\OutBackup;
use app\models\Task;
use app\models\DeviceAuthTemplate;
use yii\web\Response;
use app\components\NetSsh;
use Diff;
use Diff_Renderer_Html_Inline;


/**
 * @package app\controllers
 */
class NodeController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'                    => ['post'],
                    'inquire'                   => ['post'],
                    'ajax-run-interface-action' => ['post'],
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'inquire',
                    'ajax-download',
                    'ajax-load-config',
                    'ajax-load-file-diff',
                    'ajax-set-auth-template',
                    'ajax-load-widget',
                    'ajax-set-prepend-location',
                    'ajax-run-interface-action',
                    'ajax-backup-node',
                    'ajax-set-node-credentials',
                    'ajax-protect-node',
                ]
            ]
        ];
    }

    /**
     * @return string
     */
    public function actionList()
    {

        $searchModel  = new NodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        /** List of devices */
        $devices = ArrayHelper::map(Device::find()->all(), 'id', function ($data) { /** @var $data Device */
            return "{$data->vendor} {$data->model}";
        }, 'vendor');

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'networks'     => Network::find()->select('network')->indexBy('id')->asArray()->column(),
            'credentials'  => Credential::find()->select('name')->indexBy('id')->asArray()->column(),
            'auth_list'    => DeviceAuthTemplate::find()->select('name')->indexBy('name')->asArray()->column(),
            'devices'      => $devices
        ]);

    }


    /**
     * Render orphans list
     *
     * @return string
     */
    public function actionOrphans()
    {
        return $this->render('orphans', [
            'dataProvider' => Node::getOrphans()
        ]);
    }


    /**
     * Add new node
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new Node();

        if (isset($_POST['Node'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    $model->manual = 1;

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                    }

                    return $this->redirect(['/node/view', 'id' => $model->id]);

                }
            }
        }

        return $this->render('_form', [
            'model'       => $model,
            'networks'    => Network::find()->select('network')->indexBy('id')->asArray()->column(),
            'credentials' => Credential::find()->select('name')->indexBy('id')->asArray()->column(),
            'devices'     => ArrayHelper::map(Device::find()->all(), 'id', 'model', 'vendor')
        ]);
    }


    /**
     * Edit node
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {

        $model = $this->findModel($id);

        /** Prevent user from changing automatically added nodes */
        if ($model->manual == 0) {
            \Y::flashAndRedirect('warning', Yii::t('node', 'Edit automatically added nodes is forbidden!'), '/node/list');
        }

        if (isset($_POST['Node'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->ip));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->ip));
                    }

                    return $this->redirect(['/node/view', 'id' => $model->id]);

                }
            }
        }

        return $this->render('_form', [
            'model'       => $model,
            'networks'    => Network::find()->select('network')->indexBy('id')->asArray()->column(),
            'credentials' => Credential::find()->select('name')->indexBy('id')->asArray()->column(),
            'devices'     => ArrayHelper::map(Device::find()->all(), 'id', 'model', 'vendor')
        ]);

    }


    /**
     * @param  int $id
     * @return string
     */
    public function actionView($id)
    {

        $id   = intval($id);
        $data = Node::findOne(['id' => $id]);
        $cid  = Node::getCredentialsId($id);
        $ex   = Exclusion::exists($data->ip);

        /** Create alternative interfaces dataprovider */
        $interfaces   = ArrayHelper::toArray($data->altInterfaces);
        $int_provider = new ArrayDataProvider([
            'allModels' => $interfaces,
            'sort'  => new Sort(['attributes' => ['ip'], 'defaultOrder' => ['ip' => SORT_ASC]]),
            'pagination' => [
                'pageSize' => 9,
            ],
        ]);

        /** Create networks array for dropdownlist */
        $networks = Network::find()->select(['id', 'network', 'description'])->asArray()->all();
        $networks = ArrayHelper::map($networks, 'id', function ($data) {
            $description = (!empty($data['description'])) ? "- {$data['description']}" : "";
            return "{$data['network']} {$description}";
        });

        return $this->render('view', [
            'data'         => $data,
            'exclusion'    => $ex,
            'credential'   => Credential::findOne(['id' => $cid]),
            'task_info'    => Task::findOne('backup'),
            'commit_log'   => (Config::isGitRepo()) ? Node::getBackupCommitLog($id) : null,
            'int_provider' => $int_provider,
            'templates'    => DeviceAuthTemplate::find()->select('name')->indexBy('name')->asArray()->column(),
            'networks'     => $networks,
            'plugins'      => Plugin::find()->where(['enabled' => '1', 'widget' => 'node'])->all(),
            'credentials'  => Credential::find()->select('name')->indexBy('id')->asArray()->column(),
        ]);
    }


    /**
     * Set node auth template via Ajax
     *
     * @param  int $node_id
     * @param  string $name
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxSetAuthTemplate($node_id, $name)
    {

        $model = $this->findModel($node_id);
        $node  = (!is_null($model->hostname)) ? $model->hostname : $model->ip;
        $model->auth_template_name = $name;

        if ($model->validate(['auth_template_name']) && $model->save(false)) {
            $response = ['status' => 'success', 'msg' => Yii::t('app', 'Record <b>{0}</b> edited successfully.', $node)];
        }
        else {
            $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $node)];
        }

        return Json::encode($response);

    }


    /** @noinspection PhpUndefinedClassInspection
     *  @param  int $id
     *  @return \yii\web\Response
     *  @throws NotFoundHttpException
     *  @throws \Throwable
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('node', 'Node <b>{0}</b> was successfully deleted.', $model->ip);
        }
        catch (\Exception $e) {
            $class   = 'danger';
            $message = Yii::t('node', 'An error occurred while deleting node <b>{0}</b>.', $model->ip);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/node/list']);

    }


    /**
     * Load config via Ajax
     *
     * @return bool|mixed|string
     */
    public function actionAjaxLoadConfig()
    {

        $response = Yii::t('node', 'File not found');

        if (isset($_POST)) {

            $_post = Yii::$app->request->post();

            /** Load config from DB */
            if ($_post['put'] == 'db') {
                $db_backup = OutBackup::find()->select('config')->where(['node_id' => $_post['node_id']]);
                if ($db_backup->exists()) {
                    $config   = $db_backup->column();
                    $response = array_shift($config);
                }
            }

            /** Load config from file */
            if ($_post['put'] == 'file') {
                $path_to_file = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . "{$_post['node_id']}.txt";
                if (file_exists($path_to_file)) {
                    $response = file_get_contents($path_to_file);
                }
            }
        }

        return Html::tag('pre', Html::encode($response));

    }


    /**
     * Load file diff via Ajax
     *
     * @return array|string
     */
    public function actionAjaxLoadFileDiff()
    {

        $response = Yii::t('app', 'An error occurred while processing your request');

        if (isset($_POST)) {

            $_post        = Yii::$app->request->post();
            $path_to_file = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . "{$_post['node_id']}.txt";
            $backup       = null;
            $response     = [
                'meta' => [],
                'diff' => ''
            ];

            /** Get curent config file */
            if (file_exists($path_to_file)) {
                $content = file_get_contents($path_to_file);
                $backup  = ($content !== false) ? $content : null;
            }

            $response['meta'] = Node::getCommitMetaData($_post['hash']);

            if (!is_null($backup)) {
                $git_file_ver     = explode("\n", Node::getBackupGitVersion($_post['node_id'], $_post['hash']));
                $cur_backup_ver   = explode("\n", $backup);
                $diff             = new Diff($git_file_ver, $cur_backup_ver);
                $renderer         = new Diff_Renderer_Html_Inline;
                $response['diff'] = str_replace(
                    ['<th>Old</th>', '<th>New</th>', '<th>Differences</th>'],
                    ['<th colspan="3">' . Yii::t('app', 'File: {0}.txt', $_post['node_id']) . '</th>'],
                    $diff->Render($renderer)
                );
            }

            return $this->renderPartial('diff', [
                'response' => $response
            ]);

        }

        return $response;

    }


    /**
     * @param  $id
     * @param  string $put
     * @param  string|null $hash
     * @param  bool $crlf
     * @return Response
     * @throws \yii\web\RangeNotSatisfiableHttpException
     * @throws \yii\base\ExitException
     */
    public function actionDownload($id, $put, $hash = null, $crlf = false)
    {

        $config = '';
        $suffix = null;

        /** Get configuration backup based on put */
        if(!empty($hash)) {

            $meta   = Node::getCommitMetaData($hash);
            $config = Node::getBackupGitVersion($id, $hash);

            if( array_key_exists(3, $meta) ) {
                $suffix = preg_replace(['/:/', '/[^\d|\-]/'], ['-', '_'], $meta[3]);
                $suffix = ".".substr($suffix, 0, -7);
            }

        }
        elseif ($put == 'file') {
            $file_path = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . "{$id}.txt";
            $config    = file_get_contents($file_path);
        }
        elseif ($put == 'db') {
            $config = OutBackup::find()->select('config')->where(['node_id' => $id])->scalar();
        }
        else {
            \Y::flashAndRedirect('warning', Yii::t('node', 'Unknown backup destination passed'), 'node/view', ['id' => $id]);
            Yii::$app->end();
        }

        if( isset($crlf) && $crlf == true ) {
            $config = preg_replace('~\R~u', "\r\n", $config);
        }

        return Yii::$app->response->sendContentAsFile($config, "$id.conf{$suffix}.txt", [
            'mimeType' => 'text/plain',
            'inline'   => false,
        ]);

    }


    /**
     * @param  int         $id
     * @param  string      $put
     * @param  string|null $hash
     * @return string
     */
    public function actionAjaxDownload($id, $put, $hash = null)
    {
        return $this->renderPartial('_download_modal', [
            'id'   => $id,
            'put'  => $put,
            'hash' => $hash
        ]);
    }


    /**
     * @throws HttpException
     * @return string
     */
    public function actionInquire()
    {

        $ipaddr        = trim(Yii::$app->request->post('ip'));
        $credential_id = intval(Yii::$app->request->post('cid'));
        $network_id    = intval(Yii::$app->request->post('nid'));

        if( !filter_var($ipaddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
            throw new HttpException(400, Yii::t('network', 'Invalid IP-address'));
        }

        if( !empty($network_id) ) {

            $net           = new IPv4();
            $network       = Network::find()->where(['id' => $network_id])->asArray()->one();
            $credential_id = empty($credential_id) ? $network['credential_id'] : $credential_id;

            if( !$net->subnet($network['network'])->has($ipaddr) ) {
                throw new HttpException(400, Yii::t('network', "IP-address doesn't belong to chosen subnet"));
            }

        }

        $credentials = Credential::find()->where(['id' => $credential_id])->asArray()->one();

        if( empty($credentials) ) {
            throw new HttpException(400, Yii::t('network', 'Unable to find credential data'));
        }

        try {

            $snmp = (new NetSNMP)->init($ipaddr, [$credentials['snmp_read'], $credentials['snmp_set']], $credentials['snmp_version']);
            $mac  = @$snmp->get('1.3.6.1.2.1.2.2.1.6.1');

            if(!empty($mac)) {
                $mac = explode(':', $mac);
                $mac = array_map(function($octet){ return str_pad($octet, 2, '0', STR_PAD_LEFT); }, $mac);
                $mac = join(':', $mac);
            }
            else {
                $mac = '';
            }

            $data = [
                'name'     => $snmp->get('1.3.6.1.2.1.1.5.0'),
                'contact'  => $snmp->get('1.3.6.1.2.1.1.4.0'),
                'descr'    => $snmp->get('1.3.6.1.2.1.1.1.0'),
                'location' => $snmp->get('1.3.6.1.2.1.1.6.0'),
                'mac'      => $mac,
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            throw new HttpException(504, $e->getMessage());
        }

        return json_encode($data);

    }


    /**
     * Run alt interface action via Ajax
     *
     * @return string
     */
    public function actionAjaxRunInterfaceAction()
    {

        $model = new NodeAltInterfaceActions();
        $model->setAttributes($_POST);

        try {

            if( !$model->validate() ) {
                throw new \Exception('Invalid params');
            }

            $result = $model->run();

            if ($result) {
                \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->node_id));
                $response = ['status' => 'success', 'msg' => ''];
            }
            elseif (!$result && $model->action_type == 'setPrimary') {
                $response = [
                    'status'     => 'error',
                    'error_type' => 'wrong_subnet',
                    'msg'        => Yii::t('network', 'IP-address {0} does not belong to chosen subnet. Please choose new subnet from list.', $model->alt_ip)
                ];
            }
            else {
                $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while editing record')];
            }

        } catch (\Exception $e) {
            $response = ['status' => 'error', 'msg' => $e->getMessage()];
        }

        return Json::encode($response);

    }


    /**
     * Load plugin widget in node via Ajax
     *
     * @param  int $node_id
     * @param  string $plugin
     *
     * @return string
     */
    public function actionAjaxLoadWidget($node_id, $plugin)
    {
        try {

            /** Init plugin widget */
            $class  = 'app\\modules\\plugins\\' . strtolower(Inflector::camelize($plugin)) . '\\widgets\\' . Inflector::camelize($plugin) . 'Widget';
            $object = (new \ReflectionClass($class))->newInstance();

            $response = [
                'status' => 'success',
                'data'   => $object::widget(['node_id' => $node_id])
            ];

        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'data'   => $e->getMessage()
            ];
        }

        return Json::encode($response);
    }


    /**
     * Set node prepend location via Ajax
     *
     * @param  int $node_id
     * @param  string $prepend_location
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxSetPrependLocation($node_id, $prepend_location)
    {

        $model = $this->findModel($node_id);
        $model->prepend_location = $prepend_location;

        if ($model->validate(['prepend_location']) && $model->save(false)) {
            $response = ['status' => 'success', 'msg' => Yii::t('app', 'Action successfully finished')];
        }
        else {
            $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while processing your request')];
        }

        return Json::encode($response);

    }


    /**
     * Set node credentials via Ajax
     *
     * @param  int $node_id
     * @param  int $credential_id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxSetNodeCredentials($node_id, $credential_id)
    {

        $model = $this->findModel($node_id);
        $model->credential_id = $credential_id;

        if ($model->validate('credential_id')) {

            if ($model->save()) {
                \Y::flash('success', Yii::t('node', 'Node credentials have been successfully changed'));
            } else {
                \Y::flash('danger', Yii::t('node', 'An error occurred while changing node credentials'));
            }

            $response = ['status' => 'success', 'msg' => ''];

        } else {
            $response = ['status' => 'error', 'msg' => $model->errors['credential_id']];
        }

        return Json::encode($response);

    }


    /**
     * Run node backup via Ajax
     *
     * @param  int $node_id
     * @param  int $device_id
     * @return string
     */
    public function actionAjaxBackupNode($node_id, $device_id)
    {
        try {

            /** Check if task "backup" is assigned */
            Node::checkNodeAssignment($node_id, $device_id);

            $command  = (new NetSsh())->init()->schedulerExec("cbackup backup {$node_id} -json");
            $response = ['status' => 'success', 'msg' => Yii::t('network', 'Node backup successfully started in background. This may take a while.')];

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
     * Set node protected flag via Ajax
     *
     * @param  int $node_id
     * @param  int $protect_status
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxProtectNode($node_id, $protect_status)
    {
        $model = $this->findModel($node_id);
        $model->protected = intval($protect_status);

        if ($model->validate(['protected']) && $model->save(false)) {
            $response = ['status' => 'success', 'msg' => Yii::t('app', 'Action successfully finished')];
        }
        else {
            $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while processing your request')];
        }

        return Json::encode($response);
    }


    /**
     * Finds the Node model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  int $id
     * @return Node the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Node::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
