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

$params = require(__DIR__ . '/params.php');
$db     = require(__DIR__ . '/db.php');

if (file_exists(__DIR__ . '/settings.ini')) {
    $ini = parse_ini_file(__DIR__ . '/settings.ini');
}
else {
    $ini = ['cookieValidationKey' => 'gdy82VYeW2-uPceUhWbGfej1bQA2OnYPswpoNLwsY', 'defaultTimeZone' => 'UTC'];
}

$config = [

    'name'           => 'cBackup',
    'id'             => 'cBackup',
    'basePath'       => dirname(__DIR__),
    'version'        => require_once('version.php'),
    'bootstrap'      => ['log', 'app\helpers\ConfigHelper'],
	'aliases'        => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    'components' => [
        'request' => [
            'cookieValidationKey' => $ini['cookieValidationKey'],
            'parsers' => [
                'application/json'                => 'yii\web\JsonParser',
                'application/json; charset=UTF-8' => 'yii\web\JsonParser',
            ]
        ],
        'assetManager' => [
            'appendTimestamp' => true
        ],
        'formatter' => [
            'defaultTimeZone' => $ini['defaultTimeZone'],
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
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl'        => ['/user/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
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
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource'
                ],
            ],
        ],
        'authManager' => [
            'cache' => YII_DEBUG ? null : 'cache',
            'class' => 'yii\rbac\DbManager',
        ],
        'db' => $db,
    ],
    'as maintenanceMode' => [
        'class'         => 'app\behaviors\MaintenanceBehavior',
        'redirectUri'   => 'site/maintenance',
        'ignoredRoutes' => [
            '/\/index\.php\?r=debug.+/im',
            '/\/index\.php\?r=update.+/im'
        ],
    ],
    'as beforeRequest' => [
        'class' => 'app\behaviors\LanguageBehavior',
    ],
    'as AccessBehavior' => [
        'class'         => 'app\behaviors\AccessBehavior',
        'allowedRoutes' => [
            '/\/index\.php\?r=debug.+/im',
            '/\/index\.php\?r=v1.+/im',
            '/\/index\.php\?r=v2.+/im',
        ],
    ],
    'params' => $params,
    'modules' => [

        /** RESTful JavaCore API module */
        'v1' => [
            'class' => 'app\modules\v1\v1Module',
            'components' => [
                'output' => [
                    'class' => 'app\modules\v1\components\OutputProcessing',
                ],
            ]
        ],

        /** RESTful API module */
        'v2' => [
            'class' => 'app\modules\v2\v2Module',
        ],

        /** Access control module */
        'rbac' => [
            'class'        => 'app\modules\rbac\Rbac',
            'defaultRoute' => 'access'
        ],

        /** Network module */
        'network' => [
            'class'        => 'app\modules\network\Network',
            'defaultRoute' => 'subnet'
        ],

        /** Log module */
        'log' => [
            'class' => 'app\modules\log\Log',
        ],

        /** Mailer UI module */
        'mail' => [
            'class'        => 'app\modules\mail\Mail',
            'defaultRoute' => 'events'
        ],

        /** Custom plugins module */
        'plugins' => [
            'class' => 'app\modules\plugins\Plugins',
        ],

        /** Content delivery system */
        'cds' => [
            'class' => 'app\modules\cds\Cds',
        ],
    ],
];

if( YII_ENV_TEST || YII_ENV_DEV ) {
    $config['bootstrap'][]      = 'debug';
    $config['modules']['debug'] = [
        'class'      => 'yii\debug\Module',
        'allowedIPs' => ['*'],
        'panels'     => [
            'user' => [
                'class' => 'yii\debug\panels\UserPanel',
                'ruleUserSwitch' => [
                    'allow' => true,
                    'roles' => ['admin'],
                ]
            ]
        ]
    ];
}

if (YII_ENV_DEV) {
    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
        'allowedIPs' => ['*']
    ];
}

return $config;
