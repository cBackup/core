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

// Some PHP updates reset session save directory's permissions
$ssp = session_save_path();
if( !is_writable($ssp) ) {
    die("Session save path $ssp is not writable for PHP process");
}

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../helpers/Y.php');

$config = require(__DIR__ . '/../config/web.php');

if( !file_exists($config['basePath'].DIRECTORY_SEPARATOR.'install.lock') ) {
    header("Location: ./install/index.php");
    exit();
}

/** @noinspection PhpUnhandledExceptionInspection */
(new yii\web\Application($config))->run();
