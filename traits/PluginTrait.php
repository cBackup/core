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

namespace app\traits;

use Yii;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Application;
use app\models\Plugin;


/**
 * @package app\traits
 */
trait PluginTrait
{

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var string
     */
    private static $plugin_name = '';

    /**
     * Init plugin base settings
     *
     * @return  void
     * @throws \Exception
     */
    public function initPlugin()
    {

        /** Get plugin name */
        $class = (new \ReflectionClass($this))->getShortName();
        $name  = Inflector::underscore($class);

        /** Find plugin */
        $plugin = Plugin::find()->where(['name' => $name])->one();

        if (is_null($plugin) && \Yii::$app instanceof Application) {
            throw new NotFoundHttpException("Plugin {$name} not found.");
        }

        if ($plugin->enabled == 0 && \Yii::$app instanceof Application) {
            throw new ForbiddenHttpException("Plugin {$name} is disabled.");
        }

        /** Plugin params and translation are set via method @see Plugin::afterFind() */
        $this->params = array_merge($plugin->plugin_params, ['plugin_access' => $plugin->access, 'plugin_enabled' => $plugin->enabled]);
    }

    /**
     * Register plugin translations
     *
     * @param string $name
     * @param array $files
     *
     * @return void
     */
    public function registerTranslations($name, $files = [])
    {
        self::$plugin_name = strtolower(Inflector::camelize($name));

        Yii::$app->i18n->translations['modules/plugins/' . self::$plugin_name . '/*'] = [
            'class'          => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath'       => '@app/modules/plugins/' . self::$plugin_name . '/messages',
            'fileMap'        => $this->generateFileMap(self::$plugin_name, $files)
        ];
    }

    /**
     * Method Yii::t override
     *
     * @param  string $category
     * @param  string $message
     * @param  array $params
     * @param  null $language
     *
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/plugins/' . self::$plugin_name . '/' . $category, $message, $params, $language);
    }

    /**
     * Generate user custom file map for translations
     *
     * @param  string $name
     * @param  array $files
     *
     * @return array
     */
    private function generateFileMap($name, $files = [])
    {
        $default_file_map = [
            "modules/plugins/{$name}/general" => 'general.php'
        ];

        /** Generate custom file map */
        foreach ($files as $file) {
            $default_file_map["modules/plugins/{$name}/{$file}"] = "{$file}.php";
        }

        return $default_file_map;
    }

}
