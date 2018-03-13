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
use app\models\Network;


/**
 * NetworkSearch represents the model behind the search form about `app\models\Network`.
 * @package app\models\search
 */
class NetworkSearch extends Network
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'credential_id'], 'integer'],
            [['network', 'description', 'credential_name'], 'safe'],
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
        $query = Network::find()->select([
            'network.*',
            'INET_ATON(SUBSTRING_INDEX(network, "/", 1)) as netaddr',
            'SUBSTRING_INDEX(network, "/", -1) as netmask'
        ]);

        $query->joinWith(['credential c']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'network'         => SORT_ASC,
                    'credential_name' => SORT_ASC
                ],
                'attributes'   => [
                    'credential_name' => [
                        'asc'  => ['c.name' => SORT_ASC],
                        'desc' => ['c.name' => SORT_DESC],
                    ],
                    // sort by network address via virtual columns `netaddr` and `netmask`
                    'network' => [
                        'asc'  => [
                            'netaddr' => SORT_ASC,
                            'netmask' => SORT_ASC
                        ],
                        'desc' => [
                            'netaddr' => SORT_DESC,
                            'netmask' => SORT_DESC
                        ],
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
            'credential_id' => $this->credential_id,
        ]);

        $query->andFilterWhere(['like', 'network', $this->network])
            ->andFilterWhere(['like', 'c.name', $this->credential_name])
            ->andFilterWhere(['like', 'description', $this->description]);


        return $dataProvider;
    }
}
