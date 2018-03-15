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
 * This is the model class for table "{{%device_auth_template}}".
 *
 * @property string $name
 * @property string $auth_sequence
 * @property string $description
 *
 * @property Device[] $devices
 * @property Node[] $nodes
 */
class DeviceAuthTemplate extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%device_auth_template}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'auth_sequence'], 'required'],
            [['name'], 'unique'],
            [['name'], 'match', 'pattern' => '/^[a-z0-9_\-]+$/im', 'message' => Yii::t('network', 'Template name should contain only a-z, 0-9, dash or underscore')],
            [['auth_sequence'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 255],
            /** @see DeviceAuthTemplate::authSequenceValidator() */
            [['auth_sequence'], 'authSequenceValidator', 'params' => [
                'tags'   => [
                    '{{telnet_login}}', '{{telnet_password}}', '{{enable_password}}', '%%SEQ(CTRLY)%%',
                    '%%SEQ(CTRLC)%%', '%%SEQ(CTRLZ)%%', '%%SEQ(ESC)%%', '%%SEQ(SPACE)%%', '%%SEQ(ENTER)%%'
                ]
            ]],
            [['description'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'          => Yii::t('app', 'Name'),
            'auth_sequence' => Yii::t('network', 'Auth Sequence'),
            'description'   => Yii::t('app', 'Description'),
        ];
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function authSequenceValidator($attribute, $params)
    {

        $tagUnique = array_fill_keys(array_values($params['tags']), 0);
        $prompt    = '/^.*['. join('', \Y::param('cli_prompts')) .']$/m';
        $sequence  = explode("\n", $this->auth_sequence);
        $sequence  = array_map(function($item) { return rtrim($item, "\n\r"); }, $sequence);
        $hasPrompt = false;
        $failedTag = false;
        $tagRegex  = '(?:{{|%%)[\w\(\)]+(?:}}|%%)';

        foreach ($sequence as $row) {
            if( preg_match('/^'.$tagRegex.'$/im', $row) && !in_array($row, $params['tags']) ) {
                $failedTag = true;
            }
            if( preg_match('/^{{\w+}}$/im', $row) && array_key_exists($row, $tagUnique) ) {
                $tagUnique[$row]++;
            }
            if( preg_match('/^'.$tagRegex.'.+$/im', $row) || preg_match('/^.+'.$tagRegex.'$/im', $row) ) {
                $failedTag = true;
            }
        }

        // no array_pop(), because we want to count($sequence) below
        $last = array_values(array_slice($sequence, -1))[0];
        if( preg_match($prompt, $last) ) {
            $hasPrompt = true;
        }

        array_walk($tagUnique, function($v, $k) use($attribute) {
            if( $v > 1 ) {
                $this->addError($attribute, Yii::t('network', 'Non-unique tag {0} found in the sequence', [$k]));
                return;
            }
        });

        if( !$hasPrompt ) {
            $this->addError($attribute, Yii::t('network', 'Auth sequence must be terminated with valid CLI prompt in the end'));
        }

        if( (count($sequence) % 2) == 0 ) {
            $this->addError($attribute, Yii::t('network', 'Auth sequence must be terminated with prompt, not a command'));
        }

        if( $failedTag ) {
            $this->addError($attribute, Yii::t('network', 'Erroneuos tag or excessive symbols around tag found in sequence'));
        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevices()
    {
        return $this->hasMany(Device::class, ['auth_template_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNodes()
    {
        return $this->hasMany(Node::class, ['auth_template_name' => 'name']);
    }
}
