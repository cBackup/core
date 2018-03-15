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
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\Vendor;


/**
 * @package app\modules\network\controllers
 */
class VendorController extends Controller
{

    /**
     * @var string
     */
    public $defaultAction = 'index';


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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Redirect to device list
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['/network/device/list']);
    }


    /**
     * Add new vendor
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {

        $model = new Vendor();

        if (isset($_POST['Vendor'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record added successfully.'));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while adding record.'));
                    }

                    return $this->redirect([$this->defaultAction]);

                }
            }
        }

        return $this->render('_form', [
            'model'      => $model,
        ]);

    }


    /**
     * Edit vendor
     *
     * @param  string $name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($name)
    {

        $model = $this->findModel($name);

        if (isset($_POST['Vendor'])) {

            if ($model->load(Yii::$app->request->post())) {

                if ($model->validate()) {

                    if ($model->save()) {
                        \Y::flash('success', Yii::t('app', 'Record <b>{0}</b> edited successfully.', $model->name));
                    } else {
                        \Y::flash('danger', Yii::t('app', 'An error occurred while editing record <b>{0}</b>.', $model->name));
                    }

                    return $this->redirect([$this->defaultAction]);

                }
            }
        }

        return $this->render('_form', [
            'model'      => $model
        ]);

    }


    /**
     * Delete vendor via POST
     *
     * @param  string $name
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($name)
    {

        $model = $this->findModel($name);

        try {
            $model->delete();
            $class   = 'success';
            $message = Yii::t('app', 'Record <b>{0}</b> has been successfully deleted.', $model->name);
        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            $class   = 'danger';
            $message = Yii::t('app', 'An error occurred while deleting record <b>{0}</b>.', $model->name);
            $message.= '<br>'.$e->getMessage();
        }

        \Y::flash($class, $message);
        return $this->redirect([$this->defaultAction]);

    }


    /**
     * Finds the Vendor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param  string $name
     * @return Vendor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = Vendor::findOne($name)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
