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
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AjaxFilter;
use app\models\search\CredentialSearch;
use app\models\Credential;
use app\models\CredentialTest;


/**
 * @package app\modules\network\controllers
 */
class CredentialController extends Controller
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
                    'delete' => ['post'],
                    'ajax-test' => ['post'],
                ],
            ],
            'ajaxonly' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-add-credential',
                    'ajax-get-credentials',
                    'ajax-test',
                    'ajax-delete'
                ]
            ]
        ];
    }


    /**
     * Render list of credentials
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new CredentialSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    /**
     * Add new credential
     *
     * @return string|Response
     */
    public function actionAdd()
    {

        $model = new Credential();
        $model->loadDefaultValues();

        if (isset($_POST['Credential'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('network', 'New credential was successfully added.'));
                    } else {
                        \Y::flash('danger', Yii::t('network', 'An error occurred while adding new credential.'));
                    }

                    return $this->redirect(Url::to(['/network/credential/edit', 'id' => $model->id]));

                }
            }
        }

        return $this->render('_form', [
            'model' => $model
        ]);

    }


    /**
     * Edit credential
     *
     * @param  int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {

        $model = $this->findModel($id);

        if (isset($_POST['Credential'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('network', 'Credential <b>{0}</b> was successfully edited.', $model->name));
                    } else {
                        \Y::flash('danger', Yii::t('network', 'An error occurred while editing credential <b>{0}</b>.', $model->name));
                    }

                    return $this->redirect(Url::to(['/network/credential/edit', 'id' => $model->id]));

                }
            }
        }

        return $this->render('_form', [
            'model' => $model
        ]);

    }


    /**
     * Delete credential via POST
     *
     * @param  string $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        /** Check if credential is attached to one or more objects */
        if ($model->isCredentialAttached()) {
            \Y::flash('danger', Yii::t('network', 'Credential <b>{0}</b> is attached to one or more objects.', $model->name));
            return $this->redirect(Url::to(['/network/credential/edit', 'id' => $model->id]));
        }

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('network', 'Credential <b>{0}</b> was successfully deleted.', $model->name);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('network', 'An error occurred while deleting credential <b>{0}</b>.', $model->name);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect(['/network/credential/list']);

    }


    /**
     * Delete credential via AJAX
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($id)
    {

        $model = $this->findModel($id);

        /** Check if credential is attached to one or more objects */
        if ($model->isCredentialAttached()) {
            return Json::encode([
                'status' => 'warning',
                'msg'    => Yii::t('network', 'Credential <b>{0}</b> is attached to one or more objects.', $model->name)
            ]);
        }

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('network', 'Credential <b>{0}</b> was successfully deleted.', $model->name)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('network', 'An error occurred while deleting credential <b>{0}</b>.', $model->name)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Add new credential via AJAX
     *
     * @return string
     */
    public function actionAjaxAddCredential()
    {

        $model = new Credential();
        $model->loadDefaultValues();

        if (isset($_POST['Credential'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        $status = Json::encode([
                            'status' => 'success',
                            'msg'    => Yii::t('network', 'New credential was successfully added.')
                        ]);

                    } else {
                        $status = Json::encode([
                            'status' => 'error',
                            'msg'    => Yii::t('network', 'An error occurred while adding new credential.')
                        ]);
                    }

                    return $status;

                } else {
                    return Json::encode(['status' => 'validation_failed', 'error' => $model->errors]);
                }

            }
        }

        return $this->renderPartial('_add_modal', [
            'model' => $model
        ]);

    }


    /**
     * Get credentials for select2 via AJAX
     *
     * @return string
     */
    public function actionAjaxGetCredentials()
    {
        $credentials = Credential::find()->select('id, name as text')->asArray()->all();
        return Json::encode($credentials);
    }


    /**
     * Render full credential info in expandable table row
     *
     * @param  int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxGetCredential($id)
    {
        return $this->renderPartial('_info', [
            'data' => $this->findModel($id)
        ]);
    }


    /**
     * Test credentials
     *
     * @return array
     */
    public function actionAjaxTest()
    {

        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new CredentialTest();
        $model->load(Yii::$app->request->post(), 'Credential');

        $model->ip              = Yii::$app->request->post('ip');
        $model->login_prompt    = Yii::$app->request->post('login_prompt');
        $model->password_prompt = Yii::$app->request->post('password_prompt');
        $model->enable_prompt   = Yii::$app->request->post('enable_prompt');
        $model->enable_success  = Yii::$app->request->post('enable_success');
        $model->main_prompt     = Yii::$app->request->post('main_prompt');

        if( !$model->validate() ) {
            Yii::$app->response->statusCode = 400;
            return $model->getErrors();
        }
        else {
            return $model->run();
        }

    }


    /**
     * Finds the Exclusion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $ip
     * @return Credential the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($ip)
    {
        if (($model = Credential::findOne($ip)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
