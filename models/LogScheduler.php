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
use \yii\behaviors\BlameableBehavior;


/**
 * This is the model class for table "{{%log_scheduler}}".
 *
 * @property integer $id
 * @property string $userid
 * @property string $time
 * @property string $severity
 * @property string $schedule_type
 * @property integer $schedule_id
 * @property integer $node_id
 * @property string $action
 * @property string $message
 *
 * @property Node $node
 * @property Schedule $schedule
 * @property ScheduleType $scheduleType
 * @property Severity $severity0
 * @property User $user
 *
 * @package app\models
 */
class LogScheduler extends ActiveRecord
{

    /**
     * @var string
     */
    public $node_name;

    /**
     * @var string
     */
    public $date_from;

    /**
     * @var string
     */
    public $date_to;

    /**
     * Default page size
     * @var int
     */
    public $page_size = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%log_scheduler}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time'], 'safe'],
            [['schedule_id', 'node_id'], 'integer'],
            [['message'], 'required'],
            [['message'], 'string'],
            [['userid'], 'string', 'max' => 128],
            [['severity', 'schedule_type'], 'string', 'max' => 32],
            [['action'], 'string', 'max' => 45],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => Node::class, 'targetAttribute' => ['node_id' => 'id']],
            [['schedule_id'], 'exist', 'skipOnError' => true, 'targetClass' => Schedule::class, 'targetAttribute' => ['schedule_id' => 'id']],
            [['schedule_type'], 'exist', 'skipOnError' => true, 'targetClass' => ScheduleType::class, 'targetAttribute' => ['schedule_type' => 'name']],
            [['severity'], 'exist', 'skipOnError' => true, 'targetClass' => Severity::class, 'targetAttribute' => ['severity' => 'name']],
            [['userid'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userid' => 'userid']],
            [['node_id', 'schedule_id'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'userid'      => Yii::t('app', 'User'),
            'time'        => Yii::t('app', 'Time'),
            'severity'    => Yii::t('log', 'Severity'),
            'schedule_id' => Yii::t('log', 'Task name'),
            'node_id'     => Yii::t('app', 'Node ID'),
            'action'      => Yii::t('log', 'Action'),
            'message'     => Yii::t('app', 'Message'),
            'node_name'   => Yii::t('log', 'Node name'),
            'date_from'   => Yii::t('log', 'Date/time from'),
            'date_to'     => Yii::t('log', 'Date/time to'),
            'page_size'   => Yii::t('app', 'Page size'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(Node::class, ['id' => 'node_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedule()
    {
        return $this->hasOne(Schedule::class, ['id' => 'schedule_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheduleType()
    {
        return $this->hasOne(ScheduleType::class, ['name' => 'schedule_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeverity0()
    {
        return $this->hasOne(Severity::class, ['name' => 'severity']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['userid' => 'userid']);
    }

    /**
     * Behaviors
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'userid',
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * Render message in expandable extra row
     *
     * @return string
     */
    public function renderMessage()
    {

        $message = nl2br($this->message);

        /** Short message template */
        $template =
            '
                <div class="row">
                    <div class="col-md-11">
                        ' . $message . '
                    </div>
                    <div class="col-md-1 text-center">
                        <a  href="#" class="disabled"><i class="fa fa-caret-square-o-down"></i></a>
                    </div>
                </div>
            ';

        /** If message contains line break consider that it is long message */
        if (strpos($message, '<br />') !== false) {

            $preview   = mb_substr($message, 0, strpos($message, '<br />'));
            $full_text = mb_substr($message, strpos($message, '<br />'));
            $full_text = preg_replace('/^\s*(?:<br\s*\/?>\s*)*|\s*(?:<br\s*\/?>\s*)*$/', '', $full_text);

            /** Long message template */
            $template =
                '
                    <div class="row">
                        <div class="col-md-11">
                            <div id="msg_'.$this->id.'">
                                <div class="preview">
                                   ' . $preview . '<span class="dots">...</span>
                                </div>
                                <div class="full-text" style="display: none;">
                                    ' . $full_text . '
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1 text-center toggle-links">
                            <a id="link_'.$this->id.'" title="'.Yii::t('log', 'Show full message').'" class="grid-toggle" href="javascript:;">
                                <i class="fa fa-caret-square-o-down"></i>
                            </a>
                        </div>
                    </div>
                ';

        }

        $output = '<tr><td colspan="6" class="loggrid-extra-row">' . $template . '</td></tr>';


        return $output;

    }

    /**
     * Write custom log to DB
     *
     * @param array  $params
     * @param string $level
     */
    public function writeLog($params, $level)
    {
        $this->userid      = Yii::$app->user->id;
        $this->severity    = $level;
        $this->message     = $params[0];
        $this->schedule_id = $params[1];
        $this->action      = $params[2];
        $this->save(false);
    }

}
