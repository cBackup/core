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

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Json;
use yii\filters\AjaxFilter;
use app\widgets\ServiceWidget;
use app\models\Node;
use app\models\OutBackup;
use app\models\LogNode;
use app\models\LogScheduler;
use app\models\LogSystem;
use app\models\search\NodeSearch;
use app\components\Service;

/**
 * @package app\controllers
 */
class SiteController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'ajax' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-render-service',
                    'ajax-run-service'
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }


    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

        $searchModel  = new NodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dashb_stats  = Yii::$app->cache->getOrSet('dashboard_stats', function () {
            return [
                'nodes'      => Node::find()->count(),
                'backups'    => OutBackup::find()->count(),
                'discovery'  => LogScheduler::find()->joinWith('schedule')->where(['action' => 'TASK FINISH', 'schedule.task_name' => 'discovery'])->orderBy(['time' => SORT_DESC])->one(),
                'backup'     => LogScheduler::find()->joinWith('schedule')->where(['action' => 'TASK FINISH', 'schedule.task_name' => 'backup'])->orderBy(['time' => SORT_DESC])->one(),
            ];
        }, 60);
        $dashb_stats['disk_total'] = @disk_total_space(Yii::$app->params['dataPath']);
        $dashb_stats['disk_free']  = @disk_free_space(Yii::$app->params['dataPath']);

        return $this->render('index',[
            'scheduler_logs'  => LogScheduler::find()->limit(13)->orderBy('id DESC')->asArray()->all(),
            'node_logs'       => LogNode::find()->limit(13)->orderBy('id DESC')->asArray()->all(),
            'system_logs'     => LogSystem::find()->limit(13)->orderBy('id DESC')->asArray()->all(),
            'orphan_count'    => Node::getOrphans(true),
            'dataProvider'    => $dataProvider,
            'dashboard_stats' => $dashb_stats,
            'searchModel'     => $searchModel
        ]);

    }


    /**
     * Render maintenance mode page
     *
     * @return string
     */
    public function actionMaintenance()
    {
        return $this->render('maintenance');
    }


    /**
     * Render Service widget via Ajax on demand
     *
     * @return string
     */
    public function actionAjaxRenderService()
    {

        try {
            $response = [
                'status' => 'success',
                'data'   => ServiceWidget::widget()
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'data'   => $e->getMessage()
            ];
        }

        return Json::encode($response);

    }


    /**
     * Set service status via Ajax
     *
     * @param  int $mode
     * @return string
     */
    public function actionAjaxRunService($mode)
    {

        try {

            switch ((int)$mode) {
                case 0: $action = 'start';   break;
                case 1: $action = 'stop';    break;
                case 2: $action = 'restart'; break;
                default: throw new \Exception(Yii::t('app', 'Unknown Java service mode specified'));
            }

            (new Service())->init()->$action();
            $response = ['status' => 'success', 'msg' => ''];


        } catch (\Exception $e) {
            $response = ['status' => 'danger', 'msg' => $e->getMessage()];
        }

        return Json::encode($response);

    }

}
