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

namespace app\modules\rbac\controllers;

use app\modules\rbac\models\AuthItem;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\modules\rbac\models\AuthAssignment;
use app\modules\rbac\models\AuthAssignmentSearch;
use app\models\User;


/**
 * @package app\modules\rbac\controllers
 */
class AssignController extends Controller
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
                    'ajax-delete' => ['post']
                ],
            ],
        ];
    }


    /**
     * List of user assignments
     *
     * @return string
     */
    public function actionList()
    {
        $searchModel  = new AuthAssignmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'item_types'   => (new AuthItem())->getTypeDefinition()
        ]);
    }


    /**
     * Add new user auth item assignments
     *
     * @return string|\yii\web\Response
     * @throws \yii\base\ExitException
     * @throws \yii\db\Exception
     */
    public function actionAdd()
    {

        $model          = new AuthAssignment();
        $users          = (new User())->getUsers('join');
        $assigned_users = ArrayHelper::map($model->find()->all(), 'user_id', 'user_id');

        if (isset($_POST['AuthAssignment'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();

                    try {

                        $auth_items = ArrayHelper::merge($model->roles, $model->permissions);

                        if ($model->updateUserAssignment($auth_items)) {
                            $transaction->commit();
                            \Y::flash('success', Yii::t('rbac', 'User assignments was successfully added'));
                        }
                        else {
                            $transaction->rollBack();
                            \Y::flash('danger', Yii::t('rbac', 'An error occurred while adding user assignments'));
                        }

                        return $this->redirect(['/rbac/assign/list']);

                    }
                    catch (\Exception $e) {
                        $transaction->rollBack();
                        \Y::flash('warning', $e->getMessage());
                        $this->refresh();
                        Yii::$app->end();
                    }
                }
            }
        }

        return $this->render('_form', [
            'model'         => $model,
            'users'         => array_diff_key($users, $assigned_users), // Remove users from list which already have assigned auth items
            'roles'         => ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'name'),
            'permissions'   => ArrayHelper::map(Yii::$app->authManager->getPermissions(), 'name', 'name'),
            'locked_rights' => '',
        ]);

    }


    /**
     * Edit user auth item assignments
     *
     * @param  string $userid
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     * @throws \yii\db\Exception
     */
    public function actionEdit($userid)
    {

        $model         = $this->findModel($userid);
        $locked_rights = [];

        /**
         * Protection from deleting permanent system access rights assignments for system user
         * Permanent system access rights are taken from params system.rights
         */
        if (in_array(mb_strtolower($userid), array_map('mb_strtolower', \Y::param('system.users')))) {
            $locked_rights = \Y::param('system.rights');
        }

        if (isset($_POST['AuthAssignment'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();

                    try {

                        $auth_items = ArrayHelper::merge($model->roles, $model->permissions);

                        /** Update user assignments */
                        if (array_values($model->_assignments) != $auth_items) {
                            if ($model->updateUserAssignment($auth_items)) {
                                $transaction->commit();
                                \Y::flash('success', Yii::t('rbac', 'User <b>{0}</b> assignments was successfully updated', $model->user->fullname));
                            }
                            else {
                                $transaction->rollBack();
                                \Y::flash('danger', Yii::t('rbac', 'An error occurred while updating user <b>{0}</b> assignments', $model->user->fullname));
                            }
                        }

                        return $this->redirect(['/rbac/assign/list']);

                    }
                    catch (\Exception $e) {
                        $transaction->rollBack();
                        \Y::flash('warning', $e->getMessage());
                        $this->refresh();
                        Yii::$app->end();
                    }
                }
            }
        }

        return $this->render('_form', [
            'model'         => $model,
            'users'         => (new User())->getUsers('join'),
            'roles'         => ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'name'),
            'permissions'   => ArrayHelper::map(Yii::$app->authManager->getPermissions(), 'name', 'name'),
            'locked_rights' => Json::encode($locked_rights)
        ]);

    }


    /**
     * Delete assignments via GridView
     *
     * @param  string $user_id
     * @param  string $item_name
     * @return string
     */
    public function actionAjaxDelete($user_id, $item_name)
    {

        $item_name  = urlencode($item_name);

        /**
         * Protection from deleting permanent system access rights assignments for system user
         * Permanent system access rights are taken from params system.rights
         */
        if (in_array(mb_strtolower($user_id), array_map('mb_strtolower', \Y::param('system.users')))) {
            if (in_array(mb_strtolower($item_name), array_map('mb_strtolower', \Y::param('system.rights')))) {
                return Json::encode([
                    'status' => 'warning',
                    'msg'    => Yii::t('rbac', 'You cannot remove system assignment <b>{0}</b> from system user!', ucwords($item_name))
                ]);
            }
        }

        $model = new AuthAssignment();

        if ($model->deleteUserAssignment($user_id, $item_name)) {
            $response = [
                'status' => 'success',
                'msg'    => Yii::t('rbac', 'User assignment was successfully deleted.')
            ];
        } else {
            $response = [
                'status' => 'error',
                'msg'    => Yii::t('rbac', 'An error occurred while deleting user assignment.')
            ];
        }

        return Json::encode($response);

    }


    /**
     * Finds the AuthAssignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $userid
     * @return AuthAssignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($userid)
    {
        if (($model = AuthAssignment::findOne(['user_id' => $userid])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
