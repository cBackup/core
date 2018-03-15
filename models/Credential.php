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

namespace app\models;

use Yii;
use \yii\db\ActiveRecord;
use yii\helpers\Html;


/**
 * This is the model class for table "{{%credential}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $telnet_login
 * @property string $telnet_password
 * @property string $ssh_login
 * @property string $ssh_password
 * @property string $snmp_read
 * @property string $snmp_set
 * @property integer $snmp_version
 * @property string $snmp_encryption
 * @property string $enable_password
 * @property integer $port_telnet
 * @property integer $port_ssh
 * @property integer $port_snmp
 *
 * @property Network[] $networks
 * @property Node[] $nodes
 *
 * @package app\models
 */
class Credential extends ActiveRecord
{

    /**
     * @var string
     */
    public $network_ip;

    /**
     * @var string
     */
    public $node_name;

    /**
     * Static popover options
     *
     * @var array
     */
    public $static_popover_options = [
        'class'              => 'popup',
        'data-container'     => 'body',
        'data-toggle'        => 'popover',
        'data-click-handler' => 'custom',
        'data-html'          => 'true',
        'data-placement'     => 'right',
        'data-trigger'       => 'manual',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%credential}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'telnet_login', 'telnet_password', 'ssh_login', 'ssh_password', 'snmp_read', 'snmp_set', 'snmp_encryption', 'enable_password'], 'filter', 'filter' => 'trim'],
            [['name'], 'unique'],
            [['snmp_version', 'port_telnet', 'port_ssh', 'port_snmp'], 'integer'],
            [['port_telnet', 'port_ssh', 'port_snmp'], 'integer', 'min' => 1, 'max' => 65535],
            [['snmp_version'], 'in', 'range' => [0, 1]],
            [['name', 'telnet_login', 'telnet_password', 'ssh_login', 'ssh_password', 'snmp_read', 'snmp_set', 'snmp_encryption', 'enable_password'], 'string', 'max' => 128],
            [['snmp_read'], 'required', 'when' => function($model) { /** @var $model Credential */return (!empty($model->snmp_set)); }],
            [['telnet_login', 'telnet_password', 'ssh_login', 'ssh_password', 'snmp_read', 'snmp_set', 'snmp_encryption', 'enable_password'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'name'            => Yii::t('app', 'Name'),
            'telnet_login'    => Yii::t('network', 'Telnet Login'),
            'telnet_password' => Yii::t('network', 'Telnet Password'),
            'ssh_login'       => Yii::t('network', 'SSH Login'),
            'ssh_password'    => Yii::t('network', 'SSH Password'),
            'snmp_read'       => Yii::t('network', 'Read community'),
            'snmp_set'        => Yii::t('network', 'Set community'),
            'snmp_version'    => Yii::t('network', 'SNMP version'),
            'snmp_encryption' => Yii::t('network', 'SNMP encryption'),
            'enable_password' => Yii::t('network', 'Privileged mode password'),
            'port_telnet'     => Yii::t('network', 'Telnet port'),
            'port_ssh'        => Yii::t('network', 'SSH port'),
            'port_snmp'       => Yii::t('network', 'SNMP port'),
            'network_ip'      => Yii::t('app', 'Subnets'),
            'node_name'       => Yii::t('node', 'Nodes'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNetworks()
    {
        return $this->hasMany(Network::class, ['credential_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNodes()
    {
        return $this->hasMany(Node::class, ['credential_id' => 'id']);
    }

    /**
     * Render list of networks in popover
     *
     * @return null|string
     */
    public function renderNetworkList()
    {

        $model   = Network::find();
        $content = null;

        $networks = $model->select('network, description')
            ->where(['credential_id' => $this->id])
            ->limit(5)->asArray()->all();

        $count = $model->count();

        if (!empty($networks)) {

            $content = $networks[0]['network'];

            if ($count > 1) {

                $network_list = array_map(function ($net) {
                    return Html::tag('span', $net['network'], ['title' => trim($net['description'])]);
                }, $networks);

                if( $count > 5 ) {
                    $network_list[] = '<span>&lt;...&gt;</span>';
                }

                $link_to_list = Html::a('&nbsp;<i class="fa fa-external-link"></i>', ['/network/subnet/list',
                    'NetworkSearch[credential_name]' => $this->name
                ] , [
                    'title'  => Yii::t('network', 'View credential networks'),
                    'target' => '_blank'
                ]);

                $dynamic_popover_options = [
                    'data-original-title' => Yii::t('network', 'List of networks {0}', $link_to_list),
                    'data-content'        => implode('<br>', $network_list),
                ];

                $link_text = Yii::t('network', 'View network list {0}', Html::tag('span', $count, ['class' => 'link-label-small label label-success']));
                $content   = Html::a($link_text, 'javascript:void(0);', array_merge($this->static_popover_options, $dynamic_popover_options));

            }
        }

        return $content;

    }

    /**
     * Render list of nodes in popover
     *
     * @return null|string
     */
    public function renderNodeList()
    {

        $model   = Node::find();
        $content = null;

        $nodes = $model->select('ip, hostname as name')
            ->where(['credential_id' => $this->id])
            ->limit(5)->asArray()->all();

        $count = $model->count();

        if (!empty($nodes)) {

            $content = $nodes[0]['name'];

            if ($count > 1) {

                $node_list = array_map(function ($node) {
                    return Html::tag('span', $node['ip'], ['title' => $node['name']]);
                }, $nodes);

                if( $count > 5 ) {
                    $node_list[] = '<span>&lt;...&gt;</span>';
                }

                $link_to_list = Html::a('&nbsp;<i class="fa fa-external-link"></i>', ['/node/list',
                    'NodeSearch[credential_id]' => $this->id
                ], [
                    'title'  => Yii::t('network', 'View credential nodes'),
                    'target' => '_blank'
                ]);

                $dynamic_popover_options = [
                    'data-original-title' => Yii::t('network', 'List of nodes {0}', $link_to_list),
                    'data-content'        => implode('<br>', $node_list),
                ];

                $link_text = Yii::t('network', 'View nodes list {0}', Html::tag('span', $count, ['class' => 'link-label-small label label-success']));
                $content   = Html::a($link_text, 'javascript:void(0);', array_merge($this->static_popover_options, $dynamic_popover_options));

            }
        }

        return $content;

    }

    /**
     * Check if credential is attached to one or more objects
     *
     * @return bool
     */
    public function isCredentialAttached()
    {
        $exists_in_network = Network::find()->where(['credential_id' => $this->id])->exists();
        $exists_in_node    = Node::find()->where(['credential_id' => $this->id])->exists();

        return ($exists_in_network || $exists_in_node) ? true : false;
    }

}
