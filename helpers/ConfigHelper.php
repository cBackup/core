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

namespace app\helpers;

use Yii;
use yii\base\BootstrapInterface;
use yii\db\Query;


/**
 * @package app\helpers
 */
class ConfigHelper implements BootstrapInterface
{

    /**
     * @var \yii\db\Connection
     */
    private $db;


    /**
     * @var string
     */
    private static $template = '
        <div class="col-md-8 settings-label">
            <span class="text-bold">{label}</span><br>
            <small class="text-muted">{description}</small>
        </div>
        <div class="col-md-4">
            <div class="form-group no-error">
                {input}
            </div>
        </div>
    ';


    /**
     * ConfigHelper constructor.
     */
    public function __construct() {
        $this->db = Yii::$app->db;
    }


    /**
     * @param \yii\base\Application $application
     */
    public function bootstrap($application)
    {

        $config = Yii::$app->cache->getOrSet('config_data', function() {
            return (new Query())->select('key, value')->from('{{%config}}')->all();
        });

        foreach ($config as $key => $val) {
            Yii::$app->params[$val['key']] = $val['value'];
        }

    }


    /**
     * @param  string $name
     * @param  array  $errors
     * @param  array  $params
     * @param  string $template
     * @return string
     */
    public static function formGroup(string $name, array $errors, array $params, $template = null): string
    {

        if( !isset($template) ) {
            $template = self::$template;
        }

        $result = preg_replace_callback('/{(\w+)}/', function($match) use ($params, $template, $errors) {
            return $params[$match[1]];
        }, $template);

        if( array_key_exists($name, $errors) ) {
            $result = str_replace('form-group no-error', 'form-group has-error', $result);
            $result.= '
                <div class="col-md-12">
                    <div class="form-group has-error">
                        <div class="help-block">'.join('<br>', $errors[$name]).'</div>
                    </div>
                </div>
            ';
        }

        return $result;

    }

}
