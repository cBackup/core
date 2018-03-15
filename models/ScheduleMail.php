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
use yii\db\Query;


/**
 * This is the model class for table "{{%schedule_mail}}".
 *
 * @property integer $id
 * @property string $event_name
 * @property string $schedule_cron
 *
 * @property MailerEvents $eventName
 */
class ScheduleMail extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%schedule_mail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_name', 'schedule_cron'], 'required'],
            [['event_name'], 'string', 'max' => 128],
            [['schedule_cron'], 'string', 'max' => 255],
            [['event_name'], 'unique'],
            [['event_name'], 'exist', 'skipOnError' => true, 'targetClass' => MailerEvents::class, 'targetAttribute' => ['event_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'ID'),
            'event_name'    => Yii::t('app', 'Event name'),
            'schedule_cron' => Yii::t('network', 'Cron'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEventName()
    {
        return $this->hasOne(MailerEvents::class, ['name' => 'event_name']);
    }

    /**
     * Returns full mailer schedule list
     *
     * @return array
     */
    public static function getScheduleEvents()
    {
        return (new Query())
            ->select(['id as scheduleId', 'event_name as eventName', 'schedule_cron as scheduleCron'])
            ->from('{{%schedule_mail}}')
            ->all();
    }

}
