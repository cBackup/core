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

use Yii;
use yii\base\Model;
use dautkom\netsnmp\NetSNMP;
use app\components\Telnet;
use phpseclib\Net\SSH2;


/**
 * @package app\models
 */
class CredentialTest extends Model
{

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $snmp_set;

    /**
     * @var string
     */
    public $snmp_read;

    /**
     * @var string
     */
    public $telnet_login;

    /**
     * @var string
     */
    public $telnet_password;

    /**
     * @var string
     */
    public $enable_password;

    /**
     * @var string
     */
    public $ssh_login;

    /**
     * @var string
     */
    public $ssh_password;

    /**
     * @var int
     */
    public $port_telnet;

    /**
     * @var int
     */
    public $port_ssh;

    /**
     * @var int
     */
    public $port_snmp;

    /**
     * @var string
     */
    public $snmp_encryption;

    /**
     * @var string
     */
    public $snmp_version;

    /**
     * @var string
     */
    public $login_prompt;

    /**
     * @var string
     */
    public $password_prompt;

    /**
     * @var string
     */
    public $enable_success;

    /**
     * @var string
     */
    public $enable_prompt;

    /**
     * @var string
     */
    public $main_prompt;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip'], 'required'],
            ['ip', 'ip', 'ipv4' => true, 'ipv6' => false, 'subnet' => false],
            [['port_telnet', 'port_ssh', 'port_snmp'], 'integer', 'min' => 1, 'max' => 65535],
            [['snmp_version'], 'in', 'range' => [0, 1]],
            [['telnet_login', 'telnet_password', 'ssh_login', 'ssh_password', 'snmp_read', 'snmp_set', 'snmp_encryption', 'enable_password', 'enable_prompt', 'enable_success', 'password_prompt', 'login_prompt', 'main_prompt'], 'string', 'max' => 128],
            [['telnet_login',  'telnet_password', 'ssh_login', 'ssh_password', 'snmp_encryption', 'enable_password', 'enable_prompt', 'enable_success', 'ip', 'snmp_version', 'snmp_read', 'snmp_set', 'port_telnet', 'port_ssh', 'port_snmp', 'password_prompt', 'login_prompt', 'main_prompt'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'telnet_login' => Yii::t('network', 'Telnet Login'),
        ];
    }

    /**
     * Perform tests
     *
     * @return array
     */
    public function run()
    {

        $snmpget       = 2;
        $snmpset       = 2;
        $ssh_status    = 2;
        $telnet_status = 2;
        $enable_status = 2;

        $err_level = error_reporting();
                     error_reporting(0);

        // SNMP
        if (!empty($this->snmp_read)) {
            $snmp_host = isset($this->port_snmp) ? "$this->ip:$this->port_snmp" : $this->ip;
            $snmp      = (new NetSNMP())->init($snmp_host, [$this->snmp_read, $this->snmp_set], $this->snmp_version);
            $snmpget   = intval(boolval($snmp->get('1.3.6.1.2.1.1.3.0')));

            /**
             * Here we write into a read-only OID iso.org.dod.internet.mgmt.mib-2.system.sysServices
             * If community is correct, the 'read-only' (errcode 8) error will be retruned.
             * In case of wrong community the 'timeout' (errcode 4) will be the result.
             */
            if (!empty($this->snmp_set)) {
                $snmp->set('1.3.6.1.2.1.1.7.0', 'i', '1');
                $snmpset = ($snmp->getErrno() == 8) ? 1 : 0;
            }
        }

        // Telnet
        if (!empty($this->login_prompt) && !empty($this->password_prompt) && !empty($this->ip) && !empty($this->port_telnet)) {

            $telnet_session = new Telnet();
            $telnet_session->setTimeout(3);

            try {

                /** Set default main prompt */
                $main_prompt = (!empty($this->main_prompt)) ? $this->main_prompt : '#';

                /** Check credentials */
                $telnet_status = $telnet_session
                    ->connect($this->ip, $this->port_telnet)
                    ->setPrompt("/{$main_prompt}/")
                    ->login($this->telnet_login, $this->telnet_password, [$this->login_prompt, $this->password_prompt])
                ;

                // enable test
                if ($telnet_status && !empty($this->enable_password) && !empty($this->enable_prompt) && !empty($this->enable_success)) {
                    $telnet_session->send('enable');
                    $telnet_session->waitfor("/{$this->enable_prompt}/")->send($this->enable_password);
                    $enable_result = trim($telnet_session->waitfor("/{$this->enable_success}/")->getData());
                    $enable_status = preg_match("/$this->enable_success/", $enable_result) ? 1 : 0;
                }

            } catch (\Exception $e) {
                $telnet_status = 0;
            } finally {
                $telnet_status = intval($telnet_status);
            }

        }

        // SSH
        if (!empty($this->port_ssh) && !empty($this->ssh_login) && !empty($this->ssh_password)) {

            $ssh_session = new SSH2($this->ip, $this->port_ssh, 2);
            $ssh_session->setTimeout(2);

            if ($ssh_session->login($this->ssh_login, $this->ssh_password)) {

                $ssh_status = 1;

                if( !empty($this->enable_password) ) {

                    $ssh_session->enablePTY();
                    $ssh_session->read("/{$this->enable_prompt}/");
                    $ssh_session->write("enable\n");
                    $ssh_session->read();
                    $ssh_session->write("{$this->enable_password}\n");

                    $enable_result = $ssh_session->read();
                    $enable_status = preg_match("/$this->enable_success/", $enable_result) ? 1 : 0;

                    $ssh_session->disablePTY();
                    $ssh_session->disconnect();

                }

            } else {
                $ssh_status = 0;
            }

            unset($ssh_session);

        }

        // restore error reporting level
        error_reporting($err_level);

        /**
         * Return values:
         * 0: false, marked as red cross
         * 1: success, green checkmark
         * 2: not implemented, black minus
         */
        return [
            'test-telnet'  => $telnet_status,
            'test-ssh'     => $ssh_status,
            'test-enable'  => $enable_status,
            'test-snmpget' => $snmpget,
            'test-snmpset' => $snmpset,
        ];

    }

}
