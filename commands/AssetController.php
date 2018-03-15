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

namespace app\commands;

use yii\base\ErrorException;
use yii\console\ExitCode;
use yii\helpers\FileHelper;


/**
 * Allows you to flush assets, to combine and compress your JavaScript and CSS files
 * @package app\commands
 */
class AssetController extends \yii\console\controllers\AssetController
{

    /**
     * Flushes all assets in ./web/assets folder
     * @return int
     */
    public function actionFlushAll()
    {

        $assdir = \Yii::getAlias('@app').DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'assets';

        if(!file_exists($assdir) || !is_dir($assdir)) {
            $this->stderr("Directory $assdir doesn't exist, check your asset path\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $assets = FileHelper::findDirectories($assdir, ['recursive' => false]);
        $status = true;

        if (!empty($assets)) {

            foreach ($assets as $asset) {
                try {
                    FileHelper::removeDirectory($asset);
                }
                catch (ErrorException $e) {
                    $status = false;
                }
            }

        }

        if($status === true) {
            $this->stdout("Old assets where successfully removed\n");
            return ExitCode::OK;
        }
        else {
            $this->stderr("Unexpected error while removing assets, check $assdir\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

    }

}
