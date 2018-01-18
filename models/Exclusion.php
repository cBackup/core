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
use yii\db\Query;

/**
 * This is the model class for table "{{%exclusion}}".
 *
 * @property string $ip
 * @property string $description
 *
 * @package app\models
 */
class Exclusion extends ActiveRecord
{

    /**
     * @var string
     */
    public $save_on_warning = '0';

    /**
     * @var bool
     */
    public $show_warning = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%exclusion}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip'], 'required'],
            [['ip', 'description'], 'filter', 'filter' => 'trim'],
            [['ip'], 'unique'],
            [['ip'], 'ip', 'ipv6' => false],
            [['ip'], 'string', 'max' => 15],
            [['description'], 'string', 'max' => 255],
            [['save_on_warning'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ip'          => Yii::t('network', 'IP address'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * @param  string $ip
     * @return bool
     */
    public static function exists($ip)
    {
        return (new Query())->select('ip')->from('{{%exclusion}}')->where('ip=:ip', [':ip' => $ip])->exists();
    }

}
