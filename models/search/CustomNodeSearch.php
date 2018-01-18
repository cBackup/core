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
use yii\data\ArrayDataProvider;
use app\models\Node;
use app\models\TasksHasNodes;
use app\models\Exclusion;


/**
 * CustomNodeSearch represents the model behind the search form about `app\models\Node`.
 * @package app\models\search
 */
class CustomNodeSearch extends Node
{

    public $task_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'network_id', 'credential_id', 'device_id', 'manual'], 'integer'],
            [['ip'], 'unique', 'targetAttribute' => ['network_id', 'device_id']],
            [['ip', 'mac', 'created', 'modified', 'last_seen', 'hostname', 'serial', 'location', 'contact', 'description', 'page_size'], 'safe'],
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
     * @return ArrayDataProvider
     */
    public function search($params)
    {

        /** Get array of exclusions */
        $exclusions = Exclusion::find()->select('ip')->asArray()->all();
        $query      = Node::find()->select('node.id, ip, node.network_id, device_id, hostname, location');

        $query->joinWith(['network n', 'device d']);

        $this->load($params);

        /** Exclude nodes from search query */
        $query->andFilterWhere(['not in', 'ip', $exclusions]);

        $query->andFilterWhere([
            'network_id' => $this->network_id,
            'device_id'  => $this->device_id,
        ]);

        $query->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'hostname', $this->hostname])
            ->andFilterWhere(['like', 'location', $this->location]);

        $data = [];

        if (!empty(array_filter($this->attributes))) {

            $data = $query->orderBy('network_id')->asArray()->all();

            foreach ($data as $key => $entry) {
                $task_exists = TasksHasNodes::find()->where(['node_id' => $entry['id'], 'task_name' => $params['task_name']])->exists();
                $data[$key] += ($task_exists) ? ['node_has_task' => true] : ['node_has_task' => false];
            }

        }

        $provider = new ArrayDataProvider([
            'allModels'  => $data,
        ]);

        /** Set page size dynamically */
        $provider->pagination->pageSize = $this->page_size;

        return $provider;

    }
}
