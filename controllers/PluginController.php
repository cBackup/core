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
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use app\models\Plugin;
use app\models\search\PluginSearch;
use app\modules\rbac\models\AuthItem;


/**
 * @package app\controllers
 */
class PluginController extends Controller
{

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
            'ajax' => [
                'class' => AjaxFilter::class,
                'only'  => [
                    'ajax-install-plugin',
                    'ajax-switch-mode',
                    'ajax-delete-plugin'
                ]
            ],
        ];
    }

    /**
     * Render index view
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new PluginSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    /**
     * Edit plugin params
     *
     * @param  string $name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditPlugin($name)
    {

        $model = $this->findModel($name);

        if (isset($_POST['Plugin']) && isset($_POST['PluginParams'])) {

            $global_params = $_POST['Plugin'];
            $plugin_params = $_POST['PluginParams'];

            $model->enabled = $global_params['enabled'];
            $model->access  = $global_params['access'];
            $model->params  = Json::encode($plugin_params);

            if ($model->save()) {
                \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->name));
            } else {
                \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->name));
            }

            return $this->redirect(['edit-plugin', 'name' => $model->name]);

        }

        return $this->render('_form', [
            'model'     => $model,
            'form_data' => $model->getPluginForm(),
            'roles'     => (new AuthItem)->getAllRoles(),
        ]);

    }


    /**
     * Install plugin via Ajax
     *
     * @return string
     */
    public function actionAjaxInstallPlugin()
    {

        $model = new Plugin();

        $model->file    = UploadedFile::getInstance($model, 'file');
        $install_plugin = $model->installPlugin();

        if ($install_plugin['status'] == true) {
            $response = [
               'status' => 'success',
               'msg'    => nl2br($install_plugin['message'])
            ];
        } else {
            $response = [
                'status' => 'error',
                'msg'    => nl2br($install_plugin['message'])
            ];
        }

        return Json::encode($response);

    }


    /**
     * Enable/Disable plugin via Ajax
     *
     * @param  string $name
     * @param  int $mode
     * @return void
     * @throws NotFoundHttpException
     */
    public function actionAjaxSwitchMode($name, $mode)
    {

        $model          = $this->findModel($name);
        $model->enabled = intval($mode);

        if ($model->save()) {
            $status = ($model->enabled == 1) ? Yii::t('plugin', 'activated') : Yii::t('plugin', 'deactivated');
            \Y::flash('success', Yii::t('plugin', 'Plugin <b>{0}</b> was successfully {1}', [$name, $status]));
        }
        else {
            \Y::flash('danger', Yii::t('plugin', 'An error occurred while changing plugin <b>{0}</b> status', $name));
        }

    }


    /**
     * Delete plugin from system
     *
     * @param  string $name
     * @return void
     */
    public function actionAjaxDeletePlugin($name)
    {

        try {

            $plugin = new Plugin();
            $plugin->removePlugin($name);
            \Y::flash('success', Yii::t('plugin', 'Plugin <b>{0}</b> was successfully deleted', $name));

        } catch (\Exception $e) {
            \Y::flash('danger', $e->getMessage());
        }

    }


    /**
     * Finds the Plugin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $name
     * @return Plugin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = Plugin::findOne($name)) !== null) {
            return $model;
        }
        else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
