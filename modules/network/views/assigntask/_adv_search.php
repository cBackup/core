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
 * @var $this          yii\web\View
 * @var $model         app\models\search\CustomNodeSearch
 * @var $form          yii\widgets\ActiveForm
 * @var $devices_list  array
 * @var $networks_list array
 */
?>

<div class="node-search-form">
    <div class="row">
        <div class="col-md-12">
            <?php
                $form = ActiveForm::begin(['action' => ['adv-node-assign'], 'method' => 'get', 'enableClientValidation' => false]);
                echo Html::hiddenInput('task_name', $model->task_name)
            ?>
            <div class="search-body">
                <div class="row">
                    <div class="col-md-4">
                        <?php
                            echo $form->field($model, 'ip')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => FormHelper::label($model, 'ip')
                            ]);
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                            echo $form->field($model, 'network_id')->dropDownList($networks_list, [
                                'prompt'           => '',
                                'class'            => 'select2-min',
                                'data-placeholder' => Yii::t('network', 'Choose network')
                            ]);
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                            echo $form->field($model, 'device_id')->dropDownList($devices_list, [
                                'prompt'           => '',
                                'class'            => 'select2-clear',
                                'data-placeholder' => Yii::t('network', 'Choose device model')
                            ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <?php
                            echo $form->field($model, 'hostname')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => FormHelper::label($model, 'hostname')
                            ]);
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                            echo $form->field($model, 'location')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => FormHelper::label($model, 'location')
                            ]);
                        ?>
                    </div>
                    <div class="col-md-2">
                        <?php
                            echo $form->field($model, 'page_size')->dropDownList(\Y::param('page_size'), ['class' => 'select2']);
                        ?>
                    </div>
                    <div class="col-md-2">
                        <div class="pull-right" style="padding-top: 30px">
                            <?= Html::submitButton(Yii::t('app', 'Search'), ['id' => 'spin_btn', 'class' => 'btn bg-light-blue ladda-button', 'data-style' => 'zoom-in']) ?>
                            <?= Html::a(Yii::t('app', 'Reset'), ['/network/assigntask/adv-node-assign', 'task_name' => $model->task_name], ['class' => 'btn btn-default']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
