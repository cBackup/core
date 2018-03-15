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
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Exclusion;
use app\models\search\ExclusionSearch;
use app\models\Node;
use app\models\AltInterface;
use yii\filters\AjaxFilter;


/**
 * @package app\modules\network\controllers
 */
class ExclusionController extends Controller
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
                'class'   => VerbFilter::class,
                'actions' => [
                    'ajax-delete'        => ['post'],
                    'ajax-get-node-info' => ['post']
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-delete',
                    'ajax-get-node-info',
                ]
            ]
        ];
    }


    /**
     * Render list of exclusions
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new ExclusionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    /**
     * Add new exclusion
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new Exclusion();

        if (isset($_POST['Exclusion'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    $model->show_warning = true;

                    if ($model->save_on_warning == '1') {

                        if ($model->save()) {
                            \Y::flash('success', Yii::t('network', 'New exclusion was successfully added.'));
                        } else {
                            \Y::flash('danger', Yii::t('network', 'An error occurred while adding new exclusion.'));
                        }

                        return $this->redirect(['list']);
                    }

                }
            }
        }

        return $this->render('_form', [
            'model' => $model,
            'data'  => null,
        ]);

    }


    /**
     * Edit exclusion
     *
     * @param  string $ip
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($ip)
    {

        $model = $this->findModel($ip);

        if (isset($_POST['Exclusion'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('network', 'Exclusion <b>{0}</b> was successfully edited.', $model->ip));
                    } else {
                        \Y::flash('danger', Yii::t('network', 'An error occurred while editing exclusion <b>{0}</b>.', $model->ip));
                    }

                    return $this->redirect(['/network/exclusion/list']);

                }
            }
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        $node = Node::find()->where(['ip' => $model->ip])->with('altInterfaces')->one();
        $alt  = AltInterface::find()->where(['ip' => $model->ip])->with('node')->one();

        if( is_null($alt) ) {
            $obj  = 'node';
            $data = ${$obj};
        }
        else {
            $obj  = 'alt';
            $data = ${$obj}->node;
        }

        return $this->render('_form', [
            'model' => $model,
            'data'  => $data,
        ]);

    }


    /**
     * @param  string $ip
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($ip)
    {

        $model = $this->findModel($ip);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('network', 'Exclusion <b>{0}</b> was successfully deleted.', $model->ip);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('network', 'An error occurred while deleting exclusion <b>{0}</b>.', $model->ip);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/network/exclusion/list']);

    }


    /**
     * Delete exclusion
     *
     * @param  string $ip
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($ip)
    {

        $model = $this->findModel($ip);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('network', 'Exclusion <b>{0}</b> was successfully deleted.',$model->ip)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('network', 'An error occurred while deleting exclusion <b>{0}</b>.', $model->ip)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Get node info via AJAX
     *
     * @param  string $ip
     * @return string
     */
    public function actionAjaxGetNodeInfo($ip)
    {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $node = Node::findOne(['ip' => $ip]);
        $alt  = AltInterface::find()->where(['ip' => $ip])->with('node')->one();

        if( is_null($alt) ) {
            $obj  = 'node';
            $data = ${$obj};
        }
        else {
            $obj  = 'alt';
            $data = ${$obj}->node;
        }

        return $this->renderAjax('_view', [
            'data' => $data,
        ]);

    }


    /**
     * Finds the Exclusion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $ip
     * @return Exclusion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($ip)
    {
        if (($model = Exclusion::findOne($ip)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
