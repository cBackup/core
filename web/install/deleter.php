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

error_reporting(0);

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

if(isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], 'finalize') !== false) {
    try {
        \yii\helpers\FileHelper::removeDirectory(__DIR__ . '/../../install');
        \yii\helpers\FileHelper::removeDirectory(__DIR__.'/../../views/install');
        \yii\helpers\FileHelper::removeDirectory(__DIR__.'/../../migrations');
        unlink(__DIR__.'/../../config/install.php');
        unlink(__DIR__.'/../../views/layouts/install.php');
        unlink(__DIR__.'/../../controllers/InstallController.php');
        \yii\helpers\FileHelper::removeDirectory(__DIR__);
    } catch (\yii\base\ErrorException $e) {}
}

header("Location: ../index.php");
