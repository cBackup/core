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

namespace app\modules\network\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use app\models\search\OutCustomSearch;
use app\models\Task;


/**
 * @package app\modules\network\controllers
 */
class OutcustomController extends Controller
{

    /**
     * @var string
     */
    public $defaultAction = 'list';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-render-search',
                    'ajax-render-grid'
                ]
            ]
        ];
    }


    /**
     * Render list view
     *
     * @return string
     * @throws \yii\base\NotSupportedException
     */
    public function actionList()
    {
        return $this->render('list', [
            'out_tables' => Task::getOutTables()
        ]);
    }


    /**
     * Render grid view via Ajax
     *
     * @param  string $table
     * @return string
     */
    public function actionAjaxRenderGrid($table)
    {
        $searchModel  = new OutCustomSearch($table);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderPartial('_grid_view', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    /**
     * Render search view via Ajax
     *
     * @param  string $table
     * @return string
     */
    public function actionAjaxRenderSearch($table)
    {
        $searchModel = new OutCustomSearch($table);

        return $this->renderPartial('_search', [
            'model' => $searchModel,
            'table' => $table
        ]);
    }

}
