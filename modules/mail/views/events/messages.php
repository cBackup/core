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

use app\models\Setting;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $searchModel   app\models\search\MailerEventsTasksSearch
 * @var $events_list   array
 * @var $statuses      array
 */
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);
app\assets\DatetimepickerAsset::register($this);

$this->title = Yii::t('app', 'Mailer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mailer'), 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Message')];

$this->registerJs(
    /** @lang JavaScript */
    "
        /** Init select2 */
        $('.select2').select2({
            width: '100%',
            minimumResultsForSearch: -1
        });

        /** Show search form on button click */
        $('.search-button').click(function() {
            $('.mailer-messages-search').slideToggle('slow');
            return false;
        });
        
        /** Mailer messages search form submit and reload gridview */
        $('.mailer-messages-search-form form').submit(function(e) {
            e.stopImmediatePropagation(); // Prevent double submit
            gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
            $.pjax.reload({container:'#mailer-messages-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
            return false;
        });
        
        /** Init mailer from datetimepicker */
        $('#mailerFrom_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            ignoreReadonly: true
        }).on('dp.hide', function(e){
            if (e.target.value.length > 0) {
                $('#mailerTo_date').data('DateTimePicker').minDate(e.target.value);
            }
        });
        
        /** Init mailer to datetimepicker */
        $('#mailerTo_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            ignoreReadonly: true
        }).on('dp.hide', function(e){
            if (e.target.value.length > 0) {
                var date = moment(e.target.value).add(1, 'days');
                $('#mailerFrom_date').data('DateTimePicker').maxDate(date).disabledDates([date]);
            }
        });
        
        /** Clear selected date on button click */
        $('.date-clear').click(function() {
            var dp_id = '#' + $(this)[0].id.split('_')[0] + '_date';
            $(dp_id).data('DateTimePicker').date(null).maxDate(false).minDate(false).disabledDates(false);
        });
    "
);

?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('mail', 'List of sent messages') ?></h3>
                <div class="pull-right">
                    <?= Html::a('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), 'javascript:void(0);', ['class' => 'btn btn-sm bg-light-black search-button']) ?>
                </div>
            </div>
            <div class="box-body no-padding">

                <div class="mailer-messages-search" style="display: none;">
                    <?php
                        echo $this->render('_search', [
                            'model'       => $searchModel,
                            'events_list' => $events_list,
                            'statuses'    => $statuses
                        ]);
                    ?>
                </div>

                <?php Pjax::begin(['id' => 'mailer-messages-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'mailer-messages-grid',
                            'tableOptions' => ['class' => 'table table-bordered ellipsis'],
                            'dataProvider' => $dataProvider,
                            'afterRow'     => function($model) { /** @var $model \app\models\MailerEventsTasks */
                                $id = 'message_' . $model->id;
                                return '<tr><td class="grid-expand-row" colspan="8"><div class="grid-expand-div" id="'.$id.'">'.$model->body.'</div></td></tr>';
                            },
                            'layout'  => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'         => 'raw',
                                    'options'        => ['style' => 'width:3%'],
                                    'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                                    'value'          => function($model) { /** @var $model \app\models\MailerEventsTasks */
                                        return Html::a('<i class="fa fa-caret-square-o-down"></i>', 'javascript:void(0);', [
                                            'class'         => 'gridExpand',
                                            'title'         => Yii::t('log', 'Show full message'),
                                            'data-div-id'   => '#message_' . $model->id,
                                            'data-multiple' => 'false'
                                        ]);
                                    },
                                ],
                                [
                                    'attribute' => 'created',
                                    'value'     => function($data) {
                                        return Yii::$app->formatter->asDatetime($data->created, 'php:'.Setting::get('datetime'));
                                    },
                                    'options'   => ['style' => 'width:14%']
                                ],
                                [
                                    'format'        => 'raw',
                                    'attribute'     => 'event_name',
                                    'value'         => 'eventLinks',
                                    'options'       => ['style' => 'width:20%']
                                ],
                                [
                                    'format'        => 'raw',
                                    'label'         =>  Yii::t('app', 'Recipients'),
                                    'value'         => 'eventRecipients',
                                    'enableSorting' => false,
                                    'options'       => ['style' => 'width:20%']
                                ],
                                [
                                    'attribute'      => 'subject',
                                    'contentOptions' => ['class' => 'hide-overflow']
                                ],
                                [
                                    'format'         => 'raw',
                                    'attribute'      => 'status',
                                    'value'          => 'eventStyledStatus',
                                    'options'       => ['style' => 'width:12%']
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{history} {delete}',
                                    'buttons'        => [
                                        'history' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\MailerEventsTasks */
                                            return Html::a('<i class="fa fa-history"></i>', [
                                                '/log/mailerlog/list', 'LogMailerSearch[event_task_id]' => $model->id
                                            ], [
                                                'title'     => Yii::t('app', 'History'),
                                                'data-pjax' => '0',
                                                'target'    => '_blank'
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\MailerEventsTasks */
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:void(0);', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['ajax-event-task-delete', 'id' => $model->id]),
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
