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
 * @property string $ip
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
            [['ip'], 'string', 'max' => 15],
            [['sysobject_id', 'hw'], 'string', 'max' => 255],
            [['sys_description'], 'string', 'max' => 1024],
            [['sysobject_id'], 'unique', 'targetAttribute' => ['sysobject_id', 'hw', 'sys_description'], 'message' => Yii::t('network', 'Such combination of device attributes already exists!')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('app', 'ID'),
            'ip'              => Yii::t('app', 'Ip'),
            'sysobject_id'    => Yii::t('network', 'System OID'),
            'hw'              => Yii::t('network', 'Hardware rev.'),
            'sys_description' => Yii::t('network', 'System descr.'),
            'created'         => Yii::t('app', 'Created'),
        ];
    }


    /**
     * @param  array $attributes
     * @return bool
     * @throws \Exception
     */
    public static function addNewAttributes(array $attributes)
    {

        $success       = true;
        $unknownDevice = DeviceAttributesUnknown::find()->select(['id'])->where([
            'sysobject_id'    => $attributes['sysobject_id'],
            'hw'              => $attributes['hw'],
            'sys_description' => $attributes['sys_description']
        ]);

        if(!$unknownDevice->exists()) {

            /** Create new device attributes */
            $deviceAttributesUnknown = new DeviceAttributesUnknown($attributes);
            $transaction             = Yii::$app->db->beginTransaction();

            try {

                $success = $deviceAttributesUnknown->save();

                if ($success) {
                    $unknownDeviceId   = $deviceAttributesUnknown->getPrimaryKey();
                    $messages          = new Messages();
                    $messages->message = 'New device model found. ' . Html::a("Click here", ['/network/device/unknown-list', 'DeviceAttributesUnknownSearch[id]' => $unknownDeviceId]);
                    $success           = $messages->save();
                }

                if ($success) {
                    $transaction->commit();
                }
                else {
                    $transaction->rollBack();
                }

            }
            catch (\Exception $e) {
                $transaction->rollBack();
                $error = "\nNode IP: {$attributes['ip']}\nAn error occurred while adding new device attributes. {$e->getMessage()}";
                throw new \Exception($error);
            }

        }

        return $success;

    }

}
