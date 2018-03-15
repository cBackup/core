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
use yii\db\Query;


/**
 * This is the model class for table "{{%setting}}".
 *
 * @property string $key
 * @property string $value
 *
 * @property SettingOverride[] $settingOverrides
 * @property User[] $users
 *
 * @package app\models
 */
class Setting extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['key'], 'string', 'max' => 64],
            [['value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingOverrides()
    {
        return $this->hasMany(SettingOverride::class, ['key' => 'key']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['userid' => 'userid'])->viaTable('{{%setting_override}}', ['key' => 'key']);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->cache->delete('pluginmenu');
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Retrieve setting by it's key.
     * To avoid unnecessary database poking, keys are stored in the user session after first request
     *
     * @param  string $key
     * @return string|int
     */
    public static function get($key)
    {

        if(is_null($settings = Yii::$app->getSession()->get('settings'))) {
            $settings = [];
        }

        // If session doesn't contain requested setting, retrieve it from database
        if( !array_key_exists($key, $settings) ) {

            $result = (new Query())
                ->select('IFNULL(`o`.`value`, `s`.`value`) AS `value`')
                ->from('{{%setting}} s')
                ->leftJoin('{{%setting_override}} o', 'o.`key`=s.`key` AND o.userid=:userid', [':userid' => Yii::$app->getUser()->id])
                ->where('s.`key`=:key', [':key'=>$key])
                ->one()
            ;

            // Save the whole $settings array, because session mechanism doesn't allow to set it by single key
            if( $result ) {
                $settings[$key] = $result['value'];
                Yii::$app->getSession()->set('settings', $settings);
            }

        }

        return $settings[$key];

    }

    /**
     * Retrieve default settings from the database
     *
     * @return array
     */
    public static function getDefaultSettings()
    {

        $settings = [];
        $data     = self::find()->all();

        array_walk($data, function($v) use(&$settings) {
            $settings[$v['key']] = $v['value'];
        });

        return $settings;

    }

    /**
     * Retrieve settings for current user considering their overrides
     *
     * @return array
     */
    public static function getSettingsForUser()
    {

        $settings = [];
        $data     = (new Query())
            ->select('s.`key`, IFNULL(`o`.`value`, `s`.`value`) AS `value`')
            ->from('{{%setting}} s')
            ->leftJoin('{{%setting_override}} o', 'o.`key`=s.`key` AND o.userid=:userid', [':userid' => Yii::$app->getUser()->id])
            ->all();

        array_walk($data, function($v) use(&$settings) {
            $settings[$v['key']] = $v['value'];
        });

        return $settings;

    }

}
