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

use app\helpers\SystemHelper;
use app\modules\rbac\models\AuthAssignment;
use app\modules\rbac\models\AuthItem;
use Yii;
use yii\bootstrap\Html;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "{{%user}}".
 *
 * @property string $userid
 * @property string $auth_key
 * @property string $password_hash
 * @property string $access_token
 * @property string $fullname
 * @property string $email
 * @property integer $enabled
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItem[] $itemNames
 * @property LogMailer[] $logMailers
 * @property LogNode[] $logNodes
 * @property LogScheduler[] $logSchedulers
 * @property LogSystem[] $logSystems
 * @property Messages[] $messages
 * @property SettingOverride[] $settingOverrides
 * @property Setting[] $keys
 *
 * @package app\models
 */
class User extends ActiveRecord implements IdentityInterface
{

    /**
     * @var string
     */
    public $password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'fullname'], 'required'],
            [['password'], 'required', 'on' => 'create'],
            [['userid'], 'unique'],
            [['userid'], 'match', 'pattern' => '/^[a-z0-9\.\-_]+$/i'],
            [['enabled'], 'integer'],
            [['enabled'], 'validateEnabled'],
            [['password'], 'string', 'max' => 16],
            [['userid', 'access_token', 'fullname', 'email'], 'string', 'max' => 128],
            [['auth_key'], 'string', 'max' => 32],
            [['password_hash'], 'string', 'max' => 255],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['email', 'access_token'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userid'        => Yii::t('app', 'Username'),
            'auth_key'      => Yii::t('user', 'Auth key'),
            'password_hash' => Yii::t('user', 'Password hash'),
            'password'      => Yii::t('app', 'Password'),
            'access_token'  => Yii::t('user', 'Access token'),
            'fullname'      => Yii::t('user', 'Full name'),
            'email'         => Yii::t('app', 'E-mail'),
            'enabled'       => Yii::t('app', 'Enabled'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::class, ['user_id' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemNames()
    {
        return $this->hasMany(AuthItem::class, ['name' => 'item_name'])->viaTable('{{%auth_assignment}}', ['user_id' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogMailers()
    {
        return $this->hasMany(LogMailer::class, ['userid' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogNodes()
    {
        return $this->hasMany(LogNode::class, ['userid' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogSchedulers()
    {
        return $this->hasMany(LogScheduler::class, ['userid' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogSystems()
    {
        return $this->hasMany(LogSystem::class, ['userid' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Messages::class, ['approved_by' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingOverrides()
    {
        return $this->hasMany(SettingOverride::class, ['userid' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeys()
    {
        return $this->hasMany(Setting::class, ['key' => 'key'])->viaTable('{{%setting_override}}', ['userid' => 'userid']);
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateEnabled($attribute, /** @noinspection PhpUnusedParameterInspection */$params)
    {
        if (in_array(mb_strtolower($this->userid), array_map('mb_strtolower', \Y::param('system.users'))) && $this->enabled == 0) {
            $this->addError($attribute, Yii::t('user', 'You cannot deactivate system user <b>{0}</b>!', strtoupper($this->userid)));
            $this->enabled = 1;
        }
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($userid)
    {
        return static::findOne($userid);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'enabled' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     * @throws \yii\base\Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates RESTful API access token
     */
    public function generateRestAccessToken()
    {
        $this->access_token = SystemHelper::generateToken();
    }

    /**
     * Render contents for cell with access token
     * @return string
     */
    public function renderToken()
    {

        $res = $this->access_token;

        if( !empty($this->access_token) ) {
            $res = Html::tag('span', $this->access_token);
            $res.= '&nbsp;';
            $res.= Html::a('<i class="fa fa-copy"></i>', 'javascript:;', [
                'title'               => Yii::t('app', 'Copy'),
                'data-clipboard-text' => $this->access_token,
                'class'               => 'access_token_copy'
            ]);
        }

        return $res;

    }

    /**
     * Get all user from Users table
     * Method builds a map of key => value pairs
     *
     * Possible $displayText argument values:
     *  name: as a value fullname will be shown
     *  join: as a value fullname and userid will be shown in "Fullname (userid)" format
     *
     * @param  string $displayText default argument value is userid
     * @return array
     */
    public function getUsers($displayText = 'userid')
    {
        switch ($displayText) {
            case 'name': $displayText = 'fullname'; break;
            case 'join': $displayText = function($data){ return "$data->fullname ({$data->userid})"; }; break;
            default: $displayText = 'userid';
        }

        return ArrayHelper::map(static::find()->all(), 'userid', $displayText);
    }

}
