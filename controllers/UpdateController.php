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
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\helpers\Json;
use yii\filters\AccessControl;
use app\filters\AjaxFilter;
use app\components\Updater;


/**
 * @package app\controllers
 */
class UpdateController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'ajax' => [
                'class' => AjaxFilter::className(),
                'only'  => [
                    'ajax-check-updates',
                    'ajax-lock-system',
                    'ajax-update-core',
                    'ajax-cleanup',
                    'ajax-update-database'
                ]
            ],
        ];
    }


    /**
     * Render index page of updater
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {

        $dbname = Yii::$app->db->createCommand("SELECT DATABASE()")->queryScalar();
        $giturl = Updater::getGitUrl();

        try {

            $updater = new Updater();

            return $this->render('index', [
                'origin_info' => $updater->getOriginVersion(),
                'environment' => true,
                'database'    => $dbname,
                'giturl'      => $giturl,
                'message'     => null,
                'service'     => $updater->getServiceStatus()
            ]);

        } catch (\Exception $e) {
            return $this->render('index', [
                'origin_info' => null,
                'environment' => false,
                'database'    => $dbname,
                'giturl'      => $giturl,
                'message'     => $e->getMessage(),
                'service'     => null
            ]);
        }

    }


    /**
     * @return \yii\web\Response
     * @throws \yii\base\ErrorException
     */
    public function actionInit()
    {

        try {
            (new Updater())->initGitRepo();
        }
        catch (\Exception $e) {
            \Y::flash('danger', $e->getMessage());
            FileHelper::removeDirectory(Yii::$app->basePath.'/.git');
        }
        finally {
            return $this->redirect(['index']);
        }

    }


    /**
     * Check for new system updates
     *
     * @return string
     */
    public function actionAjaxCheckUpdates()
    {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $response = ['status' => 'error', 'msg' => Yii::t('app', 'An error occurred while processing your request')];

        try {
            $updater = new Updater();
            $updater->checkForUpdates();

            $response = [
                'status' => 'success',
                'msg'    => Yii::t('app', 'Action successfully finished')
            ];

        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'msg'    => $e->getMessage()
            ];
        }

        return Json::encode($response);

    }


    /**
     * Create update lock file for locking Web UI
     *
     * @param  bool $lock
     * @return string
     */
    public function actionAjaxLockSystem($lock)
    {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $response = ['status' => 'false'];

        try {

            $path = Yii::$app->basePath . DIRECTORY_SEPARATOR . 'update.lock';

            if ($lock) {
                file_put_contents($path, date('d.m.Y H:i:s'));
            } else {
                unlink($path);
            }

            $response = ['status' => 'true'];

        } catch (\Exception $e) {
            $response = [
                'status' => 'false',
                'msg'    => nl2br(preg_replace('/^\h*\v+/m', '', $e->getMessage()))
            ];
        }

        return Json::encode($response);

    }


    /**
     * Update web core files from GIT repo
     *
     * @return string
     */
    public function actionAjaxUpdateCore()
    {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $response = ['status' => 'false'];

        try {

            $updater = new Updater();
            $updater->updateFiles();
            $response = ['status' => 'true'];

        } catch (\Exception $e) {
            $response = [
                'status' => 'false',
                'msg'    => nl2br(preg_replace('/^\h*\v+/m', '', $e->getMessage()))
            ];
        }

        return Json::encode($response);

    }


    /**
     * Update database
     *
     * @return string
     */
    public function actionAjaxUpdateDatabase()
    {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $response = ['status' => 'false'];

        try {

            $updater = new Updater();
            if ($updater->updateDatabase()) {
                $response = ['status' => 'true'];
            }

        } catch (\Exception $e) {
            $response = [
                'status' => 'false',
                'msg'    => nl2br(preg_replace('/^\h*\v+/m', '', $e->getMessage()))
            ];
        }

        return Json::encode($response);

    }


    /**
     * @return string
     */
    public function actionAjaxCleanup()
    {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $response = ['status' => 'false'];

        try {

            if (!Yii::$app->cache->flush()) {
                throw new \Exception('Unable to flush cache');
            }

            $directories = glob(Yii::$app->assetManager->basePath . '/*', GLOB_ONLYDIR);

            foreach ($directories as $directory) {
                if (is_dir($directory)) {
                    FileHelper::removeDirectory($directory);
                }
            }

            @unlink(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'old_version.txt');
            $response = ['status' => 'true'];

        } catch (\Exception $e) {
            $response = [
                'status' => 'false',
                'msg'    => nl2br(preg_replace('/^\h*\v+/m', '', $e->getMessage()))
            ];
        }

        return Json::encode($response);

    }

}
