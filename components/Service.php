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

use Yii;


/**
 * @package app\components
 */
class Service
{

    /**
     * @var $ssh NetSsh
     */
    protected $ssh;

    /**
     * @var string
     */
    private $service_type;

    /**
     * @var array
     */
    private $supported_services = ['system.d', 'init.d'];

    /**
     * Services constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $options   = [
            'ip'       => \Y::param('javaHost'),
            'port'     => \Y::param('javaServerPort'),
            'username' => \Y::param('javaServerUsername'),
            'password' => \Y::param('javaServerPassword')
        ];

        $this->ssh = (new NetSsh())->init($options);
        $this->service_type = $this->getServiceType();

        /** Check if service is supported */
        if (!in_array($this->service_type, $this->supported_services)) {
            throw new \Exception(Yii::t('app', 'Service type {0} currently is not supported by cBackup.', $this->service_type));
        }

    }

    /**
     * Factory method
     *
     * @return object|ServiceMethods
     * @throws \Exception
     */
    public function init()
    {
        try {
            $class = "\\app\\components\\services\\" . ucfirst(str_replace('.', '', $this->service_type));
            return (new \ReflectionClass($class))->newInstance();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get service type from ini file
     *
     * @return string
     * @throws \Exception
     */
    private function getServiceType()
    {

        $ini = \Yii::$app->basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'settings.ini';

        if (!file_exists($ini)) {
            throw new \Exception(Yii::t('app', '{0} not found', $ini));
        }

        $ini = parse_ini_file($ini, true);

        if (empty($ini)) {
            throw new \Exception(Yii::t('app', 'Error while loading settings ini-file'));
        }

        if (!array_key_exists('serviceType', $ini) || empty($ini['serviceType']) ) {
            throw new \Exception(Yii::t('app', 'Service type is not defined in settings ini-file'));
        }

        return $ini['serviceType'];

    }

}
