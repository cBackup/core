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
 * @var $searchModel   app\models\search\JobGlobalVariableSearch
 */
$this->title = Yii::t('app', 'Global worker variables');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'System' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Global worker variables')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'List of global worker variables') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('network', 'Add global variable'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'global-variables-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'global-variables-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'var_name',
                                    'value'     => 'varNameStyled',
                                ],
                                [
                                    'attribute' => 'var_value',
                                ],
                                [
                                    'attribute'     => 'description',
                                    'enableSorting' => false,
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{edit} {delete}',
                                    'visible'        => $searchModel::hasUnprotectedVar(),
                                    'visibleButtons' => [
                                        'edit' => function ($model) { /** @var $model \app\models\JobGlobalVariable */
                                            return ($model->protected == 0) ? true : false;
                                        },
                                        'delete' => function ($model) { /** @var $model \app\models\JobGlobalVariable */
                                            return ($model->protected == 0) ? true : false;
                                        }
                                    ],
                                    'buttons'        => [
                                        'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\JobGlobalVariable */
                                            return Html::a('<i class="fa fa-pencil-square-o"></i>', ['edit', 'id' => $model->id], [
                                                'title'     => Yii::t('app', 'Edit'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\JobGlobalVariable */
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['ajax-delete', 'id' => $model->id]),
                                                'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->var_name),
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
