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

namespace tests\api\v1;

use \Codeception\Util\HttpCode;

class CoreCest
{

    /**
     * Authenticate before every reguest
     *
     * @param \ApiTester $I
     */
    public function _before(\ApiTester $I) {
        $I->amBearerAuthenticated('A4F6D307-E257-4A0D-A1D2-6793E4286441');
    }

    /**
     * @param \ApiTester $I
     */
    public function testSuccessfulBearerAuthentication(\ApiTester $I) {
        $I->sendGET('v1/core/get-exclusions');
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @param \ApiTester $I
     */
    public function testUnsuccessfulBearerAuthentication(\ApiTester $I) {
        $I->amBearerAuthenticated('CHICKEN-CHICKEN-CHICKEN-CHICKEN-CHICKEN');
        $I->sendGET('v1/core/get-exclusions');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetExclusions(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-exclusions');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType(['string']);
        }
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetNetworks(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-networks');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType([
                'id'           => 'string',
                'snmp_read'    => 'string|null',
                'snmp_set'     => 'string|null',
                'snmp_version' => 'string',
                'port_snmp'    => 'string',
            ], '$.[*]');
        }
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetTasks(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-tasks');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType([
                'scheduleId' => 'string',
                'taskName' => 'string',
                'scheduleCron' => 'string',
                'taskType' => 'string',
                'put' => 'string|null',
                'table' => 'string|null'
            ]);
        }
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetTask(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-task', ['task_name' => 'backup']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'scheduleId'   => 'string|null',
            'taskName'     => 'string',
            'taskType'     => 'string',
            'put'          => 'string|null',
            'table'        => 'string|null'
        ]);
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetVariables(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-variables');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType(['string'], '$.[*]');
        }
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetMailerEvents(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-mailer-events');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType([
                'scheduleId' => 'string',
                'eventName' => 'string',
                'scheduleCron' => 'string',
            ]);
        }
    }


    /**
     * @param \ApiTester $I
     */
    public function testGetNodeCredentials(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-node-credentials', ['node_id' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType([
                'id'              => 'string',
                'name'            => 'string',
                'telnet_login'    => 'string|null',
                'telnet_password' => 'string|null',
                'ssh_login'       => 'string|null',
                'ssh_password'    => 'string|null',
                'snmp_read'       => 'string|null',
                'snmp_set'        => 'string|null',
                'snmp_version'    => 'string|null',
                'snmp_encryption' => 'string|null',
                'enable_password' => 'string|null',
                'port_telnet'     => 'string|null',
                'port_ssh'        => 'string|null',
                'port_snmp'       => 'string|null',
                'auth_sequence'   => 'string'
            ]);
        }
    }


    /**
     * @param \ApiTester $I
     */
    public function testGetJobs(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-jobs', ['worker_id' => 2]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType([
                'sequence_id'         => 'string',
                'command_value'       => 'string',
                'snmp_request_type'   => 'string|null',
                'snmp_set_value'      => 'string|null',
                'snmp_set_value_type' => 'string|null',
                'timeout'             => 'string|null',
                'table_field'         => 'string|null',
                'command_var'         => 'string|null',
            ], '$.[*]');
        }
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetConfig(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-config');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(['string|null'], '$.[*]');
    }


    /**
     * @param \ApiTester $I
     */
    public function testGetWorkerByNodeId(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-worker-by-node-id', ['node_id' => 1, 'task_name' => 'backup']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType([
                'id'          => 'string',
                'name'        => 'string',
                'task_name'   => 'string',
                'get'         => 'string',
                'description' => 'string|null',
                'ip'          => 'string',
                'model'       => 'string',
                'vendor'      => 'string'
            ], '$.[*]');
        }
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetNodesWorkersByTask(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-nodes-workers-by-task', ['schedule_id' => 1, 'task_name' => 'backup']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        /** Check json type if response is not empty */
        if ($I->grabResponse() != '[]') {
            $I->seeResponseMatchesJsonType([
                'id'          => 'string',
                'name'        => 'string',
                'task_name'   => 'string',
                'get'         => 'string',
                'description' => 'string|null',
                'ip'          => 'string',
                'model'       => 'string',
                'vendor'      => 'string'
            ], '$.[*]');
        }
    }

    /**
     * @param \ApiTester $I
     */
    public function testGetHash(\ApiTester $I)
    {
        $I->sendGET('v1/core/get-hash', ['task_name' => 'backup', 'node_id' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseIsJson(['string']);
    }

}

