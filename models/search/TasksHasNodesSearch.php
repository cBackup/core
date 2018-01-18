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

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TasksHasNodes;


/**
 * TasksHasNodesSearch represents the model behind the search form about `app\models\TasksHasNodes`.
 * @package app\models\search
 */
class TasksHasNodesSearch extends TasksHasNodes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'node_id', 'worker_id'], 'integer'],
            [['task_name', 'node_name', 'node_ip', 'worker_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TasksHasNodes::find();

        $query->joinWith(['node n', 'worker w']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'node_name' => SORT_ASC,
                    'node_ip'   => SORT_ASC,
                    'task_name' => SORT_ASC
                ],
                'attributes'   => [
                    'task_name',
                    'node_name' => [
                        'asc'  => ['n.hostname' => SORT_ASC],
                        'desc' => ['n.hostname' => SORT_DESC],
                    ],
                    'node_ip' => [
                        'asc'  => ['n.ip' => SORT_ASC],
                        'desc' => ['n.ip' => SORT_DESC],
                    ],
                    'worker_name' => [
                        'asc'  => ['w.name' => SORT_ASC],
                        'desc' => ['w.name' => SORT_DESC],
                    ],
                ],
                'enableMultiSort' => true
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'node_id' => $this->node_id,
            'worker_id' => $this->worker_id,
        ]);

        $query->andFilterWhere(['like', 'tasks_has_nodes.task_name', $this->task_name])
            ->andFilterWhere(['like', 'n.hostname', $this->node_name])
            ->andFilterWhere(['like', 'n.ip', $this->node_ip])
            ->andFilterWhere(['like', 'w.name', $this->worker_name]);

        return $dataProvider;
    }
}
