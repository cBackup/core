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
use yii\base\DynamicModel;
use yii\db\ActiveRecord;
use GitWrapper\GitWrapper;


/**
 * This is the model class for table "{{%config}}".
 *
 * @property string $key
 * @property string $value
 *
 * @package app\models
 */
class Config extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%config}}';
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
     * Config form input validator
     *
     * @param  array $form_fields
     * @return DynamicModel
     */
    public function configFormValidator($form_fields)
    {

        $fields = new DynamicModel($form_fields);

        $fields->addRule([
            'adminEmail', 'dataPath', 'snmpTimeout', 'snmpRetries',
            'telnetTimeout', 'telnetBeforeSendDelay', 'sshTimeout', 'gitDays',
            'javaServerUsername', 'javaServerPort', 'javaSchedulerUsername', 'javaSchedulerPort',
            'sshBeforeSendDelay', 'threadCount', 'logLifetime', 'nodeLifetime'], 'required');
        $fields->addRule([
            'adminEmail', 'gitUsername', 'gitEmail', 'gitLogin', 'gitPassword', 'gitRepo', 'gitPath', 'dataPath', 'snmpTimeout', 'snmpRetries',
            'telnetTimeout', 'telnetBeforeSendDelay', 'sshTimeout', 'sshBeforeSendDelay', 'threadCount', 'logLifetime', 'nodeLifetime',
            'mailerFromEmail', 'mailerFromName', 'mailerSmtpHost', 'mailerSmtpPort', 'mailerSmtpUsername', 'mailerSmtpPassword', 'mailerSendMailPath',
            'defaultPrependLocation'
        ], 'filter', ['filter' => 'trim']);
        $fields->addRule(['adminEmail'], 'email');
        $fields->addRule(['gitUsername', 'javaServerUsername', 'javaSchedulerUsername'], 'string', ['max' => 64]);
        $fields->addRule(['adminEmail', 'gitRepo', 'gitPath', 'dataPath', 'defaultPrependLocation'], 'string', ['max' => 255]);
        $fields->addRule(['git', 'gitRemote'], 'boolean');
        $fields->addRule(['threadCount'], 'integer', ['min' => 1, 'max' => 30]);
        $fields->addRule(['snmpRetries'], 'integer', ['min' => 1, 'max' => 10]);
        $fields->addRule(['logLifetime', 'nodeLifetime'], 'integer', ['min' => 0]);
        $fields->addRule(['snmpTimeout', 'telnetTimeout', 'sshTimeout', 'telnetBeforeSendDelay', 'sshBeforeSendDelay'], 'integer', ['min' => 1, 'max' => 60000]);
        $fields->addRule(['dataPath'], function ($attribute) use ($fields) {
            if (!file_exists($fields->attributes[$attribute]) || !is_dir($fields->attributes[$attribute])) {
                $fields->addError($attribute, Yii::t('config', "Path folder doesn't exist"));
            }
        });

        /** Git settings validation */
        $fields->addRule(['gitRepo'], 'url');
        $fields->addRule(['gitDays'], 'integer', ['min' => 1]);
        $fields->addRule(['gitUsername', 'gitEmail'], 'required', ['when' => function() use ($fields) {
            return $fields->attributes['git'] == 1;
        }]);
        $fields->addRule(['gitEmail'], 'email', ['when' => function() use ($fields) {
            return $fields->attributes['git'] == 1;
        }]);
        $fields->addRule(['gitRepo', 'gitLogin', 'gitPassword'], 'required', ['when' => function() use ($fields) {
            return ($fields->attributes['git'] == 1 && $fields->attributes['gitRemote'] == 1);
        }]);
        $fields->addRule(['gitLogin', 'gitPassword'], 'string', ['max' => 64, 'when' => function() use ($fields) {
            return ($fields->attributes['git'] == 1 && $fields->attributes['gitRemote'] == 1);
        }]);
        $fields->addRule(['gitLogin'], 'required', ['when' => function() use ($fields) {
            return ($fields->attributes['gitRemote'] == 1 && !empty($fields->attributes['gitPassword']));
        }]);
        $fields->addRule(['gitPassword'], 'required', ['when' => function() use ($fields) {
            return ($fields->attributes['gitRemote'] == 1 && !empty($fields->attributes['gitLogin']));
        }]);
        $fields->addRule(['gitPath'], function ($attribute) use ($fields) {
            if (!file_exists($fields->attributes[$attribute])) {
                $fields->addError($attribute, Yii::t('config', 'Git executable cannot be found in specified location'));
            }
        },['when' => function() use ($fields) { return $fields->attributes['git'] == 1; }]);
        $fields->addRule(['gitUsername', 'gitEmail', 'gitLogin', 'gitPassword', 'gitRepo', 'gitPath', 'defaultPrependLocation'], 'default', ['value' => null]);

        /** SMTP settings validation */
        $fields->addRule(['mailerFromEmail', 'mailerFromName'], 'required', ['when' => function() use ($fields) {
            return $fields->attributes['mailer'] == 1;
        }]);

        $fields->addRule(['mailerFromEmail'], 'email', ['when' => function() use ($fields) {
            return $fields->attributes['mailer'] == 1;
        }]);

        $fields->addRule(['mailerSmtpPort'], 'integer', ['min' => 1, 'max' => 65535,  'when' => function() use ($fields) {
            return $fields->attributes['mailer'] == 1;
        }]);

        $fields->addRule(['mailerSmtpHost', 'mailerSmtpPort'], 'required', ['when' => function() use ($fields) {
            return ($fields->attributes['mailer'] == 1 && $fields->attributes['mailerType'] == 'smtp');
        }]);

        $fields->addRule(['mailerSendMailPath'], 'required', ['when' => function() use ($fields) {
            return ($fields->attributes['mailer'] == 1 && $fields->attributes['mailerType'] == 'local');
        }]);

        $fields->addRule(['mailerSmtpUsername', 'mailerSmtpPassword'], 'required', ['when' => function() use ($fields) {
            return ($fields->attributes['mailer'] == 1 && $fields->attributes['mailerSmtpAuth'] == 1);
        }]);

        /** SSH port number validators */
        $fields->addRule(['javaSchedulerPort', 'javaServerPort'], 'integer', ['min' => 1, 'max' => 65535]);
        $fields->addRule(['javaSchedulerPort'], 'compare', ['compareAttribute' => 'javaServerPort', 'operator' => '!=', 'type' => 'number']);

        return $fields;

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key'     => Yii::t('app', 'Key'),
            'value'   => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        parent::afterSave($insert, $changedAttributes);

        $key  = [];
        $file = Yii::$app->basePath.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'application.properties';

        if( preg_match('/^javaScheduler(\w+)$/', $this->attributes['key'], $key) ) {

            $key  = array_filter($key);
            $key  = array_values($key);
            $key  = array_key_exists(1, $key) ? mb_strtolower($key[1]) : '';

            if( !empty($key) && file_exists($file) && is_writable($file) ) {
                $in  = file_get_contents($file);
                $out = preg_replace("/^(sshd\.shell\.$key)=.*$/im", "$1={$this->attributes['value']}", $in);
                file_put_contents($file, $out);
            }

        }

    }

    /**
     * Checks if values in bin/application.properties and database
     * are even and matched to avoid descync between java behavior
     * and netssh scripts
     *
     * @param  array $data
     * @return void
     */
    public static function checkApplicationProperties($data)
    {

        $res  = [];
        $file = Yii::$app->basePath.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'application.properties';

        if(file_exists($file)) {

            $props = parse_ini_file($file);

            if(!empty($props)) {

                foreach ($data as $key => $value) {

                    $match = [];

                    if( preg_match('/^javaScheduler(\w+)$/', $key, $match) ) {

                        $match  = array_filter($match);
                        $match  = array_values($match);
                        $match  = array_key_exists(1, $match) ? mb_strtolower($match[1]) : '';
                        $pkey   = "sshd.shell.$match";

                        if( array_key_exists($pkey, $props) ) {
                            if( $props[$pkey] != $value ) {
                                $res[] = $pkey;
                            }
                        }

                    }

                }

            }

        }

        if( !empty($res) ) {
            \Y::flash('warning', Yii::t('config', 'Mismatched data in application.properties and database for following keys: <b>{0}</b>', join(', ', $res)));
        }

    }

    /**
     * Check if directory is Git repo
     *
     * @return bool
     */
    public static function isGitRepo()
    {
        $path_to_repo = \Y::param('dataPath') . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . '.git';
        return (file_exists($path_to_repo) || is_dir($path_to_repo)) ? true : false;
    }

    /**
     * Init git repository
     *
     * @param  string $user
     * @param  string $mail
     * @param  string $gitPath
     * @param  string $dataPath
     * @return bool
     * @throws \Exception
     */
    public static function runRepositoryInit($user, $mail, $gitPath, $dataPath)
    {

        $status = false;

        try {

            /** Init repository */
            $wrapper = new GitWrapper($gitPath);
            $git     = $wrapper->init($dataPath . DIRECTORY_SEPARATOR . 'backup');

            /** Set .git settings if repo successfully cloned */
            if ($git->isCloned()) {
                static::initGitSettings($user, $mail, $gitPath, $dataPath);
                $status = true;
            }

            return $status;

        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * Init Git settings based on DB saved values
     *
     * @param string $user
     * @param string $mail
     * @param string $gitPath
     * @param string $dataPath
     * @return bool
     * @throws \Exception
     */
    public static function initGitSettings($user, $mail, $gitPath, $dataPath)
    {
        try {

            /** Init GitWrapper */
            $wrapper = new GitWrapper($gitPath);

            /** Get working copy */
            $git = $wrapper->workingCopy($dataPath . DIRECTORY_SEPARATOR . 'backup');

            /** Set all necessary configuration */
            $git
                ->config('user.name', $user)
                ->config('user.email', $mail);

            if (\Y::param('gitRemote') == 1) {
                $git
                    ->config('push.default', 'simple')
                    ->config('remote.origin.url', static::prepareGitRepoUrl())
                    ->config('branch.master.remote', 'origin')
                    ->config('branch.master.merge', 'refs/heads/master');
            }

            return true;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Generate Git repository url
     *
     * @return string
     */
    private static function prepareGitRepoUrl()
    {
        $git_cred   = '';
        $parsed_url = parse_url(\Y::param('gitRepo'));
        $git_login  = \Y::param('gitLogin');
        $git_pass   = \Y::param('gitPassword');

        /** Generate git credentials */
        if (!is_null($git_login) && !is_null($git_pass)) {
            $git_cred = $git_login . ':' . $git_pass . '@';
        }

        return $parsed_url['scheme'] . '://' . $git_cred . $parsed_url['host'] . $parsed_url['path'];
    }

}
