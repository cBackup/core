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
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 */
$query_param = Yii::$app->request->getQueryParam('DeviceAttributesUnknownSearch');
$this->title = Yii::t('app', 'Unknown devices');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Devices'), 'url' => ['device/list']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title">
                    <?= Yii::t('network', 'List of unknown devices') ?>
                    <?php
                        if (!is_null($query_param)) {
                            echo Html::tag('span', Yii::t('network', ':: Filtered by record id: <b>{0}</b>', $query_param['id']), ['class' => 'margin-r-5']);
                            echo Html::a('<i class="fa fa-times"></i>', ['device/unknown-list'], [
                                'title' => Yii::t('network', 'Clear filter'),
                                'style' => ['color' => '#f39c12']
                            ]);
                        }
                    ?>
                </h3>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'credential-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'credential-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'layout'  => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'attribute'     => 'created',
                                    'options'       => ['style' => 'width:14%']
                                ],
                                [
                                    'attribute'     => 'ip',
                                    'options'       => ['style' => 'width:9%'],
                                    'enableSorting' => false
                                ],
                                [
                                    'attribute'     => 'sysobject_id',
                                    'options'       => ['style' => 'width:20%'],
                                    'enableSorting' => false
                                ],
                                [
                                    'attribute'     => 'hw',
                                    'options'       => ['style' => 'width:11%'],
                                    'enableSorting' => false
                                ],
                                [
                                    'attribute'     => 'sys_description',
                                    'enableSorting' => false
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{add} {delete}',
                                    'buttons'        => [
                                        'add' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\DeviceAttributesUnknown */
                                            return Html::a('<i class="fa fa-plus-square-o"></i>', ['add-unknown-device'], [
                                                'title' => Yii::t('network', 'Recognize device'),
                                                'data' => [
                                                    'method' => 'post',
                                                    'params' => [
                                                        'data[unkn_id]'         => $model->id,
                                                        'data[sysobject_id]'    => $model->sysobject_id,
                                                        'data[hw]'              => $model->hw,
                                                        'data[sys_description]' => $model->sys_description
                                                    ]
                                                ],
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\DeviceAttributesUnknown */
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:void(0);', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['/network/device/ajax-delete-unknown', 'id' => $model->id]),
                                                'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0} {1}?', [
                                                    $model->sys_description, $model->hw
                                                ]),
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
