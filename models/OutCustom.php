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
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;
use \yii\behaviors\TimestampBehavior;
use \yii\db\Expression;


/** @noinspection UndetectableTableInspection, PropertiesInspection
 *
 * You must instantiate model with attribute 'table'
 * in constructor configuration:
 *   $model = (new OutCustom(['table' => 'out_stp']));
 *   \Y:dump($model->attributes);
 *   \Y:dump($model::find()->where(['id' => 2])->one());
 *   \Y:dump($model::findOne(['id' => 2]));
 *
 * You must use $safeOnly = false to set custom attributes:
 *   $model->setAttributes($data, false)
 *
 * You want to validate attributes by using 'when' criteria
 *   ['hash', 'string', 'min' => 10, 'when' => function() { return strpos('out_stp', static::tableName()) === false; } ],
 *
 * @see http://www.yiiframework.com/doc-2.0/yii-base-dynamicmodel.html
 * For out_ tables there're only four common attributes: id, time, node_id and hash. To validate
 * non-common attributes (e.g. config, root_port, etc) you want to use DynamicModel functionality:
 *   $rules = new DynamicModel($data);
 *   $rules->addRule(['config'], 'string', ['min' => 10])->validate();
 *   if($rules->hasErrors()) { ... }
 *
 *
 * @property integer $id
 * @property string  $time
 * @property integer $node_id
 * @property string  $hash
 *
 * @package app\models
 */
class OutCustom extends ActiveRecord
{

    /**
     * @var string
     */
    public $table;

    /**
     * @var string
     */
    public $date_from;

    /**
     * @var string
     */
    public $date_to;

    /**
     * @var string
     */
    public $node_search = '';

    /**
     * Default page size
     * @var int
     */
    public $page_size = 20;

    /**
     * @var string
     */
    private static $tableName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hash', 'node_id'], 'required'],
            [['node_id'], 'integer'],
            [['hash'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'time'        => Yii::t('app', 'Time'),
            'node_id'     => Yii::t('app', 'Node ID'),
            'hash'        => Yii::t('app', 'Hash'),
            'node_search' => Yii::t('node', 'Node'),
            'date_from'   => Yii::t('log', 'Date/time from'),
            'date_to'     => Yii::t('log', 'Date/time to'),
            'page_size'   => Yii::t('app', 'Page size')
        ];
    }

    /**
     * @return void
     */
    public function init()
    {
        self::$tableName = $this->table;
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%'.self::$tableName.'}}';
    }

    /**
     * @return null|\yii\db\TableSchema
     * @throws InvalidConfigException
     * @throws \yii\base\NotSupportedException
     */
    public static function getTableSchema()
    {

        foreach (debug_backtrace() as $backtrace) {
            if( array_key_exists('object', $backtrace) && $backtrace['object'] instanceof ActiveQuery) {
                self::$tableName = str_replace(['{', '}', '%'], '', $backtrace['object']->from[0]);
            }
        }

        $tableSchema = static::getDb()
            ->getSchema()
            ->getTableSchema(static::tableName());

        if ($tableSchema === null) {
            throw new InvalidConfigException('The table does not exist: ' . static::tableName());
        }

        return $tableSchema;

    }

    /**
     * Behaviors
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'time',
                'updatedAtAttribute' => 'time',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

}
