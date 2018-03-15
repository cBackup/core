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
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use app\models\Messages;
use app\models\search\MessagesSearch;
use app\models\User;
use app\widgets\MessageWidget;


/**
 * @package app\controllers
 */
class MessageController extends Controller
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
                    'ajax-approve',
                    'ajax-update-widget'
                ]
            ]
        ];
    }


    /**
     * Render list of messages
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new MessagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'users'        => (new User())->getUsers('name'),
        ]);
    }


    /**
     * Mark message as approved
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxApprove($id)
    {

        $response = ['status' => 'error', 'msg' => Yii::t('user', 'Something went wrong. <br> View system logs for more info.')];

        if (Yii::$app->request->isAjax) {

            $model              = $this->findModel($id);
            $model->approved    = date('Y-m-d H:i:s');
            $model->approved_by = Yii::$app->user->id;

            if ($model->save()) {
                $response = [
                    'status' => 'success',
                    'msg'    => Yii::t('app', 'Message successfully acknowledged')
                ];
            }
            else {
                $response = [
                    'status' => 'error',
                    'msg'    => Yii::t('app', 'An error occurred while updating message status')
                ];
            }

        }

        return Json::encode($response);

    }


    /**
     * Render message widget when message is marked as done
     *
     * @return string
     * @throws \Exception
     */
    public function actionAjaxUpdateWidget()
    {
        return MessageWidget::widget();
    }


    /**
     * Finds the Messages model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $name
     * @return Messages the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = Messages::findOne($name)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
