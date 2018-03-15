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
 * This is the model class for table "{{%severity}}".
 *
 * @property string $name
 *
 * @property LogMailer[] $logMailers
 * @property LogNode[] $logNodes
 * @property LogScheduler[] $logSchedulers
 * @property LogSystem[] $logSystems
 *
 * @package app\models
 */
class Severity extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%severity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogMailers()
    {
        return $this->hasMany(LogMailer::class, ['severity' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogNodes()
    {
        return $this->hasMany(LogNode::class, ['severity' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogSchedulers()
    {
        return $this->hasMany(LogScheduler::class, ['severity' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogSystems()
    {
        return $this->hasMany(LogSystem::class, ['severity' => 'name']);
    }

}
