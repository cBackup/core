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
use app\models\LogMailer;

/**
 * LogMailerSearch represents the model behind the search form about `app\models\LogMailer`.
 * @package app\models\search
 */
class LogMailerSearch extends LogMailer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'event_task_id'], 'integer'],
            [['userid', 'time', 'severity', 'action', 'message', 'date_from', 'date_to', 'page_size'], 'safe'],
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
        $query = LogMailer::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        /** Set page size dynamically */
        $dataProvider->pagination->pageSize = $this->page_size;

        /** Time interval search  */
        if (!empty($this->date_from) && !empty($this->date_to)) {
            $query->andFilterWhere(['between', 'CAST(time AS DATE)', $this->date_from, $this->date_to]);
        }
        elseif (!empty($this->date_from)) {
            $query->andFilterWhere(['>=', 'CAST(time AS DATE)', $this->date_from]);
        }
        elseif (!empty($this->date_to)) {
            $query->andFilterWhere(['<=', 'CAST(time AS DATE)', $this->date_to]);
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'time' => $this->time,
            'event_task_id' => $this->event_task_id,
        ]);

        $query->andFilterWhere(['like', 'userid', $this->userid])
            ->andFilterWhere(['like', 'severity', $this->severity])
            ->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
