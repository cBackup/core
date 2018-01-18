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
class ExclusionCest
{

    /**
     * @param \AcceptanceTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function _before(\AcceptanceTester $I)
    {
        $I->amLoggedInAs('admin', 'admin');
    }

    /**
     * @param \AcceptanceTester $I
     */
    public function addNewExclusion(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute('/network/exclusion/add'));
        $I->expectTo('see exclusions form');
        $I->see('Add exclusion');

        $I->fillField('input[name="Exclusion[ip]"]', '192.168.0.2');
        $I->fillField('textarea[name="Exclusion[description]"]', 'This is test Exclusion');
        $I->click('button');
        $I->wait(1);

        $I->expectTo('see sweet-alert popup warning');
        $I->seeElement('div.sweet-alert');
        $I->click('button.confirm');
        $I->wait(1);

        $I->expectTo('see list of exclusions with added exclusion');
        $I->see('List of exclusions');
        $I->see('192.168.0.2');

    }

    /**
     * @param \AcceptanceTester $I
     */
    public function checkNodeInfoWindow(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute('/network/exclusion/list'));
        $I->expectTo('see list of exclusions');
        $I->see('List of exclusions');

        $I->click('a[data-div-id="#info_192_168_0_2"]');
        $I->wait(1);

        $I->expectTo('see node info');
        $I->see('[Metro-Test][Raina35]');

    }

    /**
     * @param \AcceptanceTester $I
     */
    public function editExclusion(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute('/network/exclusion/list'));
        $I->expectTo('see list of exclusions');
        $I->see('List of exclusions');

        $I->click('a[href="/index-test.php?r=network%2Fexclusion%2Fedit&ip=192.168.0.2"]');
        $I->wait(1);

        $I->expectTo('see edit exclusion form');
        $I->see('Edit exclusion ');

        $I->fillField('textarea[name="Exclusion[description]"]', 'Edited via WebDriver');
        $I->click('button');
        $I->wait(1);

        $I->expectTo('see list of exclusions with edited exclusion');
        $I->see('List of exclusions');
        $I->see('Edited via WebDriver');
        $I->seeElement('.alert.alert-success');
        $I->see('Exclusion 192.168.0.2 was successfully edited.');

    }

    /**
     * @param \AcceptanceTester $I
     */
    public function deleteExclusionViaForm(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute(['/network/exclusion/edit', 'ip' => '192.168.0.2']));
        $I->expectTo('see edit exclusion form');
        $I->see('Edit exclusion ');

        $I->click(['link' => 'Delete']);
        $I->wait(1);

        $I->expectTo('see confirm popup');
        $I->acceptPopup();
        $I->wait(1);

        $I->expectTo('see flash message');
        $I->seeElement('.alert.alert-success');
        $I->see('Exclusion 192.168.0.2 was successfully deleted.');

    }

    /**
     * @param \AcceptanceTester $I
     */
    public function deleteExclusionViaGrid(\AcceptanceTester $I)
    {

        /** Add new exclusion  */
        $this->addNewExclusion($I);
        $I->wait(1);

        $I->amOnPage(Url::toRoute('/network/exclusion/list'));
        $I->expectTo('see list of exclusions');
        $I->see('List of exclusions');

        $I->click('a[data-ajax-url="/index-test.php?r=network%2Fexclusion%2Fajax-delete&ip=192.168.0.2"]');
        $I->acceptPopup();
        $I->wait(1);

        $I->expectTo('see toast success message');
        $I->seeElement('#toast-container .toast.toast-success');
        $I->see('Exclusion 192.168.0.2 was successfully deleted.');

    }

}
