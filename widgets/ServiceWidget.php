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

namespace app\widgets;

use yii\base\Widget;
use app\components\NetSsh;
use app\components\Service;

/**
 * Class Message
 *
 * @package app\widgets
 */
class ServiceWidget extends Widget
{

    /**
     * @var bool
     */
    private $service_status;

    /**
     * @var bool
     */
    private $scheduler_status;

    /**
     * @var array
     */
    private $java_server = ['init' => true, 'error'  => ''];

    /**
     * @var array
     */
    private $java_scheduler = ['init' => true, 'error'  => ''];

    /**
     * Prepare dataset
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->service_status   = $this->getServiceStatus();
        $this->scheduler_status = ($this->service_status || !$this->java_server['init']) ? $this->getSchedulerStatus() : false;
    }

    /**
     * Render widget view
     *
     * @return string
     */
    public function run()
    {
        return $this->render('service_widget', [
            'service_status'   => $this->service_status,
            'scheduler_status' => $this->scheduler_status,
            'java_server'      => $this->java_server,
            'java_scheduler'   => $this->java_scheduler,
        ]);
    }

    /**
     * Get Java service status
     *
     * @return bool
     */
    private function getServiceStatus()
    {
        try {
            return (new Service())->init()->isServiceActive();
        } catch (\Exception $e) {
            $this->java_server['init']  = false;
            $this->java_server['error'] = $e->getMessage();
            return false;
        }
    }

    /**
     * Get Java Scheduler status
     *
     * @return bool
     */
    private function getSchedulerStatus()
    {
        try {

            $response = (new NetSsh())->init()->schedulerExec('cbackup status -json');

            /** Throw exception if error occurs */
            if (!$response['success']) {
                throw new \Exception($response['exception']);
            }

            return $response['object'];

        } catch (\Exception $e){
            $this->java_scheduler['init']  = false;
            $this->java_scheduler['error'] = $e->getMessage();
            return false;
        }
    }

}
