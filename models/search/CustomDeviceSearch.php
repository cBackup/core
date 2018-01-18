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

use yii\data\ArrayDataProvider;
use yii\base\Model;
use app\models\TasksHasDevices;
use app\models\Worker;
use app\models\Device;


/**
 * CustomDeviceSearch represents the model behind the search form about `app\models\Device`.
 * @package app\models\search
 */
class CustomDeviceSearch extends Device
{

    /**
     * @var string
     */
    public $task_name;

    /**
     * @var int
     */
    public $worker_id;

    /**
     * @var string
     */
    public $worker_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['vendor', 'model', 'auth_sequence', 'page_size'], 'safe'],
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

        $query = Device::find();

        $this->load($params);

        $query->andFilterWhere(['like', 'vendor', $this->vendor]);

        $data = $query->orderBy('vendor')->asArray()->all();

        $this->task_name   = $params['task_name'];
        $this->worker_id   = $params['worker_id'];
        $this->worker_name = Worker::find()->select('name')->where(['id' => $this->worker_id])->scalar();

        foreach ($data as $key => $entry) {
            /** Check full match */
            $task_exists = TasksHasDevices::find()->where([
                'device_id' => $entry['id'], 'task_name' => $this->task_name, 'worker_id' => $this->worker_id
            ])->exists();

            $data[$key]['has_selected_task'] = ($task_exists) ? true : false;

            /** Check if device is already assigned to selected task with other worker */
            $device_tasks = TasksHasDevices::find()->joinWith('worker')->where([
                'device_id' => $entry['id'], 'tasks_has_devices.task_name' => $this->task_name
            ]);

            $data[$key]['other_device_task'] = [];

            if ($device_tasks->exists()) {
                $device = $device_tasks->asArray()->one();
                if ($device['worker_id'] != $this->worker_id) {
                    $data[$key]['other_device_task'] = [
                        'task_name'   => $device['task_name'],
                        'worker_id'   => $device['worker_id'],
                        'worker_name' => $device['worker']['name']
                    ];
                }
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
