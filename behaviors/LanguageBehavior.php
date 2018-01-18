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

namespace app\behaviors;

use Yii;
use app\models\Setting;
use yii\base\Behavior;
use yii\web\Cookie;


/**
 * @package app\behaviors
 */
class LanguageBehavior extends Behavior
{

    /**
     * @inheritdoc
     */
    public function init()
    {

        if(!Yii::$app->user->isGuest) {
            Yii::$app->language = Setting::get('language');
        }
        else {

            $cookie = Yii::$app->request->cookies->get('language');

            if ( is_null($cookie) ) {
                Yii::$app->language = Setting::find()->select('value')->where(['key' => 'language'])->scalar();
            }
            else {
                Yii::$app->language = $cookie->value;
            }

        }

        Yii::$app->response->cookies->add(new Cookie([
            'name'  => 'language',
            'value' => Yii::$app->language,
        ]));

    }

}
