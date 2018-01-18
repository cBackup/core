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

use yii\web\View;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\widgets\Pjax;

/**
 * @var $this           yii\web\View
 * @var $dataProvider   yii\data\ActiveDataProvider
 * @var $searchModel    app\models\search\PluginSearch
 */
app\assets\PluginAsset::register($this);

$this->title = Yii::t('plugin', 'cBackup plugins');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'System' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Plugin manager')];

/** @var  $options */
$options = ['install_plugin' => Url::to(['ajax-install-plugin'])];

/** Register url */
$this->registerJs(/** @lang JavaScript */" var install_url = " . Json::htmlEncode($options) . ";", View::POS_HEAD);

/** Register CSS */
/** @noinspection CssUnusedSymbol */
$this->registerCss(
    /** @lang CSS */
    '
        .preview-input {
            position: relative;
            overflow: hidden;
            margin: 0;
            color: #333;
            background-color: #fff;
            border-color: #ccc;
        }
        
        .preview-input input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            margin: 0;
            padding: 0;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            filter: alpha(opacity=0);
        }
        
        .preview-input-title {
            margin-left:2px;
        }
    '
);
?>

<div class="row">
    <div class="col-md-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#plugin_list" data-toggle="tab" aria-expanded="true">
                        <i class="fa fa-list"></i> <?= Yii::t('plugin', 'Plugins list') ?>
                    </a>
                </li>
                <li>
                    <a href="#upload_plugin" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-upload"></i> <?= Yii::t('plugin', 'Upload plugin') ?>
                    </a>
                </li>
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane active" id="plugin_list">
                    <?php Pjax::begin(['id' => 'plugin-grid-pjax']); ?>
                        <?php
                            /** @noinspection PhpUnhandledExceptionInspection */
                            echo GridView::widget([
                                'id'           => 'schedule-grid',
                                'options'      => ['class' => 'grid-view tab-grid-view'],
                                'tableOptions' => ['class' => 'table table-bordered'],
                                'rowOptions'   => function($model) {
                                    return ['class' => ($model->enabled == 0) ? 'danger text-muted' : ''];
                                },
                                'dataProvider' => $dataProvider,
                                'filterModel'  => $searchModel,
                                'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                                'columns' => [
                                    [
                                        'format'    => 'raw',
                                        'attribute' => 'name',
                                        'options'   => ['style' => 'width:20%'],
                                        'value'     => function($data) { /** @var $data \app\models\Plugin */
                                            return Html::a($data->name, ['edit-plugin', 'name' => $data->name], [
                                                'class'       => 'set-active-tab',
                                                'data-pjax'   => '0',
                                                'data-target' => ''
                                            ]);
                                        },
                                    ],
                                    [
                                        'attribute' => 'author',
                                    ],
                                    [
                                        'attribute' => 'version',
                                        'options'   => ['style' => 'width:10%'],
                                    ],
                                    [
                                        'attribute' => 'description',
                                    ],
                                    [
                                        'class'          => 'yii\grid\ActionColumn',
                                        'contentOptions' => ['class' => 'narrow'],
                                        'template'       => '{edit} {mode} {delete}',
                                        'buttons'        => [
                                            'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Plugin */
                                                return Html::a('<i class="fa fa-pencil-square-o"></i>', ['edit-plugin', 'name' => $model->name], [
                                                    'class'       => 'set-active-tab',
                                                    'title'       => Yii::t('app', 'Edit'),
                                                    'data-pjax'   => '0',
                                                    'data-target' => ''
                                                ]);
                                            },
                                            'mode' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Plugin */

                                                $icon  = 'fa fa-times-circle-o';
                                                $color = 'color: #D65C4F';
                                                $text  = 'Are you sure you want to disable plugin {0}?';
                                                $tip   = 'Disable';
                                                $mode  = 0;

                                                if($model->enabled == 0) {
                                                    $icon  = 'fa fa-check-circle-o';
                                                    $color = 'color: #00a65a';
                                                    $text  = 'Are you sure you want to enable plugin {0}?';
                                                    $tip   = 'Enable';
                                                    $mode  = 1;
                                                }

                                                return Html::a('<i class="'. $icon .'"></i>', 'javascript:;', [
                                                    'class'             => 'ajaxGridUpdate',
                                                    'title'             => Yii::t('app', $tip),
                                                    'style'             => $color,
                                                    'data-ajax-url'     => Url::to(['ajax-switch-mode', 'name' => $model->name, 'mode' => $mode]),
                                                    'data-ajax-confirm' => Yii::t('app', $text, $model->name),
                                                    'data-pjax-reload'  => 'false'
                                                ]);
                                            },
                                            'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Plugin */
                                                return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                    'class'             => 'ajaxGridUpdate',
                                                    'title'             => Yii::t('app', 'Delete'),
                                                    'style'             => 'color: #D65C4F',
                                                    'data-ajax-url'     => Url::to(['ajax-delete-plugin', 'name' => $model->name]),
                                                    'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', ''),
                                                    'data-pjax-reload'  => 'false'
                                                ]);
                                            },
                                        ],
                                    ]
                                ],
                            ]);
                        ?>
                    <?php Pjax::end(); ?>
                </div>

                <div class="tab-pane" id="upload_plugin">
                    <div class="col-md-12">
                        <div class="callout callout-info" style="margin: 10px 0 10px 0;">
                            <p><?= Yii::t('plugin', 'Install third party plugins at your own risk') ?></p>
                        </div>
                    </div>
                    <div style="padding: 15px">
                        <?php Pjax::begin(['id' => 'plugin-pjax', 'enablePushState' => false]); ?>
                            <div class="input-group preview">
                                <input placeholder="" type="text" class="form-control preview-filename" disabled="disabled">
                                <div class="input-group-btn">
                                    <?php
                                        echo Html::button('<span class="glyphicon glyphicon-remove"></span> ' . Yii::t('app', 'Clear'), [
                                            'class' => 'btn btn-default preview-clear',
                                            'style' => ['display' => 'none']
                                        ]);
                                    ?>
                                    <div class="btn btn-default preview-input">
                                        <span class="glyphicon glyphicon-folder-open"></span>
                                        <span class="preview-input-title"><?= Yii::t('app', 'Browse') ?></span>
                                        <?= Html::fileInput('Plugin[file]', '', ['id' => 'file_input', 'accept' => 'application/x-zip-compressed']) ?>
                                    </div>
                                    <?php
                                        echo Html::button(Yii::t('plugin', 'Install plugin'), [
                                            'id'         => 'install_btn',
                                            'class'      => 'btn bg-light-blue ladda-button',
                                            'data-style' => 'zoom-in',
                                            'data-pjax'  => '0'
                                        ]);
                                    ?>
                                </div>
                            </div>
                        <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
