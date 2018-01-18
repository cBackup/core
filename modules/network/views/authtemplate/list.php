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
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $searchModel   app\models\search\DeviceAuthTemplateSearch
 */
$this->title = Yii::t('app', 'Device auth templates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Device auth templates')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'List of device auth templates') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('network', 'Add auth template'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'auth-template-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'auth-template-grid',
                            'tableOptions' => ['class' => 'table table-bordered ellipsis'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'name',
                                    'value'     => function($model) { /** @var $model \app\models\DeviceAuthTemplate */
                                        return Html::a($model->name, ['edit', 'name' => $model->name], ['data-pjax' => '0']);
                                    },
                                ],
                                [
                                    'attribute'      => 'auth_sequence',
                                    'options'        => ['style' => 'width:35%']
                                ],
                                [
                                    'attribute'      => 'description',
                                    'contentOptions' => ['class' => 'hide-overflow'],
                                    'enableSorting'  => false
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{edit} {delete}',
                                    'buttons'        => [
                                        'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\DeviceAuthTemplate */
                                            return Html::a('<i class="fa fa-pencil-square-o"></i>', ['edit', 'name' => $model->name], [
                                                'title'     => Yii::t('app', 'Edit'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\DeviceAuthTemplate */
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['ajax-delete', 'name' => $model->name]),
                                                'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->name),
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
