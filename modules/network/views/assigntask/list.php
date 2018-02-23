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
use kartik\depdrop\DepDrop;

/**
 * @var $this                yii\web\View
 * @var $deviceDataProvider  yii\data\ActiveDataProvider
 * @var $deviceSearchModel   app\models\search\TasksHasDevicesSearch
 * @var $nodeDataProvider    yii\data\ActiveDataProvider
 * @var $nodeSearchModel     app\models\search\TasksHasNodesSearch
 * @var $tasks_list          array
 */
app\assets\Select2Asset::register($this);

$this->title = Yii::t('app', 'Task assignments');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Task assignments')];

$this->registerJs(
    /** @lang JavaScript */
    "   
  
        /** Init select2 with search*/
        $('.select2-search-modal').select2({
            width: '100%'
        });
        
        /** Redirect to advanced node assignment */
        $('#task_select').change(function() {
            var redirect_btn  = $('#redirect_node');
            var redirect_url  = redirect_btn.data('url');
            redirect_btn.attr('href', redirect_url + '&task_name=' + $(this).val()).removeClass('disabled');
        });
        
        
        $('#task_name').change(function() {
            $('#redirect_device').addClass('disabled');
        });
        
        
        $('#worker_list').change(function() {
            var redirect_btn  = $('#redirect_device');
            var redirect_url  = redirect_btn.data('url');
            redirect_btn.attr('href', redirect_url + '&task_name=' + $('#task_name').val() + '&worker_id=' + $(this).val()).removeClass('disabled');
        });
        
        /** Clear input after modal closed */
        $('body').on('hidden.bs.modal', '.modal', function () {
            $('#task_select, #task_name, #worker_list').val('').trigger('change');
            $('#worker_list').attr('disabled', true);
            $('#redirect_node, #redirect_device').attr('href', 'javascript:;').addClass('disabled');
        });
        
        /** Save active tab to session storage and show necessary tab buttons */
        $('a[data-toggle=tab]').on('shown.bs.tab', function () {
           
            var target = $(this).attr('href');
            
            /** Check if session storage is available */
            if (_supportsSessionStorage) {
                sessionStorage.setItem('active', target);
            }
                      
            if (target === '#node_tasks') {
                $('.devices-links').addClass('hidden');      
                $('.node-links').removeClass('hidden');      
            }
            
            if (target === '#device_tasks') {
                $('.devices-links').removeClass('hidden');      
                $('.node-links').addClass('hidden');      
            }
            
        });
        
        /** Check if session storage is available */
        if (_supportsSessionStorage) {
            
            /** Get active tab from session storage */
            var active = sessionStorage.getItem('active');
            
            /** Set active tab on page reload */
            if (active !== '') {
                $('[href=\"' + active + '\"]').tab('show');
            }
            
        }
        
    "
)
?>

<div class="row">
    <div class="col-md-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#node_tasks" data-toggle="tab" aria-expanded="true">
                        <i class="fa fa-tasks"></i> <?= Yii::t('network', 'Node tasks')?></a>
                </li>
                <li>
                    <a href="#device_tasks" class="test" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-list"></i> <?= Yii::t('network', 'Default device workers')?></a>
                </li>
                <li class="pull-right">
                    <div class="tab-links node-links">
                        <?= Html::a(Yii::t('network', 'Assign task to node'), ['assign-node-task'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                        <?= Html::a(Yii::t('network', 'Advanced task assign'), 'javascript:;', [
                                'class'       => 'btn btn-sm bg-light-blue',
                                'data-toggle' => 'modal',
                                'data-target' => '#task_modal',
                            ])
                        ?>
                    </div>
                    <div class="tab-links devices-links hidden">
                        <?= Html::a(Yii::t('network', 'Assign worker to device'), ['assign-device-task'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                        <?= Html::a(Yii::t('network', 'Advanced worker assign'), 'javascript:;', [
                            'class'       => 'btn btn-sm bg-light-blue',
                            'data-toggle' => 'modal',
                            'data-target' => '#device_task_modal',
                        ])
                        ?>
                    </div>
                </li>
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane active" id="node_tasks">
                    <?php Pjax::begin(['id' => 'task-nodes-pjax']); ?>
                        <?php
                            /** @noinspection PhpUnhandledExceptionInspection */
                            echo GroupGridView::widget([
                                'id'           => 'task-nodes-grid',
                                'options'      => ['class' => 'grid-view tab-grid-view'],
                                'tableOptions' => ['class' => 'table table-bordered'],
                                'dataProvider' => $nodeDataProvider,
                                'filterModel'  => $nodeSearchModel,
                                'mergeColumns' => ['node_name', 'node_ip'],
                                'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                                'columns' => [
                                    [
                                        'format'    => 'raw',
                                        'attribute' => 'node_name',
                                        'options'   => ['style' => 'width:25%'],
                                        'value'     => 'nodeNameStyled'
                                    ],
                                    [
                                        'attribute' => 'node_ip',
                                        'options'   => ['style' => 'width:15%'],
                                        'value'     => 'node.ip'
                                    ],
                                    [
                                        'format'    => 'raw',
                                        'attribute' => 'task_name',
                                        'options'   => ['style' => 'width:25%']
                                    ],
                                    [
                                        'attribute' => 'worker_name',
                                        'value'     => 'worker.name',
                                        'options'   => ['style' => 'width:35%']
                                    ],
                                    [
                                        'class'          => 'yii\grid\ActionColumn',
                                        'contentOptions' => ['class' => 'narrow'],
                                        'template'       => '{edit} {delete}',
                                        'buttons'        => [
                                            'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\TasksHasNodes */
                                                return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/network/assigntask/edit-node-task', 'id' => $model->id], [
                                                    'title'     => Yii::t('app', 'Edit'),
                                                    'data-pjax' => '0',
                                                ]);
                                            },
                                            'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\TasksHasNodes */
                                                return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                    'class'             => 'ajaxGridUpdate',
                                                    'title'             => Yii::t('app', 'Delete'),
                                                    'style'             => 'color: #D65C4F',
                                                    'data-ajax-url'     => Url::to(['/network/assigntask/ajax-delete-node-task', 'id' => $model->id]),
                                                    'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0} {1}?', [
                                                        $model->node->hostname, $model->node->ip
                                                    ])
                                                ]);
                                            },
                                        ],
                                    ]
                                ],
                            ]);
                        ?>
                    <?php Pjax::end(); ?>
                </div>
                <div class="tab-pane" id="device_tasks">
                    <?php Pjax::begin(['id' => 'task-devices-pjax']); ?>
                        <?php
                            /** @noinspection PhpUnhandledExceptionInspection */
                            echo GroupGridView::widget([
                                'id'           => 'task-devices-grid',
                                'options'      => ['class' => 'grid-view tab-grid-view'],
                                'tableOptions' => ['class' => 'table table-bordered'],
                                'dataProvider' => $deviceDataProvider,
                                'filterModel'  => $deviceSearchModel,
                                'mergeColumns' => ['device_name'],
                                'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                                'columns' => [
                                    [
                                        'attribute' => 'device_name',
                                        'options'   => ['style' => 'width:25%'],
                                        'value'     => function($data) { /** @var $data \app\models\TasksHasDevices */
                                            return "{$data->device->vendor} {$data->device->model}";
                                        }
                                    ],
                                    [
                                        'format'    => 'raw',
                                        'attribute' => 'task_name',
                                        'options'   => ['style' => 'width:25%']
                                    ],
                                    [
                                        'attribute' => 'worker_name',
                                        'value'     => 'worker.name',
                                        'options'   => ['style' => 'width:50%']
                                    ],
                                    [
                                        'class'          => 'yii\grid\ActionColumn',
                                        'contentOptions' => ['class' => 'narrow'],
                                        'template'       => '{edit} {delete}',
                                        'buttons'        => [
                                            'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\TasksHasDevices */
                                                return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/network/assigntask/edit-device-task', 'id' => $model->id], [
                                                    'title'     => Yii::t('app', 'Edit'),
                                                    'data-pjax' => '0',
                                                ]);
                                            },
                                            'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\TasksHasDevices */
                                                return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                    'class'             => 'ajaxGridUpdate',
                                                    'title'             => Yii::t('app', 'Delete'),
                                                    'style'             => 'color: #D65C4F',
                                                    'data-ajax-url'     => Url::to(['/network/assigntask/ajax-delete-device-task', 'id' => $model->id]),
                                                    'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0} {1}?', [
                                                        $model->device->vendor, $model->device->model
                                                    ])
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
</div>

<!-- assign task to nodes -->
<div id="task_modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= Yii::t('network', 'Redirect to advanced node assignment') ?></h4>
            </div>
            <div class="modal-body">
                <?php
                    echo Html::dropDownList('task_name', '', $tasks_list, [
                        'id'               => 'task_select',
                        'prompt'           => '',
                        'class'            => 'select2-search-modal',
                        'data-placeholder' => Yii::t('network', 'Choose task'),
                    ]);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
                <?php
                    echo Html::a(Yii::t('app', 'Confirm'), 'javascript:;', [
                        'id'         => 'redirect_node',
                        'class'      => 'btn btn-primary disabled',
                        'data-url'   => Url::to(['/network/assigntask/adv-node-assign']),
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>


<!-- assign worker to devices -->
<div id="device_task_modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= Yii::t('network', 'Redirect to advanced worker assignment') ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" style="margin-bottom: 15px">
                        <?php
                            echo Html::dropDownList('task_name', '', $tasks_list, [
                                'id'               => 'task_name',
                                'prompt'           => '',
                                'class'            => 'select2-search-modal',
                                'data-placeholder' => Yii::t('network', 'Choose task'),
                            ]);
                        ?>
                    </div>
                    <div class="col-md-12">
                        <?php
                            /** @noinspection PhpUnhandledExceptionInspection */
                            echo DepDrop::widget([
                                'name'  => 'worker_select',
                                'options' => [
                                    'id'               => 'worker_list',
                                    'class'            => 'select2-search-modal',
                                    'data-placeholder' => Yii::t('network', 'Choose worker')
                                ],
                                'pluginOptions' => [
                                    'depends'     => ['task_name'],
                                    'loadingText' => Yii::t('app', 'Wait...'),
                                    'url'         => Url::to(['/network/assigntask/ajax-get-task-workers']),
                                ]
                            ]);
                        ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
                <?php
                    echo Html::a(Yii::t('app', 'Confirm'), 'javascript:;', [
                        'id'         => 'redirect_device',
                        'class'      => 'btn btn-primary disabled',
                        'data-url'   => Url::to(['/network/assigntask/adv-device-assign']),
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>
