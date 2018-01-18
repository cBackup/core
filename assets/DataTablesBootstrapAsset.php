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

use yii\web\AssetBundle;


/**
 * @package app\assets
 */
class DataTablesBootstrapAsset extends AssetBundle
{

    public $sourcePath = '@bower/datatables';

    public $css = [
        'media/css/dataTables.bootstrap.min.css',
    ];

    public $js = [
        'media/js/jquery.dataTables.js',
        'media/js/dataTables.bootstrap.min.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

    /**
     * Set dataTables translation based on interface language
     */
    public function init()
    {
        parent::init();

        $translation = '{}';
        $view        = \Yii::$app->getView();
        $lang_file   = \Yii::getAlias('@bower') . '/datatables-i18n/i18n/' . strtok(\Yii::$app->language, '-'). '.json';

        /** Check if translation file exists */
        if (file_exists($lang_file)) {
            $translation = file_get_contents($lang_file);
        }

        /** Global DataTable settings */
        $view->registerJs(
            "
                $.extend(true, $.fn.dataTable.defaults, {
                    'language': ".$translation."
                });
            "
        );
    }

}
