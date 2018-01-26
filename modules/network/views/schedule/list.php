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
use app\helpers\StringHelper;

/**
 * @var $this              yii\web\View
 * @var $dataProvider      yii\data\ActiveDataProvider
 * @var $mailDataProvider  yii\data\ActiveDataProvider
 * @var $searchModel       app\models\search\ScheduleSearch
 * @var $mailSearchModel   app\models\search\ScheduleMailSearch
 */
$this->title = Yii::t('app', 'Schedules');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Schedules')];

$this->registerJs(
    /** @lang JavaScript */
    "   
        /** Save active tab to session storage and show necessary tab buttons */
        $('a[data-toggle=tab]').on('shown.bs.tab', function () {
           
            var target = $(this).attr('href');
            
            /** Check if session storage is available */
            if (_supportsSessionStorage) {
                sessionStorage.setItem('active', target);
            }
                      
            if (target === '#tasks_schedule') {
                $('.mail-schedule-links').addClass('hidden');      
                $('.tasks-schedule-links').removeClass('hidden');      
            }
            
            if (target === '#mail_schedule') {
                $('.mail-schedule-links').removeClass('hidden');      
                $('.tasks-schedule-links').addClass('hidden');      
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
    <div class="col-md-9">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#tasks_schedule" data-toggle="tab" aria-expanded="true">
                        <i class="fa fa-calendar"></i> <?= Yii::t('network', 'Task schedules')?>
                    </a>
                </li>
                <li>
                    <a href="#mail_schedule" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-calendar"></i> <?= Yii::t('network', 'Mailer schedules')?>
                    </a>
                </li>
                <li class="pull-right">
                    <div class="tab-links tasks-schedule-links">
                        <?= Html::a(Yii::t('network', 'Add scheduled task'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                    </div>
                    <div class="tab-links mail-schedule-links hidden">
                        <?= Html::a(Yii::t('network', 'Add scheduled mail event'), ['add-mail-schedule'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                    </div>
                </li>
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane active" id="tasks_schedule">
                    <?php Pjax::begin(['id' => 'schedule-pjax']); ?>
                        <?php
                            /** @noinspection PhpUnhandledExceptionInspection */
                            echo GridView::widget([
                                'id'           => 'schedule-grid',
                                'options'      => ['class' => 'grid-view tab-grid-view'],
                                'tableOptions' => ['class' => 'table table-bordered'],
                                'dataProvider' => $dataProvider,
                                'filterModel'  => $searchModel,
                                'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                                'columns' => [
                                    [
                                        'attribute' => 'id',
                                        'options'   => ['style' => 'width:7%'],
                                    ],
                                    [
                                        'format'    => 'raw',
                                        'attribute' => 'task_name',
                                        'options'   => ['style' => 'width:25%'],
                                        'value'     => function($data) { /** @var $data \app\models\Schedule */
                                            return Html::a($data->task_name, ['/network/schedule/edit', 'id' => $data->id], ['data-pjax' => '0']);
                                        },
                                    ],
                                    [
                                        'attribute'     => 'schedule_cron',
                                        'enableSorting' => false,
                                    ],
                                    [
                                        'label'   => Yii::t('network', 'Next run'),
                                        'options' => ['style' => 'width:15%'],
                                        'value'   => function($data) { /** @var $data \app\models\Schedule */
                                            return StringHelper::cronNextRunDate($data->schedule_cron);
                                        }
                                    ],
                                    [
                                        'class'          => 'yii\grid\ActionColumn',
                                        'contentOptions' => ['class' => 'narrow'],
                                        'template'       => '{edit} {start} {delete}',
                                        'buttons'        => [
                                            'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Schedule */
                                                return Html::a('<i class="fa fa-pencil-square-o"></i>', ['edit', 'id' => $model->id], [
                                                    'title'     => Yii::t('app', 'Edit'),
                                                    'data-pjax' => '0',
                                                ]);
                                            },
                                            'start' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Schedule */
                                                return Html::a('<i class="fa fa-play-circle-o"></i>', 'javascript:void(0);', [
                                                    'class'             => 'ajaxGridUpdate',
                                                    'title'             => Yii::t('app', 'Start task'),
                                                    'data-ajax-url'     => Url::to(['ajax-scheduler-run-task', 'task_name' => $model->task_name]),
                                                    'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to start task {0}?', $model->task_name),
                                                ]);
                                            },
                                            'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Schedule */
                                                return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:void(0);', [
                                                    'class'             => 'ajaxGridUpdate',
                                                    'title'             => Yii::t('app', 'Delete'),
                                                    'style'             => 'color: #D65C4F',
                                                    'data-ajax-url'     => Url::to(['ajax-delete', 'id' => $model->id]),
                                                    'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->task_name),
                                                ]);
                                            },
                                        ],
                                    ]
                                ],
                            ]);
                        ?>
                    <?php Pjax::end(); ?>
                </div>
                <div class="tab-pane" id="mail_schedule">
                    <?php Pjax::begin(['id' => 'schedule-mail-pjax']); ?>
                        <?php
                            /** @noinspection PhpUnhandledExceptionInspection */
                            echo GridView::widget([
                                'id'           => 'schedule-mail-grid',
                                'options'      => ['class' => 'grid-view tab-grid-view'],
                                'tableOptions' => ['class' => 'table table-bordered'],
                                'dataProvider' => $mailDataProvider,
                                'filterModel'  => $mailSearchModel,
                                'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                                'columns' => [
                                    [
                                        'attribute' => 'id',
                                        'options'   => ['style' => 'width:7%'],
                                    ],
                                    [
                                        'format'    => 'raw',
                                        'attribute' => 'event_name',
                                        'options'   => ['style' => 'width:25%'],
                                        'value'     => function($data) { /** @var $data \app\models\ScheduleMail */
                                            return Html::a($data->event_name, ['/network/schedule/edit-mail-schedule', 'id' => $data->id], ['data-pjax' => '0']);
                                        },
                                    ],
                                    [
                                        'attribute'     => 'schedule_cron',
                                        'enableSorting' => false,
                                    ],
                                    [
                                        'label'   => Yii::t('network', 'Next run'),
                                        'options' => ['style' => 'width:15%'],
                                        'value'   => function($data) { /** @var $data \app\models\ScheduleMail */
                                            return StringHelper::cronNextRunDate($data->schedule_cron);
                                        }
                                    ],
                                    [
                                        'class'          => 'yii\grid\ActionColumn',
                                        'contentOptions' => ['class' => 'narrow'],
                                        'template'       => '{edit} {delete}',
                                        'buttons'        => [
                                            'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\ScheduleMail */
                                                return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/network/schedule/edit-mail-schedule', 'id' => $model->id], [
                                                    'title'     => Yii::t('app', 'Edit'),
                                                    'data-pjax' => '0',
                                                ]);
                                            },
                                            'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\ScheduleMail */
                                                return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:void(0);', [
                                                    'class'             => 'ajaxGridUpdate',
                                                    'title'             => Yii::t('app', 'Delete'),
                                                    'style'             => 'color: #D65C4F',
                                                    'data-ajax-url'     => Url::to(['/network/schedule/ajax-delete-mail-schedule', 'id' => $model->id]),
                                                    'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->event_name),
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
    <div class="col-md-3">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Information') ?></h3>
            </div>
            <div class="box-body text-justify">
                <?= Yii::t('network', 'After making changes in schedules on the left, do not forget to apply them by restarting scheduler. Press <i class="fa fa-hdd-o text-info"></i> button in the top navigation bar and press <i class="fa fa-refresh text-warning"></i> button for <b>Java Scheduler</b>.') ?>
        </div>
    </div>
</div>
