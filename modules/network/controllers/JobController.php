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

use app\models\Job;
use app\models\TasksHasDevices;
use app\models\Worker;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AjaxFilter;


/**
 * @package app\modules\network\controllers
 */
class JobController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-get-jobs'
                ]
            ]
        ];
    }


    /**
     * @param  null $task_name
     * @param  null $device_id
     * @param  null $worker_id
     * @return string
     */
    public function actionAjaxGetJobs($task_name = null, $device_id = null, $worker_id = null)
    {

        /** Show jobs inheritance message  */
        $show_msg = false;
        $jobs     = [];

        /** Get worker id if not given */
        if (is_null($worker_id)) {
            $worker_id = TasksHasDevices::find()
                ->select(['worker_id'])
                ->where(['task_name' => $task_name, 'device_id' => $device_id])
                ->scalar()
            ;
            $show_msg = true;
        }

        /** Get worker jobs only if worker id is not empty */
        if (!empty($worker_id)) {
            $jobs = Worker::find()->joinWith(['sortedJobs'])->where(['worker.id' => intval($worker_id)])->one()->sortedJobs;
        }

        return $this->renderPartial('//node/_job_view', [
            'show_msg' => $show_msg,
            'jobs'     => $jobs
        ]);

    }


    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $name
     * @return Job the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = Job::findOne($name)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
