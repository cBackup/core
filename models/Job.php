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

use Yii;
use \yii\db\ActiveRecord;
use \yii\db\Query;
use \yii\helpers\ArrayHelper;


/**
 * This is the model class for table "{{%job}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $worker_id
 * @property integer $sequence_id
 * @property string $command_value
 * @property string $command_var
 * @property string $cli_custom_prompt
 * @property string $snmp_request_type
 * @property string $snmp_set_value
 * @property string $snmp_set_value_type
 * @property integer $timeout
 * @property string $table_field
 * @property integer $enabled
 * @property string $description
 *
 * @property JobSnmpRequestTypes $snmpRequestType
 * @property JobSnmpTypes $snmpSetValueType
 * @property Worker $worker
 *
 * @package app\models
 */
class Job extends ActiveRecord
{

    /**
     * @var int
     */
    public $after_job;

    /**
     * @var array
     */
    public $job_dependencies = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%job}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'worker_id', 'command_value'], 'required'],
            [['name', 'command_value', 'command_var', 'cli_custom_prompt', 'snmp_set_value', 'description'], 'filter', 'filter' => 'trim'],
            [['command_var'], 'filter', 'filter' => 'strtoupper'],
            [['command_value'], 'filter', 'filter' => function ($value) {
                return preg_replace_callback('/%%\w+%%/', function ($matches) { return strtoupper($matches[0]); }, $value);
            }],
            [['command_var'], 'match', 'pattern' => '/^%%\w+%%$/',
                'message' => Yii::t('network', 'Command variable must start and end with - %%. Example %%TEST%%')
            ],
            [['command_var'], 'in', 'not' => true, 'range' => \Y::param('system_variables'),
                'message' => Yii::t('network', 'Command variable <b>{value}</b> is system reserved variable.')
            ],
            [['command_var'], 'in', 'not' => true, 'range' => JobGlobalVariable::find()->select('var_name')->column(),
                'message' => Yii::t('network', 'Variable <b>{value}</b> is global variable.')
            ],
            [['worker_id', 'sequence_id', 'timeout', 'enabled'], 'integer'],
            [['timeout'], 'integer', 'min' => 1, 'max' => 60000],
            [['name', 'command_value', 'command_var', 'cli_custom_prompt', 'snmp_set_value', 'table_field', 'description'], 'string', 'max' => 255],
            [['snmp_request_type', 'snmp_set_value_type'], 'string', 'max' => 32],
            [['snmp_request_type'], 'exist', 'skipOnError' => true, 'targetClass' => JobSnmpRequestTypes::class, 'targetAttribute' => ['snmp_request_type' => 'name']],
            [['snmp_set_value_type'], 'exist', 'skipOnError' => true, 'targetClass' => JobSnmpTypes::class, 'targetAttribute' => ['snmp_set_value_type' => 'name']],
            [['worker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Worker::class, 'targetAttribute' => ['worker_id' => 'id']],
            [['snmp_set_value_type'], 'required', 'when' => function($model) {/** @var $model Job */
                return ($model->snmp_request_type == 'set');
            }],
            [['snmp_set_value'], 'required', 'when' => function($model) {/** @var $model Job */
                return ($model->snmp_request_type == 'set' && $model->snmp_set_value_type != 'null');
            }],
            [['snmp_set_value'], 'integer', 'when' => function($model) { /** @var $model Job */
                return ($model->snmp_set_value_type == 'int' || $model->snmp_set_value_type == 'uint');
            }],
            [['snmp_set_value'], 'ip', 'ipv6' => false, 'when' => function($model) { /** @var $model Job */
                return ($model->snmp_set_value_type == 'ip_address');
            }],
            [['table_field'], 'checkTableField'],
            [['command_var'], 'isWorkerVariableUnique'],
            [['command_var'], 'isWorkerVariableUsed', 'skipOnEmpty' => false],
            [['command_value'], 'workerVariableExists'],
            [['command_value'], 'isWorkerJobPositionCorrect'],
            [['command_value'], 'isKeySeqOnePerCommand'],
            [['timeout', 'snmp_request_type', 'snmp_set_value', 'snmp_set_value_type', 'table_field', 'command_var', 'cli_custom_prompt', 'description'], 'default', 'value' => null],
            [['after_job'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => 'ID',
            'name'                => Yii::t('network', 'Job Name'),
            'worker_id'           => Yii::t('network', 'Worker'),
            'sequence_id'         => Yii::t('network', 'Sequence'),
            'command_value'       => Yii::t('network', 'Command'),
            'command_var'         => Yii::t('network', 'Command variable'),
            'cli_custom_prompt'   => Yii::t('network', 'CLI custom prompt'),
            'snmp_request_type'   => Yii::t('network', 'SNMP request type'),
            'snmp_set_value'      => Yii::t('network', 'SNMP value'),
            'snmp_set_value_type' => Yii::t('network', 'SNMP value type'),
            'timeout'             => Yii::t('network', 'Timeout'),
            'table_field'         => Yii::t('network', 'SQL table field'),
            'enabled'             => Yii::t('app', 'Enabled'),
            'description'         => Yii::t('app', 'Description'),
            'after_job'           => Yii::t('network', 'After job')
        ];
    }

    /**
     * Check if key sequence is one per line and not surrounded by other commands
     *
     * @param $attribute
     */
    public function isKeySeqOnePerCommand($attribute)
    {
        $matches = [];
        preg_match_all('/%%SEQ.*?%%/im', $this->command_value, $matches);

        if (count($matches[0]) > 1) {
            $this->addError($attribute, Yii::t('network', 'Only one key sequence is allowed per command'));
        }
        elseif (count($matches[0]) == 1 && !preg_match('/^%%SEQ\(\w+\)%%$/im', $this->command_value)) {
            $this->addError($attribute, Yii::t('network', 'Key sequence must be used exclusively'));
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function checkTableField($attribute, /** @noinspection PhpUnusedParameterInspection */ $params)
    {
        /** @var  $check Job */
        $check = Job::find()->where(['worker_id' => $this->worker_id, 'table_field' => $this->table_field])->one();

        if (!is_null($check)) {
            if ($check->id != $this->id) {
                $attributes = [$check->table_field, $check->name];
                $this->addError($attribute, Yii::t('network', 'Table field <b>{0}</b> is already set in job <b>{1}</b>.', $attributes));
            }
        }
    }

    /**
     * @param $attribute
     */
    public function isWorkerVariableUnique($attribute)
    {
        $query = static::find()->where(['worker_id' => $this->worker_id, 'command_var' => $this->command_var])->one();

        if (!is_null($query)) {
            if ($query->id != $this->id) {
                $attributes = [$this->command_var, $query->name];
                $this->addError($attribute, Yii::t('network', 'Duplicate variable <b>{0}</b>. Variable is already used in job <b>{1}</b>.', $attributes));
            }
        }
    }

    /**
     * @param $attribute
     */
    public function workerVariableExists($attribute)
    {
        if (preg_match_all('/%%\w+%%/', $this->command_value, $matches)) {
            foreach ($matches[0] as $match) {
                if (!in_array($match, \Y::param('system_variables'))) {
                    $worker_variables = static::find()->where(['worker_id' => $this->worker_id, 'command_var' => $match]);
                    if (!$worker_variables->exists()) {
                        $global_variables = JobGlobalVariable::find()->where(['var_name' => $match]);
                        if (!$global_variables->exists()) {
                            $this->addError($attribute, Yii::t('network', 'Variable <b>{0}</b> do not exists.</br>', $match));
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $attribute
     */
    public function isWorkerJobPositionCorrect($attribute)
    {
        $sequence = (is_null($this->sequence_id)) ? $this->after_job : $this->sequence_id;

        if (!empty($sequence)) {
            if (preg_match_all('/%%\w+%%/', $this->command_value, $matches)) {
                $query   = static::find()->where(['worker_id' => $this->worker_id, 'command_var' => $matches[0]])->asArray()->all();
                foreach ($query as $item) {
                    if ($item['id'] == $this->id) {
                        $attributes = [$item['command_var'], $item['name']];
                        $this->addError($attribute, Yii::t('network', 'Trying to assign variable to itself. Variable <b>{0}</b> is current job variable.', $attributes));
                    } else if ($sequence < $item['sequence_id']) {
                        $attributes = [$item['command_var'], $this->name, $item['name']];
                        $this->addError($attribute, Yii::t('network', 'To use variable <b>{0}</b>, job <b>{1}</b> must go after job <b>{2}</b></br>', $attributes));
                    }
                }
            }
        }
    }

    /**
     * @param $attribute
     */
    public function isWorkerVariableUsed($attribute)
    {

        if (!$this->getIsNewRecord()) {

            $matches = [];
            $error   = false;
            $old_var = $this->getOldAttribute('command_var');

            if (!empty($old_var) && $old_var !== $this->command_var) {

                $query = static::find()->where(['worker_id' => $this->worker_id])->orderBy('sequence_id')->asArray()->all();

                foreach ($query as $item) {
                    if (strpos($item['command_value'], $old_var)) {
                        array_push($matches, $item['name']);
                        $error = true;
                    }
                }

                if ($error) {
                    $this->addError($attribute,
                        Yii::t('network', 'Worker jobs: </br><b>{0}</b></br> depends on job variable <b>{1}</b>. Remove all dependencies first.', [implode('</br>', $matches), $old_var])
                    );
                }

            }
        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSnmpRequestType()
    {
        return $this->hasOne(JobSnmpRequestTypes::class, ['name' => 'snmp_request_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSnmpSetValueType()
    {
        return $this->hasOne(JobSnmpTypes::class, ['name' => 'snmp_set_value_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorker()
    {
        return $this->hasOne(Worker::class, ['id' => 'worker_id']);
    }

    /**
     * @param  bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($this->getIsNewRecord()) {
            $jobs = static::find()->select('sequence_id')
                ->where(['worker_id' => $this->worker_id])
                ->column();

            $sequence          = (!empty($jobs)) ? max($jobs) + 1 : 1;
            $this->sequence_id = (empty($this->after_job)) ? $sequence : $this->after_job;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!empty($this->after_job)) {
            static::updateSequence($this->worker_id, $this->after_job);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        static::updateSequence($this->worker_id, $this->sequence_id);
        parent::afterDelete();
    }

    /**
     * Check job variable dependencies before delete
     *
     * @return bool
     */
    public function beforeDelete()
    {
        $delete = true;

        if (!empty($this->command_var)) {
            $query = static::find()->where(['worker_id' => $this->worker_id])->orderBy('sequence_id')->asArray()->all();
            foreach ($query as $item) {
                if (strpos($item['command_value'], $this->command_var)) {
                    array_push($this->job_dependencies, $item['name']);
                    $delete = false;
                }
            }
        }

        return $delete;
    }

    /**
     * Get all worker jobs
     *
     * @return array
     */
    public function getWorkerJobs()
    {
        $jobs = (new Query())
            ->select(['sequence_id', 'name'])
            ->from('{{%job}}')
            ->where(['worker_id' => $this->worker_id])
            ->orderBy('sequence_id')
            ->all();

        return ArrayHelper::map($jobs, 'sequence_id', 'name');
    }

    /**
     * Get task table fields based on task name
     *
     * @param  string $task_name
     * @return array
     */
    public static function getTaskTableFields($task_name)
    {
        $default_fields = ['id', 'time', 'node_id', 'hash'];
        $table_fields   = [];
        $db             = Yii::$app->getDb();
        $table_schema   = $db->getTableSchema("out_{$task_name}");

        if (!is_null($table_schema)) {
            $columns      = $table_schema->getColumnNames();
            $table_fields = array_filter($columns, function($field) use ($default_fields) {
                return (!in_array($field, $default_fields));
            });
        }

        return array_combine($table_fields, $table_fields);
    }

    /**
     * Getting sorted job list by worker
     *
     * @param $worker_id
     * @return array
     */
    public static function getJobsByWorker($worker_id)
    {

        $jobs = (new Query())
            ->select(['sequence_id', 'command_value', 'cli_custom_prompt', 'snmp_request_type', 'snmp_set_value', 'snmp_set_value_type', 'timeout', 'table_field', 'command_var'])
            ->from('{{%job}}')
            ->where(['worker_id' => $worker_id, 'enabled' => '1'])
            ->orderBy('sequence_id')
            ->all();

        return ArrayHelper::index($jobs, 'sequence_id');

    }

    /**
     * Get worker variables
     *
     * @return array
     */
    public function getWorkerVariables()
    {
        $query = static::find()->select(['name', 'worker_id', 'sequence_id', 'command_var']);

        $query->where(['and', ['worker_id' => $this->worker_id], ['not', ['command_var' => null]]]);

        if ($this->sequence_id !== null) {
            $query->andWhere(['<', 'sequence_id', $this->sequence_id]);
        }

        $result = $query->asArray()->all();

        return $result;
    }

    /**
     * Check if all corresponding task table fields are set to worker jobs
     *
     * @return array
     */
    public static function checkJobsIntegrity()
    {

        /** Get all workers with jobs */
        $workers = Worker::find()->joinWith('jobs')->asArray()->all();

        /** Get task names from workers list */
        $task_list = ArrayHelper::map($workers, 'task_name', 'task_name');

        $result = [];

        foreach ($task_list as $task_name) {
            $fields = static::getTaskTableFields($task_name);
            foreach ($fields as $field) {
                foreach ($workers as $worker) {
                    if ($task_name == $worker['task_name']) {
                        if (array_search($field, array_column($worker['jobs'], 'table_field')) === false) {
                            $result[$task_name][$worker['name']][] = $field;
                        }
                    }
                }
            }
        }

        return $result;

    }

    /**
     * Update jobs sequence
     *
     * @param  int $worker_id
     * @param  int $sequence
     * @return bool
     */
    private static function updateSequence($worker_id, $sequence)
    {

        $jobs = static::find()->select('id, sequence_id')
            ->where(['worker_id' => $worker_id])
            ->orderBy(['sequence_id' => SORT_ASC])
            ->asArray()
            ->all();

        try {

            $iterator = $sequence;
            foreach ($jobs as $job) {
                if ($job['sequence_id'] >= $sequence) {
                    static::updateAll(['sequence_id' => $iterator], ['id' => $job['id']]);
                    $iterator++;
                }
            }

            $updated = true;

        } catch (\Exception $e) {
            $updated = false;
        }

        return $updated;

    }

}
