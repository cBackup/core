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
use app\models\Network;

/**
 * @package functional
 */
class NetworkCest
{

    /**
     * @inheritdoc
     */
    public function _before(\FunctionalTester $I)
    {
        $I->amLoggedInAs('ADMIN');
        /** @noinspection PhpParamsInspection */
        $I->amOnPage(['network/subnet/add']);
    }

    /**
     * @param \FunctionalTester $I
     */
    public function seeExclusionAddForm(\FunctionalTester $I)
    {
        $I->see('Add subnet');
        $I->seeElement('form#subnet_form');
    }

    /**
     * @param \FunctionalTester $I
     */
    public function submitEmptyParameters(\FunctionalTester $I)
    {
        $I->submitForm('#subnet_form', []);
        $I->expectTo('see validations errors');
        $I->see('Subnet cannot be blank.');
    }

    /**
     * @param \FunctionalTester $I
     */
    public function submitWrongParameters(\FunctionalTester $I)
    {
        $I->submitForm('#subnet_form', [
            'Network[network]'       => '999.999.999.999',
            'Network[credential_id]' => '0',
            'Network[description]'   =>
                'h7DQRsMl5oPpFXYyqU6jHBE3O5Gnu0pRG8NcQP90axtxCIN94FzM8OGdTXTgVXS33ouyfDqEtFN1cQJzo6FV
                 qJGca2vbpMUJAHOHvGACunlltuXR1LjKy49c6qztdmHsY0sONlRboE1rJObCwk7TTJG76iKrJe4t4pPPeltd2LoCtSXPzXRet84xAsOzcF1al
                 uWPSApwqjrnGseKD7UzT2dGGDMUkCGVMapcKBkOUmuxXTgSw0ViSw3QFKPYaO6C',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Subnet address must be in CIDR format.');
        $I->see('Credential name is invalid.');
        $I->see('Description should contain at most 255 characters.');
    }


    /**
     * @param \FunctionalTester $I
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function submitCorrectParameters(\FunctionalTester $I)
    {
        $I->submitForm('#subnet_form', [
            'Network[network]'       => '192.168.1.0/26',
            'Network[credential_id]' => '1',
            'Network[discoverable]'  => '1',
            'Network[description]'   => 'This is test subnet.',
        ]);

        $I->expectTo('not to see validation errors');
        $I->dontSeeElement('.field-network-network.has-error');
        $I->dontSeeElement('.field-credential_id.has-error');
        $I->dontSeeElement('.field-network-discoverable.has-error');
        $I->dontSeeElement('.field-network-description.has-error');

        /** Clean up after submit */
        $net_id = $I->grabAttributeFrom('//tr[contains(td, "192.168.1.0/26")]', 'data-key');
        Network::findOne($net_id)->delete();

    }

}
