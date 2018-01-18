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

use app\controllers\InstallController;
use app\helpers\SystemHelper;
use Yii;
use yii\base\Model;
use DateTime;
use DateTimeZone;


/**
 * @property $syspassword string
 * @property $timezone    string
 * @property $schema      string
 * @property $username    string
 * @property $password    string
 * @property $gitpath     string
 * @property $email       string
 * @property $host        string
 * @property $port        integer
 * @property $path        string
 *
 * @package app\models
 */
class Install extends Model
{

    /**
     * @var string
     */
    public $syspassword;

    /**
     * @var string
     */
    public $timezone;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $gitpath;

    /**
     * @var string
     */
    public $schema;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * @var string
     */
    public $path;

    /**
     * @var int
     */
    public $java_port;

    /**
     * @var int
     */
    public $threads;

    /**
     * @var string
     */
    public $java_username;

    /**
     * @var string
     */
    public $java_password;

    /**
     * @var int
     */
    public $server_port;

    /**
     * @var string
     */
    public $server_login;

    /**
     * @var string
     */
    public $server_password;

    /**
     * @var string
     */
    public $systeminit;


    /**
     * @inheritdoc
     */
    public function init()
    {

        parent::init();

        if( mb_stripos(PHP_OS, 'WIN') !== false ) {
            $git = SystemHelper::exec('where git');
        }
        if( mb_stripos(PHP_OS, 'Linux') !== false ) {
            $git = SystemHelper::exec('which git');
        }

        if( isset($git) && !$git->exitcode && !empty($git->stdout) ) {
            $this->gitpath = explode("\n", $git->stdout);
            $this->gitpath = trim($this->gitpath[0]);
        }

    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['username', 'gitpath', 'syspassword', 'email', 'host', 'path', 'schema', 'threads', 'java_username',
                    'java_password', 'java_port', 'server_port', 'server_login', 'server_password', 'systeminit'
                ], 'required'
            ],
            [['email'], 'email'],
            [['gitpath'], function($attribute, /** @noinspection PhpUnusedParameterInspection */ $params) {
                if (!file_exists($this->$attribute)) {
                    $this->addError($attribute, Yii::t('install', "Could not find file"));
                }
            }],
            [['path'], function($attribute, /** @noinspection PhpUnusedParameterInspection */ $params) {
                if (!file_exists($this->$attribute) || !is_dir($this->$attribute)) {
                    $this->addError($attribute, Yii::t('install', "Could not find directory"));
                }
            }],
            [['schema'], 'match', 'pattern' => '/^[a-z0-9_]+$/i'],
            [['port', 'server_port', 'java_port'], 'integer', 'max' => 65535, 'min' => 1],
            [['threads'], 'integer', 'max' => 1000, 'min' => 1],
            [['server_port'], 'compare', 'compareAttribute' => 'java_port', 'operator' => '!=', 'type' => 'number'],
            [['systeminit'], 'in', 'range' => ['system.d', 'init.d']],
            [
                [
                    'syspassword', 'timezone', 'username', 'password', 'gitpath', 'email', 'host', 'path', 'port',
                    'schema', 'threads', 'java_port', 'java_password', 'java_username', 'server_password',
                    'server_port', 'server_login', 'systeminit'
                ], 'safe'
            ]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'syspassword'     => Yii::t('app', 'Password'),
            'username'        => Yii::t('app', 'Username'),
            'timezone'        => Yii::t('app', 'Time zone'),
            'password'        => Yii::t('app', 'Password'),
            'gitpath'         => Yii::t('config', 'Path to the Git executable'),
            'email'           => Yii::t('app', 'E-mail'),
            'host'            => Yii::t('install', 'Host'),
            'port'            => Yii::t('network', 'Port'),
            'path'            => Yii::t('config', 'Path to storage folder'),
            'schema'          => Yii::t('install', 'Database name'),
            'threads'         => Yii::t('install', 'Daemon threads count'),
            'java_username'   => Yii::t('install', 'Daemon SSH username'),
            'java_password'   => Yii::t('install', 'Daemon SSH password'),
            'java_port'       => Yii::t('install', 'Daemon SSH port binding'),
            'server_password' => Yii::t('config', 'SSH password'),
            'server_port'     => Yii::t('network', 'SSH port'),
            'server_login'    => Yii::t('config', 'SSH login'),
            'systeminit'      => Yii::t('install', 'Service init system'),
        ];
    }


    /**
     * Checks if cBackup has access to the internet. Test is performed
     * against three predefined resources via cURL. Shorter version with
     * get_headers() methods works significantly slower.
     *
     * If at least one resource is available, method returns TRUE for
     * success. Result is used in InstallController::actionIntegrity()
     * and is saved to the database table `config` as 'isolated' var
     *
     * @see InstallController::actionIntegrity()
     * @return bool
     */
    public static function checkInternet(): bool
    {

        $urls = ['http://example.com', 'https://github.com', 'https://google.com'];
        $ch   = [];    // array of curl handlers
        $res  = false; // method result
        $exec = null;  // curl_multi_exec runner

        $curl = curl_version();
        $mh   = curl_multi_init();

        foreach($urls as $key => $url) {

            $ch[$key] = curl_init($url);

            curl_setopt_array($ch[$key], [
                CURLOPT_TIMEOUT        => 1,
                CURLOPT_NOBODY         => true,
                CURLOPT_HEADER         => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            if(version_compare(PHP_VERSION, '7.0.7') >= 0 && version_compare($curl['version'], '7.41.0') >= 0) {
                curl_setopt($ch[$key], CURLOPT_SSL_VERIFYSTATUS, false);
            }

            curl_multi_add_handle($mh, $ch[$key]);

        }

        do {
            curl_multi_exec($mh, $exec);
            curl_multi_select($mh);
        } while ($exec > 0);

        foreach(array_keys($ch) as $key) {

            $code = curl_getinfo($ch[$key], CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($mh, $ch[$key]);

            if($code < 400 && $code >= 200) {
                $res = true;
            }

        }

        curl_multi_close($mh);
        return $res;

    }


    /**
     * Ensure protected locations are not visible from outside.
     *
     * Returns TRUE if files that are not supposed to be accessible
     * are world-visible.
     *
     * @return bool
     */
    public static function checkWorldAccess()
    {

        if( function_exists('curl_init') ) {

            $url  = preg_replace('/install/', '', Yii::$app->request->baseUrl);
            $url  = rtrim($url, "/");
            $curl = curl_version();

            if( isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ) {
                $url = "https://".$_SERVER['HTTP_HOST']."$url/../README.md";
            }
            else {
                $url = "http://".$_SERVER['HTTP_HOST']."$url/../README.md";
            }

            $ch = curl_init();
                  curl_setopt_array($ch, [
                      CURLOPT_URL              => $url,
                      CURLOPT_HEADER           => true,
                      CURLOPT_RETURNTRANSFER   => true,
                      CURLOPT_NOBODY           => true,
                      CURLOPT_SSL_VERIFYHOST   => false,
                      CURLOPT_SSL_VERIFYPEER   => false,
                  ]);
                  if(version_compare(PHP_VERSION, '7.0.7') >= 0 && version_compare($curl['version'], '7.41.0') >= 0) {
                      curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, false);
                  }
                  curl_exec($ch);
            $ci = curl_getinfo($ch);
                  curl_close($ch);

            if($ci['http_code'] == 0) {
                /** @noinspection HtmlUnknownTarget */
                \Y::flash('warning', 'Unable to determine if data outside your /web folder is world-accessible, please check it (e.g. <a href="../../LICENSE.md">this link</a> should not be accessible) and fix if necessary');
            }

            if( in_array($ci['http_code'], [200, 302, 304]) ) {
                return true;
            }

            return false;

        }

        return null;

    }


    /**
     * Check files and folders permissions
     *
     * @return array
     */
    public static function checkPermissions()
    {

        /**
         * lockedonly   should exist only on installed system if true
         * path         absolute path to the file or folder
         * writable     null: doesn't matter; true: should be writable; false: should not be writable
         * executable   true: should be writable; false: should not be writable
         *              can be associative array ["Linux" => bool, "WIN" => bool] with corresponding
         *              meaning per each system
         */
        $locations = [
            [
                'lockedonly' => false,
                'path'       => Yii::$app->basePath,
                'writable'   => true,
                'executable' => [
                    'Linux' => true,
                    'WIN'   => false
                ],
            ],
            [
                'lockedonly' => false,
                'path'       => Yii::$app->runtimePath,
                'writable'   => true,
                'executable' => [
                    'Linux' => true,
                    'WIN'   => false
                ],
            ],
            [
                'lockedonly' => false,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'assets',
                'writable'   => true,
                'executable' => [
                    'Linux' => true,
                    'WIN'   => false
                ],
            ],
            [
                'lockedonly' => false,
                'path'       => Yii::$app->session->get('path'),
                'writable'   => true,
                'executable' => [
                    'Linux' => true,
                    'WIN'   => false
                ],
            ],
            [
                'lockedonly' => false,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'config',
                'writable'   => true,
                'executable' => [
                    'Linux' => true,
                    'WIN'   => false
                ],
            ],
            [
                'lockedonly' => false,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'bin',
                'writable'   => true,
                'executable' => [
                    'Linux' => true,
                    'WIN'   => false
                ],
            ],
            [
                'lockedonly' => true,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'db.php',
                'writable'   => null,
                'executable' => false,
            ],
            [
                'lockedonly' => true,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'settings.ini',
                'writable'   => null,
                'executable' => false,
            ],
            [
                'lockedonly' => false,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'cbackup.jar',
                'writable'   => null,
                'executable' => [
                    'Linux' => true,
                    'WIN'   => false
                ],
            ],
            [
                'lockedonly' => true,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'application.properties',
                'writable'   => true,
                'executable' => false,
            ],
            [
                'lockedonly' => false,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'yii',
                'writable'   => null,
                'executable' => [
                    'Linux' => true,
                    'WIN'   => false
                ],
            ],
            [
                'lockedonly' => false,
                'path'       => Yii::$app->basePath.DIRECTORY_SEPARATOR.'yii.bat',
                'writable'   => null,
                'executable' => false,
            ]
        ];


        for($i=0; $i<count($locations); $i++) {

            $locations[$i]['errors'] = [
                'r' => false,
                'w' => false,
                'x' => false,
            ];

            if($locations[$i]['lockedonly']===true && !file_exists(Yii::$app->basePath.DIRECTORY_SEPARATOR.'install.lock')) {
                $locations[$i]['path'] = null;
                continue;
            }

            if( !file_exists($locations[$i]['path']) ) {
                $locations[$i]['errors']['r'] = true;
                $locations[$i]['errors']['w'] = true;
                $locations[$i]['errors']['x'] = true;
            }
            else {
                if( !is_null($locations[$i]['writable']) && is_writable($locations[$i]['path']) !== $locations[$i]['writable'] ) {
                    $locations[$i]['errors']['w'] = true;
                }
                if( !is_array($locations[$i]['executable']) ) {
                    if( is_executable($locations[$i]['path']) !== $locations[$i]['executable'] ) {
                        $locations[$i]['errors']['x'] = true;
                    }
                }
                else {
                    foreach ($locations[$i]['executable'] as $os => $executable) {
                        if( mb_stripos(PHP_OS, $os) !== false ) {
                            if( $executable !== is_executable($locations[$i]['path']) ) {
                                $locations[$i]['errors']['x'] = true;
                            }
                        }
                    }
                }
            }
        }

        return $locations;

    }


    /**
     * Generate array for timezone dropdown
     *
     * @return array
     */
    public static function getTimezoneList()
    {
        $timezones = [];
        $regions   = [
            'Africa'     => DateTimeZone::AFRICA,
            'America'    => DateTimeZone::AMERICA,
            'Antarctica' => DateTimeZone::ANTARCTICA,
            'Aisa'       => DateTimeZone::ASIA,
            'Atlantic'   => DateTimeZone::ATLANTIC,
            'Europe'     => DateTimeZone::EUROPE,
            'Indian'     => DateTimeZone::INDIAN,
            'Pacific'    => DateTimeZone::PACIFIC
        ];

        foreach($regions as $name => $mask) {

            $zones = DateTimeZone::listIdentifiers($mask);

            foreach($zones as $timezone) {
                $time = new DateTime(null, new DateTimeZone($timezone));
                $desc = sprintf("%s (GMT %+03d:00)", $timezone, ($time->getOffset()/3600)+0);
                $desc = str_replace('_', ' ', $desc);
                $timezones[$name][$timezone] = $desc;
            }

        }

        return $timezones;

    }


    /**
     * @return array
     */
    public static function getPhpExtensions()
    {

        $result     = [];
        $extensions = ['mbstring', 'snmp', 'SSH2', 'Reflection', 'pcre', 'spl', 'ctype', 'openssl', 'intl', 'mysqlnd', 'pdo_mysql', 'PDO', 'gmp', 'curl', 'zip'];
        natcasesort($extensions);

        foreach ($extensions as $extension) {
            $result[$extension] = extension_loaded($extension);
        }

        return $result;

    }


    /**
     * @return int
     */
    public static function estimatePerformance()
    {

        $cores   = 1;
        $matches = [];

        if (is_file('/proc/cpuinfo') && is_readable('/proc/cpuinfo')) {
            $cpuinfo = @file_get_contents('/proc/cpuinfo');
            if(preg_match_all('/^processor/m', $cpuinfo, $matches)) {
                $cores = count($matches[0]);
            }
        }
        elseif ( mb_stripos(PHP_OS, 'WIN') !== false ) {
            $process = @popen('wmic cpu get NumberOfCores', 'rb');
            if (isset($process) && $process !== false) {
                fgets($process);
                $cores = intval(fgets($process));
                pclose($process);
            }
        }
        else {
            $process = @popen('sysctl -a', 'rb');
            if ($process !== false) {
                $output = @stream_get_contents($process);
                if (preg_match('/hw.ncpu: (\d+)/', $output, $matches)) {
                    if (!empty($matches) && array_key_exists(1, $matches) && array_key_exists(0, $matches[1])) {
                        $cores = intval($matches[1][0]);
                    }
                }
                pclose($process);
            }
        }

        return intval((pow($cores, 1/7) + 1.28) * 10);

    }


    /**
     * @return string
     */
    public static function getDocumentUri(): string
    {

        $uri = empty($_SERVER['PHP_SELF']) ? $_SERVER['DOCUMENT_URI'] : $_SERVER['PHP_SELF'];

        if( !preg_match('/index\.php$/i', $uri) ) {
            $uri = rtrim($uri, '/') . "/index.php";
        }

        return $uri;

    }

}
