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
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;

/**
 * @var $this           yii\web\View
 * @var $model          app\models\TasksHasDevices
 * @var $form           yii\bootstrap\ActiveForm
 * @var $devices_list   array
 * @var $tasks_list     array
 */
app\assets\TaskAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action = $this->context->action->id;

$page_name   = ($action == 'assign-device-task') ? Yii::t('network', 'Assign worker to device') : Yii::t('network', 'Edit device assignment');
$this->title = Yii::t('app', 'Worker assignments');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Task assignments'), 'url' => ['/network/assigntask/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];
?>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'assign-device-task') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php
                $form = ActiveForm::begin([
                    'id'                     => 'user_form',
                    'layout'                 => 'horizontal',
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'errorOptions' => ['encode' => false],
                        'horizontalCssClasses' => [
                            'label'   => 'col-sm-2',
                            'wrapper' => 'col-sm-10'
                        ],
                    ],
                ]);
            ?>
                <div class="box-body">
                    <?php
                        echo $form->field($model, 'device_id')->dropDownList($devices_list, [
                            'class'            => 'select2',
                            'prompt'           => '',
                            'data-placeholder' => Yii::t('network', 'Choose device')
                        ]);
                        echo $form->field($model, 'task_name')->dropDownList($tasks_list, [
                            'id'               => 'task_name',
                            'class'            => 'select2',
                            'prompt'           => '',
                            'data-placeholder' => Yii::t('network', 'Choose task')
                        ]);
                        echo Html::hiddenInput('worker_id', $model->worker_id, ['id' => 'worker_id']);
                        echo $form->field($model, 'worker_id',[
                            'inputTemplate' =>
                                '
                                    <div class="input-group">
                                        {input}
                                        <div class="input-group-btn">
                                            '.Html::a('<i class="fa fa-plus-square-o"></i>', 'javascript:;', [
                                                'id'            => 'modal_link',
                                                'class'         => 'btn btn-default',
                                                'title'         => Yii::t('network', 'Add worker'),
                                                'data-url'      => Url::to(['/network/worker/ajax-add-worker']),
                                                'data-toggle'   => 'modal',
                                                'data-target'   => '#form_modal',
                                                'data-backdrop' => 'static',
                                                'data-keyboard' => 'false',
                                            ]).'
                                        </div>
                                    </div>
                                '
                        ])->widget(DepDrop::class, [
                            'options' => [
                                'id'               => 'worker_list',
                                'class'            => 'select2',
                                'data-placeholder' => Yii::t('network', 'Choose worker'),
                                'data-update-url'  => Url::to(['/network/assigntask/ajax-update-workers'])
                            ],
                            'pluginOptions' => [
                                'depends'     => ['task_name'],
                                'loadingText' => Yii::t('app', 'Wait...'),
                                'url'         => Url::to(['/network/assigntask/ajax-get-task-workers']),
                                'params'      => ['worker_id'],
                                'initialize'  => (!empty($model->task_name)) ? true : false,
                            ]
                        ]);
                    ?>
                </div>
                <div class="box-footer text-right">
                    <?php
                        if ($action == 'edit-device-task') {
                            echo Html::a(Yii::t('app', 'Delete'), ['delete-device-task', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-danger pull-left',
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete record {0} {1}?', [$model->device->vendor, $model->device->model]),
                                    'method'  => 'post'
                                ],
                            ]);
                        }
                        echo Html::a(Yii::t('app', 'Cancel'), ['/network/assigntask'], ['class' => 'btn btn-sm btn-default margin-r-5']);
                        echo Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']);
                    ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- worker form modal -->
<div id="form_modal" class="modal fade">
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
