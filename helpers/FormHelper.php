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

namespace app\helpers;

use Yii;
use yii\db\ActiveRecord;


/**
 * @package app\helpers
 */
class FormHelper
{

    /**
     * @param  ActiveRecord $model
     * @param  string       $attribute  attribute label, comes from model, already processed by Yii:t()
     * @param  bool         $prefix     if prefix 'enter ...smth' should be added
     * @return string
     */
    public static function label($model, $attribute, $prefix = true)
    {
        if( $prefix ) {
            return Yii::t('app', 'Enter') . ' ' . mb_strtolower($model->getAttributeLabel($attribute));
        }
        else {
            return $model->getAttributeLabel($attribute);
        }
    }

}