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

namespace tests\models;

use app\models\Network;
use Codeception\Test\Unit;


/**
 * @package tests\models
 */
class NetworkTest extends Unit
{

    public function testSetWrongParameters()
    {
        $model = new Network([
            'credential_id' => '0',
            'network'       => '0',
            'discoverable'  => '0',
            'description'   =>
                'h7DQRsMl5oPpFXYyqU6jHBE3O5Gnu0pRG8NcQP90axtxCIN94FzM8OGdTXTgVXS33ouyfDqEtFN1cQJzo6FV
                 qJGca2vbpMUJAHOHvGACunlltuXR1LjKy49c6qztdmHsY0sONlRboE1rJObCwk7TTJG76iKrJe4t4pPPeltd2LoCtSXPzXRet84xAsOzcF1al
                 uWPSApwqjrnGseKD7UzT2dGGDMUkCGVMapcKBkOUmuxXTgSw0ViSw3QFKPYaO6C',
        ]);

        $this->assertFalse($model->validate());
    }

    public function testSetEmptyRequiredFields()
    {
        $model = new Network([
            'credential_id' => '',
            'network'       => ''
        ]);
        $this->assertFalse($model->validate());
    }

    public function testUniqueNetworkValidator()
    {
        $model = new Network([
            'network' => '192.168.0.0/26'
        ]);
        $this->assertFalse($model->validate());
    }

    public function testSetCorrectParameters()
    {
        $model = new Network([
            'network'       => '192.168.1.0/26',
            'credential_id' => 1,
            'discoverable'  => 1,
            'description'   => 'This is test network',
        ]);

        $this->assertTrue($model->validate());
    }

    public function testCheckSubnetValidator()
    {
        $model = new Network(['credential_id' => 1]);

        /** Validate network format */
        $model->network = '999.999.999.999';
        $model->validate('network');
        $this->assertContains('Subnet address must be in CIDR format.<br>Example: 192.168.0.0/26', $model->errors['network']);

        /** Validate correctness of network */
        $model->network = '192.168.0.1/26';
        $model->validate('network');
        $this->assertContains('Invalid subnet address', $model->errors['network']);
    }

    public function testNetworkSave()
    {
        $model = new Network([
            'network'       => '192.168.1.0/26',
            'credential_id' => 1,
            'discoverable'  => 1,
            'description'   => 'This is test network',
        ]);

        $this->assertTrue($model->save());
    }

    /**
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function testNetworkDelete()
    {
        $model = Network::find()->where(['network' => '192.168.1.0/26'])->one();
        $this->assertEquals(1, $model->delete());
    }

}
