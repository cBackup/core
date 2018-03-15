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

namespace app\models;

use Yii;
use \yii\base\DynamicModel;
use \yii\db\ActiveRecord;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Inflector;
use \yii\helpers\FileHelper;
use \yii\helpers\Html;


/**
 * This is the model class for table "{{%task}}".
 *
 * @property string $name
 * @property string $put
 * @property string $table
 * @property string $task_type
 * @property string $yii_command
 * @property integer $protected
 * @property string $description
 *
 * @property Schedule $schedule
 * @property TaskDestination $destination
 * @property TaskType $taskType
 * @property TasksHasDevices[] $tasksHasDevices
 * @property TasksHasNodes[] $tasksHasNodes
 * @property Worker[] $workers
 *
 * @package app\models
 */
class Task extends ActiveRecord
{

    /**
     * @var string
     */
    public $task_has_nodes;

    /**
     * @var string
     */
    public $task_has_devices;

    /**
     * @var bool
     */
    public $out_table_exists = true;

    /**
     * @var array
     */
    public $clean_up = [
        'show_msg'    => false,
        'table'       => false,
        'files'       => false,
        'disable_git' => false
    ];

    /**
     * @var array
     */
    protected $default_columns = [
        'id'      => 'INT(11) NOT NULL AUTO_INCREMENT',
        'time'    => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'node_id' => 'INT(11) NOT NULL',
        'hash'    => 'VARCHAR(255) NOT NULL',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['protected'], 'integer'],
            [['name'], 'match', 'pattern' => '/^[a-z0-9_\-]+$/im', 'message' => Yii::t('app', 'Task name should contain only a-z, 0-9, dash or underscore')],
            [['description'], 'filter', 'filter' => 'trim'],
            [['name', 'table', 'yii_command', 'description'], 'string', 'max' => 255],
            [['put'], 'string', 'max' => 16],
            [['name'], 'unique'],
            [['task_type'], 'string', 'max' => 32],
            [['put'], 'exist', 'skipOnError' => true, 'targetClass' => TaskDestination::class, 'targetAttribute' => ['put' => 'name']],
            [['task_type'], 'exist', 'skipOnError' => true, 'targetClass' => TaskType::class, 'targetAttribute' => ['task_type' => 'name']],
            [['put', 'table', 'description', 'yii_command'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'            => Yii::t('app', 'Name'),
            'put'             => Yii::t('network', 'Destination'),
            'table'           => Yii::t('network', 'Table'),
            'task_type'       => Yii::t('network', 'Task Type'),
            'yii_command'     => Yii::t('network', 'Yii Command'),
            'protected'       => Yii::t('network', 'Protected'),
            'description'     => Yii::t('app', 'Description'),
            'task_has_nodes'  => Yii::t('network', 'Task has nodes'),
            'task_has_devices'=> Yii::t('network', 'Task has devices')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedule()
    {
        return $this->hasOne(Schedule::class, ['task_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDestination()
    {
        return $this->hasOne(TaskDestination::class, ['name' => 'put']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaskType()
    {
        return $this->hasOne(TaskType::class, ['name' => 'task_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksHasDevices()
    {
        return $this->hasMany(TasksHasDevices::class, ['task_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksHasNodes()
    {
        return $this->hasMany(TasksHasNodes::class, ['task_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorkers()
    {
        return $this->hasMany(Worker::class, ['task_name' => 'name']);
    }

    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        if (empty($this->errors) && !empty($this->put)) {
            $db           = Yii::$app->getDb();
            $table_schema = $db->getTableSchema("out_{$this->name}");

            if (is_null($table_schema)) {
                $this->out_table_exists = false;
            }
        }

        parent::afterValidate();
    }

    /**
     * Delete all task related data after destination change
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!$this->getIsNewRecord()) {

            $old_put = (array_key_exists('put', $changedAttributes)) ? $changedAttributes['put'] : '';

            if ($old_put != $this->put && !empty($old_put)) {

                try {
                    /** Delete all from out_task-name table */
                    static::getDb()->createCommand()->truncateTable("{{%{$this->table}}}")->execute();
                    $this->clean_up['table'] = true; // Set table clean up flag

                    /** Delete all files related to current task */
                    static::deleteFiles($this->name);
                    $this->clean_up['files'] = true;

                    /** Disable GIT if put is not set to file storage */
                    if ($this->put != 'file' && \Y::param('git') == 1) {
                        Config::updateAll(['value' => 0], ['key' => 'git']);
                        $this->clean_up['disable_git'] = true;
                    }

                    /** Clear cache after task destination change */
                    Yii::$app->cache->delete('config_data');

                } catch (\Exception $e) {
                    $this->clean_up['table']       = false;
                    $this->clean_up['files']       = false;
                    $this->clean_up['disable_git'] = false;
                }

                $this->clean_up['show_msg'] = true;
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Render header row with filter
     *
     * @param  array $search_attributes
     * @return string
     */
    public function renderExtraRowHeader($search_attributes)
    {

        $icon        = 'fa fa-question';
        $destination = Yii::t('network', 'Tasks without destination');
        $query_param = Yii::$app->request->getQueryParam('TaskSearch');
        $filtered    = (isset($query_param['put']) && !empty($query_param['put'])) ? 'filtered' : '';

        switch ($this->put) {
            case 'db':   $icon = 'fa fa-database';   break;
            case 'file': $icon = 'fa fa-file';       break;
            case null:   $icon = 'fa fa-tasks';      break;
        }

        if (!is_null($this->put)) {
            $description = Yii::t('network', $this->destination->description);
            $destination =
                $description .
                Html::beginTag('span', ['class' => 'extra-row-tools ' . $filtered])
               .Html::a('<i class="fa fa-filter"></i>', ['/network/task/list',
                    'TaskSearch[name]'  => $search_attributes['name'],
                    'TaskSearch[put]'   => $this->put,
                    'TaskSearch[table]' => $search_attributes['table']
               ], [
                    'title' => Yii::t('network', 'Filter by {0}', $description),
                    'class' => (!empty($filtered)) ? 'hide' : ''
               ])
               .Html::a('<i class="fa fa-times"></i>', ['/network/task/list'], ['title' => Yii::t('network', 'Clear filter')])
               .Html::endTag('span')
            ;
        }

        return Html::tag('i', '', ['class' => $icon]) . '&nbsp' . $destination;

    }

    /**
     * Render custom filter for destinations filter
     *
     * @return string
     */
    public function renderCustomFilter()
    {

        $destinations = TaskDestination::find()->asArray()->all();
        $query_param  = Yii::$app->request->getQueryParam('TaskSearch');
        $query_param  = (isset($query_param['put'])) ? $query_param['put'] : '';
        $list_items   = [];

        /** Generate list items */
        foreach ($destinations as $destination){
            $list_items[] = Html::tag('li', Html::a(Yii::t('network', $destination['description']),
                ['/network/task/list',
                    'TaskSearch[name]'  => $this->name,
                    'TaskSearch[put]'   => $destination['name'],
                    'TaskSearch[table]' => $this->table
                ]),
                ['class' => ($query_param == $destination['name']) ? 'active' : '']
            );
        }

        /** Set filter button color */
        $btn_color = (!empty($query_param)) ? 'bg-olive' : 'btn-default';

        /** Render dropdown with filter options */
        $result = '
            <div class="input-group">
                '.Html::textInput('TaskSearch[name]', $this->name, ['class' => 'form-control']).'
                <div class="input-group-btn">
                    '.Html::button('<i class="fa fa-filter"></i>', [
                        'class'       => 'btn dropdown-toggle ' . $btn_color,
                        'data-toggle' => 'dropdown'
                    ]).'
                    <ul class="dropdown-menu dropdown-menu-left">
                        <li>'.Html::a(Yii::t('network', 'Filter by:'), '#', ['class' => 'disabled']).'</li>
                        <li class="divider"></li>
                        '.implode('',$list_items).'
                        <li class="divider"></li>
                        <li>'.Html::a(Yii::t('network', 'Clear filter'), ['/network/task/list']).'</li>
                    </ul>
                </div>
            </div>
        ';

        return $result;

    }

    /**
     * Check if task has nodes
     *
     * @return string
     */
    public function getTaskHasNodes()
    {
        $has_nodes = TasksHasNodes::find()->where(['task_name' => $this->name])->exists();
        return static::prepareResult($has_nodes, Yii::t('network', 'Task is attached to one or more nodes'), Yii::t('network', 'Task is not attached to nodes'));
    }

    /**
     * Check if task has devices
     *
     * @return string
     */
    public function getTaskHasDevices()
    {
        $has_devices = TasksHasDevices::find()->where(['task_name' => $this->name])->exists();
        return static::prepareResult($has_devices, Yii::t('network', 'Task is attached to one or more devices'), Yii::t('network', 'Task is not attached to devices'));
    }


    /**
     * Get list of out_ tables
     *
     * @return array
     * @throws \yii\base\NotSupportedException
     */
    public static function getOutTables()
    {
        $table_names = Yii::$app->getDb()->getSchema()->getTableNames();

        $out_tables  = array_filter($table_names, function($table) {
            return strpos($table, 'out_') === 0;
        });

        return array_combine($out_tables, $out_tables);
    }


    /**
     * Get task out table fields
     *
     * @param string $task_name
     * @return array
     */
    public static function getTaskOutTableFields($task_name)
    {

        $default_fields = ['id', 'time', 'node_id', 'hash'];
        $db             = Yii::$app->getDb();
        $table_schema   = $db->getTableSchema("out_{$task_name}");
        $table_fields   = [
            'default_fields' => [],
            'custom_fields'  => []
        ];

        if (!is_null($table_schema)) {
            $columns = $table_schema->getColumnNames();
            foreach ($columns as $column) {
                if (in_array($column, $default_fields)) {
                    $table_fields['default_fields'][] = $table_schema->columns[$column];
                } else {
                    $table_fields['custom_fields'][] = $table_schema->columns[$column];
                }
            }
        }

        return $table_fields;

    }

    /**
     * Check if out table exists
     *
     * @param  string $table
     * @return bool
     */
    public static function outTableExists($table)
    {
        $db           = static::getDb();
        $table_schema = $db->getTableSchema("out_{$table}");
        return (!is_null($table_schema)) ? true : false;
    }

    /**
     * Validate input form
     *
     * @param  array $fields
     * @return DynamicModel
     */
    public function formValidator($fields)
    {

        $rules      = new DynamicModel($fields);
        $duplicates = array_diff_assoc($fields, array_unique($fields));

        $rules->addRule(array_keys($fields), 'filter', ['filter' => 'trim']);
        $rules->addRule(array_keys($fields), 'string', [
            'max'     => 15,
            'tooLong' => Yii::t('network', 'Field with value <b>{value}</b> should contain at most {max} characters.')
        ]);
        $rules->addRule(array_keys($fields), 'match', [
            'pattern' => '/^[a-z][a-z0-9_]*$/',
            'message' => Yii::t('network', 'Field with value <b>{value}</b> should start with letter and contain only a-z, 0-9 or underscore')
        ]);
        $rules->addRule(array_keys($duplicates), function ($attributes) use ($rules) {
            $rules->addError($attributes, Yii::t('network', 'Duplicate column name found'));
        });
        $rules->addRule(array_keys($fields), function ($attributes) use ($rules) {
            if (in_array($rules->attributes[$attributes], array_keys($this->default_columns))) {
                $rules->addError($attributes,
                    Yii::t('network', 'Column name <b>{0}</b> is reserved.<br> Please choose another column name', $rules->attributes[$attributes])
                );
            }
        });

        return $rules;

    }

    /**
     * Create new out table
     *
     * @param  string $table
     * @param  array $columns
     * @return bool
     * @throws \yii\db\Exception
     */
    public function createTable($table, $columns)
    {

        $db           = static::getDb();
        $command      = $db->createCommand();
        $table_schema = $db->getTableSchema("out_{$table}");
        $table_name   = "{{%out_{$table}}}";

        /** Create custom fields array */
        $custom_fields = [];
        foreach (array_values($columns) as $columns) {
            $custom_fields[$columns] = 'TEXT NULL DEFAULT NULL';
        }

        /** Merge default table fields and custom table fields */
        $table_columns = array_merge($this->default_columns, $custom_fields);
        array_push($table_columns, 'PRIMARY KEY (`id`)');

        try {

            /** Drop table if exits */
            if (!is_null($table_schema)) {
                $command->dropTable($table_name)->execute();
            }

            /** Create table with index and foreign key */
            $command->createTable($table_name, $table_columns)->execute();
            $command->addForeignKey("fk_out_{$table}_node1", $table_name, 'node_id', '{{%node}}', 'id', 'CASCADE', 'CASCADE')->execute();
            $command->createIndex("out_{$table}_node_id_unique", $table_name, 'node_id', true)->execute();

            $status = true;

        } catch (\Exception $e) {
            /** If error occurred while creating index of FK drop table  */
            if (!is_null($table_schema)) {
                $command->dropTable($table_name)->execute();
            }
            $status = false;
        }

        return $status;

    }

    /**
     * Drop out table
     *
     * @param   string $table
     * @return  bool
     * @throws \Exception
     */
    public static function deleteTable($table)
    {
        try {
            static::getDb()->createCommand()->dropTable("{{%out_{$table}}}")->execute();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Delete all task related files
     *
     * @param  string $task_name
     * @return bool
     * @throws \Exception
     */
    public static function deleteFiles($task_name)
    {
        try {

            $dir_path = \Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $task_name;
            if (file_exists($dir_path) && is_dir($dir_path)) {
                FileHelper::removeDirectory($dir_path);
            }
            return true;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Check if task is attached to one or more objects
     *
     * @return bool
     */
    public function isTaskAttached() : bool
    {
        $has_devices = TasksHasDevices::find()->where(['task_name' => $this->name])->exists();
        $has_nodes   = TasksHasNodes::find()->where(['task_name' => $this->name])->exists();

        return ($has_devices || $has_nodes) ? true : false;
    }

    /**
     * Get task name styled
     *
     * @return string
     */
    public function getTaskNameStyled()
    {
        $warning = '';
        $link    = $this->name;

        /** Show warning if task is protected */
        if ($this->protected == 1) {
            $warning = Html::tag('i', '', [
                'class'               => 'fa fa-lock margin-r-5 text-danger',
                'style'               => 'cursor: help;',
                'data-toggle'         => 'tooltip',
                'data-placement'      => 'bottom',
                'data-original-title' => Yii::t('network', 'Permanent system task')
            ]);
        }

        /** Render edit link */
        if (!in_array($this->name, \Y::param('forbidden_tasks_list')) && $this->task_type != 'yii_console_task') {
            $link = Html::a($this->name, ['/network/task/edit', 'name' => $this->name], [
                'data-pjax' => '0',
                'title'     => Yii::t('network', 'Edit task')
            ]);
        }

        return $warning . $link;
    }

    /**
     * Get list of tasks grouped by task type
     *
     * @return array
     */
    public static function getTasksList()
    {
        $tasks = static::find()->select(['name', 'task_type'])->asArray()->all();

        return ArrayHelper::map($tasks, 'name', 'name', function ($data) {
            return Yii::t('network', Inflector::humanize($data['task_type']));
        });
    }

    /**
     * Prepera result for gridview
     *
     * @param  bool $has_data
     * @param  string $has_text
     * @param  string $has_not_text
     * @return string
     */
    private static function prepareResult(bool $has_data, string $has_text = '', string $has_not_text = '') : string
    {

        $class = 'fa fa-minus text-silver';
        $text  = $has_not_text;

        if ($has_data) {
            $class = 'fa fa-check text-success';
            $text  = $has_text;
        }

        $result = Html::tag('i', '', [
            'class'               => $class,
            'style'               => 'cursor: help;',
            'data-toggle'         => 'tooltip',
            'data-placement'      => 'bottom',
            'data-original-title' => $text
        ]);

        return $result;

    }

}
