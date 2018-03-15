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
use app\models\Config;
use app\models\Task;
use yii\filters\AjaxFilter;
use app\mailer\CustomMailer;
use app\models\Severity;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * @package app\controllers
 */
class ConfigController extends Controller
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
                    'ajax-init-repo',
                    'ajax-reinit-git-settings'
                ]
            ],
        ];
    }


    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {

        $config  = new Config();
        $data    = ArrayHelper::map($config::find()->asArray()->all(), 'key', 'value');
        $changed = false;
        $errors  = [];

        $config->checkApplicationProperties($data);

        if(Yii::$app->request->isPost) {

            $validator = $config->configFormValidator(Yii::$app->request->post('Config'));
            $data      = array_merge($data, $validator->attributes); // Set attributes before validate

            if ($validator->validate()) {

                $data = $validator->attributes; // Reset attributes after validate

                foreach ($validator->attributes as $key => $value) {

                    $model = $this->findModel($key);

                    if ($model->value != $value) {

                        $model->key   = $key;
                        $model->value = $value;

                        if ($model->save()) {
                            $changed = true;
                        }
                    }
                }

            } else {
                $errors = $validator->errors;
            }

            if( $changed ) {
                Yii::$app->session->removeAllFlashes();
                Yii::$app->cache->delete('config_data');
                \Y::flash('success', Yii::t('config', 'Configuration saved'));
                return $this->redirect(['config/index']);
            }

        }

        return $this->render('index', [
            'config'     => $config,
            'data'       => $data,
            'errors'     => $errors,
            'backup_put' => Task::find()->select('put')->where(['name' => 'backup'])->scalar(),
            'severities' => Severity::find()->select('name')->indexBy('name')->asArray()->column()
        ]);

    }


    /**
     * Run repository init via Ajax
     *
     * @return string
     */
    public function actionAjaxInitRepo()
    {

        $status  = 'error';
        $message = Yii::t('app', 'An error occurred while processing your request');

        if (Yii::$app->request->isAjax) {
            try {

                Config::runRepositoryInit(\Y::param('gitUsername'), \Y::param('gitEmail'), \Y::param('gitPath'), \Y::param('dataPath'));
                $status  = 'success';
                $message = Yii::t('app', 'Action successfully finished');

            } catch (\Exception $e) {
                $message.= '<br>'.$e->getMessage();
            }
        }

        return Json::encode(['status' => $status, 'msg' => $message]);

    }


    /**
     * Reinit git settings via Ajax
     *
     * @return string
     */
    public function actionAjaxReinitGitSettings()
    {

        $status  = 'error';
        $message = Yii::t('app', 'An error occurred while processing your request');

        if (Yii::$app->request->isAjax) {
            try {

                Config::initGitSettings(\Y::param('gitUsername'), \Y::param('gitEmail'), \Y::param('gitPath'), \Y::param('dataPath'));
                $status  = 'success';
                $message = Yii::t('app', 'Action successfully finished');

            } catch (\Exception $e) {
                $message.= '<br>'.$e->getMessage();
            }
        }

        return Json::encode(['status' => $status, 'msg' => $message]);

    }


    /**
     * Send test mail via Ajax
     *
     * @return string
     */
    public function actionAjaxSendTestMail()
    {

        $status  = 'error';
        $message = Yii::t('app', 'An error occurred while processing your request');

        if (Yii::$app->request->isAjax) {

            $response = (new CustomMailer())->sendTestMail();

            switch ($response) {
                case '0':
                    $status  = 'success';
                    $message = \Yii::t('app', 'Test mail successfully sent to {0}', \Y::param('mailerFromEmail'));
                break;
                case '1':
                    $status  = 'error';
                    $message = \Yii::t('app', 'Error while sending test mail to {0}', \Y::param('mailerFromEmail'));
                break;
                case '2':
                    $status  = 'error';
                    /** @noinspection HtmlUnknownTarget */
                    $message = \Yii::t('app', 'Mailer is disabled. To use mailer please enable it in <a href="{url}">System settings</a>');
                break;
                default:
                    $status  = 'error';
                    $message = \Yii::t('app', 'Error while sending mail. Check mailer settings. </br>Exception:</br> {0}', $response);
                break;
            }

        }

        return Json::encode(['status' => $status, 'msg' => $message]);

    }


    /**
     * Finds the Config model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $id
     * @return Config the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Config::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
