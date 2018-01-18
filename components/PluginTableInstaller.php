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

namespace app\components;

use yii\db\SchemaBuilderTrait;


/**
 * @package app\components
 */
abstract class PluginTableInstaller
{

    /**
     * SchemaBuilderTrait contains shortcut methods to create instances of [[ColumnSchemaBuilder]].
     */
    use SchemaBuilderTrait;

    /**
     * @var \yii\db\Migration
     */
    public $command;

    /**
     * @var \yii\db\Connection
     */
    public $db;

    /**
     * @return object|string
     */
    protected function getDb()
    {
        return $this->db;
    }

    /**
     * Migration constructor.
     * @throws \yii\base\NotSupportedException
     */
    public function __construct()
    {
        $this->db = \Yii::$app->getDb();
        $this->db->getSchema()->refresh();
        $this->command = $this->db->createCommand();
    }

    /**
     * Check if specific table
     *
     * @param  string $table
     * @return bool
     */
    public function tableExists($table)
    {
        return ($this->db->getTableSchema("{$table}") !== null) ? true : false;
    }

    /**
     * Add plugin tables to database
     *
     * @return bool
     * @throws \Exception
     */
    abstract public function install();

    /**
     * Update plugin tables
     *
     * @return bool
     * @throws \Exception
     */
    abstract public function update();

    /**
     * Remove plugin tables from database
     *
     * @return bool
     * @throws \Exception
     */
    abstract public function remove();

}
