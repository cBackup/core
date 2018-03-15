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

namespace app\models;

use dautkom\ipv4\IPv4;
use Yii;
use \yii\db\ActiveRecord;
use \yii\db\Query;
use \yii\data\ActiveDataProvider;
use GitWrapper\GitWrapper;


/**
 * This is the model class for table "{{%node}}".
 *
 * @property integer $id
 * @property string $ip
 * @property integer $network_id
 * @property integer $credential_id
 * @property integer $device_id
 * @property string $auth_template_name
 * @property string $mac
 * @property string $created
 * @property string $modified
 * @property string $last_seen
 * @property integer $manual
 * @property string $hostname
 * @property string $serial
 * @property string $prepend_location
 * @property string $location
 * @property string $contact
 * @property string $sys_description
 * @property integer $protected
 *
 * @property AltInterface[] $altInterfaces
 * @property LogNode[] $logNodes
 * @property LogScheduler[] $logSchedulers
 * @property DeviceAuthTemplate $authTemplateName
 * @property Credential $credential
 * @property Device $device
 * @property Network $network
 * @property OutBackup[] $outBackups
 * @property OutStp[] $outStps
 * @property TasksHasNodes[] $tasksHasNodes
 *
 * @package app\models
 */
class Node extends ActiveRecord
{

    /**
     * Default page size
     *
     * @var int
     */
    public $page_size = 20;

    /**
     * @var string
     */
    public $device_name;

    /**
     * @var string
     */
    public $search_option;

    /**
     * @var string
     */
    public $search_string;

    /**
     * @var bool
     */
    public $wrong_subnet = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%node}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip', 'device_id'], 'required'],
            [['ip', 'mac', 'serial', 'prepend_location', 'hostname', 'location', 'contact', 'sys_description'], 'filter', 'filter' => 'trim'],
            [['ip'], 'ip', 'ipv6' => false],
            [['network_id', 'credential_id', 'device_id', 'manual', 'protected'], 'integer'],
            [['created', 'modified', 'last_seen'], 'safe'],
            [['ip'], 'string', 'max' => 15],
            [['mac'], 'filter', 'filter' => function($value) { return strtoupper(preg_replace('/[^a-z0-9]/i', '', $value)); }],
            [['mac'], 'match', 'pattern' => '/^[A-F0-9]{12}$/m', 'message' => Yii::t('app', 'Wrong MAC address specified')],
            [['mac'], 'string', 'max' => 12],
            [['auth_template_name'], 'string', 'max' => 64],
            [['hostname', 'prepend_location', 'location', 'contact'], 'string', 'max' => 255],
            [['sys_description'], 'string', 'max' => 1024],
            [['serial'], 'string', 'max' => 45],
            [['ip'], 'unique'],
            [['credential_id'], 'required', 'when' => function($model) {/** @var $model Node*/ return empty ($model->network_id); }],
            [['credential_id'], 'exist', 'skipOnError' => true, 'targetClass' => Credential::class, 'targetAttribute' => ['credential_id' => 'id']],
            [['auth_template_name'], 'exist', 'skipOnError' => true, 'targetClass' => DeviceAuthTemplate::class, 'targetAttribute' => ['auth_template_name' => 'name']],
            [['ip'], 'subnetValidation', 'when' => function($model) {/** @var $model Node*/ return !empty($model->network_id); }],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::class, 'targetAttribute' => ['device_id' => 'id']],
            [['network_id'], 'exist', 'skipOnError' => true, 'targetClass' => Network::class, 'targetAttribute' => ['network_id' => 'id']],
            [['mac', 'serial', 'hostname', 'location', 'contact', 'sys_description', 'auth_template_name', 'prepend_location'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'ip'                 => Yii::t('network', 'IP address'),
            'network_id'         => Yii::t('network', 'Network'),
            'credential_id'      => Yii::t('network', 'Credential name'),
            'device_id'          => Yii::t('network', 'Device'),
            'auth_template_name' => Yii::t('network', 'Auth template name'),
            'mac'                => Yii::t('network', 'MAC address'),
            'created'            => Yii::t('app', 'Created'),
            'modified'           => Yii::t('app', 'Modified'),
            'last_seen'          => Yii::t('app', 'Last Seen'),
            'manual'             => Yii::t('app', 'Manual'),
            'hostname'           => Yii::t('network', 'Hostname'),
            'serial'             => Yii::t('network', 'Serial'),
            'prepend_location'   => Yii::t('network', 'Prepend location'),
            'location'           => Yii::t('network', 'Location'),
            'contact'            => Yii::t('app', 'Contact'),
            'sys_description'    => Yii::t('app', 'Description'),
            'page_size'          => Yii::t('app', 'Page size'),
            'device_name'        => Yii::t('network', 'Device name'),
            'protected'          => Yii::t('node', 'Protected'),
        ];
    }

    /**
     * @param $attribute
     */
    public function subnetValidation($attribute)
    {
        $net    = new IPv4();
        $subnet = Network::findOne(['id' => $this->network_id]);

        if( !$net->subnet($subnet->network)->has($this->ip) ) {
            $this->wrong_subnet = true;
            $this->addError($attribute, Yii::t('network', "IP-address doesn't belong to chosen subnet"));
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAltInterfaces()
    {
        return $this->hasMany(AltInterface::class, ['node_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogNodes()
    {
        return $this->hasMany(LogNode::class, ['node_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogSchedulers()
    {
        return $this->hasMany(LogScheduler::class, ['node_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthTemplateName()
    {
        return $this->hasOne(DeviceAuthTemplate::class, ['name' => 'auth_template_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCredential()
    {
        return $this->hasOne(Credential::class, ['id' => 'credential_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::class, ['id' => 'device_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNetwork()
    {
        return $this->hasOne(Network::class, ['id' => 'network_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOutBackups()
    {
        return $this->hasMany(OutBackup::class, ['node_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOutStps()
    {
        return $this->hasMany(OutStp::class, ['node_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksHasNodes()
    {
        return $this->hasMany(TasksHasNodes::class, ['node_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {

        parent::afterDelete();
        $file = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . "{$this->id}.txt";

        if( file_exists($file) ) {
            @unlink($file);
        }

    }

    /**
     * Getting node credential_id or device credential_id, if node credential_id is empty
     *
     * @param string $node_id
     * @return string|null
     */
    public static function getCredentialsId($node_id)
    {
        $fullInfo = (new Query())
            ->select(['t.credential_id as node_credential_id', 'n.credential_id as network_credential_id'])
            ->from('{{%node}} t')
            ->leftJoin('{{%network}} n', 't.network_id = n.id')
            ->where('t.id=:node_id', [':node_id' => $node_id])
            ->one();

        return (is_null($fullInfo['node_credential_id']))? $fullInfo['network_credential_id'] : $fullInfo['node_credential_id'];
    }

    /**
     * Get model auth_sequence by node id
     *
     * @param $node_id
     * @return false|null|string
     */
    public static function getAuthSequence($node_id)
    {
        return (new Query())
            ->select(['s.auth_sequence'])
            ->from('{{%node}} n')
            ->leftJoin('{{%device}} d', 'n.device_id = d.id')
            ->leftJoin(
                '{{%device_auth_template}} s',
                '(n.auth_template_name = s.name AND n.auth_template_name IS NOT NULL) 
                    OR (d.auth_template_name = s.name AND n.auth_template_name IS NULL)'
            )
            ->where(['n.id' => $node_id])
            ->scalar();
    }

    /**
     * Create-Update node data
     *
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public static function createOrUpdateNode($data)
    {
        $isNewNode = false;

        $node = Node::find()->where(['ip' => $data['ip']])->one();

        if(empty($node)) {
            $isNewNode = true;
            $node = new Node();
        }

        $node->network_id      = (int)$data['network_id'];
        $node->device_id       = (int)$data['device_id'];
        $node->ip              = $data['ip'];
        $node->mac             = strtoupper(preg_replace('/[^a-z0-9]/i', '', $data['mac']));
        $node->hostname        = $data['hostname'];
        $node->serial          = $data['serial'];
        $node->location        = $data['location'];
        $node->contact         = $data['contact'];
        $node->sys_description = $data['sys_description'];

        $transaction = Yii::$app->db->beginTransaction();
        $changes     = $node->getDirtyAttributes();

        if ($node->validate()) {

            if (!empty($changes)) {

                $node->modified  = date('Y-m-d H:i:s');
                $node->last_seen = date('Y-m-d H:i:s');
                $success = $node->save();

                /** Write Log */
                if ($success) {
                    if ($isNewNode) {
                        $message = "Node has been created.\nData -\nip: {$data['ip']}\nhostname: {$data['location']}";
                        Yii::info([$message, $node->getPrimaryKey(), 'CREATE'], 'node.writeLog');
                    } else {
                        $message = "Node data has been updated.\nNew data -\n";
                        foreach ($changes as $key => $value) {
                            $message .= "{$key}: {$value}\n";
                        }
                        Yii::info([$message, $node->getPrimaryKey(), 'UPDATE'], 'node.writeLog');
                    }
                }

            } else {
                $node->last_seen = date('Y-m-d H:i:s');
                $success = $node->save();
            }

            if ($success) {
                $transaction->commit();
            }
            else {
                $transaction->rollBack();
            }

        } else {
            $node_errors = implode("\n", array_map(function ($a) { return implode("\n", $a);}, $node->getErrors()));
            throw new \Exception("\n{$node_errors}");
        }

        return $success;
    }

    /**
     * Get full backup file commit history
     *
     * @param  int $id
     * @return array|string
     */
    public static function getBackupCommitLog($id)
    {
        try {

            $wrapper     = new GitWrapper(\Y::param('gitPath'));
            $backup_path = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup';
            $git         = $wrapper->workingCopy($backup_path);

            $git->log('master', "$id.txt", [
                'skip'      => '1',
                'max-count' => \Y::param('gitDays'),
                'pretty'    => '%H|%s|%an|%ai'
            ]);

            $git_log = explode("\n", $git->getOutput());
            $log     = array_map(function ($v) { return explode('|', $v); }, array_filter($git_log));

            return $log;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get commit metadata by commit hash
     *
     * @param $hash
     * @return array|string
     */
    public static function getCommitMetaData($hash)
    {
        try {

            $wrapper     = new GitWrapper(\Y::param('gitPath'));
            $backup_path = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup';
            $git         = $wrapper->workingCopy($backup_path);

            $git->show("{$hash}", [
                'quiet'  => true,
                'pretty' => '%H|%s|%an|%ai'
            ]);

            $commit_meta = explode('|', $git->getOutput());

            return array_filter($commit_meta);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get git version of backup file
     *
     * @param  int $id
     * @param  string $hash
     * @return string
     */
    public static function getBackupGitVersion($id, $hash)
    {
        try {

            $wrapper     = new GitWrapper(\Y::param('gitPath'));
            $backup_path = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup';
            $git         = $wrapper->workingCopy($backup_path);

            $git->show("{$hash}:{$id}.txt");

            return $git->getOutput();

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Check node assignment
     *
     * @param   int $node_id
     * @param   int $device_id
     * @return  bool
     * @throws \Exception
     */
    public static function checkNodeAssignment($node_id, $device_id)
    {
        $node_task = TasksHasNodes::find()->where(['task_name' => 'backup', 'node_id' => $node_id])->one();

        if (is_null($node_task)) {
            throw new \Exception(Yii::t('node', 'Node is not assigned to "backup" task.'));
        }

        if (!is_null($node_task) && is_null($node_task->worker_id)) {
            $device_task = TasksHasDevices::find()->where(['task_name' => 'backup', 'device_id' => $device_id])->exists();
            if (!$device_task) {
                throw new \Exception(Yii::t('node', 'Worker for task "backup" is not set. Please set worker directly to node or via device.'));
            }
        }

        return true;
    }

    /**
     * Get list|count of orphans (nodes which are not assigned to any task)
     *
     * If parameter $count is set to true orphans count will be returned
     *
     * @param bool $count
     * @return int|ActiveDataProvider
     */
    public static function getOrphans($count = false)
    {
        $exclusions = Exclusion::find()->select('ip')->asArray()->all();

        $query = static::find()
            ->joinWith('tasksHasNodes t', false)
            ->where(['t.node_id' => null])
            ->andWhere(['not in', 'ip', $exclusions])
            ->orderBy(['id' => SORT_ASC])
        ;

        if ($count) {
            $result = $query->count();
        }
        else {
            $result = new ActiveDataProvider([
                'query' => $query,
                'sort' => false
            ]);
        }

        return $result;
    }

}
