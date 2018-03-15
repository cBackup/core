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
use \yii\behaviors\BlameableBehavior;
use \yii\db\ActiveRecord;


/**
 * This is the model class for table "{{%log_node}}".
 *
 * @property integer $id
 * @property string $userid
 * @property string $time
 * @property integer $node_id
 * @property string $severity
 * @property string $action
 * @property string $message
 *
 * @property Node $node
 * @property Severity $severity0
 * @property User $user
 *
 * @package app\models
 */
class LogNode extends ActiveRecord
{

    /**
     * @var string
     */
    public $node_params;

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
        return '{{%log_node}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'required'],
            [['time'], 'safe'],
            [['node_id'], 'integer'],
            [['message'], 'string'],
            [['userid'], 'string', 'max' => 128],
            [['severity'], 'string', 'max' => 32],
            [['action'], 'string', 'max' => 45],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => Node::class, 'targetAttribute' => ['node_id' => 'id']],
            [['severity'], 'exist', 'skipOnError' => true, 'targetClass' => Severity::class, 'targetAttribute' => ['severity' => 'name']],
            [['userid'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userid' => 'userid']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'userid'      => Yii::t('app', 'User'),
            'time'        => Yii::t('app', 'Time'),
            'node_id'     => Yii::t('app', 'Node ID'),
            'severity'    => Yii::t('log', 'Severity'),
            'action'      => Yii::t('log', 'Action'),
            'message'     => Yii::t('app', 'Message'),
            'node_params' => Yii::t('node', 'Node'),
            'date_from'   => Yii::t('log', 'Date/time from'),
            'date_to'     => Yii::t('log', 'Date/time to'),
            'page_size'   => Yii::t('app', 'Page size')
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
        $this->node_id     = $params[1];
        $this->action      = $params[2];
        $this->save(false);
    }

}
