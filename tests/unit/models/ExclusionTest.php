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

use app\models\Exclusion;
use Codeception\Test\Unit;


/**
 * @package tests\models
 */
class ExclusionTest extends Unit
{

    public function testSetWrongParameters()
    {
        $model = new Exclusion([
            'ip'          => '999.999.999.999',
            'description' =>
                'h7DQRsMl5oPpFXYyqU6jHBE3O5Gnu0pRG8NcQP90axtxCIN94FzM8OGdTXTgVXS33ouyfDqEtFN1cQJzo6FV
                 qJGca2vbpMUJAHOHvGACunlltuXR1LjKy49c6qztdmHsY0sONlRboE1rJObCwk7TTJG76iKrJe4t4pPPeltd2LoCtSXPzXRet84xAsOzcF1al
                 uWPSApwqjrnGseKD7UzT2dGGDMUkCGVMapcKBkOUmuxXTgSw0ViSw3QFKPYaO6C',
        ]);

        $this->assertFalse($model->validate());
    }

    public function testSetEmptyIp()
    {
        $model = new Exclusion(['ip' => '']);
        $this->assertFalse($model->validate());
    }

    public function testUniqueIpValidator()
    {
        $model = new Exclusion(['ip' => '192.168.0.1']);
        $this->assertFalse($model->validate());
    }

    public function testSetCorrectParameters()
    {
        $model = new Exclusion([
            'ip'          => '192.168.0.3',
            'description' => 'This is test exclusion',
        ]);

        $this->assertTrue($model->validate());
    }

    public function testExclusionExistsMethod()
    {
        $exists = Exclusion::exists('192.168.0.1');
        $this->assertTrue($exists);
    }

    public function testExclusionSave()
    {
        $model = new Exclusion([
            'ip'          => '192.168.0.3',
            'description' => 'This is test exclusion',
        ]);

        $this->assertTrue($model->save());
    }

    /**
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function testExclusionDelete()
    {
        $model = Exclusion::findOne('192.168.0.3');
        $this->assertEquals(1, $model->delete());
    }

}
