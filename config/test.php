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

$params = require __DIR__ . '/params.php';
$db     = require __DIR__ . '/test_db.php';

return [
    'id'             => 'cbackup-tests',
    'sourceLanguage' => 'en-US',
    'basePath'       => dirname(__DIR__),
    'version'        => require_once('version.php'),
    'bootstrap'      => ['log'],
    'aliases'        => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
        'assetManager' => [            
            'basePath'        => __DIR__ . '/../web/assets',
            'appendTimestamp' => true
        ],
        'formatter' => [
            'defaultTimeZone' => "Europe/Riga",
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl'     => false,
            'enableStrictParsing' => false,
            'showScriptName'      => true,
            'rules'               => [
                [
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => ['v1/core', 'v2/node'],
                    'pluralize'  => false,
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'   => 'app\logger\CDbTarget',
                    'levels'  => ['info', 'error', 'warning'],
                    'except'  => ['yii*'],
                    'logVars' => []
                ],
                [
                    'class'   => 'yii\log\FileTarget',
                    'levels'  => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST'],
                ],
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'loginUrl'      => ['/user/login'],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource'
                ],
            ],
        ],
        'request' => [
            'cookieValidationKey'  => 'test',
            'enableCsrfValidation' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'authManager' => [
            'cache' => YII_DEBUG ? null : 'cache',
            'class' => 'yii\rbac\DbManager',
        ],
        'db' => $db,
    ],
    'params' => $params,
    'modules' => [

        /** Network module */
        'network' => [
            'class' => 'app\modules\network\Network'
        ],

        /** RESTful JavaCore API module */
        'v1' => [
            'class' => 'app\modules\v1\v1Module',
            'components' => [
                'output' => [
                    'class' => 'app\modules\v1\components\OutputProcessing',
                ],
            ]
        ],

    ],
];
