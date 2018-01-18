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
use app\models\Worker;


/**
 * WorkerSearch represents the model behind the search form about `app\models\Worker`.
 * @package app\models\search
 */
class WorkerSearch extends Worker
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'task_name', 'get', 'description', 'job_name', 'command_value', 'table_field', 'enabled', 'worker_id', 'page_size'], 'safe'],
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
        $query = Worker::find();

        $query->joinWith(['sortedJobs j']);

        $this->load($params);

        $query->andFilterWhere(['like', 'worker.name', $this->name])
            ->andFilterWhere(['like', 'task_name', $this->task_name])
            ->andFilterWhere(['like', 'get', $this->get])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'j.name', $this->job_name])
            ->andFilterWhere(['like', 'j.command_value', $this->command_value])
            ->andFilterWhere(['like', 'j.table_field', $this->table_field])
            ->andFilterWhere(['like', 'j.enabled', $this->enabled])
            ->andFilterWhere(['j.worker_id' => $this->worker_id]);

        $provider = new ArrayDataProvider([
            'allModels'  => $query->orderBy('worker.task_name, worker.get')->asArray()->all(),
        ]);

        /** Set page size dynamically */
        $provider->pagination->pageSize = $this->page_size;

        return $provider;
    }
}
