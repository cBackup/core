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
use \yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * This is the model class for table "{{%tasks_has_nodes}}".
 *
 * @property integer $id
 * @property integer $node_id
 * @property string $task_name
 * @property integer $worker_id
 *
 * @property Node $node
 * @property Task $taskName
 * @property Worker $worker
 *
 * @package app\models
 */
class TasksHasNodes extends ActiveRecord
{

    /**
     * @var string
     */
    public $node_name;

    /**
     * @var
     */
    public $node_ip;

    /**
     * @var string
     */
    public $worker_name;

    /**
     * @var array
     */
    public $selected_node = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tasks_has_nodes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['node_id', 'task_name'], 'required'],
            [['node_id', 'worker_id'], 'integer'],
            [['task_name'], 'string', 'max' => 255],
            [['node_id'], 'unique', 'targetAttribute' => ['node_id', 'task_name'], 'message' => Yii::t('network', 'Such node-task combination already exists!')],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => Node::class, 'targetAttribute' => ['node_id' => 'id']],
            [['task_name'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_name' => 'name']],
            [['worker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Worker::class, 'targetAttribute' => ['worker_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'node_id'     => Yii::t('node', 'Node'),
            'task_name'   => Yii::t('network', 'Task'),
            'worker_id'   => Yii::t('network', 'Worker'),
            'node_name'   => Yii::t('node', 'Node'),
            'node_ip'     => Yii::t('node', 'IP'),
            'worker_name' => Yii::t('network', 'Worker'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getTaskName()
    {
        return $this->hasOne(Task::class, ['name' => 'task_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorker()
    {
        return $this->hasOne(Worker::class, ['id' => 'worker_id']);
    }

    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        if (!empty($this->node_id)) {
            $this->selected_node = static::getNodeById($this->node_id);
        }

        parent::afterValidate();
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        if (!empty($this->node_id) && Yii::$app->controller->action->id == 'edit-node-task') {
            $this->selected_node = static::getNodeById($this->node_id);
        }

        parent::afterFind();
    }

    /**
     * Getting task node list
     *
     * @param string $task
     * @return array
     */
    public static function getTaskNodes($task): array
    {
        /** Get array of exclusions */
        $exclusions = Exclusion::find()->select('ip')->asArray()->all();

        return (new Query())
            ->select(['node_id'])
            ->from('{{%tasks_has_nodes}} t')
            ->leftJoin('{{%node}} n', 't.node_id = n.id')
            ->where('task_name=:task', [':task' => $task])
            ->andWhere(['not in', 'ip', $exclusions])
            ->column();
    }

    /**
     * Getting node info by node_id and task_name
     *
     * @param string $node_id
     * @param string $task_name
     * @return array
     */
    public static function getInfoByNodeAndTask($node_id, $task_name): array
    {
        $worker = (new Query())
            ->select(['*'])
            ->from('{{%tasks_has_nodes}} t')
            ->leftJoin('{{%node}} n', 't.node_id = n.id')
            ->leftJoin('{{%device}} d', 'd.id = n.device_id')
            ->where('task_name=:task AND node_id=:node', [':task' => $task_name, ':node' => $node_id])
            ->one();

        return ($worker === false)? [] : $worker;
    }

    /**
     * Search node by hostname or ip
     *
     * @param  string $value
     * @return array
     */
    public static function searchNode($value)
    {

        /** Get array of exclusions */
        $exclusions = Exclusion::find()->select('ip')->asArray()->all();

        return (new Query())
            ->select(['id', 'hostname', 'ip'])
            ->from('{{%node}}')
            ->where(['or',
                ['like', 'hostname', $value],
                ['like', 'ip', $value],
            ])
            ->andWhere(['not in', 'ip', $exclusions])
            ->all()
        ;
    }

    /**
     * Get node by ID
     *
     * @param  int $node_id
     * @return array
     */
    public static function getNodeById($node_id)
    {

        $query = (new Query())
            ->select(['id', 'hostname', 'ip'])
            ->from('{{%node}}')
            ->where(['id' => $node_id])
            ->all()
        ;

        return ArrayHelper::map($query, 'id', function ($data) { /** @var $data Node */
            $hostname = (!empty($data['hostname'])) ? $data['hostname'] : Yii::t('yii', '(not set)');
            return "{$hostname} - {$data['ip']}";
        });

    }

    /**
     * Get node name styled
     *
     * @return string
     */
    public function getNodeNameStyled()
    {
        $warning   = '';
        $exclusion = Exclusion::find()->where(['ip' => $this->node->ip])->exists();

        if ($exclusion) {
            $warning = Html::tag('span', '<i class="fa fa-warning"></i>', [
                'class'          => 'margin-r-5 text-danger',
                'data-toggle'    => 'tooltip',
                'data-placement' => 'top',
                'data-html'      => 'true',
                'title'          => Yii::t('network', 'This node is excluded')
            ]);
        }

        return (!empty($this->node->hostname)) ? $warning . $this->node->hostname : $warning . Yii::t('yii', '(not set)');
    }


}
