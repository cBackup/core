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

namespace app\assets;

use Yii;
use yii\web\AssetBundle;


/**
 * @package app\assets
 */
class i18nextAsset extends AssetBundle
{

    public $basePath  = '@webroot';
    public $baseUrl   = '@web';

    public $js        = [
        'plugins/i18next/3.0.0/i18next.min.js',
        'plugins/i18next/3.0.0/i18nextXHRBackend.min.js'
    ];

    public $depends   = [
        'yii\web\YiiAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {

        parent::init();

        $language = Yii::$app->language;

        Yii::$app->getView()->registerJs(
            /** @lang JavaScript */
            "
                i18next.use(i18nextXHRBackend).init({
                    load:  'currentOnly',
                    lng:   '$language',
                    nsSeparator:  false,
                    keySeparator: false,
                    fallbackLng:  false,
                    backend: {
                        // path where resources get loaded from
                        loadPath: 'messages/{{lng}}/{{ns}}.json',
                        // your backend server supports multiloading
                        allowMultiLoading: false
                    }
                });
            "
        );

    }

}
