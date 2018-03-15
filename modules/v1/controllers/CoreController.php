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

namespace app\modules\v1\controllers;

use app\modules\v1\components\OutputProcessing;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\rest\Controller;
use yii\web\Response;
use app\helpers\ApiHelper;
use app\models\LogScheduler;
use app\models\LogSystem;
use app\models\TasksHasNodes;
use app\models\TasksHasDevices;
use app\models\Worker;
use app\models\Network;
use app\models\Exclusion;
use app\models\Node;
use app\models\Credential;
use app\models\Config;
use app\models\Job;
use app\models\OutStp;
use app\models\OutBackup;
use app\models\DeviceAttributes;
use app\models\DeviceAttributesUnknown;
use app\models\OutCustom;
use app\models\Schedule;
use app\models\ScheduleMail;
use app\models\AltInterface;
use app\models\LogNode;
use app\models\Messages;
use app\models\JobGlobalVariable;
use app\models\Task;
use yii\filters\ContentNegotiator;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use GitWrapper\GitWrapper;
use app\mailer\CustomMailer;
use toriphes\console\Runner;


/**
 * @package app\modules\v1\controllers
 */
class CoreController extends Controller
{

    /**
     * @return array
     */
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        /*
         * To return JSON format
         */
        $behaviors['contentNegotiator'] = [
            'class'   => ContentNegotiator::class,
            'formats' => [
                'application/json'  => Response::FORMAT_JSON,
                'charset'           => 'UTF-8',
            ],
        ];

        /*
         * Authentication
         */
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        /*
         * Access rules
         */
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'   => true,
                    'roles'   => ['APICore'],
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'set-schedule-log'          => ['post'],
                'set-system-log'            => ['post'],
                'set-worker-result'         => ['post'],
                'set-discovery-result'      => ['post'],
                'get-exclusions'            => ['get'],
                'get-variables'             => ['get'],
                'get-worker-by-node-id'     => ['get'],
                'get-nodes-workers-by-task' => ['get'],
                'get-node-credentials'      => ['get'],
                'get-config'                => ['get'],
                'get-jobs'                  => ['get'],
                'get-tasks'                 => ['get'],
                'git-commit'                => ['get'],
                'log-processing'            => ['get'],
                'node-processing'           => ['get'],
                'run-console-command'       => ['get'],
                'get-networks'              => ['get'],
                'get-mailer-events'         => ['get'],
                'send-mail'                 => ['get'],
            ],
        ];

        return $behaviors;

    }


    /**
     * Discovery node
     * If device does not exist, create device_attributes_unknown
     * Else create/update node and nodes alt interfaces
     *
     * POST example:
     * [
     * 'network_id'      => node network id
     * 'ip'              => node ip
     * 'sysobject_id'    => ..
     * 'hw'              => ..
     * 'sys_description' => ..
     * 'hostname'        => ..
     * 'location'        => ..
     * 'contact'         => ..
     * 'mac'             => ..
     * 'serial'          => ..
     * 'ip_interfaces' => json ['172.166.1.1, 192.168.1.1, ...']
     * ]
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function actionSetDiscoveryResult(): array
    {
        $data = Yii::$app->request->getBodyParams();

        /*
         * Empty POST data or empty all model identifiers
         */
        if(empty($data) || (is_null($data['sysobject_id']) && is_null($data['hw']) && is_null($data['sys_description']))) {
            Yii::$app->response->statusCode = 422;
            return ApiHelper::getResponseBodyByCode(422);
        }

        /*
         * Required data validation
         */
        if(empty($data['ip']) || empty($data['network_id'])) {
            Yii::$app->response->statusCode = 422;
            return ApiHelper::getResponseBodyByCode(422);
        }

        /*
         * Get all device interfaces
         */
        $ips = [];
        if(!empty($data['ip_interfaces'])) {
            $ips = json_decode($data['ip_interfaces']);
        }

        /*
         * Removing device main interface from interface list
         */
        $positionIp = array_search($data['ip'], $ips);
        if($positionIp !== false) {
            unset($ips[$positionIp]);
            $ips = array_values($ips);
        }

        /*
         * Converting empty values to nulls
         */
        foreach ($data as &$currentData) {
            if(mb_strlen($currentData) == 0) {
                $currentData = null;
            }
            else {
                $currentData = trim(str_replace("\r\n", '', $currentData));
            }
        }

        /*
         * Get device model id
         */
        $deviceId = DeviceAttributes::find()
            ->select(['device_id'])
            ->where([
                'sysobject_id'    => $data['sysobject_id'],
                'hw'              => $data['hw'],
                'sys_description' => $data['sys_description']
            ])->scalar()
        ;

        try {

            /** Add new device attributes */
            if (empty($deviceId)) {
                $attributes = [
                    'ip'              => $data['ip'],
                    'sysobject_id'    => $data['sysobject_id'],
                    'hw'              => $data['hw'],
                    'sys_description' => $data['sys_description']
                ];

                $success = DeviceAttributesUnknown::addNewAttributes($attributes);

                if (!$success) {
                    $error = "\nAn error occurred while adding new device attributes.\nIP: {$data['ip']}\nHostname: {$data['hostname']}";
                    throw new \Exception($error);
                }

            }
            /** Create-update node */
            else {

                $data['device_id'] = $deviceId;
                $success           = Node::createOrUpdateNode($data);

                if ($success) {
                    $success = AltInterface::updateInterfaces($data['ip'], $ips);
                }

            }

            if ($success) {
                Yii::$app->response->statusCode = 201;
                return [];
            } else {
                Yii::$app->response->statusCode = 500;
                return ApiHelper::getResponseBodyByCode(500);
            }

        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 500;
            return ApiHelper::getResponseBodyByCode(500, $e->getMessage());
        }

    }


    /**
     * Getting all IP exclusions
     *
     * @return array
     */
    public function actionGetExclusions()
    {
        return Exclusion::find()
            ->select('ip')
            ->column();
    }


    /**
     * Getting all networks with credentials
     * Return example:
     * [ '192.168.1.0/24' => [
     *  'id'           => .. //network id
     *  'snmp_read'    => ..
     *  'snmp_set'     => ..
     *  'snmp_version' => ..
     *  'port_snmp'    => ..
     * ]]
     *
     * @return array
     */
    public function actionGetNetworks()
    {
        $toReturn = [];

        $subnets = Network::find()
            ->select(['network.id', 'network', 'credential_id', 'snmp_read', 'snmp_set', 'snmp_version', 'port_snmp'])
            ->where(['discoverable' => 1])
            ->joinWith('credential')
            ->asArray()
            ->all();

        foreach($subnets as $network) {
            $toReturn[$network['network']] = [
                'id'           => $network['id'],
                'snmp_read'    => $network['snmp_read'],
                'snmp_set'     => $network['snmp_set'],
                'snmp_version' => $network['snmp_version'],
                'port_snmp'    => $network['port_snmp']];
        }

        return $toReturn;
    }


    /**
     * Getting task list
     * Return example:
     * [[
     * "scheduleId"   => ..,
     * "taskName"     => ..,
     * "scheduleCron" => ..,
     * "put"          => ..,
     * "taskType"     => ..,
     * "table"        => ..,
     * ],..]
     *
     * @return array
     */
    public function actionGetTasks()
    {
        return Schedule::getScheduleTasks();
    }


    /**
     * Get specific task
     *
     * Return example:
     * [[
     * "scheduleId"   => null,
     * "taskName"     => ..,
     * "taskType"     => ..,
     * "put"          => ..,
     * "table"        => ..,
     * ]]
     *
     * @param  string $task_name
     * @return array
     */
    public function actionGetTask($task_name)
    {
        $task = Task::find()->select(['name as taskName', 'task_type as taskType', 'put', 'table'])->where(['name' => $task_name])->asArray()->one();
        return (!empty($task)) ? [array_merge(['scheduleId' => null], $task)] : [];
    }


    /**
     * Getting variable list
     *
     * @return array
     */
    public function actionGetVariables()
    {
        return JobGlobalVariable::find()
            ->select('var_value')
            ->indexBy('var_name')
            ->asArray()
            ->column();
    }


    /**
     * Get mailer events list
     *
     * Return example:
     * [[
     * "scheduleId"   => ..,
     * "eventName"    => ..,
     * "scheduleCron" => ..,
     * ],..]
     *
     * @return array
     */
    public function actionGetMailerEvents()
    {
        return ScheduleMail::getScheduleEvents();
    }


    /**
     * Getting node credentials
     *
     * @param string $node_id
     * @return array
     */
    public function actionGetNodeCredentials($node_id)
    {
        $credentialsId = Node::getCredentialsId($node_id);

        if(empty($credentialsId)) {
            Yii::$app->response->statusCode = 404;
            return ApiHelper::getResponseBodyByCode(404);
        }

        $credential    = Credential::find()->where(['id' => $credentialsId])->asArray()->one();

        if(empty($credential)) {
            Yii::$app->response->statusCode = 404;
            return ApiHelper::getResponseBodyByCode(404);
        }

        $authSequence = Node::getAuthSequence($node_id);

        if(empty($authSequence)) {
            Yii::$app->response->statusCode = 404;
            return ApiHelper::getResponseBodyByCode(404);
        }

        $credential['auth_sequence'] = $authSequence;

        return $credential;
    }


    /**
     * Getting job list by worker id
     *
     * @param string $worker_id
     * @return array
     */
    public function actionGetJobs($worker_id): array
    {
        $jobs = Job::getJobsByWorker($worker_id);

        if(empty($jobs)) {
            Yii::$app->response->statusCode = 404;
            return ApiHelper::getResponseBodyByCode(404);
        }

        return $jobs;
    }


    /**
     * Run Yii console command
     *
     * Note: Task start message is written to log by Java Deamon
     *
     * @param  int $schedule_id
     * @param  string $task_name
     * @return bool
     */
    public function actionRunConsoleCommand($schedule_id, $task_name)
    {

        /** Set schedule id text */
        $schedule_id_text = $schedule_id;

        try {

            $model  = Schedule::find()->where(['task_name' => $task_name]);
            $exists = $model->exists();

            /** Set schedule_id_text and schedule_id if task executed manually or task does not exist */
            if (empty($schedule_id) || is_null($schedule_id) || !$exists) {
                $schedule_id      = null;
                $schedule_id_text = 'NONE';
            }

            /** Check if schedule exists */
            if (!$exists){
                throw new \Exception("Task {$task_name} does not exist in schedules table.");
            }

            /** Get entry data */
            $data = $model->one();

            $command = $data->taskName->yii_command;

            /** Check if yii command is set */
            if (is_null($command) || empty($command)) {
                throw new \Exception("Yii console command for given task is not set.");
            }

            $output = '';
            $runner = new Runner();

            /** error in phpdoc for run() method  */
            /** @noinspection PhpParamsInspection */
            $runner->run($command, $output);

            if ($output == '0') {
                $message = "Task {$task_name} finished successfully.\nSchedule id: {$schedule_id_text}\nTask name: {$task_name}";
                Yii::info([$message, $schedule_id, 'TASK FINISH'], 'scheduler.writeLog');
                return true;
            } else {
                $message = "Task {$task_name} failed.\nSchedule id: {$schedule_id_text}\nTask name: {$task_name}\nConsole response:\n{$output}";
                Yii::error([$message, $schedule_id, 'TASK FINISH'], 'scheduler.writeLog');
                return false;
            }

        } catch (\Exception $e) {
            $message  = "Task {$task_name} failed.\nSchedule id: {$schedule_id_text}\nTask name: {$task_name}\nException:\n{$e->getMessage()}";
            Yii::error([$message, $schedule_id, 'TASK FINISH'], 'scheduler.writeLog');
            return false;
        }

    }


    /**
     * Commits file changes to git
     * Depends on settings(table config):
     * - git
     * - gitRepo
     * - gitPassword
     *
     * @param  null $schedule_id
     * @return bool
     */
    public function actionGitCommit($schedule_id = null): bool
    {

        /** Process $schedule_id */
        $sched_id = (is_null($schedule_id)) ? 'NONE' : $schedule_id;

        /** Stop processing script if git is disabled in system settings */
        if (\Y::param('git') == 0) {
            $message  = "Git commit is disabled. To use git commit please enable it in 'System settings'\nSchedule id: {$sched_id}";
            Yii::warning([$message, $schedule_id, 'GIT COMMIT'], 'scheduler.writeLog');
            return false;
        }

        /** Stop processing script if git is not initialized */
        if (!Config::isGitRepo()) {
            $message  = "Backup git repository is not initialized. To use git commit please initialize it in 'System settings'\nSchedule id: {$sched_id}";
            Yii::warning([$message, $schedule_id, 'GIT COMMIT'], 'scheduler.writeLog');
            return false;
        }

        /** Execute git commands */
        try {

            $action      = 'INIT';
            $wrapper     = new GitWrapper(\Y::param('gitPath'));
            $backup_path = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup';
            $git         = $wrapper->workingCopy($backup_path);

            /** Perform git action if configuration backup was changed */
            if ($git->hasChanges()) {

                $action = 'COMMIT';
                $git->add($backup_path);
                $git->commit('Configuration backup changes ' . date('Y-m-d H:i:s'));

                $git->clearOutput();
                $git->show('--shortstat', ['pretty' => 'format:[%H] %s']);
                $git->show('--name-status', ['pretty' => 'format:']);

                $message = "All changed files successfully committed\nSchedule id: {$sched_id}\nSummary:\n {$git->getOutput()}";
                Yii::info([$message, $schedule_id, 'GIT COMMIT'], 'scheduler.writeLog');

                /**
                 * Push all changes to remote repo. Push will be made with --force flag.
                 * All data on remote repo will be lost!
                 */
                if (\Y::param('gitRemote')) {
                    $action = 'PUSH';
                    $git->push(['force' => true]);
                    $message = "All commits successfully pushed to remote repository\nSchedule id: {$sched_id}";
                    Yii::info([$message, $schedule_id, 'GIT PUSH'], 'scheduler.writeLog');
                }


            } else {
                $message = "Configuration backups was not changed. Commit is not necessary\nSchedule id: {$sched_id}";
                Yii::info([$message, $schedule_id, 'GIT COMMIT'], 'scheduler.writeLog');
            }

            return true;

        } catch (\Exception $e) {

            $exception = preg_replace(
                '/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i',
                'Git url removed for privacy reasons',
                $e->getMessage()
            );

            $message = "An error occurred while executing git " . strtolower($action) . "\nSchedule id: {$sched_id}\nException:\n{$exception}";
            Yii::error([$message, $schedule_id, "GIT {$action}"], 'scheduler.writeLog');

            return false;

        }

    }


    /**
     * Log clearing
     * Tables: log_node, log_scheduler, log_system, messages
     * Depends on settings(table config):
     * - logLifetime
     *
     * @param  null $schedule_id
     * @return bool
     */
    public function actionLogProcessing($schedule_id = null): bool
    {

        /** Process $schedule_id */
        $sched_id = (is_null($schedule_id)) ? 'NONE' : $schedule_id;

        /** Stop processing script if logLifetime is set to 0 in system settings */
        if (\Y::param('logLifetime') == 0) {
            $message = "Logs were not cleared because lifetime parameter is set to 0. ";
            $message.= "Set lifetime parameter in 'System settings' to enable log clearing.\nSchedule id: {$sched_id}";
            Yii::warning([$message, $schedule_id, 'LOG PROCESSING'], 'scheduler.writeLog');
            return false;
        }

        $expression     = new Expression('NOW() - INTERVAL :days DAY', [':days' => \Y::param('logLifetime')]);
        $node_logs      = LogNode::find()->where(['<', 'time', $expression])->exists();
        $scheduler_logs = LogScheduler::find()->where(['<', 'time', $expression])->exists();
        $system_logs    = LogSystem::find()->where(['<', 'time', $expression])->exists();
        $messages       = Messages::find()->where(['and', ['<', 'created', $expression], ['not', ['approved' => null]]])->exists();

        try {

            $action        = '';
            $clear_message = "Logs was succesfully cleared\nSchedule id: {$sched_id}\nSummary:\n";

            /** Stop log processing if logs are already cleared */
            if (!$node_logs && !$scheduler_logs && !$system_logs && !$messages) {
                $pluralize = \Yii::t('app', '{n,plural,=1{# day} other{# days}}', ['n' => \Y::param('logLifetime')]);
                $message   = "There aren't any logs which are older than {$pluralize}. Nothing to clear\nSchedule id: {$sched_id}";
                Yii::info([$message, $schedule_id, 'LOG PROCESSING'], 'scheduler.writeLog');
                return true;
            }

            /** Clear node logs */
            if ($node_logs) {
                $action = 'node logs';
                LogNode::deleteAll(['<', 'time', $expression]);
                $clear_message .= "Node logs was successfully cleared\n";
            }

            /** Clear scheduler logs */
            if ($scheduler_logs) {
                $action = 'scheduler logs';
                LogScheduler::deleteAll(['<', 'time', $expression]);
                $clear_message .= "Scheduler logs was successfully cleared\n";
            }

            /** Clear system logs */
            if ($system_logs) {
                $action = 'system logs';
                LogSystem::deleteAll(['<', 'time', $expression]);
                $clear_message .= "System logs was successfully cleared\n";
            }

            /** Clear system messages */
            if ($messages) {
                $action = 'system messages';
                Messages::deleteAll(['and', ['<', 'created', $expression], ['not', ['approved' => null]]]);
                $clear_message .= "System messages was successfully cleared\n";
            }

            Yii::info([$clear_message, $schedule_id, 'LOG PROCESSING'], 'scheduler.writeLog');
            return true;

        } catch (\Exception $e) {
            $message = "An error occurred while clearing {$action}\nSchedule id: {$sched_id}\nException:\n{$e->getMessage()}";
            Yii::error([$message, $schedule_id, 'LOG PROCESSING'], 'scheduler.writeLog');
            return false;
        }

    }


    /**
     * Old nodes clearing
     * Depends on settings(table config):
     * - nodeLifetime
     *
     * @param  null $schedule_id
     * @return bool
     */
    public function actionNodeProcessing($schedule_id = null): bool
    {

        /** Process $schedule_id */
        $sched_id = (is_null($schedule_id)) ? 'NONE' : $schedule_id;

        /** Path to backup dir */
        $dir_path = \Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'backup';

        /** Stop processing script if nodeLifetime is set to 0 in system settings */
        if (\Y::param('nodeLifetime') == 0) {
            $message = "Nodes were not deleted because lifetime parameter is set to 0. ";
            $message.= "Set lifetime parameter in 'System settings' to enable node deleting.\nSchedule id: {$sched_id}";
            Yii::warning([$message, $schedule_id, 'NODE PROCESSING'], 'scheduler.writeLog');
            return false;
        }

        /** Find all nodes which were inactive more than specified number of days */
        $nodes = Node::find()->select(['id', 'ip', 'hostname', 'location', 'last_seen'])->where(['and',
                    ['>=', new Expression('TO_DAYS(NOW()) - TO_DAYS(`last_seen`)'), \Y::param('nodeLifetime')],
                    ['manual'    => 0],
                    ['protected' => 0]
                 ])->all();

        /** Stop processing if nodes were not found */
        if (empty($nodes)) {
            $pluralize = \Yii::t('app', '{n,plural,=1{# day} other{# days}}', ['n' => \Y::param('nodeLifetime')]);
            $message = "There aren't any nodes which are inactive more than {$pluralize}. Nothing to delete\nSchedule id: {$sched_id}";
            Yii::info([$message, $schedule_id, 'NODE PROCESSING'], 'scheduler.writeLog');
            return true;
        }

        try {

            /** Counters */
            $nodes_deleted = 0;
            $files_deleted = 0;

            /** Delete node, config files and write log */
            foreach ($nodes as $node) {

                /** Delete inactive nodes */
                $node->delete();

                /** Delete inactive node config file */
                $config_file = $dir_path . DIRECTORY_SEPARATOR . "{$node['id']}.txt";
                if (file_exists($config_file)) {
                    unlink($config_file);
                    $files_deleted++;
                }

                /** Write node delete log */
                $delete_msg = "Node {$node['ip']} - {$node['hostname']} successfully deleted\nSchedule id: {$sched_id}\nDetailed info:\n";
                $delete_msg.= "IP: {$node['ip']}\nHostname: {$node['hostname']}\nLocation: {$node['location']}\nLast seen: {$node['last_seen']}";
                Yii::info([$delete_msg, $schedule_id, 'NODE PROCESSING'], 'scheduler.writeLog');

                $nodes_deleted++;
            }

            /** Write node processing summary log */
            $delete_msg = "Node processing summary: {$nodes_deleted} nodes was successfully deleted";
            $delete_msg.= ($files_deleted > 0) ? ". {$files_deleted} files was successfully deleted" : "";
            $delete_msg.= "\nSchedule id: {$sched_id}";
            Yii::info([$delete_msg, $schedule_id, 'NODE PROCESSING'], 'scheduler.writeLog');

            return true;

        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $message = "An error occurred while deleting node\nSchedule id: {$sched_id}\nException:\n{$e->getMessage()}";
            Yii::error([$message, $schedule_id, 'NODE PROCESSING'], 'scheduler.writeLog');
            return false;
        }

    }


    /**
     * Getting app settings
     *
     * @return array
     */
    public function actionGetConfig()
    {
        return Config::find()->select('value')->indexBy('key')->asArray()->column();
    }


    /**
     * Getting worker by node id
     * Return example:
     * [node_id1=>[
     *  id          => (worker id),
     *  name        => (worker name),
     *  task_name   => (task name),
     *  get         => (ssh||snmp||telnet)
     *  description => ""
     *  ip          => (node ip)
     *  model       => (node model)
     *  vendor      => (node vendor)
     *  ]
     * ]
     *
     *
     * @param integer $node_id
     * @param string $task_name
     * @return array
     */
    public function actionGetWorkerByNodeId($node_id, $task_name)
    {
        $result = [];

        if(empty($task_name) || empty($node_id)) {
            Yii::$app->response->statusCode = 422;
            return ApiHelper::getResponseBodyByCode(422);
        }

        $workerId   = null;
        $workerInfo = null;
        $nodeInfo   = TasksHasNodes::getInfoByNodeAndTask($node_id, $task_name);

        if(empty($nodeInfo)) {
            return $result;
        }

        if(!is_null($nodeInfo['worker_id'])) {
            $workerId = $nodeInfo['worker_id'];
        }
        else {

            $workerId = TasksHasDevices::find()
                ->select('worker_id')
                ->where(['device_id' => $nodeInfo["device_id"], 'task_name' => $task_name])
                ->one();

            if(empty($workerId)){

                $logScheduler               = new LogScheduler();
                $logScheduler->severity     = 'WARNING';
                $logScheduler->schedule_id  = null;
                $logScheduler->node_id      = $node_id;
                $logScheduler->action       = 'NODE INFO GET';
                $logScheduler->message      = "Node has no worker!\nTask: $task_name.\nNode: $node_id.";
                $logScheduler->save();

                return $result;
            }
        }

        $result[$node_id]           = Worker::find()->where(['id' => $workerId])->asArray()->one();
        $result[$node_id]['ip']     = $nodeInfo['ip'];
        $result[$node_id]['model']  = $nodeInfo['model'];
        $result[$node_id]['vendor'] = $nodeInfo['vendor'];

        return $result;

    }


    /**
     * Getting all task nodes with workers
     * Return example:
     * [node_id1=>[
     *  id          => (worker id),
     *  name        => (worker name),
     *  task_name   => (task name),
     *  get         => (ssh||snmp||telnet)
     *  description => ""
     *  ip          => (node ip)
     *  model       => (node model)
     *  vendor      => (node vendor)
     * ],
     * node_id2=>...]
     *
     *
     * @param string $schedule_id
     * @param string $task_name
     * @return array
     */
    public function actionGetNodesWorkersByTask($schedule_id, $task_name)
    {
        $result = [];

        if(empty($task_name)) {
            Yii::$app->response->statusCode = 422;
            return ApiHelper::getResponseBodyByCode(422);
        }

        $nodes = TasksHasNodes::getTaskNodes($task_name);
        $nodes = array_unique($nodes);

        foreach($nodes as $node_id) {

            $workerId   = null;
            $workerInfo = null;
            $nodeInfo   = TasksHasNodes::getInfoByNodeAndTask($node_id, $task_name);

            if(empty($nodeInfo)) {
                continue;
            }

            if(!is_null($nodeInfo['worker_id'])) {
                $workerId = $nodeInfo['worker_id'];
            }
            else {

                $workerId = TasksHasDevices::find()
                    ->select('worker_id')
                    ->where(['device_id' => $nodeInfo["device_id"], 'task_name' => $task_name])
                    ->one();

                if(empty($workerId)){

                    $logScheduler               = new LogScheduler();
                    $logScheduler->severity     = 'WARNING';
                    $logScheduler->schedule_id  = $schedule_id;
                    $logScheduler->node_id      = $node_id;
                    $logScheduler->action       = 'NODE INFO GET';
                    $logScheduler->message      = "Node has no worker!\nTask: $task_name.\nNode: $node_id.";
                    $logScheduler->save();

                    continue;
                }
            }

            $result[$node_id]           = Worker::find()->where(['id' => $workerId])->asArray()->one();
            $result[$node_id]['ip']     = $nodeInfo['ip'];
            $result[$node_id]['model']  = $nodeInfo['model'];
            $result[$node_id]['vendor'] = $nodeInfo['vendor'];
        }

        return $result;
    }


    /**
     * Getting last task result hash by task and node_id
     *
     * @param string $task_name
     * @param string $node_id
     * @return string|null
     */
    public function actionGetHash($task_name, $node_id) {

         switch($task_name) {
             case 'stp' :
                 $result = OutStp::find()->select('hash')->where(['node_id' => $node_id])->scalar();
                 $result = (!$result)? null : $result;
                 break;
             case 'backup':
                 $result = OutBackup::find()->select('hash')->where(['node_id' => $node_id])->scalar();
                 $result = (!$result)? null : $result;
                 break;
             default:
                 $model  = new OutCustom(['table' => 'out_'.$task_name]);
                 $result = $model->find()->select('hash')->where(['node_id' => $node_id])->scalar();
                 $result = (!$result)? null : $result;
         }

        return $result;
    }


    /**
     * Setting worker result for custom and standart tasks
     *
     * POST data structure:
     * [
     *      put       => String destination
     *      nodeId    => String node_id
     *      table     => String observing table name
     *      datyiiaPath  => String file save path
     *      taskName  => String task Name
     *      hash      => String hash
     *      data      => Array  [attributes]
     * ]
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSetWorkerResult(): array
    {

        $data   = Yii::$app->request->getBodyParams();
        $errors = [
            'put'      => 'Data destination is not set. Can not save result.',
            'table'    => 'Target table is not set. Can not save result.',
            'data'     => 'Empty data. Can not save result.',
            'nodeId'   => 'Node id is not set. Can not save result.',
            'taskName' => 'Task name is not set. Can not recognize task directory.',
            'hash'     => 'Hash is not set. Can not save result.',
        ];

        /*
         * Input data validation
         */
        foreach($errors as $field => $message) {
            if(!isset($data[$field])) {
                Yii::$app->response->statusCode = 422;
                return ApiHelper::getResponseBodyByCode(422, $message);
            }
        }

        /*
         * Writing result
         */
        switch($data['put']) {

            case 'file':

                /** @var OutputProcessing $this->module->output */
                /** @noinspection PhpUndefinedFieldInspection */
                $storagePath = $this->module->output->getFullPath($data['dataPath'], $data['taskName']);

                /*
                 * Checking-creating task directory
                 */
                if( !file_exists($storagePath)) {
                    if( !mkdir($storagePath, 0755, true) ) {
                        Yii::$app->response->statusCode = 500;
                        return ApiHelper::getResponseBodyByCode(500, "Can not create directory $storagePath.");
                    }
                }

                /*
                 * Writing file
                 */
                $f_error = null;
                $toWrite = $this->module->output->getFileData($data['data']);
                $f_name  = $storagePath . DIRECTORY_SEPARATOR . $data['nodeId'] . '.txt';

                set_error_handler(
                    function(/** @noinspection PhpUnusedParameterInspection */$errno, $errstr, $errfile, $errline, array $errcontext) use (&$f_error) {
                        $f_error = (!empty($errstr)) ? $errstr : "Can not write file";
                    }
                );

                $f_write = file_put_contents($f_name, $toWrite);

                if($f_write === false) {
                    Yii::$app->response->statusCode = 500;
                    return ApiHelper::getResponseBodyByCode(500, $f_error);
                }
                elseif($f_write === 0) {
                    Yii::$app->response->statusCode = 500;
                    return ApiHelper::getResponseBodyByCode(500, "Empty file $f_name created. Check 'Workers and Jobs' for warnings.");
                }

                restore_error_handler();

                /** @noinspection PhpUndefinedFieldInspection
                 *  @var ActiveRecord $outputModel
                 *  Updating hashes
                 */
                $outputModel = $this->module->output->getOutputModel($data['taskName'], $data['nodeId']);
                $outputModel->setAttribute('hash', $data['hash']);

                if(!$outputModel->validate()) {
                    Yii::$app->response->statusCode = 422;
                    return ApiHelper::getResponseBodyByCode(422, 'Can not update result hash.');
                }

                if(!$outputModel->save()) {
                    Yii::$app->response->statusCode = 500;
                    return ApiHelper::getResponseBodyByCode(500, 'Can not update result hash. Check your data.');
                }

                /*
                 * Success
                 */
                Yii::$app->response->statusCode = 201;
                return $data['data'];

            break;

            case 'db':

                /** @var OutputProcessing $this->module->output */
                /** @noinspection PhpUndefinedFieldInspection */
                $outputModel = $this->module->output->getOutputModel($data['taskName'], $data['nodeId']);

                /** @var ActiveRecord $outputModel */
                $outputModel->setAttributes($data['data'], false);
                $outputModel->setAttribute('hash', $data['hash']);

                if(!$outputModel->validate()) {
                    Yii::$app->response->statusCode = 422;
                    return ApiHelper::getResponseBodyByCode(422, 'Can not save to database. Data validation is failed.');
                }
                if(!$outputModel->save()) {
                    Yii::$app->response->statusCode = 500;
                    return ApiHelper::getResponseBodyByCode(500, 'Can not save to database. DB error. Check your data.');
                }

                // Success
                Yii::$app->response->statusCode = 201;
                return $data['data'];

            break;

            default:
                // Wrong destination
                Yii::$app->response->statusCode = 422;
                return ApiHelper::getResponseBodyByCode(422, 'Wrong worker result destination.');
        }

    }


    /**
     * Send user created mail
     *
     * @param  string $event_name
     * @param  int $schedule_id
     * @return bool
     */
    public function actionSendMail($event_name, $schedule_id)
    {
        try {

            $mailer = (new CustomMailer())->sendMail($event_name);
            return ($mailer === "0") ? true : false;

        } catch (\Exception $e) {
            $message = "An error occurred while sending mail. See Mailer log for detailed information.\nSchedule id: {$schedule_id}\nException:\n{$e->getMessage()}";
            Yii::error([$message, null, 'MAILER SEND MAIL'], 'scheduler.writeLog');
            return false;
        }
    }


    /**
     * Setting schedule log
     * POST only
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSetScheduleLog(): array
    {
        $model = new LogScheduler();
        return $this->setLog($model);
    }


    /**
     * Setting System log
     * POST only
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSetSystemLog(): array
    {
        $model = new LogSystem();
        return $this->setLog($model);
    }


    /**
     * @param  \yii\db\ActiveRecord $model
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function setLog($model): array
    {

        $data              = Yii::$app->request->getBodyParams();
        $model->attributes = $data;

        if(!$model->validate()) {
            Yii::$app->response->statusCode = 422;
            return ApiHelper::getResponseBodyByCode(422);
        }

        if(!$model->save()) {
            Yii::$app->response->statusCode = 500;
            return ApiHelper::getResponseBodyByCode(500);
        }

        Yii::$app->response->statusCode = 201;
        return $model->attributes;

    }

}
