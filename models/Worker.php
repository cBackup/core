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
use yii\helpers\Html;
use \yii\db\ActiveRecord;


/**
 * This is the model class for table "{{%worker}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $task_name
 * @property string $get
 * @property string $description
 *
 * @property Job[] $jobs
 * @property Job[] $sortedJobs
 * @property TasksHasDevices[] $tasksHasDevices
 * @property TasksHasNodes[] $tasksHasNodes
 * @property Task $taskName
 * @property WorkerProtocol $protocol
 *
 * @package app\models
 */
class Worker extends ActiveRecord
{

    /**
     * @var string
     */
    public $job_name;

    /**
     * @var string
     */
    public $command_value;

    /**
     * @var string
     */
    public $table_field;

    /**
     * @var int
     */
    public $enabled;

    /**
     * @var int
     */
    public $worker_id;

    /**
     * Default page size
     * @var int
     */
    public $page_size = 12;

    /**
     * @var bool
     */
    public $job_delete_status = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%worker}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'task_name', 'get'], 'required'],
            [['name'], 'match', 'pattern' => '/^[a-z0-9_\-]+$/im', 'message' => Yii::t('app', 'Worker name should contain only a-z, 0-9, dash or underscore')],
            [['name', 'description'], 'filter', 'filter' => 'trim'],
            [['name'], 'unique'],
            [['name', 'task_name', 'description'], 'string', 'max' => 255],
            [['get'], 'string', 'max' => 16],
            [['task_name'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_name' => 'name']],
            [['get'], 'exist', 'skipOnError' => true, 'targetClass' => WorkerProtocol::class, 'targetAttribute' => ['get' => 'name']],
            [['description'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'name'          => Yii::t('app', 'Name'),
            'task_name'     => Yii::t('network', 'Task Name'),
            'get'           => Yii::t('network', 'Protocol'),
            'description'   => Yii::t('app', 'Description'),
            'job_name'      => Yii::t('network', 'Job name'),
            'command_value' => Yii::t('network', 'Command'),
            'table_field'   => Yii::t('network', 'SQL table field'),
            'enabled'       => Yii::t('app', 'Enabled'),
            'worker_id'     => Yii::t('network', 'Worker ID'),
            'page_size'     => Yii::t('app', 'Page size'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(Job::class, ['worker_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSortedJobs()
    {
        return $this->hasMany(Job::class, ['worker_id' => 'id'])->orderBy(['sequence_id' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksHasDevices()
    {
        return $this->hasMany(TasksHasDevices::class, ['worker_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksHasNodes()
    {
        return $this->hasMany(TasksHasNodes::class, ['worker_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaskName()
    {
        return $this->hasOne(Task::class, ['name' => 'task_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProtocol()
    {
        return $this->hasOne(WorkerProtocol::class, ['name' => 'get']);
    }

    /**
     * Delete all worker jobs if get was changed
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {

        $old_get = (array_key_exists('get', $changedAttributes)) ? $changedAttributes['get'] : '';

        if ($old_get != $this->get && !empty($old_get)) {
            try {
                Job::deleteAll(['worker_id' => $this->id]);
            } catch (\Exception $e) {
                $this->job_delete_status = false;
            }
        }

        parent::afterSave($insert, $changedAttributes);

    }

    /**
     * Check if worker is assigned to nodes or devices
     *
     * @param  int $worker_id
     * @return string
     */
    public function renderWorkerAssignments($worker_id)
    {

        $worker_id = intval($worker_id);
        $device    = TasksHasDevices::find()->where(['worker_id' => $worker_id])->with('worker')->asArray()->one();
        $node      = TasksHasNodes::find()->where(['worker_id' => $worker_id])->with('worker')->asArray()->one();

        $device_link = ['icon' => 'red.png', 'text' => Yii::t('network', 'No devices assigned'), 'url' => '', 'target' => ''];
        $node_link   = ['icon' => 'red.png', 'text' => Yii::t('network', 'No nodes assigned'), 'url' => '', 'target' => ''];

        /** Check if at least one device is assigned to worker */
        if (!is_null($device)) {
            $device_link = [
                'icon'   => 'green.png',
                'text'   => Yii::t('network', 'Click to view assigned devices'),
                'target' => 'device_tasks',
                'url'    => ['/network/assigntask/list',
                    'TasksHasDevicesSearch[task_name]'   => $device['task_name'],
                    'TasksHasDevicesSearch[worker_name]' => $device['worker']['name']
                ]
            ];
        }

        /** Check if at least one node is assigned to worker */
        if (!is_null($node)) {
            $node_link = [
                'icon'   => 'green.png',
                'text'   => Yii::t('network', 'Click to view assigned nodes'),
                'target' => 'node_tasks',
                'url'    => ['/network/assigntask/list',
                    'TasksHasNodesSearch[task_name]'   => $node['task_name'],
                    'TasksHasNodesSearch[worker_name]' => $node['worker']['name']
                ]
            ];
        }

        $result = '&nbsp;'.Html::a(Html::img("@web/img/sq_{$node_link['icon']}"), $node_link['url'], [
            'data-toggle' => 'tooltip',
            'title'       => $node_link['text'],
            'data-target' => $node_link['target'],
            'data-open'   => '_blank',
            'class'       => is_null($node) ? 'cursor-default' : 'set-active-tab cursor-hand',
        ]);

        $result.= Html::a(Html::img("@web/img/sq_{$device_link['icon']}"), $device_link['url'], [
            'data-toggle' => 'tooltip',
            'title'       => $device_link['text'],
            'data-target' => $device_link['target'],
            'data-open'   => '_blank',
            'class'       => is_null($device) ? 'cursor-default' : 'set-active-tab cursor-hand'

        ]);

        return $result;

    }

    /**
     * Render worker job info tooltip
     *
     * @param  int $worker_id
     * @param  string $command_var
     * @return string
     */
    public function renderJobTooltip($worker_id, $command_var)
    {
        $result = '';

        if (!empty($command_var)) {
            $dependencies = [];
            $query = Job::find()->where(['worker_id' => $worker_id])->orderBy('sequence_id')->asArray()->all();

            foreach ($query as $item) {
                if (strpos($item['command_value'], $command_var)) {
                    array_push($dependencies, $item['name']);
                }
            }

            $title = Yii::t('network', 'Job variable: {0}</br>', $command_var);
            if (!empty($dependencies)) {
                $title .= Yii::t('network', 'Dependent operations:</br>{0}', [implode('</br>', $dependencies)]);
            }

            $result = Html::tag('i', '', [
                'class'               => 'fa fa-key',
                'style'               => 'cursor: help; padding-left: 5px',
                'data-toggle'         => 'tooltip',
                'data-placement'      => 'bottom',
                'data-template'       => '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner" style="max-width: none;"></div></div>',
                'data-html'           => 'true',
                'data-original-title' => $title,
            ]);
        }

        return $result;
    }

}
