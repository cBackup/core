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

return [

    /** List of system auth items and users which cannot be deleted */
    'system' => [
        'users'  => ['ADMIN', 'JAVACORE', 'CONSOLE_APP'],
        'rights' => ['admin', 'APICore', 'APIReader']
    ],

    /** Page size array for GridView */
    'page_size' => [20 => 20, 40 => 40, 60 => 60, 80 => 80,  100 => 100, 200 => 200],

    /**
     * List of SNMP versions
     * @see http://docs.php.net/manual/ru/class.snmp.php#snmp.class.constants.protocols
     */
    'snmp_versions' => ['0' => 'v1', '1' => 'v2 / v2c'],

    /** List of permanent task which can not be assigned to nodes */
    'forbidden_tasks_list' => ['discovery', 'log_processing', 'node_processing', 'git_commit'],

    /**
     * List of java factory units
     *
     * Parameter format:
     *      key2 => [], Check only vendor
     *      key  => ['value1', 'value2', ....], Check vendor + device model
     *      ...
     *
     * Example: 'Mikrotik' => ['RB123'], where 'Mikrotik' -> vendor, 'RB123' -> device model
     */
    'java_factory' => [
        'Mikrotik' => [],
        'Nortel'   => []
    ],

    /** List of dynamic system variables */
    'system_variables' => ['%%DATE%%', '%%NODE_ID%%', '%%TASK%%', '%%NODE_IP%%'],

    /** Devices' valid CLI prompts anchors*/
    'cli_prompts' => ['$', '>', '#', '~'],

];
