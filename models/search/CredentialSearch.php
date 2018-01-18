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
use yii\db\Query;
use yii\data\ActiveDataProvider;
use app\models\Credential;


/**
 * CredentialSearch represents the model behind the search form about `app\models\Credential`.
 * @package app\models\search
 */
class CredentialSearch extends Credential
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'snmp_version', 'port_telnet', 'port_ssh', 'port_snmp'], 'integer'],
            [['name', 'telnet_login', 'telnet_password', 'ssh_login', 'ssh_password', 'snmp_read', 'snmp_set', 'snmp_encryption', 'enable_password', 'network_ip', 'node_name'], 'safe'],
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
        $query = Credential::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['name' => SORT_ASC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'snmp_version' => $this->snmp_version,
            'port_telnet' => $this->port_telnet,
            'port_ssh' => $this->port_ssh,
            'port_snmp' => $this->port_snmp,
        ]);

        /** Find credentials by network IP */
        if (!empty($this->network_ip)) {
            $net_cred_ids = $this->findByNetworkIp();
            $net_cred_ids = (!empty($net_cred_ids)) ? $net_cred_ids : '0=1';
            $query->andFilterWhere(['id' => $net_cred_ids]);
        }

        /** Find credentials by node hostname */
        if (!empty($this->node_name)) {
            $node_cred_ids = $this->findByNodeName();
            $node_cred_ids = (!empty($node_cred_ids)) ? $node_cred_ids : '0=1';
            $query->andFilterWhere(['id' => $node_cred_ids]);
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'telnet_login', $this->telnet_login])
            ->andFilterWhere(['like', 'telnet_password', $this->telnet_password])
            ->andFilterWhere(['like', 'ssh_login', $this->ssh_login])
            ->andFilterWhere(['like', 'ssh_password', $this->ssh_password])
            ->andFilterWhere(['like', 'snmp_read', $this->snmp_read])
            ->andFilterWhere(['like', 'snmp_set', $this->snmp_set])
            ->andFilterWhere(['like', 'snmp_encryption', $this->snmp_encryption])
            ->andFilterWhere(['like', 'enable_password', $this->enable_password]);

        return $dataProvider;
    }

    /**
     * Get credential ID's by network IP
     *
     * @return array
     */
    private function findByNetworkIp()
    {
        return (new Query())
            ->select('credential_id')
            ->from('{{%network}}')
            ->where(['like', 'network', $this->network_ip])
            ->column()
        ;
    }

    /**
     * Get credential ID's by node hostname
     *
     * @return array
     */
    private function findByNodeName()
    {
        return (new Query())
            ->select('credential_id')
            ->from('{{%node}}')
            ->where(['like', 'hostname', $this->node_name])
            ->column()
        ;
    }

}
