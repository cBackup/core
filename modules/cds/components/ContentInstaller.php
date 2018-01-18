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

namespace app\modules\cds\components;

use yii\db\SchemaBuilderTrait;
use yii\db\Query;

/**
 * @package app\modules\cds\components
 */
abstract class ContentInstaller
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
     * Check if record exists
     *
     * @param  string$table
     * @param  array $condition
     * @return bool
     */
    public function recordExists($table, $condition)
    {
        return (new Query())->from("{$table}")->where($condition)->exists();
    }

    /**
     * Get unique entry identifier
     *
     * @param  string $table
     * @param  array $condition
     * @param  string $field
     * @return false|null|string
     */
    public function getEntryIdentifier($table, $condition, $field)
    {
        return (new Query())->select("{$field}")->from("{$table}")->where($condition)->scalar();
    }

    /**
     * Add content to database
     *
     * @return bool
     * @throws \Exception
     */
    abstract public function install();

}
