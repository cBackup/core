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

$config = [

    'name'           => 'cBackup',
    'id'             => 'cBackup',
    'basePath'       => dirname(__DIR__),
    'sourceLanguage' => 'en-US',
    'defaultRoute'   => 'install/index',
    'layout'         => 'install',
    'version'        => require_once('version.php'),
    'bootstrap'      => ['log'],
    'aliases'        => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    'components' => [
        'request' => [
            'cookieValidationKey' => 'gdy82VYeW2-uPceUhWbGfej1bQA2OnYPswpoNLwsY',
        ],
        'urlManager' => [
            'enablePrettyUrl'     => false,
            'showScriptName'      => true
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource'
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'   => 'yii\log\FileTarget',
                    'levels'  => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST'],
                ],
            ],
        ],
        'as beforeRequest' => [
            'class' => 'app\behaviors\LanguageBehavior',
        ],
    ],

    'params' => require(__DIR__ . '/params.php'),

];


if( YII_ENV_TEST || YII_ENV_DEV ) {

    $config['components']['view'] = [
        'theme' => [
            'pathMap' => [
                '@vendor/yiisoft/yii2-debug/views/default' => '@app/views/install/debug'
            ],
        ],
    ];

    $config['bootstrap'][]      = 'debug';
    $config['modules']['debug'] = [
        'class'      => 'yii\debug\Module',
        'allowedIPs' => ['*'],
        'panels'     => [
            'db'   => null,
            'mail' => null,
            'user' => null,
        ]
    ];
}

return $config;
