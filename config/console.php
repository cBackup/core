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

Yii::setAlias('@tests', dirname(__DIR__) . '/tests/codeception');

require(__DIR__ . '/../helpers/Y.php');
$params = require(__DIR__ . '/params.php');
$db     = require(__DIR__ . '/db.php');

if (file_exists(__DIR__ . '/settings.ini')) {
    $ini = parse_ini_file(__DIR__ . '/settings.ini');
}
else {
    $ini = ['defaultTimeZone' => 'UTC'];
}

$config = [

    'id'                  => 'cBackup-console',
    'basePath'            => dirname(__DIR__),
    'version'             => require_once('version.php'),
    'sourceLanguage'      => 'en-US',
    'controllerNamespace' => 'app\commands',
    'bootstrap'           => ['log', 'app\helpers\ConfigHelper'],

    'controllerMap' => [
        'message' => [
            'class' => 'app\commands\MessageController',
        ],
        'asset' => [
            'class' => 'app\commands\AssetController',
        ],
    ],

    'modules' => [
        'plugins' => [
            'class' => 'app\modules\plugins\Plugins',
        ],
    ],

    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'formatter' => [
            'defaultTimeZone' => $ini['defaultTimeZone'],
        ],
        'log' => [
            'targets' => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class'   => 'app\logger\CDbTarget',
                    'levels'  => ['info', 'error', 'warning'],
                    'except'  => ['yii*'],
                    'logVars' => []
                ],
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'db' => $db,
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = ['class' => 'yii\gii\Module'];
}

return $config;
