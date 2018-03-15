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


/**
 * This is the model class for table "{{%schedule}}".
 *
 * @property integer $id
 * @property string $task_name
 * @property string $schedule_cron
 *
 * @property LogScheduler[] $logSchedulers
 * @property Task $taskName
 *
 * @package app\models
 */
class Schedule extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schedule}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_name', 'schedule_cron'], 'required'],
            [['task_name', 'schedule_cron'], 'string', 'max' => 255],
            [['task_name'], 'unique'],
            [['task_name'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'ID'),
            'task_name'     => Yii::t('network', 'Task Name'),
            'schedule_cron' => Yii::t('network', 'Cron'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogSchedulers()
    {
        return $this->hasMany(LogScheduler::class, ['schedule_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaskName()
    {
        return $this->hasOne(Task::class, ['name' => 'task_name']);
    }

    /**
     * Returns full schedule list with task info
     *
     * @return array
     */
    public static function getScheduleTasks()
    {
        return (new Query())
            ->select(['s.id as scheduleId', 's.task_name as taskName', 's.schedule_cron as scheduleCron', 'task_type as taskType', 'put', 'table'])
            ->from('{{%schedule}} s')
            ->leftJoin('{{%task}} t', 's.task_name = t.name')
            ->all();
    }
}
