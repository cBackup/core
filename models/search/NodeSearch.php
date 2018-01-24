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

use app\models\Node;
use dautkom\ipv4\IPv4;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;


/**
 * NodeSearch represents the model behind the search form about `app\models\Node`.
 *
 * @package app\models\search
 */
class NodeSearch extends Node
{

    /**
     * @var IPv4
     */
    private $net;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->net = new IPv4();
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'network_id', 'credential_id', 'device_id', 'manual'], 'integer'],
            [['search_string'], 'filter', 'filter' => 'trim'],
            [['ip', 'mac', 'created', 'modified', 'last_seen', 'hostname', 'serial', 'location', 'contact',
                'sys_description', 'search_option', 'search_string', 'device_name', 'auth_template_name', 'prepend_location', 'page_size'], 'safe'],
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
        $query = Node::find();

        $query->joinWith(['device d']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'id', 'hostname', 'location', 'modified',
                    'device_name' => [
                        'asc'  => ['d.model' => SORT_ASC],
                        'desc' => ['d.model' => SORT_DESC]
                    ],
                    // sort by IP
                    'ip' => [
                        'asc'  => [
                            'INET_ATON(ip)' => SORT_ASC
                        ],
                        'desc' => [
                            'INET_ATON(ip)' => SORT_DESC
                        ],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        /** Set page size dynamically */
        $dataProvider->pagination->pageSize = $this->page_size;

        /** Process search from index page */
        if (isset($this->search_string) && $this->validate()) {
            switch ($this->search_option) {
                case 'ip':

                    $range = $this->getRangeOrSubnet($this->search_string);

                    if( count($range) != 2 ) {
                        $query->andFilterWhere(['like', 'ip', $this->search_string]);
                    }
                    // else 'between range' will be performed below

                break;
                case 'hostname': $query->andFilterWhere(['like', 'hostname', $this->search_string]); break;
                case 'location': $query->andFilterWhere(['like', 'location', $this->search_string]); break;
                case 'device': $query->orFilterWhere(['like', "CONCAT(d.vendor, ' ', d.model)", $this->search_string]); break;
            }
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'node.id' => $this->id,
            'network_id' => $this->network_id,
            'credential_id' => $this->credential_id,
            'device_id' => $this->device_id,
            'created' => $this->created,
            'modified' => $this->modified,
            'last_seen' => $this->last_seen,
            'manual' => $this->manual,
        ]);


        if( !isset($this->search_option) && !empty($this->ip) ) {
            $range = $this->getRangeOrSubnet($this->ip);
        }

        if( isset($range) && count($range) == 2 && $this->net->address($range[0])->isValid() && $this->net->address($range[1])->isValid() ) {

            $query->andFilterWhere([
                'between',
                'INET_ATON(ip)',
                new Expression('INET_ATON(:ip1)', [':ip1' => $range[0]]),
                new Expression('INET_ATON(:ip2)', [':ip2' => $range[1]]),
            ]);

        }
        else {
            $query->andFilterWhere(['like', 'ip', $this->ip]);
        }

        $query->andFilterWhere(['like', 'mac', $this->mac])
            ->andFilterWhere(['like', 'hostname', $this->hostname])
            ->andFilterWhere(['like', 'node.auth_template_name', $this->auth_template_name])
            ->andFilterWhere(['like', 'serial', $this->serial])
            ->andFilterWhere(['like', 'prepend_location', $this->prepend_location])
            ->andFilterWhere(['like', 'location', $this->location])
            ->andFilterWhere(['like', 'contact', $this->contact])
            ->andFilterWhere(['like', 'sys_description', $this->sys_description])
            ->orFilterWhere(['like', "CONCAT(d.vendor, ' ', d.model)", $this->device_name]);

        return $dataProvider;
    }


    /**
     * @param  string $query
     * @return array
     */
    private function getRangeOrSubnet($query)
    {

        // For determining search criteria as 192.168.10.1-5
        $range = explode('-', $query);

        // For determining search criteria as 192.168.10.0/24
        if( count($range) < 2 && $this->net->subnet($query)->isValid() ) {
            $range = $this->net->subnet($query)->getRange();
        }

        if( count($range) == 2 ) {
            if (is_numeric($range[1])) {
                $range[1] = substr_replace($range[0], ".{$range[1]}", strripos($range[0], '.'));
            }
        }

        return $range;

    }

}
