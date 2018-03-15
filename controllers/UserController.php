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
use yii\filters\AccessControl;
use yii\helpers\HtmlPurifier;
use yii\helpers\Json;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\models\LoginForm;
use app\models\search\UserSearch;
use app\models\User;
use app\models\Setting;
use app\models\SettingOverride;


/**
 * @package app\controllers
 */
class UserController extends Controller
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
                        'allow'   => true,
                        'actions' => ['list', 'add', 'edit', 'ajax-delete', 'ajax-switch-status'],
                        'roles'   => ['admin'],
                    ],
                    [
                        'actions' => ['logout', 'profile', 'settings', 'generate-token'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => ['login'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'ajax-delete'        => ['post'],
                    'ajax-switch-status' => ['post'],
                    'generate-token'     => ['post'],
                    'logout'             => ['post']
                ],
            ],
        ];
    }


    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {

        $this->layout = 'plain';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);

    }


    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }


    /**
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionProfile()
    {
        return $this->actionEdit(Yii::$app->user->id);
    }


    /**
     * Users' list
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Add new user
     *
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionAdd()
    {

        $model = new User(['scenario' => 'create']);
        $model->loadDefaultValues();

        if (isset($_POST['User'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    $model->setPassword($model->password);
                    $model->generateAuthKey();

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('user', 'New user was successfully added'));
                    } else {
                        \Y::flash('danger', Yii::t('user', 'An error occurred while adding new user'));
                    }

                    return $this->redirect(['/user/list']);

                }
            }
        }

        return $this->render('_form', [
            'model' => $model
        ]);

    }


    /**
     * Edit user
     *
     * @param  string $userid
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionEdit($userid)
    {

        $model = $this->findModel($userid);

        if (isset($_POST['User'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if (!empty($model->password)) {
                        $model->setPassword($model->password);
                    }

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('user', 'User was successfully edited'));
                    } else {
                        \Y::flash('danger', Yii::t('user', 'An error occurred while editing user'));
                    }

                    if($this->action->id == 'profile') {
                        return $this->redirect(['/user/profile']);
                    }

                    return $this->redirect(['/user/list']);
                }
            }
        }

        return $this->render('_form', [
            'model' => $model
        ]);

    }


    /**
     * Delete user via GridView
     *
     * @param  string $userid
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxDelete($userid)
    {

        $userid = urlencode($userid);

        /** This is bad idea to delete yourself from system! */
        if ($userid == Yii::$app->user->id) {
            return Json::encode(['status' => 'warning', 'msg' => Yii::t('user', 'You are trying to delete yourself from system!')]);
        }

        /**
         * Protection from system user deleting
         * Permanent system users can be set in params system.users
         */
        if (in_array(mb_strtolower($userid), array_map('mb_strtolower', \Y::param('system.users')))) {
            return Json::encode([
                'status' => 'warning',
                'msg'    => Yii::t('user', 'You cannot delete system user <b>{0}</b>!', strtoupper($userid))
            ]);
        }

        $model = $this->findModel($userid);

        try {
            $model->delete();
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('user', 'User <b>{0}</b> was successfully deleted', $model->fullname)
            ];
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            return Json::encode([
                'status' => 'error',
                'msg'    => Yii::t('user', 'An error occurred while deleting user <b>{0}</b>', $model->fullname)
            ]);
        }

        return Json::encode($response);

    }


    /**
     * Change user status via GridView
     *
     * @param  string $userid
     * @param  int $mode
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAjaxSwitchStatus($userid, $mode)
    {

        $userid   = urlencode($userid);
        $response = ['status' => 'error', 'msg' => Yii::t('user', 'Something went wrong. <br> View system logs for more info.')];

        /** Protection from deactivating yourself */
        if (($userid == Yii::$app->user->id && $mode == 0)) {
            return Json::encode(['status' => 'warning', 'msg' => Yii::t('user', 'You are trying to deactivate yourself!')]);
        }

        /**
         * Protection from system user disabling
         * Permanent system users can be set in params system.users
         */
        if (in_array(mb_strtolower($userid), array_map('mb_strtolower', \Y::param('system.users')))) {
            return Json::encode([
                'status' => 'warning',
                'msg'    => Yii::t('user', 'You cannot deactivate system user <b>{0}</b>!', strtoupper($userid))
            ]);
        }

        if (Yii::$app->request->isAjax) {

            $model          = $this->findModel($userid);
            $model->enabled = intval($mode);

            if ($model->save()) {
                $response = [
                    'status' => 'success',
                    'msg'    => Yii::t('user', 'User <b>{0}</b> status was successfully changed', $model->fullname)
                ];
            }
            else {
                $response = [
                    'status' => 'error',
                    'msg'    => Yii::t('user', 'An error occurred while changing user <b>{0}</b> status', $model->fullname)
                ];
            }

        }

        return Json::encode($response);

    }


    /** @noinspection PhpUndefinedClassInspection
     *  User personalization settings
     *
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionSettings()
    {

        if(Yii::$app->request->isPost) {

            $changed  = true;
            $settings = Setting::getDefaultSettings();

            foreach (Yii::$app->request->post('Setting', []) as $key => $value) {

                $key    = HtmlPurifier::process($key);
                $value  = HtmlPurifier::process($value);
                $record = SettingOverride::findOne(['key' => $key, 'userid' => Yii::$app->getUser()->id]);

                if( (array_key_exists($key, $settings) && $value !== $settings[$key]) || (isset($record->value) && $value != $record->value) ) {

                    // Delete from overrides, let the default value be an active setting
                    if( array_key_exists($key, $settings) && $value == $settings[$key] ) {
                        if ( !empty($record) ) {
                            if (!$record->validate() || !$record->delete()) {
                                $changed = false;
                            }
                        }
                    }
                    else {

                        if (!$record) {
                            $record = new SettingOverride();
                        }

                        $record->key    = $key;
                        $record->value  = $value;
                        $record->userid = Yii::$app->getUser()->id;

                        if (!$record->validate() || !$record->save()) {
                            $changed = false;
                        }

                    }

                }

            }

            if ( $changed === true ) {
                Yii::$app->getSession()->remove('settings');
                \Y::flash('success', Yii::t('app', 'Changes were successfully saved', null, Setting::get('language')));
                return $this->redirect(['settings']);
            }
            else {
                \Y::flash('danger', Yii::t('app', 'An error occured while saving changes'));
            }

        }

        return $this->render('settings', [
            'data' => Setting::getSettingsForUser(),
        ]);

    }


    /**
     * Generate new access token for user.
     *
     * @return string
     */
    public function actionGenerateToken()
    {

        $response = ['status' => 'error', 'msg' => Yii::t('user', 'Something happened. Access token cannot be generated.'), 'key' => ''];

        if (Yii::$app->request->isAjax) {

            $model = new User();

            try {
                $model->generateRestAccessToken();
                $response = [
                    'status' => 'success',
                    'msg' => Yii::t('user', 'Token was successfully generated.<br>Do not forget to save changes!'),
                    'key' => $model->access_token
                ];
            } catch (\Exception $e) {
                return Json::encode([
                    'status' => 'error',
                    'msg'    => Yii::t('user', $e->getMessage()),
                    'key'    => ''
                ]);
            }
        }

        return Json::encode($response);

    }


    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
