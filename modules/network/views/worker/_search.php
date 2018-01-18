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
use app\helpers\FormHelper;

/**
 * @var $this         yii\web\View
 * @var $model        app\models\search\WorkerSearch
 * @var $form         yii\widgets\ActiveForm
 * @var $protocols    array
 * @var $tasks        array
 * @var $table_fields array
 */
?>

<!-- The Right Sidebar -->
<aside class="control-sidebar sidebar-custom control-sidebar-dark">
    <div class="tab-content">
        <div class="worker-job-search-form">
            <h4 class="control-sidebar-heading"><?= Yii::t('network', 'Filter records')?></h4>
            <?php $form = ActiveForm::begin(['action' => ['list'], 'method' => 'get', 'enableClientValidation' => false]); ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            echo $form->field($model, 'worker_id')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => FormHelper::label($model, 'worker_id')
                            ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            echo $form->field($model, 'name')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => Yii::t('network', 'Enter worker name'),
                            ])->label(Yii::t('network', 'Worker name'));
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            echo $form->field($model, 'task_name')->dropDownList($tasks, [
                                'prompt' => Yii::t('network', 'All tasks'),
                                'class'  => 'select2'
                            ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            echo $form->field($model, 'get')->dropDownList($protocols, [
                                'prompt' => Yii::t('network', 'All protocols'),
                                'class'  => 'select2'
                            ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            echo $form->field($model, 'job_name')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => FormHelper::label($model, 'job_name')
                            ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            echo $form->field($model, 'command_value')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => FormHelper::label($model, 'command_value')
                            ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            echo $form->field($model, 'table_field')->dropDownList($table_fields, [
                                'prompt' => Yii::t('network', 'All table fields'),
                                'class'  => 'select2'
                            ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            $status = ['0' => Yii::t('app', 'No'), '1' => Yii::t('app', 'Yes')];
                            echo $form->field($model, 'enabled')->dropDownList($status, [
                                'prompt' => Yii::t('network', 'All statuses'),
                                'class'  => 'select2'
                            ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            $page_size = [$model->page_size => $model->page_size] + \Y::param('page_size');
                            echo $form->field($model, 'page_size')->dropDownList($page_size, ['class' => 'select2']);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style="padding-top: 10px">
                        <?php
                            echo Html::a(Yii::t('app', 'Reset'), yii\helpers\Url::to(['/network/worker/list']), ['class' => 'btn btn-default']);
                            echo Html::submitButton(Yii::t('app', 'Search'), [
                                'id'         => 'spin_btn',
                                'class'      => 'btn bg-light-blue ladda-button pull-right',
                                'data-style' => 'zoom-in'
                            ]);
                        ?>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</aside>
<!-- The sidebar's background -->
<div class="control-sidebar-bg"></div>



