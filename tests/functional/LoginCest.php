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

namespace functional;

/**
 * @package functional
 */
class LoginCest
{

    public function _before(\FunctionalTester $I)
    {
        $I->amOnRoute('user/login');
    }

    public function openLoginPage(\FunctionalTester $I)
    {
        $I->see('backup');
        $I->see('login', 'button');
    }

    /** @noinspection PhpUndefinedClassInspection
     *  @param \FunctionalTester $I
     *  @throws \_generated\ModuleException
     */
    public function internalLoginById(\FunctionalTester $I)
    {
        $I->amLoggedInAs('ADMIN');
        $I->amOnPage('/');
        $I->see('Admin');
        $I->seeElement('form#node_search');
    }

    public function loginWithEmptyCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function loginWithWrongCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'wrong',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password.');
    }

    public function loginSuccessfully(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'admin',
        ]);
        $I->see('Admin');
        $I->seeElement('form#node_search');
        $I->dontSeeElement('form#login-form');              
    }

}
