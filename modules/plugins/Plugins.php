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

namespace app\modules\plugins;

use \yii\base\Module;


/**
 * @package app\modules\plugins
 */
class Plugins extends Module
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /**
         * Get/Set plugin clases array from/to cache
         * Cache will be flushed when plugin is installed/deleted
         * @see \app\models\Plugin::afterSave()
         * @see \app\models\Plugin::afterDelete()
         */
        $modules = \Yii::$app->cache->getOrSet('pluginmodules', function() {
            return $this->generateModules();
        });

        /** Set modules */
        $this->modules = $modules;
    }


    /**
     * Generate plugin class array
     *
     * @return array
     */
    private function generateModules()
    {
        $modules     = [];
        $plugins_dir = \Yii::getAlias('@app'). DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'plugins';
        $main_dirs   = new \DirectoryIterator($plugins_dir);

        /** Generate modules */
        foreach ($main_dirs as $dir) {
            if ($dir->isDir() && !$dir->isDot()) {
                $plugin_name = $dir->getFilename();
                $sub_dirs    = new \DirectoryIterator($plugins_dir . DIRECTORY_SEPARATOR . $plugin_name);
                foreach ($sub_dirs as $file) {
                    $class_name = [];
                    if ($file->isFile() && !$file->isDot()) {
                        if (preg_match("/^{$plugin_name}(?=\.php)/i", $file->getFilename(), $class_name)) {
                            $modules[$plugin_name]['class'] = "app\\modules\\plugins\\" . $plugin_name . "\\"  . $class_name[0];
                        }
                    }
                }
            }
        }

        return $modules;
    }

}
