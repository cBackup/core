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
use app\models\TasksHasDevices;


/**
 * TasksHasDevicesSearch represents the model behind the search form about `app\models\TasksHasDevices`.
 * @package app\models\search
 */
class TasksHasDevicesSearch extends TasksHasDevices
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'device_id', 'worker_id'], 'integer'],
            [['task_name', 'device_name', 'worker_name'], 'safe'],
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
        $query = TasksHasDevices::find();

        $query->joinWith(['device d', 'worker w']);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'device_name' => SORT_ASC,
                    'task_name'   => SORT_ASC
                ],
                'attributes'   => [
                    'task_name',
                    'device_name' => [
                        'asc'  => ['d.model' => SORT_ASC],
                        'desc' => ['d.model' => SORT_DESC],
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
            'device_id' => $this->device_id,
            'worker_id' => $this->worker_id,
        ]);

        $query->andFilterWhere(['like', 'tasks_has_devices.task_name', $this->task_name])
            ->andFilterWhere(['like', 'w.name', $this->worker_name])
            ->orFilterWhere(['like', "CONCAT(d.vendor, ' ', d.model)", $this->device_name]);

        return $dataProvider;
    }

}
