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

namespace app\modules\cds\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Controller;
use yii\filters\AjaxFilter;
use app\models\Setting;
use app\modules\cds\models\Cds;

/**
 * Default controller for the `cds` module
 *
 * @package app\modules\cds\controllers
 */
class CdsController extends Controller
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
                    'ajax-render-grid',
                    'ajax-get-install-file',
                    'ajax-install-content',
                    'ajax-update-content'
                ]
            ],
        ];
    }

    /**
     * Renders the index view
     *
     * @return string
     */
    public function actionIndex()
    {

        try {

            (new Cds())->updateContent();

            $file  = Yii::$app->getModule('cds')->getBasePath().DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'FETCH_HEAD';
            $mtime = file_exists($file) ? date(Setting::get('datetime'), filemtime($file)) : null;

        }
        catch (\Exception $e) {
            \Y::flash('danger', $e->getMessage());
        }

        return $this->render('index', [
            'types' => array_keys((new Cds())->dataset),
            'mtime' => $mtime ?? null,
        ]);

    }


    /**
     * Render grid view via Ajax
     *
     * @param  string $type
     * @return string
     */
    public function actionAjaxRenderGrid($type)
    {
        $model        = new Cds();
        $dataProvider = $model->dataProvider($type, Yii::$app->request->queryParams);

        return $this->renderPartial('_grid_view', [
            'model'        => $model,
            'dataProvider' => $dataProvider,
            'type'         => $type
        ]);
    }


    /**
     * Get highlighted installation file via Ajax
     *
     * @param  string $path
     * @return bool|string
     */
    public function actionAjaxGetInstallFile($path)
    {
        return Html::tag('div', highlight_file($path, true), ['style' => 'overflow: hidden; overflow-x: auto; white-space: nowrap;']);
    }


    /**
     * Add content to system via Ajax
     *
     * @param  string $content
     * @param  string $vendor
     * @param  string $class
     * @return string
     */
    public function actionAjaxInstallContent($content, $vendor, $class)
    {
        try {

            $class = 'app\\modules\\cds\\content\\' . $content . '\\' . $vendor . '\\' .$class;
            $object = (new \ReflectionClass($class))->newInstance();
            $object->install();

            $response = [
                'status' => 'success',
                'msg'    => Yii::t('app', 'Action successfully finished')
            ];

        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'msg'    => Yii::t('app', "An error occurred while processing your request <b>{0}</b>", $e->getMessage())
            ];
            Yii::error(["An error occurred while installing content\nException:\n{$e->getMessage()}", "INSTALL CONTENT"], 'system.writeLog');
        }

        return Json::encode($response);
    }


    /**
     * Get content from git repo via Ajax
     *
     * @return void
     */
    public function actionAjaxUpdateContent()
    {
        try {

            $model = new Cds();
            $model->updateContent();
            \Y::flash('success', Yii::t('app', 'Content successfully updated'));

        } catch (\Exception $e) {
            \Y::flash('danger', Yii::t('app', "An error occurred while updating content\nException:\n{$e->getMessage()}", $e->getMessage()));
            Yii::error(["An error occurred while updating content\nException:\n{$e->getMessage()}", "UPDATE CONTENT"], 'system.writeLog');
        }
    }

}
