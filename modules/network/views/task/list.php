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
use yii\helpers\Inflector;
use cbackup\grid\GroupGridView;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $searchModel   app\models\search\TaskSearch
 * @var $task_types    array
 */
$this->title = Yii::t('app', 'Tasks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tasks')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'List of tasks') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('network', 'Add task'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'task-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GroupGridView::widget([
                            'id'              => 'task-grid',
                            'tableOptions'    => ['class' => 'table table-bordered ellipsis'],
                            'dataProvider'    => $dataProvider,
                            'filterModel'     => $searchModel,
                            'mergeColumns'    => ['task_type'],
                            'extraRowColumns' => ['put'],
                            'extraRowValue'   => function($model) use ($searchModel) { /** @var $model app\models\Task */
                                return $model->renderExtraRowHeader($searchModel->attributes);
                            },
                            'layout'  => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'name',
                                    'filter'    => $searchModel->renderCustomFilter(),
                                    'value'     => 'taskNameStyled',
                                    'options'   => ['style' => 'width:25%']
                                ],
                                [
                                    'attribute' => 'task_type',
                                    'options'   => ['style' => 'width:15%'],
                                    'filter'    => $task_types,
                                    'value'     => function($data) { /** @var $data app\models\Task */
                                        return Yii::t('network', Inflector::humanize($data->task_type));
                                    },
                                ],
                                [
                                    'attribute'      => 'description',
                                    'options'        => ['style' => 'width:20%'],
                                    'contentOptions' => ['class' => 'hide-overflow'],
                                ],
                                [
                                    'attribute' => 'table',
                                ],
                                [
                                    'format'         => 'raw',
                                    'attribute'      => 'task_has_nodes',
                                    'value'          => 'taskHasNodes',
                                    'contentOptions' => ['class' => 'text-center']
                                ],
                                [
                                    'format'         => 'raw',
                                    'attribute'      => 'task_has_devices',
                                    'value'          => 'taskHasDevices',
                                    'contentOptions' => ['class' => 'text-center']
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{edit} {assign_task} {delete}',
                                    'visibleButtons' => [
                                        'edit' => function ($model) {/** @var $model \app\models\Task */
                                            return (!in_array($model->name, \Y::param('forbidden_tasks_list')) && $model->task_type != 'yii_console_task') ? true : false;
                                        },
                                        'delete' => function ($model) { /** @var $model \app\models\Task */
                                            return ($model->protected == 0) ? true : false;
                                        },
                                        'assign_task' => function ($model) { /** @var $model \app\models\Task */
                                            return (!in_array($model->name, \Y::param('forbidden_tasks_list')) && $model->task_type != 'yii_console_task') ? true : false;
                                        },
                                    ],
                                    'buttons'        => [
                                        'edit' => function (/** @noinspection PhpUnusedParameterInspection */ $url, $model) { /** @var $model \app\models\Task */
                                            return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/network/task/edit', 'name' => $model->name], [
                                                'title'     => Yii::t('app', 'Edit'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'assign_task' => function (/** @noinspection PhpUnusedParameterInspection */ $url, $model) { /** @var $model \app\models\Task */
                                            return Html::a('<i class="fa fa-plus-square-o"></i>', ['/network/assigntask/adv-node-assign',
                                                'task_name' => $model->name
                                            ], [
                                                'title'     => Yii::t('network', 'Assign task to node'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */ $url, $model) { /** @var $model \app\models\Task */
                                            $warning = ($model::outTableExists($model->name))
                                                ? Yii::t('network', 'Warning! By deleting task {0} simultaneously will be deleted all task related data.', $model->name) . "\n"
                                                : '';
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['/network/task/ajax-delete', 'name' => $model->name]),
                                                'data-ajax-confirm' => $warning. Yii::t('network', 'Are you sure you want to delete task {0}?', $model->name)
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

