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

use yii\helpers\VarDumper;
use yii\helpers\Url;


/**
 * Shortcuts for Yii framework
 * Inspired by svyatov/yii-shortcut
 */
class Y
{

    /**
     * Shortcut for dump function of VarDumper class enframed by 'pre' tags
     *
     * @param  mixed $var
     * @param  boolean $end
     * @param  int $depth
     * @return void
     * @throws \yii\base\ExitException
     */
    public static function dump($var, $end = true, $depth = 10)
    {

        echo '<pre>';
        VarDumper::dump($var, $depth, true);
        echo '</pre>';

        if( $end ) {
            if( !isset(\Yii::$app) ) {
                die();
            }
            \Yii::$app->end();
        }

    }


    /**
     * Stores a flash message available only in the current and the next requests.
     *
     * @param  string $key
     * @param  string $message
     * @return void
     */
    public static function flash($key, $message)
    {
        \Yii::$app->getSession()->setFlash($key, $message);
    }


    /**
     * Stores a flash message and redirects to specified route
     *
     * @param  string $key
     * @param  string $message
     * @param  string $route
     * @param  array  $params
     * @return void
     */
    public static function flashAndRedirect($key, $message, $route, $params = [])
    {
        \Yii::$app->getSession()->setFlash($key, $message, false);
        array_unshift($params, $route);
        \Yii::$app->getResponse()->redirect(Url::toRoute($params));
    }

    /**
     * Returns user-defined application parameter
     *
     * @param string $key key identifying the parameter (could be used dot delimiter for nested key)
     * Example: 'Media.Foto.thumbsize' will return value at ['Media']['Foto']['thumbsize']
     * @param  mixed $defaultValue the default value to be returned when the parameter variable does not exist
     * @return mixed
     */
    public static function param($key, $defaultValue = null)
    {
        return self::getValueByComplexKeyFromArray($key, \Yii::$app->params, $defaultValue);
    }

    /**
     * Returns the array variable value or $defaultValue if the array variable does not exist
     *
     * @param string $key the array variable name (could be used dot delimiter for nested variable)
     * Example: variable name 'Media.Foto.thumbsize' will return value at $array['Media']['Foto']['thumbsize']
     * @param array $array an array containing variable to return
     * @param mixed $defaultValue the default value to be returned when the array variable does not exist
     * @return mixed
     */
    public static function getValueByComplexKeyFromArray($key, $array, $defaultValue = null)
    {
        if (strpos($key, '.') === false) {
            return (isset($array[$key])) ? $array[$key] : $defaultValue;
        }

        $keys = explode('.', $key);
        $firstKey = array_shift($keys);

        if (!isset($array[$firstKey])) {
            return $defaultValue;
        }

        $value = $array[$firstKey];

        foreach ($keys as $k) {
            if (!isset($value[$k]) && !array_key_exists($k, $value)) {
                return $defaultValue;
            }
            $value = $value[$k];
        }

        return $value;
    }

}
