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

namespace app\components\services;

use app\components\Service;
use app\components\ServiceMethods;


/**
 * @package app\components\services
 */
class Initd extends Service implements ServiceMethods
{

    /**
     * @inheritdoc
     */
    public function isServiceActive(): bool
    {

        $status = $this->ssh->exec('sudo /etc/init.d/cbackup status');
        $state  = [];

        if (preg_match('/.*\[0;3\dm(Running|Not).*/', $status, $state)) {
            if( array_key_exists(1, $state) ) {
                $state = $state[1];
            }
        }

        if( empty($state) ) {
            throw new \Exception($status);
        }

        return preg_match('/Running/', $state) ? true : false;

    }

    /**
     * @inheritdoc
     */
    public function start(): bool
    {
        $result = $this->ssh->exec('sudo /etc/init.d/cbackup start');

        if (!preg_match('/.*\[0;3\dm(Started).*/', $result)) {
            throw new \Exception($result);
        }

        return $this->isServiceActive();
    }

    /**
     * @inheritdoc
     */
    public function stop(): bool
    {
        $result = $this->ssh->exec('sudo /etc/init.d/cbackup stop');

        if (!preg_match('/.*\[0;3\dm(Stopped).*/', $result)) {
            throw new \Exception($result);
        }

        return !$this->isServiceActive();
    }

    /**
     * @inheritdoc
     */
    public function restart(): bool
    {
        $result = $this->ssh->exec('sudo /etc/init.d/cbackup restart');

        if (!preg_match('/.*\[0;3\dm(Stopped).+(Started).*/s', $result)) {
            throw new \Exception($result);
        }

        return $this->isServiceActive();
    }

}
