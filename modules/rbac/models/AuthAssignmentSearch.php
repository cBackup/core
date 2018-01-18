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

namespace app\modules\rbac\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * AuthAssignmentSearch represents the model behind the search form about `app\modules\rbac\models\AuthAssignment`.
 * @package app\modules\rbac\models
 */
class AuthAssignmentSearch extends AuthAssignment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id', 'name_search', 'type'], 'safe'],
            [['created_at'], 'integer'],
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AuthAssignment::find();

        $query->joinWith(['user u', 'itemName i']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'name_search' => SORT_DESC,
                    'type'        => SORT_ASC
                ],
                'attributes'   => [
                    'name_search' => [
                        'asc'  => ['u.fullname' => SORT_ASC],
                        'desc' => ['u.fullname' => SORT_DESC],
                    ],
                    'type' => [
                        'asc'  => ['i.type' => SORT_ASC],
                        'desc' => ['i.type' => SORT_DESC],
                    ]
                ],
                'enableMultiSort' => true
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'item_name', $this->item_name])
            ->andFilterWhere(['like', 'u.fullname', $this->name_search])
            ->andFilterWhere(['like', 'i.type', $this->type]);

        return $dataProvider;
    }

}
