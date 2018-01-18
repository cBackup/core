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

namespace Helper;

use Codeception\Module;
use Codeception\Exception\TestRuntimeException;
use yii\helpers\Url;

/**
 * @package Helper
 */
class Acceptance extends Module
{

    /**
     * @param  int $timeout
     * @return void
     */
    public function wait(int $timeout)
    {
        if ($timeout >= 1000) {
            throw new TestRuntimeException(
                "
                Waiting for more then 1000 seconds: 16.6667 mins\n
                Please note that wait method accepts number of seconds as parameter."
            );
        }
        usleep($timeout * 1000000);
    }

    /**
     * @param string $username
     * @param string $password
     * @param int $timeout
     * @throws \Codeception\Exception\ModuleException
     */
    public function amLoggedInAs(string $username, string $password, int $timeout = 1)
    {
        /** @var $WD \Codeception\Module\WebDriver */
        $WD = $this->getModule('WebDriver');

        if ($WD->loadSessionSnapshot('login')) {
            return;
        }

        $WD->amOnPage(Url::toRoute('/user/login'));
        $WD->fillField('input[name="LoginForm[username]"]', $username);
        $WD->fillField('input[name="LoginForm[password]"]', $password);
        $WD->click('button');
        $WD->wait($timeout);

        $WD->saveSessionSnapshot('login');
    }

}
