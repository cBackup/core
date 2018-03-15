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


/**
 * This is the model class for table "{{%tasks_has_devices}}".
 *
 * @property integer $id
 * @property string $task_name
 * @property integer $device_id
 * @property integer $worker_id
 *
 * @property Device $device
 * @property Task $taskName
 * @property Worker $worker
 *
 * @package app\models
 */
class TasksHasDevices extends ActiveRecord
{

    /**
     * @var string
     */
    public $device_name;

    /**
     * @var string
     */
    public $worker_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tasks_has_devices}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_name', 'device_id', 'worker_id'], 'required'],
            [['device_id', 'worker_id'], 'integer'],
            [['task_name'], 'string', 'max' => 255],
            [['device_id'], 'unique', 'targetAttribute' => ['task_name', 'device_id'], 'message' => Yii::t('network', 'Such device-task combination already exists!')],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::class, 'targetAttribute' => ['device_id' => 'id']],
            [['task_name'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_name' => 'name']],
            [['worker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Worker::class, 'targetAttribute' => ['worker_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'task_name'   => Yii::t('network', 'Task'),
            'device_id'   => Yii::t('network', 'Device'),
            'worker_id'   => Yii::t('network', 'Worker'),
            'device_name' => Yii::t('network', 'Device name'),
            'worker_name' => Yii::t('network', 'Worker name')
        ];
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
    public function getTaskName()
    {
        return $this->hasOne(Task::class, ['name' => 'task_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorker()
    {
        return $this->hasOne(Worker::class, ['id' => 'worker_id']);
    }
}
