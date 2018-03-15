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
use \yii\helpers\Html;
use \yii\db\ActiveRecord;
use \dautkom\ipv4\IPv4;


/**
 * This is the model class for table "{{%network}}".
 *
 * @property integer $id
 * @property integer $credential_id
 * @property string $network
 * @property integer $discoverable
 * @property string $description
 *
 * @property Credential $credential
 * @property Node[] $nodes
 *
 * @package app\models
 */
class Network extends ActiveRecord
{

    /**
     * @var string
     */
    public $credential_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%network}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['credential_id', 'network'], 'required'],
            [['credential_id', 'discoverable'], 'integer'],
            [['network'], 'string', 'max' => 18],
            [['description'], 'string', 'max' => 255],
            [['network'], 'unique'],
            [['network'], 'checkSubnet'],
            [['credential_id'], 'exist', 'skipOnError' => true, 'targetClass' => Credential::class, 'targetAttribute' => ['credential_id' => 'id']],
            [['description'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'credential_id'   => Yii::t('network', 'Credential name'),
            'network'         => Yii::t('network', 'Subnet'),
            'description'     => Yii::t('app', 'Description'),
            'discoverable'    => Yii::t('network', 'Discoverable'),
            'credential_name' => Yii::t('network', 'Credential name'),
        ];
    }

    /**
     * Check if given subnet address is valid
     *
     * @param $attribute
     */
    public function checkSubnet($attribute)
    {

        if(!preg_match('%^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/\d{1,2}$%m', $this->network)) {
            $this->addError($attribute, Yii::t('network', 'Subnet address must be in CIDR format.<br>Example: 192.168.0.0/26'));
        }
        else {

            $net     = new IPv4();
            $network = explode('/', $this->network);
            $address = $network[0];
            $mask    = $network[1];

            if (!$net->address($address)->mask($mask)->isValid(1)) {
                $this->addError($attribute, Yii::t('network', 'Invalid subnet address'));
            }
        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCredential()
    {
        return $this->hasOne(Credential::class, ['id' => 'credential_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNodes()
    {
        return $this->hasMany(Node::class, ['network_id' => 'id']);
    }

    /**
     * Get network name styled
     *
     * @return string
     */
    public function getNetworkNameStyled()
    {
        $warning = '';

        if ($this->discoverable == 0) {
            $warning = Html::tag('span', '<i class="fa fa-warning"></i>', [
                'class'          => 'margin-r-5 text-danger',
                'data-toggle'    => 'tooltip',
                'data-placement' => 'top',
                'data-html'      => 'true',
                'title'          => Yii::t('network', 'This subnet is excluded from discovery')
            ]);
        }

        $link = Html::a($this->network, ['/network/subnet/edit', 'id' => $this->id], [
            'data-pjax' => '0',
            'title'     => Yii::t('network', 'Edit subnet')
        ]);

        return $warning . $link;
    }

}
