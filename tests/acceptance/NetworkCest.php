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

use app\models\Credential;
use yii\helpers\Url;


/**
 * @package acceptance
 */
class NetworkCest
{

    /**
     * @var null
     */
    private $net_id = null;

    /**
     * @var null
     */
    private $cred_id = null;

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
    public function addNewNetwork(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute('/network/subnet/add'));
        $I->expectTo('see subnet add form');
        $I->see('Add subnet');

        $I->fillField('input[name="Network[network]"]', '192.168.1.0/26');
        $I->selectOption('select[name="Network[credential_id]"]', 1);
        $I->click('div.toggle.btn');
        $I->fillField('textarea[name="Network[description]"]', 'This is test Network');
        $I->click('button');
        $I->wait(1);

        $I->expectTo('see list of subnets and success alert');
        $I->see('List of subnets');
        $I->seeElement('.alert.alert-success');
        $I->see('New subnet was successfully added.');

        $I->expectTo('grab and store network ID for future tests');
        $this->net_id = $I->grabAttributeFrom('//tr[contains(td, "192.168.1.0/26")]', 'data-key');

    }

    /**
     * @param \AcceptanceTester $I
     */
    public function checkCredentialModalWindow(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute('/network/subnet/add'));
        $I->expectTo('see subnet add form');
        $I->see('Add subnet');

        $I->expectTo('see credential add modal window');
        $I->click('a[data-target="#credential_form_modal"]');
        $I->wait(1);
        $I->see('Add credential');

        $I->fillField('input[name="Credential[name]"]', 'test_webdriver_cred');
        $I->click('#save');
        $I->wait(1);

        $I->expectTo('see toast success message');
        $I->seeElement('#toast-container .toast.toast-success');
        $I->see('New credential was successfully added.');

    }

    /**
     * @param \AcceptanceTester $I
     */
    public function editNetwork(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute('/network/subnet/list'));
        $I->expectTo('see subnet list');
        $I->see('List of subnets');

        $I->click('a[href="/index-test.php?r=network%2Fsubnet%2Fedit&id='. $this->net_id .'"]');
        $I->wait(1);

        $I->expectTo('grab and store credential ID for future tests');
        $this->cred_id = $I->grabAttributeFrom('//select/option[text()="test_webdriver_cred"]', 'value');

        $I->expectTo('see edit network form');
        $I->see('Edit subnet');

        $I->fillField('input[name="Network[network]"]', '192.168.2.0/26');
        $I->selectOption('select[name="Network[credential_id]"]', $this->cred_id);
        $I->fillField('textarea[name="Network[description]"]', 'Edited via WebDriver');
        $I->click('button');
        $I->wait(1);

        $I->expectTo('see list of subnets with edited subnet');
        $I->see('List of subnets');
        $I->see('Edited via WebDriver');
        $I->seeElement('.alert.alert-success');
        $I->see('Subnet 192.168.2.0/26 was successfully edited.');

    }

    /**
     * @param \AcceptanceTester $I
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function deleteNetworkViaForm(\AcceptanceTester $I)
    {

        $I->amOnPage(Url::toRoute(['/network/subnet/edit', 'id' => $this->net_id]));
        $I->expectTo('see edit subnet form');
        $I->see('Edit subnet');

        $I->click(['link' => 'Delete']);
        $I->wait(1);

        $I->expectTo('see confirm popup');
        $I->acceptPopup();
        $I->wait(1);

        $I->expectTo('see flash message');
        $I->seeElement('.alert.alert-success');
        $I->see('Subnet 192.168.2.0/26 was successfully deleted. ');

        /** Clean up data */
        Credential::findOne($this->cred_id)->delete();

    }

    /**
     * @param \AcceptanceTester $I
     */
    public function deleteNetworkViaGrid(\AcceptanceTester $I)
    {

        /** Add new exclusion  */
        $this->addNewNetwork($I);
        $I->wait(1);

        $I->amOnPage(Url::toRoute('/network/subnet/list'));
        $I->expectTo('see subnet list');
        $I->see('List of subnets');

        $I->click('a[data-ajax-url="/index-test.php?r=network%2Fsubnet%2Fajax-delete&id='. $this->net_id .'"]');
        $I->acceptPopup();
        $I->wait(1);

        $I->expectTo('see toast success message');
        $I->seeElement('#toast-container .toast.toast-success');
        $I->see('Subnet 192.168.1.0/26 was successfully deleted.');

    }

}
