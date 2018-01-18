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

namespace app\logger;

use yii\log\DbTarget;


/**
 * @package app\logger
 */
class CDbTarget extends DbTarget
{

    /**
     * Default log model namaspece
     *
     * @var string
     */
    public $logNamespace = 'app\models';


    /**
     * @throws \Exception
     */
    public function export()
    {

        /** Message level map  */
        $levelMap = [1 => 'ERROR', 2 => 'WARNING', 4 => 'INFO'];

        foreach ($this->messages as $message) {

            if (array_key_exists(2, $message)) {

                $methods        = explode('.', $message[2]);
                $logNamespace   = (array_key_exists(2, $methods)) ? $methods[2] : $this->logNamespace;
                $method_name    = (array_key_exists(1, $methods)) ? $methods[1] : false;
                $this->logTable = $logNamespace . '\Log' . ucfirst($methods[0]);

                if( @class_exists($this->logTable)) {

                    $model = new $this->logTable;

                    if (method_exists($model, $method_name)) {
                        $level = (array_key_exists($message[1], $levelMap)) ? $levelMap[$message[1]] : $levelMap[4];
                        $model->$method_name($message[0], $level);
                    } else {
                        throw new \Exception("Method `{$method_name}` not found in class `{$this->logTable}`");
                    }

                } else {
                    throw new \Exception("Class `{$this->logTable}` not found.");
                }

            }

        }

    }

}
