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
use yii\widgets\Pjax;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ArrayDataProvider
 * @var $searchModel   app\models\search\WorkerSearch
 * @var $data          array
 * @var $protocols     array
 * @var $tasks         array
 * @var $table_fields  array
 * @var $check_jobs    array
 */
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);
app\assets\ToggleAsset::register($this);
app\assets\WorkerAsset::register($this);
app\assets\i18nextAsset::register($this);
app\assets\DataTablesBootstrapAsset::register($this);
yii2mod\alert\AlertAsset::register($this);

$this->title = Yii::t('app', 'Workers & Jobs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Workers & Jobs' )];

/** Variable helper CSS, used in _job_form_modal */
/** @noinspection CssUnusedSymbol */
$this->registerCss(
    /** @lang CSS */
    '
        table.dataTable {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            width: 100% !important;
        }

        table.table.static-var.dataTable > thead > tr > th  {
            border-top: 1px solid #f4f4f4;
        }
        
        table.static-var.dataTable > thead > tr > td {
            border-top: none;
        }

        table.static-var.dataTable  > thead > tr > td,
        table.dynamic-var.dataTable > thead > tr > th {
            border-bottom: 1px solid #f4f4f4;
        }
        
        .dataTables_scroll > .dataTables_scrollHead > .dataTables_scrollHeadInner {
            width: 100% !important;
        }

        .table > thead > tr > th {
            border-bottom: 1px solid #f4f4f4;
            font-weight: 600;
            text-align: center;
        }
        
        .tab-content .tab-pane .table > tbody > tr > td {
            cursor: pointer !important;
        }
    '
);
?>

<div class="row">
    <?php Pjax::begin(['id' => 'tree-pjax']); ?>
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'List of workers & jobs') ?></h3>
                    <div class="pull-right">
                        <?php
                            echo Html::a(Yii::t('network', 'Add worker'), Url::to(['/network/worker/ajax-add-worker',
                                'task_name' => ''
                            ]), [
                                'class'         => 'btn btn-sm bg-light-blue margin-r-5',
                                'data-toggle'   => 'modal',
                                'data-target'   => '#job_form_modal',
                                'data-backdrop' => 'static',
                                'data-keyboard' => 'false'
                            ]);
                            echo Html::a('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), 'javascript:;', [
                                'class'       => 'btn btn-sm bg-light-black',
                                'data-toggle' => 'control-sidebar'
                            ]);
                        ?>
                    </div>
                </div>

                <?php if(empty($data)): ?>
                    <div class="box-body">
                        <div class="callout callout-info" style="margin-bottom: 0;">
                            <p><?= Yii::t('network', 'Nothing to show here') ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="box-body">
                        <ul class="tree-ul">
                            <?php foreach ($data as $key => $entry): ?>
                                <li class="header-li header-task">
                                    <?= Yii::t('network', 'Task name: {0}', $key) ?>
                                    <div class="tools">
                                        <?php
                                            echo Html::a('<i class="fa fa-plus"></i>', Url::to(['/network/worker/ajax-add-worker',
                                                'task_name' => $key,
                                            ]), [
                                                'class'         => 'visible',
                                                'title'         => Yii::t('network', 'Add worker'),
                                                'data-toggle'   => 'modal',
                                                'data-target'   => '#job_form_modal',
                                                'data-backdrop' => 'static',
                                                'data-keyboard' => 'false'
                                            ]);
                                        ?>
                                    </div>
                                </li>
                                <?php foreach ($entry as $worker): ?>
                                    <li class="header-li header-worker">
                                        <?php
                                            echo strtoupper($worker['get']) . ' :: ' . $worker['name'] . (!empty($worker['description']) ? ' :: ' . $worker['description'] : '');
                                            echo $searchModel->renderWorkerAssignments($worker['id']);
                                        ?>
                                        <div class="tools">
                                            <?php
                                                echo Html::a('<i class="fa fa-eye"></i>', 'javascript:;', [
                                                    'class'            => 'worker-view',
                                                    'title'            => Yii::t('network', 'View worker'),
                                                    'data-ajax-url'    => Url::to(['/network/worker/ajax-view-worker', 'worker_id' => $worker['id']])
                                                ]);
                                                echo Html::a('<i class="fa fa-save"></i>', 'javascript:;', [
                                                    'id'            => 'worker_' . $worker['id'],
                                                    'class'         => 'save-order',
                                                    'title'         => Yii::t('network', 'Save sequence'),
                                                    'data-ajax-url' => Url::to(['/network/worker/ajax-save-order']),
                                                    'disabled'      => (count($worker['sortedJobs']) < 1) ? 'disabled' : '',
                                                ]);
                                                echo Html::a('<i class="fa fa-plus"></i>', Url::to(['/network/worker/ajax-add-job',
                                                    'worker_id' => $worker['id'],
                                                    'task_name' => $worker['task_name']
                                                ]), [
                                                    'title'         => Yii::t('network', 'Add job'),
                                                    'data-toggle'   => 'modal',
                                                    'data-target'   => '#job_form_modal_lg',
                                                    'data-backdrop' => 'static',
                                                    'data-keyboard' => 'false'
                                                ]);
                                                echo Html::a('<i class="fa fa-pencil-square-o"></i>', Url::to(['/network/worker/ajax-edit-worker',
                                                    'worker_id' => $worker['id'],
                                                ]), [
                                                    'title'         => Yii::t('network', 'Edit worker'),
                                                    'data-toggle'   => 'modal',
                                                    'data-target'   => '#job_form_modal',
                                                    'data-backdrop' => 'static',
                                                    'data-keyboard' => 'false'
                                                ]);
                                                echo Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                    'class'            => 'entry-delete',
                                                    'title'            => Yii::t('network', 'Delete worker'),
                                                    'style'            => 'color: #dd4b39',
                                                    'data-confirm-txt' => Yii::t('network', 'Are you sure you want to delete worker {0}?', $worker['name']),
                                                    'data-ajax-url'    => Url::to(['/network/worker/ajax-delete-worker', 'worker_id' => $worker['id']])
                                                ]);
                                                echo Html::a('<i class="fa fa-angle-down" style="font-size: 18px"></i>', 'javascript:;' , [
                                                    'id'          => 'toggle_jobs_' . $worker['id'],
                                                    'class'       => 'toggle visible ' . (empty($worker['sortedJobs']) ? 'disabled' : ''),
                                                    'data-toggle' => 'collapse',
                                                    'data-target' => '#jobs_' . $worker['id'],
                                                    'title'       => Yii::t('network', 'View jobs')
                                                ]);
                                            ?>
                                        </div>
                                    </li>
                                    <?php if (!empty($worker['sortedJobs'])): ?>
                                        <li class="sort-li">
                                            <ul id="jobs_<?= $worker['id'] ?>" class="todo-list job_list collapse">
                                                <?php foreach ($worker['sortedJobs'] as $job): ?>
                                                    <li id="job_<?= $job['id'] ?>" class="<?= ($job['enabled'] == 0) ? 'job-disabled' : ''?>">
                                                        <span class="handle <?= ($job['enabled'] == 1) ? 'movable' : ''?>">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </span>
                                                        <?php
                                                            echo Html::checkbox('', $job['enabled'], [
                                                                'class'         => 'job-status',
                                                                'data-ajax-url' => Url::to(['/network/worker/ajax-switch-status', 'job_id' => $job['id']])
                                                            ]);
                                                        ?>
                                                        <span class="text">
                                                            <?php
                                                                echo Yii::t('network', 'Sequence: {0} :: Job name: {1}', [$job['sequence_id'], $job['name']]);
                                                                echo $searchModel->renderJobTooltip($worker['id'], $job['command_var']);
                                                            ?>
                                                        </span>
                                                        <div class="tools">
                                                            <?php
                                                                echo Html::a('<i class="fa fa-edit"></i>', Url::to(['/network/worker/ajax-edit-job',
                                                                        'job_id' => $job['id'],
                                                                        'task_name' => $worker['task_name']
                                                                    ]), [
                                                                        'title'         => Yii::t('network', 'Edit job'),
                                                                        'data-toggle'   => 'modal',
                                                                        'data-target'   => '#job_form_modal_lg',
                                                                        'data-backdrop' => 'static',
                                                                        'data-keyboard' => 'false'
                                                                    ]);
                                                                echo Html::a('<i class="fa fa-eye"></i>', 'javascript:;', [
                                                                    'class'            => 'job-view',
                                                                    'title'            => Yii::t('network', 'View job'),
                                                                    'data-ajax-url'    => Url::to(['/network/worker/ajax-view-job', 'job_id' => $job['id']])
                                                                ]);
                                                                echo Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                                    'class'            => 'entry-delete',
                                                                    'title'            => Yii::t('network', 'Delete job'),
                                                                    'style'            => 'color: #dd4b39',
                                                                    'data-confirm-txt' => Yii::t('network', 'Are you sure you want to delete job {0}?', $job['name']),
                                                                    'data-ajax-url'    => Url::to(['/network/worker/ajax-delete-job', 'job_id' => $job['id']])
                                                                ]);
                                                            ?>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="box-footer clearfix no-border">
                        <div class="col-md-3">
                            <div class="summary">
                                <?= Yii::t('network', 'Showing <b>{0}</b> of <b>{1}</b>.', [$dataProvider->getCount(), $dataProvider->getTotalCount()]) ?>
                            </div>
                        </div>
                        <div class="box-tools pull-right">
                            <?php
                               /** @noinspection PhpUnhandledExceptionInspection */
                               echo \yii\widgets\LinkPager::widget([
                                   'pagination' => $dataProvider->pagination,
                                   'options' => [
                                       'class' => 'pagination pagination-sm inline'
                                   ]
                               ]);
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <i class="fa fa-info-circle"></i>
                    <h3 class="box-title box-title-align"><?= Yii::t('network', 'Detailed information') ?></h3>
                </div>
                <div class="box-body start-message">
                    <div class="callout callout-info" style="margin-bottom: 0;">
                        <p><?= Yii::t('network', 'Choose job to view detailed information') ?></p>
                    </div>
                </div>
                <div class="info div-scroll"></div>
            </div>

            <?php if (!empty($check_jobs)): ?>
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <i class="fa fa-warning"></i>
                        <h3 class="box-title box-title-align"><?= Yii::t('app', 'Warning') ?></h3>
                    </div>
                    <div class="box-body">
                        <span class="text-justify">
                            <?= Yii::t('network', 'Some errors were detected in worker jobs. The following error tree represents where required table fields are missing. Please fix all errors otherwise worker may not work correctly.<br>') ?>
                        </span>
                        <ul class="tree">
                            <?php foreach ($check_jobs as $task_name => $workers): ?>
                                <li><?= Yii::t('network', 'Task') . ': ' . $task_name ?>
                                    <ul>
                                        <?php foreach ($workers as $worker_name => $field): ?>
                                            <li>
                                                <?php
                                                    echo Html::a($worker_name . ' <i style="font-size: 10px" class="fa fa-filter"></i>',
                                                        Url::to(['list', 'WorkerSearch[name]' => $worker_name]), ['data-pjax'=> 0]
                                                    );
                                                    echo Html::ul($field);
                                                ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php Pjax::end(); ?>
</div>

<?php
    /** Control sidebar */
    echo $this->render('_search', [
        'model'        => $searchModel,
        'protocols'    => $protocols,
        'tasks'        => $tasks,
        'table_fields' => $table_fields
    ]);
?>

<!-- job modal -->
<div id="job_form_modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= Yii::t('app', 'Wait...') ?></h4>
            </div>
            <div class="modal-body">
                <span style="margin-left: 24%;"><?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- job modal large -->
<div id="job_form_modal_lg" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= Yii::t('app', 'Wait...') ?></h4>
            </div>
            <div class="modal-body">
                <span style="margin-left: 24%;"><?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
