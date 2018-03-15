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
use yii\base\Model;
use yii\helpers\Inflector;


/**
 * @package app\models
 */
class NodeAltInterfaceActions extends Model
{

    /**
     * @var int
     */
    public $node_id;

    /**
     * @var string
     */
    public $alt_ip;

    /**
     * @var string
     */
    public $action_type;

    /**
     * @var int
     */
    public $network_id;

    /**
     * @var Node
     */
    private $node;


    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->node = new Node();
    }


    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['node_id', 'alt_ip', 'action_type'], 'required'],
            [['node_id', 'network_id'], 'integer', 'min' => 1],
            [['alt_ip'], 'ip', 'ipv6' => false],
            [['action_type'], 'in', 'range' => ['set_primary', 'add_exclusion', 'remove_exclusion'], 'strict' => true],
            [['action_type'], 'filter', 'filter' => function($value) {
                return Inflector::variablize($value);
            }],
        ];
    }


    /**
     * @throws \RuntimeException
     * @return bool
     */
    public function run(): bool
    {

        if(!method_exists($this, $this->action_type)) {
            throw new \RuntimeException('Undefined method ' . self::class .'::'. $this->action_type.'()');
        }

        return $this->{$this->action_type}();

    }


    /** @noinspection PhpUnusedPrivateMethodInspection
     *  @throws \Exception
     *  @return bool
     */
    private function setPrimary(): bool
    {

        $node        = Node::findOne($this->node_id);
        $alt_int     = AltInterface::find()->where(['ip' => $this->alt_ip])->one();
        $transaction = Yii::$app->db->beginTransaction();

        try {

            /** Set new network id */
            if (!empty($this->network_id)) {
                $node->network_id = $this->network_id;
            }

            /** Swap primary ip to alternative ip */
            $alt_int->ip = $node->ip;
            $node->ip    = $this->alt_ip;

            if ($node->validate()) {
                if (!$alt_int->save() || !$node->save()) {
                    $transaction->rollBack();
                    throw new \Exception(Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $node->hostname));
                }

                $transaction->commit();
                return true;

            } else {

                if ($node->wrong_subnet) {
                    return false;
                } else {
                    $node_errors = implode("&", array_map(function ($a) { return implode("; ", $a);}, $node->getErrors()));
                    throw new \Exception(Yii::t('app', 'An error occurred while editing record <b>{0} - {1}</b>.', [$node->hostname, $node_errors]));
                }

            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new \Exception($e->getMessage());
        }

    }


    /** @noinspection PhpUnusedPrivateMethodInspection
     *  @throws \Exception
     *  @return bool
     */
    private function addExclusion(): bool
    {

        $model = new Exclusion();

        /** Check if IP already exists */
        if (Exclusion::exists($this->alt_ip)) {
            throw new \Exception(Yii::t('node', 'IP-address {0} already exists in exclusions', $this->alt_ip));
        }

        $model->ip = $this->alt_ip;
        return $model->save();

    }


    /** @noinspection PhpUnusedPrivateMethodInspection, PhpUndefinedClassInspection
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @return bool
     */
    private function removeExclusion(): bool
    {
        $query = Exclusion::findOne(['ip' => $this->alt_ip])->delete();
        return ($query >= 1) ? true : false;
    }

}
