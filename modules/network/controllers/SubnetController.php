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
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\search\NetworkSearch;
use app\models\Network;
use app\models\Credential;


/**
 * @package app\modules\network\controllers
 */
class SubnetController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'      => ['post'],
                    'ajax-delete' => ['post']
                ],
            ],
        ];
    }

    /**
     * Render list of subnets
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new NetworkSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    /**
     * Add new subnet
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new Network();

        if (isset($_POST['Network'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('network', 'New subnet was successfully added.'));
                    } else {
                        \Y::flash('danger', Yii::t('network', 'An error occurred while adding new subnet.'));
                    }

                    return $this->redirect(['/network/subnet/list']);

                }
            }
        }

        return $this->render('_form', [
            'model'     => $model,
            'cred_list' => ArrayHelper::map(Credential::find()->all(), 'id', 'name')
        ]);

    }


    /**
     * Edit subnet
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {

        $model = $this->findModel($id);

        if (isset($_POST['Network'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('network', 'Subnet <b>{0}</b> was successfully edited.', $model->network));
                    } else {
                        \Y::flash('danger', Yii::t('network', 'An error occurred while editing subnet <b>{0}</b>.', $model->network));
                    }

                    return $this->redirect(['/network/subnet/list']);

                }
            }
        }

        return $this->render('_form', [
            'model'     => $model,
            'cred_list' => ArrayHelper::map(Credential::find()->all(), 'id', 'name')
        ]);

    }


    /**
     * Delete subnet
     *
     * @param  string $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('network', 'Subnet <b>{0}</b> was successfully deleted.', $model->network);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('network', 'An error occurred while deleting subnet <b>{0}</b>.', $model->network);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/network/subnet/list']);

    }


    /**
     * Delete subnet
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($id)
    {

        $model = $this->findModel($id);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('network', 'Subnet <b>{0}</b> was successfully deleted.',$model->network)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('network', 'An error occurred while deleting subnet <b>{0}</b>.', $model->network)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Finds the Network model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $id
     * @return Network the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Network::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
