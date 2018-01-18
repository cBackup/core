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

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/**
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel  app\models\search\UserSearch
 */
app\assets\i18nextAsset::register($this);

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users')];

$this->registerJsFile('@web/plugins/clipboard/1.5.16/clipboard.min.js');

/** @noinspection JSUnusedLocalSymbols */
$this->registerJs(/** @lang JavaScript */"

        var clipboard = new Clipboard('.access_token_copy');

        clipboard.on('success', function(e) {
            if( e.text === '' ) {
                toastr.warning(i18next.t('Empty token, nothing to copy'), '', {timeOut: 5000, progressBar: true, closeButton: true});
            }
            else {
                toastr.success(i18next.t('Token copied to clipboard'), '', {timeOut: 5000, progressBar: true, closeButton: true});
            }
        });
       
        clipboard.on('error', function(e) {
            toastr.warning(i18next.t('Error while copying token'), '', {closeButton: true});
        });
    "
);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-users"></i><h3 class="box-title box-title-align"><?= Yii::t('user', 'List of system users') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('user', 'Add user'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'user-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'users-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'rowOptions'   => function($model) {
                                return ['class' => ($model->enabled == 0) ? 'danger' : ''];
                            },
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'fullname',
                                    'value'     => function($data) { return Html::a($data->fullname, ['/user/edit', 'userid' => $data->userid], ['data-pjax' => '0']); },
                                ],
                                [
                                    'attribute'     => 'email',
                                    'enableSorting' => false,
                                ],
                                [
                                    'format'        => 'raw',
                                    'attribute'     => 'access_token',
                                    'enableSorting' => false,
                                    'value'         => function($data) { /** @var $data \app\models\User */ return $data->renderToken(); },
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{edit} {user_mode} {delete}',
                                    'buttons'        => [
                                        'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/user/edit', 'userid' => $model->userid], [
                                                'title'     => Yii::t('app', 'Edit'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'user_mode' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {

                                            $icon  = 'fa fa-times-circle-o';
                                            $color = 'color: #D65C4F';
                                            $text  = Yii::t('user', 'Are you sure you want to disable user {0}?', $model->fullname);;
                                            $tip   = 'Disable';
                                            $mode  = 0;

                                            if($model->enabled == 0) {
                                                $icon  = 'fa fa-check-circle-o';
                                                $color = 'color: #00a65a';
                                                $text  = Yii::t('user', 'Are you sure you want to enable user {0}?', $model->fullname);
                                                $tip   = 'Enable';
                                                $mode  = 1;
                                            }

                                            return Html::a('<i class="'. $icon .'"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', $tip),
                                                'style'             => $color,
                                                'data-ajax-url'     => Url::to(['/user/ajax-switch-status', 'userid' => $model->userid, 'mode' => $mode]),
                                                'data-ajax-confirm' => $text,
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['/user/ajax-delete', 'userid' => $model->userid]),
                                                'data-ajax-confirm' => Yii::t('user', 'Are you sure you want to delete user {0}?', $model->fullname),
                                            ]);
                                        },
                                    ],
                                ]
                            ],
                        ]);
                    ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
