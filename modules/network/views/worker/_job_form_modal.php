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
use yii\bootstrap\ToggleButtonGroup;
use app\helpers\FormHelper;

/** @noinspection PhpUndefinedFieldInspection
 *  @var $this          yii\web\View
 *  @var $model         app\models\Job
 *  @var $form          yii\bootstrap\ActiveForm
 *  @var $jobs          array
 *  @var $table_fields  array
 *  @var $snmp_types    array
 *  @var $request_types array
 *  @var $worker_var    array
 *  @var $static_var    array
 */
$action     = $this->context->action->id;
$snmp_view  = (strcasecmp($model->worker->get, 'snmp') == 0) ? true : false;
$modal_name = Yii::t('network', 'Add job to {0}', $model->worker->name);

if ($action == 'ajax-edit-job') {
    $modal_name = Yii::t('network', 'Edit job of {0}', $model->worker->name);
}
?>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header modal-default">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            <h4 class="modal-title">
                <i class="fa <?= ($action == 'ajax-add-worker') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $modal_name ?>
            </h4>
        </div>
        <?php $form = ActiveForm::begin(['id' => 'job_form', 'enableClientValidation' => false]); ?>
        <div class="modal-body">

            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <?= Html::beginTag('div', ['class' => 'col-md-' . (($action == 'ajax-edit-job') ? '10' : '5')]) ?>
                            <?php
                                echo $form->field($model, 'name')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'name')
                                ]);
                            ?>
                        <?= Html::endTag('div') ?>
                        <?php if($action == 'ajax-add-job'): ?>
                            <div class="col-md-5">
                                <?php
                                    echo $form->field($model, 'after_job')->dropDownList($jobs, [
                                        'prompt'           => '',
                                        'class'            => 'select2-clear-modal',
                                        'data-placeholder' => Yii::t('network', 'Choose job'),
                                    ]);
                                ?>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-2">
                            <?php
                                echo $form->field($model, 'enabled')->checkbox([
                                    'id'          => 'job_enable',
                                    'template'    => "{label}<br>{input}<br>{error}",
                                    'data-toggle' => 'toggle',
                                    'data-size'   => 'normal',
                                    'data-on'     => Yii::t('app', 'Yes'),
                                    'data-off'    => Yii::t('app', 'No')
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="<?= ($snmp_view) ? 'col-md-5' : 'col-md-6'?>">
                            <?php
                                echo $form->field($model, 'table_field')->dropDownList($table_fields, [
                                    'prompt'           => '',
                                    'class'            => 'select2-clear-modal',
                                    'data-placeholder' => Yii::t('network', 'Choose table field'),
                                ]);
                            ?>
                        </div>
                        <div class="<?= ($snmp_view) ? 'col-md-4' : 'col-md-6'?>">
                            <?php
                                echo $form->field($model, 'timeout')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'timeout'),
                                    'readonly'     => ($model->snmp_request_type == 'get' && $snmp_view) ? true : false
                                ]);
                            ?>
                        </div>
                        <?php if ($snmp_view): ?>
                            <div class="col-md-3">
                                <?php
                                    echo $form->field($model, 'snmp_request_type')->widget(ToggleButtonGroup::class, [
                                        'type'         => 'radio',
                                        'labelOptions' => ['class' => 'btn-primary', 'style' => 'width:57px'],
                                        'items'        => $request_types
                                    ]);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                $label = ($snmp_view) ? Yii::t('network', 'SNMP OID') : Yii::t('network', 'Command');
                                echo $form->field($model, 'command_value')->textInput([
                                    'id'           => 'command_value',
                                    'class'        => 'form-control',
                                    'placeholder'  => Yii::t('app', 'Enter') . ' ' . mb_strtolower($label)
                                ])->label($label);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'command_var')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'command_var')
                                ]);
                            ?>
                        </div>
                    </div>

                    <?php if (!$snmp_view): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                    $info = Html::tag('span', ' <i class="fa fa-question-circle-o"></i>', [
                                        'data-toggle'    => 'tooltip',
                                        'data-placement' => 'right',
                                        'title'          => Yii::t('network', 'Command completion wait prompt'),
                                        'style'          => ['color' => '#3c8dbc', 'cursor' => 'pointer']
                                    ]);
                                    echo $form->field($model, 'cli_custom_prompt')->textInput([
                                        'class'        => 'form-control',
                                        'placeholder'  => FormHelper::label($model, 'cli_custom_prompt')
                                    ])->label($model->getAttributeLabel('cli_custom_prompt') . $info);
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($snmp_view): ?>
                        <div class="row">
                            <div class="col-md-4">
                                <?php
                                    /** Hidden field value will be submited if dropdownlist is disabled */
                                    echo Html::activeHiddenInput($model, 'snmp_set_value_type', ['id' => 'snmp_hidden_type']);
                                    echo $form->field($model, 'snmp_set_value_type')->dropDownList($snmp_types, [
                                        'prompt'           => '',
                                        'class'            => 'select2-modal',
                                        'data-placeholder' => Yii::t('network', 'Choose type'),
                                        'disabled'         => ($model->snmp_request_type == 'get') ? true : false
                                    ]);
                                ?>
                            </div>
                            <div class="col-md-8">
                                <?php
                                    echo $form->field($model, 'snmp_set_value')->textInput([
                                        'class'        => 'form-control',
                                        'placeholder'  => FormHelper::label($model, 'snmp_set_value', false),
                                        'readonly'     => ($model->snmp_request_type == 'get') ? true : false
                                    ]);
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                $height = ($snmp_view) ? '65px' : '72px';
                                echo $form->field($model, 'description')->textarea([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'description'),
                                    'style'        => ['resize' => 'vertical', 'height' => $height]
                                ]);
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Variable helper -->
                <div class="col-md-4">
                    <div class="tabbable-panel">
                        <div class="tabbable-line">
                            <ul class="nav nav-tabs nav-justified">
                                <li class="<?= (!empty($worker_var)) ? "active" : "" ?>">
                                    <a href="#worker_var_tab" class="<?= (empty($worker_var)) ? "disabled" : "" ?>" data-toggle="tab">
                                        <?= Yii::t('network', 'Worker variables') ?>
                                    </a>
                                </li>
                                <li class="<?= (empty($worker_var)) ? "active" : "" ?>">
                                    <a href="#system_var_tab" data-toggle="tab"><?= Yii::t('network', 'System variables') ?></a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane <?= (!empty($worker_var)) ? "active" : "" ?>" id="worker_var_tab">
                                    <table class="table table-no-outer table-hover ellipsis" style="margin-bottom: 0;">
                                        <?php foreach ($worker_var as $var): ?>
                                            <tr class="text-center">
                                                <td class="add_var hide-overflow">
                                                    <span class="text-primary"><?= $var['command_var'] ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                                <div class="tab-pane <?= (empty($worker_var)) ? "active" : "" ?>" id="system_var_tab">
                                    <table id="dynamic_var_table" class="table dynamic-var table-hover">
                                        <thead>
                                            <tr class="warning">
                                                <th><?= Yii::t('network', 'Dynamic variables') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (Y::param('system_variables') as $var): ?>
                                                <tr class="text-center">
                                                    <td class="add_var">
                                                        <span class="text-primary"><?= $var ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php if (!empty($static_var)): ?>
                                        <table id="static_var_table" class="table static-var table-hover ellipsis">
                                            <thead>
                                                <tr class="warning">
                                                    <th><?= Yii::t('network', 'Static variables') ?></th>
                                                </tr>
                                                <?php $scroll_limit = ($snmp_view) ? 7 : 5; ?>
                                                <?php if (count($static_var) > $scroll_limit): ?>
                                                    <tr>
                                                        <td>
                                                            <?php
                                                                echo Html::textInput('dt-search', '', [
                                                                    'id'          => 'dt_search',
                                                                    'class'       => 'form-control input-sm',
                                                                    'placeholder' => Yii::t('network', 'Enter variables name'),
                                                                    'style'       => ['width' => '100%']
                                                                ]);
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($static_var as $var): ?>
                                                    <tr class="text-center">
                                                        <td class="add_var hide-overflow">
                                                            <span class="text-primary"><?= $var ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
            <?php
                echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save changes'), [
                    'id'         => 'save',
                    'class'      => 'btn btn-primary ladda-button',
                    'data-style' => 'zoom-in'
                ]);
            ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
