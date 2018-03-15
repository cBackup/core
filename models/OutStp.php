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
use \yii\db\ActiveRecord;
use \yii\behaviors\TimestampBehavior;
use \yii\db\Expression;


/**
 * This is the model class for table "{{%out_stp}}".
 *
 * @property integer $id
 * @property string $time
 * @property integer $node_id
 * @property string $hash
 * @property string $node_mac
 * @property string $root_port
 * @property string $root_mac
 * @property string $bridge_mac
 *
 * @property Node $node
 *
 * @package app\models
 */
class OutStp extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%out_stp}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time'], 'safe'],
            [['node_id', 'hash'], 'required'],
            [['node_id'], 'integer'],
            [['hash', 'node_mac', 'root_port', 'root_mac', 'bridge_mac'], 'string', 'max' => 255],
            [['node_id'], 'unique'],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => Node::class, 'targetAttribute' => ['node_id' => 'id']],
            [['root_mac', 'root_port', 'bridge_mac'], 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => Yii::t('app', 'ID'),
            'time'      => Yii::t('app', 'Time'),
            'hash'      => Yii::t('app', 'Hash'),
            'node_id'   => Yii::t('app', 'Node ID'),
            'root_port' => Yii::t('app', 'Root Port'),
            'root_mac'  => Yii::t('app', 'Root Mac'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(Node::class, ['id' => 'node_id']);
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

    /**
     * Create stp tree based on database stored data
     *
     * @param  string $search_param Accept node id or node mac or node ip
     * @return array|mixed
     */
    public function createStpTree($search_param)
    {

        $root_mac = static::getNodeRootMac($search_param);

        /** Return empty array if node was not found */
        if (empty($root_mac)) {
            return [];
        }

        $result   = [];
        $subtrees = [];
        $dataset  = static::find()->where(['root_mac' => $root_mac])->orderBy('bridge_mac')->all();

        foreach ($dataset as $node) {
            if ($node['root_port'] == 0 && $node['bridge_mac'] == '') {
                $result[] = [
                    'id'         => $node['node_id'],
                    'node_mac'   => $node['node_mac'],
                    'node_ip'    => $node->node->ip,
                    'location'   => $node->node->location,
                    'hostname'   => $node->node->hostname,
                    'device'     => "{$node->node->device->vendor} {$node->node->device->model}",
                    'bridge_mac' => $node['bridge_mac'],
                    'root_port'  => $node['root_port']
                ];
            }
            else {
                if (!array_key_exists($node['bridge_mac'], $subtrees) ) {
                    $subtrees[$node['bridge_mac']] = [];
                }
                $subtrees[$node['bridge_mac']][] = [
                    'id'         => $node['node_id'],
                    'node_mac'   => $node['node_mac'],
                    'node_ip'    => $node->node->ip,
                    'location'   => $node->node->location,
                    'hostname'   => $node->node->hostname,
                    'device'     => "{$node->node->device->vendor} {$node->node->device->model}",
                    'bridge_mac' => $node['bridge_mac'],
                    'root_port'  => $node['root_port']
                ];
            }
        }

        return $this->createChildren($result, $subtrees);

    }

    /**
     * Create children
     *
     * @param  array $result
     * @param  array $subtrees
     * @return mixed
     */
    private function createChildren(&$result, $subtrees)
    {

        foreach ($result as &$curNode) {
            if (array_key_exists($curNode['node_mac'], $subtrees)) {
                if (!array_key_exists('children', $curNode)) {
                    $curNode['children'] = $subtrees[$curNode['node_mac']];
                    $this->createChildren($curNode['children'], $subtrees);
                }
            }
        }

        return $result;

    }

    /**
     * Get node root mac by multiple attributes
     *
     * Node root mac can be found by passing node id or node mac or node ip to $value param
     *
     * @param  string $value
     * @return false|null|string
     */
    private static function getNodeRootMac(string $value)
    {
        return static::find()->joinWith('node n')
            ->select(['root_mac', 'node_id'])
            ->where(['or',
                ['node_id'  => $value],
                ['node_mac' => $value],
                ['n.ip'     => $value]
            ])
            ->scalar()
        ;
    }

}
