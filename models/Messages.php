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
use \yii\db\Expression;
use \yii\db\ActiveRecord;
use \yii\behaviors\TimestampBehavior;


/**
 * This is the model class for table "{{%messages}}".
 *
 * @property integer $id
 * @property string $message
 * @property string $created
 * @property string $approved
 * @property string $approved_by
 *
 * @property User $approvedBy
 *
 * @package app\models
 */
class Messages extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%messages}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'required'],
            [['message'], 'string'],
            [['created', 'approved'], 'safe'],
            [['approved_by'], 'string', 'max' => 128],
            [['approved_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['approved_by' => 'userid']],
            [['approved', 'approved_by'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'message'     => Yii::t('app', 'Message'),
            'created'     => Yii::t('app', 'Created'),
            'approved'    => Yii::t('app', 'Approved'),
            'approved_by' => Yii::t('app', 'Approved By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApprovedBy()
    {
        return $this->hasOne(User::class, ['userid' => 'approved_by']);
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
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }


    /**
     * Check if table contains unmarked messages
     *
     * @return bool
     */
    public static function hasUnmarkedMessages()
    {
        $model = static::find()->where(['approved' => null])->count();
        return ($model > 0) ? true : false;
    }

}
