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

namespace app\components;

use phpseclib\File\ANSI;
use phpseclib\Net\SSH2;
use yii\helpers\Json;

/**
 * @package app\components
 */
class NetSsh
{

    /**
     * @var \phpseclib\Net\SSH2
     */
    private $ssh;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $timeout = 2;

    /**
     * NetSsh constructor.
     */
    public function __construct()
    {
        $this->ip       = \Y::param('javaHost');
        $this->port     = \Y::param('javaSchedulerPort');
        $this->username = \Y::param('javaSchedulerUsername');
        $this->password = \Y::param('javaSchedulerPassword');
    }

    /**
     * Init SSH wrapper
     *
     * @param  array $options
     * @return $this
     * @throws \Exception
     */
    public function init(array $options = [])
    {
        /** Set custom init options */
        array_walk($options, function($value, $option) { $this->$option = $value; });

        /** Connect to device */
        $this->ssh = new SSH2($this->ip, $this->port, $this->timeout);

        /** Show exception if can not login */
        if (!$this->ssh->login($this->username, $this->password)) {
            throw new \Exception("Authentication failed. Host:{$this->ip}:{$this->port}. Check SSH credentials");
        }

        return $this;
    }

    /**
     * Execute command using exec command
     *
     * @param  string $command
     * @return string
     */
    public function exec(string $command)
    {
        return trim($this->ssh->exec($command));
    }

    /**
     * Execute command by parsing terminal output
     *
     * @param  string $command
     * @return array
     * @throws \Exception
     */
    public function schedulerExec(string $command):array
    {
        /** Execute command */
        $this->ssh->read('/.*[>]\s$/', $this->ssh::READ_REGEX);
        $this->ssh->write("{$command}\n");
        $this->ssh->setTimeout(10);

        /** Read command output */
        $output = $this->ssh->read('/.*[>]\s$/', $this->ssh::READ_REGEX);

        /** Show console output if error occurs */
        if (!preg_match('/{.*}/i', $output, $json)) {
            $ansi = new ANSI();
            $ansi->appendString($output);
            $prep_output = htmlspecialchars_decode(strip_tags($ansi->getScreen()));
            $error_array = explode("\n", $prep_output);
            $error_text  = (array_key_exists(1, $error_array) && !empty($error_array[1])) ? $error_array[1] : $prep_output;
            throw new \Exception($error_text);
        }

        return Json::decode($json[0]);
    }

}
