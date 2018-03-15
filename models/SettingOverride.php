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
use yii\db\ActiveRecord;


/**
 * This is the model class for table "{{%setting_override}}".
 *
 * @property string $key
 * @property string $userid
 * @property string $value
 *
 * @property Setting $key0
 * @property User $user
 *
 * @package app\models
 */
class SettingOverride extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%setting_override}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'userid'], 'required'],
            [['key'], 'string', 'max' => 64],
            [['userid'], 'string', 'max' => 128],
            [['value'], 'string', 'max' => 255],
            [['key'], 'exist', 'skipOnError' => true, 'targetClass' => Setting::class, 'targetAttribute' => ['key' => 'key']],
            [['userid'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userid' => 'userid']],
            ['value', 'match', 'pattern' => '/^[a-z]{2}\-[A-Z]{2}$/', 'when' => function($model) {  return $model->key == 'language'; }],
            ['value', 'boolean', 'when' => function($model) {  return $model->key == 'sidebar_collapsed'; }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key'    => Yii::t('app', 'Key'),
            'userid' => Yii::t('app', 'Userid'),
            'value'  => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKey0()
    {
        return $this->hasOne(Setting::class, ['key' => 'key']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['userid' => 'userid']);
    }

}
