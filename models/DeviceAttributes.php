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
 * This is the model class for table "{{%device_attributes}}".
 *
 * @property integer $id
 * @property integer $device_id
 * @property string $sysobject_id
 * @property string $hw
 * @property string $sys_description
 *
 * @property Device $device
 *
 * @package app\models
 */
class DeviceAttributes extends ActiveRecord
{

    /**
     * @var int
     */
    public $unkn_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%device_attributes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id'], 'required'],
            [['device_id'], 'integer'],
            [['sysobject_id', 'hw'], 'string', 'max' => 255],
            [['sys_description'], 'string', 'max' => 1024],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::class, 'targetAttribute' => ['device_id' => 'id']],
            [['sysobject_id', 'hw', 'sys_description'], 'default', 'value' => null],
            [['sysobject_id'], 'unique', 'targetAttribute' => ['sysobject_id', 'hw', 'sys_description'], 'message' => Yii::t('network', 'Such combination of device attributes already exists!')],
            [['unkn_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('app', 'ID'),
            'device_id'       => Yii::t('network', 'Device'),
            'sysobject_id'    => Yii::t('network', 'System OID'),
            'hw'              => Yii::t('network', 'Hardware rev.'),
            'sys_description' => Yii::t('network', 'System descr.'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::class, ['id' => 'device_id']);
    }
}
