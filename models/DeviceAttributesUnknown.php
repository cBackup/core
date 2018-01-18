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
use yii\helpers\Html;


/**
 * This is the model class for table "{{%device_attributes_unknown}}".
 *
 * @property integer $id
 * @property string $sysobject_id
 * @property string $hw
 * @property string $sys_description
 * @property string $created
 *
 * @package app\models
 */
class DeviceAttributesUnknown extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%device_attributes_unknown}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created'], 'safe'],
            [['sysobject_id', 'hw', 'sys_description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('app', 'ID'),
            'sysobject_id'    => Yii::t('network', 'System OID'),
            'hw'              => Yii::t('network', 'Hardware rev.'),
            'sys_description' => Yii::t('network', 'System descr.'),
            'created'         => Yii::t('app', 'Created'),
        ];
    }

    /**
     * Create new attributes if not exist
     *
     * @param $sysobject_id
     * @param $hw
     * @param $sys_description
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function addNewAttributes($sysobject_id, $hw, $sys_description)
    {
        $success = true;

        $unknownDeviceId = DeviceAttributesUnknown::find()->select(['id'])->where(['sysobject_id' => $sysobject_id, 'hw' => $hw, 'sys_description' => $sys_description])->scalar();

        if(empty($unknownDeviceId)) {

            $transaction = Yii::$app->db->beginTransaction();

            /*
             * Create new device attributes
             */
            $deviceAttributesUnknown = new DeviceAttributesUnknown();
            $deviceAttributesUnknown->sysobject_id = $sysobject_id;
            $deviceAttributesUnknown->hw = $hw;
            $deviceAttributesUnknown->sys_description = $sys_description;

            $success = $deviceAttributesUnknown->save();

            if($success) {
                $unknownDeviceId = $deviceAttributesUnknown->getPrimaryKey();

                /*
                 * Create message
                 */
                $messages = new Messages();
                $messages->message = 'New device model found. ' .
                    Html::a("Click here", ['/network/device/unknown-list', 'DeviceAttributesUnknownSearch[id]' => $unknownDeviceId]);

                $success = $messages->save();
            }

            if($success) {
                $transaction->commit();
            }
            else {
                $transaction->rollBack();
            }
        }

        return $success;

    }
}
