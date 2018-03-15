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
 * This is the model class for table "{{%vendor}}".
 *
 * @property string $name
 *
 * @property Device[] $devices
 *
 * @package app\models
 */
class Vendor extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%vendor}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'filter', 'filter' => 'trim'],
            [['name'], 'unique'],
            [['name'], 'match', 'pattern' => '/^[a-z][\w_]*$/i',
                'message' => Yii::t('network', 'Vendor name should start with letter and contain only a-z, 0-9 or underscore')
            ],
            [['name'], 'string', 'max' => 64],
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
    public function getDevices()
    {
        return $this->hasMany(Device::class, ['vendor' => 'name']);
    }
}
