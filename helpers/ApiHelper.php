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


/**
 * @package app\helpers
 */
class ApiHelper
{

    /**
     * @param int $code
     * @param string|null $message
     * @return array
     */
    public static function getResponseBodyByCode($code, $message = null): array
    {

        $return = [
            'name'    => '',
            'message' => '',
            'code'	  => 0,
            'status'  => $code,
            'type'    => '',
        ];

        if(!is_null($message)) {
            $return['message'] = ". " . $message;
        }

        switch($code) {
            case 400:
                $return['name']    = 'Bad request';
                $return['message'] = Yii::t('app', 'Bad request') . $return['message'];
                return $return;
            case 404:
                $return['name']    = 'Not Found';
                $return['message'] = Yii::t('app', 'Resource not found') . $return['message'];
                return $return;
            case 422:
                $return['name']    = 'Unprocessable Entity';
                $return['message'] = Yii::t('app', 'Validation failed')  . $return['message'];
                return $return;
            case 500:
                $return['name']    = 'Internal Server Error';
                $return['message'] = Yii::t('app', 'Internal Server Error')  . $return['message'];
                return $return;
            default:
                return [];
        }
    }

}
