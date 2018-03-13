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

use app\helpers\StringHelper;
use app\models\Setting;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var $this               \yii\web\View
 * @var $form               yii\bootstrap\ActiveForm
 * @var $scheduler_logs     array
 * @var $node_logs          array
 * @var $system_logs        array
 * @var $dataProvider       yii\data\ActiveDataProvider
 * @var $dashboard_stats    array
 * @var $searchModel        app\models\search\NodeSearch
 * @var $orphan_count       int
 */
$this->title = Yii::t('app', 'Dashboard');
$this->params['breadcrumbs'][] = Yii::t('app', 'Dashboard');

$backup    = [];
$discovery = [];

if( isset($dashboard_stats['discovery']->message) ) {
    /** @noinspection HtmlUnknownTag */
    preg_match('/success.+?(?<success>\d+).+?fail.+?(?<failed>\d+)/im', $dashboard_stats['discovery']->message, $discovery);
}
if( isset($dashboard_stats['backup']->message) ) {
    /** @noinspection HtmlUnknownTag */
    preg_match('/success.+?(?<success>\d+).+?fail.+?(?<failed>\d+)/im', $dashboard_stats['backup']->message, $backup);
}
?>
<div class="row">
    <div class="col-xs-12 col-lg-7">
        <div class="box">
            <div class="box-header"><i class="fa fa-tasks"></i>
                <h3 class="box-title"><?= Yii::t('node', 'Nodes') ?></h3>
                <div class="box-tools pull-right">
                    <?php
                        $options = [
                            'ip'       => Yii::t('network', 'IP address'),
                            'hostname' => Yii::t('network', 'Hostname'),
                            'location' => Yii::t('network', 'Location'),
                            'device'   => Yii::t('network', 'Device')
                        ];
                        $form = ActiveForm::begin([
                            'id' => 'node_search', 'action' => ['index'], 'options' => ['class' => 'form-inline dashboard-search'],
                            'method' => 'get', 'enableClientValidation' => false
                        ]);
                            echo $form->field($searchModel, 'search_option')->dropDownList($options, [
                                'class' => 'form-control input-sm margin-r-5'
                            ])->label(false);
                            echo $form->field($searchModel, 'search_string')->textInput([
                                'class' => 'form-control input-sm margin-r-5'
                            ])->label(false);
                            echo Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-sm bg-light-blue']);

                        ActiveForm::end();
                    ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php

                    $dataProvider->sort = false;

                    /** @noinspection PhpUnhandledExceptionInspection */
                    echo \yii\grid\GridView::widget([
                        'id'           => 'node-grid',
                        'tableOptions' => ['class' => 'table table-hover'],
                        'dataProvider' => $dataProvider,
                        'layout'       => '{items}',
                        'columns' => [
                            [
                                'attribute' => 'hostname',
                                'format'    => 'raw',
                                'value'     => function($data) {
                                    $link = (empty($data->hostname)) ? $data->ip : $data->hostname;
                                    return Html::a($link, ['/node/view', 'id' => $data->id]);
                                }
                            ],
                            'ip',
                            'location',
                            [
                                'label' => Yii::t('network', 'Device'),
                                'value' => function($data) {
                                    return "{$data->device->vendor} {$data->device->model}";
                                }
                            ]
                        ]
                    ]);

                ?>
            </div>
        </div>
    </div>

    <div class="col-xs-12 col-lg-5">

        <div class="box box-default" style="margin-bottom: 30px">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> <?= Yii::t('app', 'Statistics') ?></h3>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td width="25%"><?= Yii::t('app', 'Disk space') ?></td>
                    <?php if( empty($dashboard_stats['disk_total']) ): ?>
                        <td class="bg-red">
                            <?= Yii::t('app', 'Error - incorrect configuration for') ?> "<?= Html::a(Yii::t('config', 'Path to storage folder'), ['/config'], ['style' => 'color: cyan']) ?>"
                        </td>
                    <?php else: ?>
                    <td style="padding-top: 3px; padding-bottom: 0;">
                        <?php
                            $p_free = round($dashboard_stats['disk_free']/$dashboard_stats['disk_total']*100, 2);
                            $p_used = 100 - $p_free;
                            $s_used = StringHelper::beautifySize($dashboard_stats['disk_total'] - $dashboard_stats['disk_free'])
                        ?>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" role="progressbar" style="width:<?= $p_free ?>%" <?php if($p_free < 30) echo 'data-toggle="tooltip" data-original-title="'.$s_used.'" data-placement="right"'; ?>>
                                <?php
                                    if($p_free > 30) {
                                        echo Yii::t('app', 'Free') . ': ' . StringHelper::beautifySize($dashboard_stats['disk_free']);
                                    }
                                ?>
                            </div>
                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:<?= $p_used ?>%" <?php if($p_used < 30) echo 'data-toggle="tooltip" data-original-title="'.$s_used.'" data-placement="left"'; ?>>
                                <?php
                                    if ($p_used > 30) {
                                        echo Yii::t('app', 'Used') . ': ' . $s_used;
                                    }
                                ?>
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td><?= Yii::t('node', 'Nodes') ?></td>
                    <td><?= $dashboard_stats['nodes'] ?></td>
                </tr>
                <tr>
                    <td><?= Yii::t('app', 'Backups') ?></td>
                    <td><?= $dashboard_stats['backups'] ?></td>
                </tr>
                <tr>
                    <td><?= Yii::t('app', 'Orphans') ?></td>
                    <td><?= Html::a($orphan_count, ['/node/orphans'], ['title' => Yii::t('node', 'List of orphans')]) ?></td>
                </tr>
                <tr>
                    <td><?= Yii::t('app', 'Last discovery') ?></td>
                    <td>
                        <div class="row">
                            <div class="col-md-4">
                                <?php
                                    /** @noinspection PhpUnhandledExceptionInspection */
                                    echo isset($dashboard_stats['discovery']->time) ? Yii::$app->formatter->asDatetime($dashboard_stats['discovery']->time, 'php:'.Setting::get('datetime')) : Yii::t('app', 'n/a')
                                ?>
                            </div>
                            <div class="col-md-4">
                                <?php
                                    if( array_key_exists('success', $discovery) ) {
                                        echo Yii::t('app', 'Successful: {0}', $discovery['success']);
                                    }
                                ?>
                            </div>
                            <div class="col-md-4">
                                <?php
                                    if( array_key_exists('failed', $discovery) ) {
                                        echo mb_strtoupper(Yii::t('app', 'n/a')) .": {$discovery['failed']}";
                                    }
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><?= Yii::t('app', 'Last backup') ?></td>
                    <td>
                        <div class="row">
                            <div class="col-md-4">
                                <?php
                                    /** @noinspection PhpUnhandledExceptionInspection */
                                    echo isset($dashboard_stats['backup']->time) ? Yii::$app->formatter->asDatetime($dashboard_stats['backup']->time, 'php:'.Setting::get('datetime')) : Yii::t('app', 'n/a')
                                ?>
                            </div>
                            <div class="col-md-4">
                                <?php
                                    if( array_key_exists('success', $backup) ) {
                                        echo Yii::t('app', 'Successful: {0}', $backup['success']);
                                    }
                                ?>
                            </div>
                            <?php
                                $warn = (array_key_exists('failed', $backup) && $backup['failed'] > 0) ? 'text-danger' : '';
                            ?>
                            <div class="col-md-4 <?= $warn ?>">
                                <?php
                                    if( array_key_exists('failed', $backup) && $backup['failed'] > 0 ) {
                                        echo Html::a(Yii::t('app', 'Failed: {0}', $backup['failed']), [
                                            '/log/scheduler/list',
                                            'LogSchedulerSearch[severity]'    => 'ERROR',
                                            'LogSchedulerSearch[date_from]'   => date('Y-m-d H:i', strtotime('midnight')),
                                            'LogSchedulerSearch[date_to]'     => date('Y-m-d H:i', strtotime('now'))
                                        ], ['class' => 'text-danger']);
                                    }
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="nav-tabs-custom box box-default dashboard-logs">
            <ul class="nav nav-tabs pull-right ui-sortable-handle" style="margin-top: -3px">
                <li class="active"><a href="#nodes-logs" data-toggle="tab" aria-expanded="true"><?= Yii::t('node', 'Nodes') ?></a></li>
                <li class=""><a href="#scheduler-logs" data-toggle="tab" aria-expanded="false"><?= Yii::t('app', 'Scheduler') ?></a></li>
                <li class=""><a href="#system-logs" data-toggle="tab" aria-expanded="false"><?= Yii::t('app', 'System') ?></a></li>
                <li class="pull-left header"><i class="fa fa-list"></i><?= Yii::t('app', 'Events') ?></li>
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane active" id="nodes-logs">
                    <table class="table">
                        <?php if(empty($node_logs)): ?>
                            <tr>
                                <td colspan="4" class="bg-warning text-warning">
                                    <?= Yii::t('log', 'No logs found in database') ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($node_logs as $entry): ?>
                                <tr>
                                    <td>
                                        <?php
                                            /** @noinspection PhpUnhandledExceptionInspection */
                                            echo Yii::$app->formatter->asDatetime($entry['time'], 'php:'.Setting::get('datetime'))
                                        ?>
                                    </td>
                                    <td><?= $entry['severity'] ?></td>
                                    <td><?= $entry['action'] ?></td>
                                    <td><div><?= $entry['message'] ?></div></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="text-right">
                                    <?= Html::a(Yii::t('log', "View all nodes' logs"), ['/log/nodelog/list']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="tab-pane" id="scheduler-logs">
                    <table class="table">
                        <?php if(empty($scheduler_logs)): ?>
                            <tr>
                                <td colspan="4" class="bg-warning text-warning">
                                    <?= Yii::t('log', 'No logs found in database') ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($scheduler_logs as $entry): ?>
                                <tr>
                                    <td>
                                        <?php
                                            /** @noinspection PhpUnhandledExceptionInspection */
                                            echo Yii::$app->formatter->asDatetime($entry['time'], 'php:'.Setting::get('datetime'))
                                        ?>
                                    </td>
                                    <td><?= $entry['severity'] ?></td>
                                    <td><?= $entry['action'] ?></td>
                                    <td><div><?= $entry['message'] ?></div></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="text-right">
                                    <?= Html::a(Yii::t('log', 'View all scheduler logs'), ['/log/scheduler/list']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="tab-pane" id="system-logs">
                    <table class="table">
                        <?php if(empty($system_logs)): ?>
                            <tr>
                                <td colspan="4" class="bg-warning text-warning">
                                    <?= Yii::t('log', 'No logs found in database') ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($system_logs as $entry): ?>
                                <tr>
                                    <td>
                                        <?php
                                            /** @noinspection PhpUnhandledExceptionInspection */
                                            echo Yii::$app->formatter->asDatetime($entry['time'], 'php:'.Setting::get('datetime'))
                                        ?>
                                    </td>
                                    <td><?= $entry['severity'] ?></td>
                                    <td><?= $entry['action'] ?></td>
                                    <td><div><?= $entry['message'] ?></div></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="text-right">
                                    <?= Html::a(Yii::t('log', 'View all system logs'), ['/log/system/list']) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
