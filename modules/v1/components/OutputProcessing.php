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

namespace app\modules\v1\components;

use Yii;
use yii\base\Component;
use app\models\OutStp;
use app\models\OutBackup;
use app\models\OutCustom;


/**
 * Output functions decomposition
 * 
 * @package app\modules\v1\components
 */
class OutputProcessing extends Component
{

    /**
     * Returns model for insert-update
     *
     * @param $taskName
     * @param $nodeId
     * @return OutBackup|OutCustom|OutStp
     */
    public function getOutputModel(string $taskName, string $nodeId)
    {
        switch($taskName) {

            case 'stp' :

                $id =  OutStp::find()->select('id')->where(['node_id' => $nodeId])->scalar();

                if(!$id) {
                    $model          = new OutStp();
                    $model->node_id = $nodeId;
                }
                else {
                    $model = OutStp::findOne($id);
                }

                break;

            case 'backup':

                $id =  OutBackup::find()->select('id')->where(['node_id' => $nodeId])->scalar();

                if(!$id) {
                    $model          = new OutBackup();
                    $model->node_id = $nodeId;
                }
                else {
                    $model = OutBackup::findOne($id);
                }

                break;

            default:

                $model = new OutCustom(['table' => 'out_'.$taskName]);
                $id    = $model::find()->select('id')->where(['node_id' => $nodeId])->scalar();

                if(!$id) {
                    $model->node_id = $nodeId;
                }
                else {
                    $model = $model::findOne(['id' => $id]);
                }

        }

        return $model;
    }


    /**
     * Returns data save directory
     *
     * @param string $path
     * @param string $taskName
     * @return string
     */
    public function getFullPath(string $path, string $taskName): string
    {
        if(empty($path)) {
            return Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $taskName;
        }
        else {
           return $path . DIRECTORY_SEPARATOR . $taskName;
        }
    }


    /**
     * Concatenating all attributes to file format
     *
     * @param array $attributes
     * @return string
     */
    public function getFileData(array $attributes): string
    {
        $toWrite = '';

        foreach($attributes as $key => $value) {
            $currentKey = mb_strtoupper($key);
            $toWrite .= "##$currentKey##\n\n$value\n\n##ENDOF_$currentKey##\n\n";
        }

        return $toWrite;
    }
}