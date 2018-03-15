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
 * This is the model class for table "{{%device}}".
 *
 * @property integer $id
 * @property string $vendor
 * @property string $model
 * @property string $auth_template_name
 *
 * @property DeviceAuthTemplate $authTemplateName
 * @property Vendor $vendorName
 * @property DeviceAttributes[] $deviceAttributes
 * @property Node[] $nodes
 * @property TasksHasDevices[] $tasksHasDevices
 *
 * @package app\models
 */
class Device extends ActiveRecord
{

    /**
     * Default page size
     *
     * @var int
     */
    public $page_size = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%device}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vendor', 'model', 'auth_template_name'], 'required'],
            [['model'], 'filter', 'filter' => 'trim'],
            [['vendor', 'auth_template_name'], 'string', 'max' => 64],
            [['model'], 'string', 'max' => 128],
            [['auth_template_name'], 'exist', 'skipOnError' => true, 'targetClass' => DeviceAuthTemplate::class, 'targetAttribute' => ['auth_template_name' => 'name']],
            [['vendor'], 'exist', 'skipOnError' => true, 'targetClass' => Vendor::class, 'targetAttribute' => ['vendor' => 'name']],
            [['vendor', 'model'], 'unique', 'targetAttribute' => ['vendor', 'model'], 'message' => 'The combination of Vendor and Model has already been taken.'],
            [['model'], 'match', 'pattern' => '/^[a-z](?!.*[\-_]{2,})[\w\-]*/i',
                'message' => Yii::t('network', 'Device name should start with letter, contain only a-z, 0-9 and non-repeating hyphens and/or underscores')
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'vendor'             => Yii::t('network', 'Vendor'),
            'model'              => Yii::t('network', 'Model'),
            'auth_template_name' => Yii::t('network', 'Auth template name'),
            'page_size'          => Yii::t('app', 'Page size'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthTemplateName()
    {
        return $this->hasOne(DeviceAuthTemplate::class, ['name' => 'auth_template_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendorName()
    {
        return $this->hasOne(Vendor::class, ['name' => 'vendor']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeviceAttributes()
    {
        return $this->hasMany(DeviceAttributes::class, ['device_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNodes()
    {
        return $this->hasMany(Node::class, ['device_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksHasDevices()
    {
        return $this->hasMany(TasksHasDevices::class, ['device_id' => 'id']);
    }
}
