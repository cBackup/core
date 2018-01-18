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
use yii\widgets\Pjax;
use yii\helpers\Url;
use cbackup\grid\GroupGridView;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $searchModel   app\models\search\ExclusionSearch
 */
$this->title = Yii::t('app', 'Exclusions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('node', 'Nodes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Exclusions')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'List of exclusions') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('network', 'Add exclusion'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'exclusion-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GroupGridView::widget([
                            'id'           => 'exclusion-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'afterRow'     => function($model) { /** @var $model \app\models\Exclusion */
                                $id = 'info_' . str_replace('.', '_', $model->ip);
                                return '<tr><td class="grid-expand-row" colspan="4"><div class="grid-expand-div" id="'.$id.'"></div></td></tr>';
                            },
                            'layout'  => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'         => 'raw',
                                    'options'        => ['style' => 'width:3%'],
                                    'contentOptions' => ['class' => 'text-center'],
                                    'value'          => function($model) { /** @var $model \app\models\Exclusion */
                                        return Html::a('<i class="fa fa-caret-square-o-down"></i>', 'javascript:;', [
                                            'class'         => 'ajaxGridExpand',
                                            'title'         => Yii::t('node', 'Node info'),
                                            'data-ajax-url' => Url::to(['/network/exclusion/ajax-get-node-info', 'ip' => $model->ip]),
                                            'data-div-id'   => '#info_' . str_replace('.', '_', $model->ip),
                                            'data-multiple' => 'false'
                                        ]);
                                    },
                                ],
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'ip',
                                    'options'   => ['style' => 'width:25%'],
                                    'value'     => function($data) { /** @var $data \app\models\Exclusion */
                                        return Html::a($data->ip, ['/network/exclusion/edit', 'ip' => $data->ip], [
                                            'data-pjax' => '0',
                                            'title'     => Yii::t('network', 'Edit exclusion')
                                        ]);
                                    },
                                ],
                                [
                                    'attribute'     => 'description',
                                    'enableSorting' => false
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{edit} {delete}',
                                    'buttons'        => [
                                        'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Exclusion */
                                            return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/network/exclusion/edit', 'ip' => $model->ip], [
                                                'title'     => Yii::t('app', 'Edit'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Exclusion */
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['/network/exclusion/ajax-delete', 'ip' => $model->ip]),
                                                'data-ajax-confirm' => Yii::t('network', 'Are you sure you want to delete exclusion {0}?', $model->ip),
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




