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

namespace app\models;

use app\helpers\SystemHelper;


/**
 * @package app\models
 */
class Sysinfo
{

    /**
     * @var string PHP info
     */
    protected $php_info = null;


    /**
     * Method to get the PHP info
     *
     * @return string PHP info
     */
    public function &getPhpInfo()
    {

        if (is_null($this->php_info)) {

            ob_start();
            phpinfo();
            $phpinfo = array('phpinfo' => array());

            /** @noinspection HtmlUnknownTag */
            if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER)) {

                foreach($matches as $match) {

                    if(strlen($match[1])) {
                        $phpinfo[$match[1]] = array();
                    }

                    elseif(isset($match[3])) {
                        $keys = array_keys($phpinfo);
                        $phpinfo[end($keys)][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
                    }

                    else {
                        $keys = array_keys($phpinfo);
                        $phpinfo[end($keys)][] = $match[2];
                    }

                }

            }

            // Remove init logo
            unset($phpinfo['phpinfo'][0]);

            // Set property
            $this->php_info = $phpinfo;

        }

        return $this->php_info;

    }


    /**
     * @return string|null
     */
    public static function getJavaVersion()
    {

        $java = SystemHelper::exec('java -version');

        if( $java->exitcode ) {
            return null;
        }
        else {
            return $java->stdout.$java->stderr;
        }

    }


    /**
     * @return string|null
     */
    public static function getGitVersion()
    {

        $git = SystemHelper::exec('git version');

        if( $git->exitcode ) {
            return null;
        }
        else {
            return $git->stdout;
        }

    }

}
