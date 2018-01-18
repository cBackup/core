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
use \yii\db\Query;


/**
 * This is the model class for table "{{%alt_interface}}".
 *
 * @property integer $id
 * @property integer $node_id
 * @property string $ip
 *
 * @property Node $node
 *
 * @package app\models
 */
class AltInterface extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%alt_interface}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['node_id', 'ip'], 'required'],
            [['node_id'], 'integer'],
            [['ip'], 'string', 'max' => 15],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => Node::className(), 'targetAttribute' => ['node_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'      => Yii::t('app', 'ID'),
            'node_id' => Yii::t('app', 'Node ID'),
            'ip'      => Yii::t('app', 'Ip'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(Node::className(), ['id' => 'node_id']);
    }


    /**
     * Updating alt interfaces
     *
     * @param $ips    array
     * @param $nodeIp string
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function updateInterfaces($nodeIp, $ips)
    {
        $success = true;

        $nodeId = Node::find()->select(['id'])->where(['ip' => $nodeIp])->scalar();

        if(empty($nodeId)) {
            return false;
        }

        /*
         * Get old interfaces
         */
        $oldAltInterfaces = AltInterface::find()->select(['ip'])->where(['node_id' => $nodeId])->asArray()->column();

        /*
         * No changes case
         */
        if(empty(array_merge(array_diff($ips, $oldAltInterfaces), array_diff($oldAltInterfaces, $ips)))) {
            return true;
        }


        $transaction = Yii::$app->db->beginTransaction();

        /*
         * Deleting old interfaces
         */
        if(!empty($oldAltInterfaces)) {
            try {
                (new Query)
                    ->createCommand()
                    ->delete('{{%alt_interface}}', ['node_id' => $nodeId])
                    ->execute();
            }
            /** @noinspection PhpUndefinedClassInspection */
            catch (\Throwable $e) {
                $success = false;
            }
        }

        /*
         * Creating new interfaces
         */
        if($success && !empty($ips)) {

            $insertArray = [];
            foreach($ips as $ip) {
                $insertArray[]=[
                    'node_id' => $nodeId,
                    'ip'      => $ip,
                ];
            }

            try {
                $insertCount = Yii::$app->db->createCommand()
                    ->batchInsert(
                        '{{%alt_interface}}', ['node_id', 'ip'], $insertArray
                    )
                    ->execute();

                if($insertCount !== count($insertArray)) {
                    $success = false;
                }
            }
            /** @noinspection PhpUndefinedClassInspection */
            catch (\Throwable $e) {
                $success = false;
            }
        }

        /*
         * Log write
         */
        if($success) {

            $logNode = new LogNode();
            $logNode->severity = 'INFO';
            $logNode->node_id  = $nodeId;
            $logNode->action  = 'UPDATE';
            $logNode->message = 'Node alt interfaces changed.';
            $success = $logNode->save();
        }

        if($success) {
            $transaction->commit();
        }
        else {
            $transaction->rollBack();
        }

        return $success;
    }
}
