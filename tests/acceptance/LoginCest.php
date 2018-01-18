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

namespace acceptance;

use yii\helpers\Url;


/**
 * @package acceptance
 */
class LoginCest
{

    /**
     * @param \AcceptanceTester $I
     */
    public function ensureThatLoginWorks(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute('/user/login'));
        $I->expectTo('see login form');
        $I->see('backup');
        $I->see('login', 'button');

        $I->amGoingTo('try to login with correct credentials');
        $I->fillField('input[name="LoginForm[username]"]', 'admin');
        $I->fillField('input[name="LoginForm[password]"]', 'admin');
        $I->click('button');

        // wait for button to be clicked
        $I->wait(2);

        $I->expectTo('see user info');
        $I->see('Admin');

    }

}
