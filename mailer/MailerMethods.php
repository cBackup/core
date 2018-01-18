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

namespace app\mailer;

use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use app\helpers\StringHelper;
use app\models\LogSystem;
use app\models\Node;


/**
 * Methods are called from CustomMailer class with the help of reflections
 *
 * @package app\mailer
 */
class MailerMethods
{

    /**
     * List of mailer methods and their arguments.
     * This method array is meant for template variables rendering in "Edit template" view.
     * When new mailer method is added method description must be added in this array too.
     *
     * Method name must be passed in lowercase and separated with underscore
     * 'method_name' => [
     *    'args' => [
     *      List of method arguments. On empty arguments array, inputs will not be rendered.
     *      'key'  => '', will render simple input
     *      'key2' => ['value1', 'value2', 'value3'], will render select box with given values
     *    ],
     *    'output' => ['display1', 'display2', 'display3'], List of values which will be displayed in mail template
     * ]
     *
     * @var array
     */
    public static $template_variables = [
        'hdd_usage' => [
            'args'   => [],
            'output' => ['total', 'free', 'used']
        ],
        'node' => [
            'args'   => ['node_id' => ''],
            'output' => ['ip', 'mac', 'created', 'modified', 'last_seen', 'hostname', 'serial', 'location', 'contact', 'sys_description']
        ],
        'node_logs' => [
            'args'   => [
                'node_id'  => '',
                'log_type' => ['node', 'scheduler'],
                'limit'    => [10, 20, 30, 40, 50]
            ],
            'output' => ['all', 'info', 'warning', 'error']
        ],
        'system_logs' => [
            'args'   => [
                'limit' => [10, 20, 30, 40, 50]
            ],
            'output' => ['all', 'info', 'warning', 'error']
        ]
    ];

    /**
     * List of method arguments
     *
     * @var array
     */
    private static $arguments = [];

    /**
     * List of display arguments
     *
     * @var string
     */
    private static $display;

    /**
     * @var array
     */
    private static $text_messages = [
        'not_found_msg' => 'Nothing to show sorry...',
        'exception_msg' => 'It looks like something went wrong while generating this email. Please contact to system administrator.'
    ];


    /**
     * @param $arguments
     * @param $display
     */
    public function __construct($arguments, $display)
    {
        static::$arguments = array_values($arguments);
        static::$display   = array_values($display);
    }


    /**
     * Get HDD usage
     *
     * @return string
     */
    public static function getHddUsage()
    {

        try {

            $hdd_total = @disk_total_space(\Y::param('dataPath'));
            $hdd_free  = @disk_free_space(\Y::param('dataPath'));

            $hdd_usage = [
                'total' => StringHelper::beautifySize($hdd_total),
                'free'  => StringHelper::beautifySize($hdd_free),
                'used'  => StringHelper::beautifySize($hdd_total - $hdd_free)
            ];

            return $hdd_usage[static::$display[0]];

        } /** @noinspection PhpUndefinedClassInspection */ catch (\Throwable $e) {
            $msg = "An error occurred while preparing template variables. \nClass: ".__CLASS__." \nMethod: ".__FUNCTION__." \nException:\n{$e->getMessage()}";
            \Yii::error([$msg, null, 'PREPARE VARIABLES'], 'mailer.writeLog');
            return static::$text_messages['exception_msg'];
        }

    }


    /**
     * Get info about specific node
     *
     * @return string
     */
    public static function getNode()
    {
        $result = static::$text_messages['not_found_msg'];

        try {
            $node = Node::find()->select(static::$display[0])->where(['id' => static::$arguments[0]]);
            if ($node->exists() && !is_null($node->scalar())) {
                $result = (static::$display[0] == 'mac') ? StringHelper::beautifyMac($node->scalar()) : $node->scalar();
            }
        } /** @noinspection PhpUndefinedClassInspection */ catch (\Throwable $e) {
            $msg = "An error occurred while preparing template variables. \nClass: ".__CLASS__." \nMethod: ".__FUNCTION__." \nException:\n{$e->getMessage()}";
            \Yii::error([$msg, null, 'PREPARE VARIABLES'], 'mailer.writeLog');
            $result = static::$text_messages['exception_msg'];
        }

        return $result;
    }


    /**
     * Get logs about specific node
     *
     * @return string
     */
    public static function getNodeLogs()
    {

        $result = static::$text_messages['not_found_msg'];

        try {

            /** Check log type before trying to get data */
            if (in_array(static::$arguments[1], static::$template_variables['node_logs']['args']['log_type'])) {

                /** @var $model \yii\db\ActiveRecord */
                $model = 'app\models\Log' . ucfirst(static::$arguments[1]);

                $query = $model::find()->where(['node_id' => static::$arguments[0]]);

                if ($query->exists()) {

                    /** Validate record limit */
                    $limit_val = intval(static::$arguments[2]);
                    $limit = ($limit_val !== 0 && $limit_val <= 100) ? $limit_val : 10;

                    if (static::$display[0] !== 'all') {
                        $query->andWhere(['severity' => strtoupper(static::$display[0])]);
                    }

                    $output = $query->orderBy(['id' => SORT_DESC])->limit($limit)->asArray()->all();

                    if (!empty($output)) {
                        $result = static::prepareTableOutput($output, ['time', 'severity', 'node_id', 'action', 'message']);
                    }

                }

            }

        } /** @noinspection PhpUndefinedClassInspection */ catch (\Throwable $e) {
            $msg = "An error occurred while preparing template variables.\nClass: ".__CLASS__."\nMethod: ".__FUNCTION__." \nException:\n{$e->getMessage()}";
            \Yii::error([$msg, null, 'PREPARE VARIABLES'], 'mailer.writeLog');
            $result = static::$text_messages['exception_msg'];
        }

        return $result;

    }


    /**
     * Get system logs
     *
     * @return string
     */
    public static function getSystemLogs()
    {

        $result = static::$text_messages['not_found_msg'];

        try {

            $query = LogSystem::find();

            if ($query->exists()) {

                /** Validate record limit */
                $limit_val = intval(static::$arguments[0]);
                $limit     = ($limit_val !== 0 && $limit_val <= 100) ? $limit_val : 10;

                if (static::$display[0] !== 'all') {
                    $query->where(['severity' => strtoupper(static::$display[0])]);
                }

                $output = $query->orderBy(['id' => SORT_DESC])->limit($limit)->asArray()->all();

                if (!empty($output)) {
                    $result = static::prepareTableOutput($output, ['time', 'severity', 'action', 'message']);
                }

            }

        } /** @noinspection PhpUndefinedClassInspection */ catch (\Throwable $e) {
            $msg = "An error occurred while preparing template variables.\nClass: ".__CLASS__."\nMethod: ".__FUNCTION__." \nException:\n{$e->getMessage()}";
            \Yii::error([$msg, null, 'PREPARE VARIABLES'], 'mailer.writeLog');
            $result = static::$text_messages['exception_msg'];
        }

        return $result;

    }


    /**
     * Render table
     *
     * @param  array $data
     * @param  array $columns List of table columns which will be rendered, if left empty all columns will be rendered
     * @return string
     * @throws \Exception
     */
    private static function prepareTableOutput($data, $columns = [])
    {

        /** Get columns if not specified */
        $columns = (empty($columns) && array_key_exists(0, $data)) ? array_keys($data[0]) : $columns;

        /** Create array data provider */
        $dataProvider = new ArrayDataProvider([
            'allModels' => $data
        ]);

        /** Disabled pagination */
        $dataProvider->pagination = false;

        /** Set headerOptions and contentOptions to  each attribute (Grid do not allow make it globaly) */
        $attributes = array_map(function ($column) {
            return [
                'attribute'      => $column,
                'headerOptions'  => ['style' => 'padding: 2px 10px 2px 10px'],
                'contentOptions' => ['style' => 'padding: 2px 10px 2px 10px']
            ];
        }, $columns);

        return GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['border' => '1', 'style' => 'border-collapse: collapse;'],
            'layout'       => '{items}',
            'columns'      => $attributes
        ]);

    }

}
