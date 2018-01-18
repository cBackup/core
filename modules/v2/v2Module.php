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

namespace app\modules\v2;

use \yii\base\Module;


/**
 * REST API v2 module definition class
 * 
 * @package app\modules\v1
 */
class v2Module extends Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\v2\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {

        parent::init();

        // Disabling session for rest
        \Yii::$app->user->enableSession = false;

        // Show a HTTP 403 error instead of redirecting to the login page
        \Yii::$app->user->loginUrl      = null;

    }

}
