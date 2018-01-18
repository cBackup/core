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

use Cron\CronExpression;
use app\models\Setting;

/**
 * Collection of custom helpers to format and beautify strings
 *
 * @package app\helpers
 */
class StringHelper extends \yii\helpers\StringHelper
{

    /**
     * An analog of function ucwords for names, surnames and initials
     *
     * @param  string $name
     * @return string
     */
    public static function ucname($name)
    {

        $name = ucwords(mb_strtolower($name));

        foreach (['-', '\'', '.'] as $delimiter) {
            if (mb_strpos($name, $delimiter)!==false) {
                $name = implode($delimiter, array_map('ucfirst', explode($delimiter, $name)));
            }
        }

        return $name;

    }


    /**
     * Convert bytes to human-readable format
     *
     * @param  int    $bytes
     * @param  int    $precision
     * @param  bool   $append
     * @return string
     */
    public static function beautifySize($bytes, $precision=2, $append=true)
    {

        $units = array('B', 'KB', 'MB', 'GB');
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes/= pow(1024, $pow);
        $tail  = ($append) ? ' '.$units[$pow] : '';

        return round($bytes, $precision) . $tail;

    }


    /**
     * @param  string $mac
     * @param  string $delimeter
     * @return string
     */
    public static function beautifyMac(string $mac, string $delimeter = ':'): string
    {
        return strtoupper(
            join($delimeter,
                str_split(
                    preg_replace('/[^a-f0-9]/im', '', $mac), 2
                )
            )
        );
    }


    /**
     * @param  string $cron
     * @return string
     */
    public static function cronNextRunDate(string $cron): string
    {
        $cron_expression = CronExpression::factory($cron);
        return $cron_expression->getNextRunDate()->format(Setting::get('datetime'));
    }

}
