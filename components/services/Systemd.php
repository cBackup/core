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
class Systemd extends Service implements ServiceMethods
{

    /**
     * @inheritdoc
     */
    public function isServiceActive(): bool
    {
        /**
         * Due to  https://bugzilla.redhat.com/show_bug.cgi?id=1073481 we can't
         * rely on `systemctl is-active cbackup.service`. It's possible to read
         * output of `systemctl show cbackup -p ActiveState` but still we won't
         * be able to receive  an error if service is not present in the system
         * at all. That's why we parse `status`
         */
        $status = $this->ssh->exec('sudo systemctl status cbackup');
        $state  = [];

        if (preg_match('/^\s*Active:\s?(active|inactive|failed|unknown)/im', $status, $state)) {
            if( array_key_exists(1, $state) ) {
                $state = $state[1];
            }
        }

        if( empty($state) ) {
            throw new \Exception($status);
        }

        return preg_match('/^active$/i', $state) ? true : false;

    }

    /**
     * @inheritdoc
     */
    public  function start(): bool
    {
        $result = $this->ssh->exec('sudo systemctl start cbackup');

        if (!empty($result)) {
            throw new \Exception($result);
        }

        return $this->isServiceActive();
    }

    /**
     * @inheritdoc
     */
    public function stop(): bool
    {
        $result = $this->ssh->exec('sudo systemctl stop cbackup');

        if (!empty($result)) {
            throw new \Exception($result);
        }

        return !$this->isServiceActive();
    }

    /**
     * @inheritdoc
     */
    public  function restart(): bool
    {
        $result = $this->ssh->exec('sudo systemctl restart cbackup');

        if (!empty($result)) {
            throw new \Exception($result);
        }

        return $this->isServiceActive();
    }

}
