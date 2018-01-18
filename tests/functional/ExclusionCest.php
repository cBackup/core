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
class ExclusionCest
{

    /**
     * @inheritdoc
     */
    public function _before(\FunctionalTester $I)
    {
        $I->amLoggedInAs('ADMIN');
        /** @noinspection PhpParamsInspection */
        $I->amOnPage(['network/exclusion/add']);
    }

    /**
     * @param \FunctionalTester $I
     */
    public function seeExclusionAddForm(\FunctionalTester $I)
    {
        $I->see('Add exclusion');
        $I->seeElement('form#exclusion_form');
    }

    /**
     * @param \FunctionalTester $I
     */
    public function submitEmptyParameters(\FunctionalTester $I)
    {
        $I->submitForm('#exclusion_form', []);
        $I->expectTo('see validations errors');
        $I->see('IP address cannot be blank.');
    }

    /**
     * @param \FunctionalTester $I
     */
    public function submitWrongParameters(\FunctionalTester $I)
    {
        $I->submitForm('#exclusion_form', [
            'Exclusion[ip]'          => '999.999.999.999',
            'Exclusion[description]' =>
                'h7DQRsMl5oPpFXYyqU6jHBE3O5Gnu0pRG8NcQP90axtxCIN94FzM8OGdTXTgVXS33ouyfDqEtFN1cQJzo6FV
                 qJGca2vbpMUJAHOHvGACunlltuXR1LjKy49c6qztdmHsY0sONlRboE1rJObCwk7TTJG76iKrJe4t4pPPeltd2LoCtSXPzXRet84xAsOzcF1al
                 uWPSApwqjrnGseKD7UzT2dGGDMUkCGVMapcKBkOUmuxXTgSw0ViSw3QFKPYaO6C',
        ]);
        $I->expectTo('see validations errors');
        $I->see('IP address must be a valid IP address.');
        $I->see('Description should contain at most 255 characters.');
    }


    /**
     * @param \FunctionalTester $I
     */
    public function submitCorrectParameters(\FunctionalTester $I)
    {
        $I->submitForm('#exclusion_form', [
            'Exclusion[ip]'          => '127.0.0.1',
            'Exclusion[description]' => 'This is test add',
        ]);

        $I->expectTo('not to see IP field validation error');
        $I->dontSeeElement('.field-exclusion-ip.has-error');

        $I->expectTo('not to see Description field validation error');
        $I->dontSeeElement('.field-exclusion-description.has-error');
    }

}
