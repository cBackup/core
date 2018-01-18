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
use yii\bootstrap\ActiveForm;
use app\helpers\FormHelper;

/**
 * @var $this         yii\web\View
 * @var $model        app\models\Node
 * @var $form         yii\widgets\ActiveForm
 * @var $networks     array
 * @var $credentials  array
 * @var $devices      array
 * @var $auth_list    array
 */
?>

<div class="node-search-form">
    <div class="row">
        <div class="col-md-12">
            <?php $form = ActiveForm::begin(['action' => ['list'], 'method' => 'get', 'enableClientValidation' => false]); ?>
                <?php echo Html::hiddenInput('NodeSearch[adv_search]'); ?>
                <div class="search-body">
                    <div class="row">
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'ip')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'ip')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'network_id')->dropDownList($networks, [
                                    'class'            => 'select2-search',
                                    'prompt'           => '',
                                    'data-placeholder' => Yii::t('node', 'All networks'),

                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'credential_id')->dropDownList($credentials, [
                                    'class'            => 'select2-search',
                                    'prompt'           => '',
                                    'data-placeholder' => Yii::t('node', 'All credentials'),
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'device_id')->dropDownList($devices, [
                                    'class'            => 'select2-search',
                                    'prompt'           => '',
                                    'data-placeholder' => Yii::t('node', 'All devices'),
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'auth_template_name')->dropDownList($auth_list, [
                                    'class'            => 'select2-search',
                                    'prompt'           => '',
                                    'data-placeholder' => Yii::t('node', 'All auth templates'),
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'mac')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'mac')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'hostname')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'hostname')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'serial')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'serial')
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'prepend_location')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'prepend_location')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'location')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'location')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                            echo $form->field($model, 'contact')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => FormHelper::label($model, 'contact')
                            ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                            echo $form->field($model, 'sys_description')->textInput([
                                'class'       => 'form-control',
                                'placeholder' => FormHelper::label($model, 'sys_description')
                            ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'page_size')->dropDownList(\Y::param('page_size'), ['class' => 'select2']);
                            ?>
                        </div>
                        <div class="col-md-5">
                            <?php
                                /** Set initial value */
                                $model->manual = '';

                                /** Render button group */
                                $items = [
                                    0  => Yii::t('node', 'Discovery'),
                                    1  => Yii::t('node', 'Manually'),
                                    '' => Yii::t('node', 'Any')
                                ];

                                echo $form->field($model, 'manual', [
                                    'template' => "{label}<br>{input}<br>{error}"
                                ])->radioList($items, [
                                    'class'       => 'btn-group',
                                    'data-toggle' => 'buttons',
                                    'item'        => function(/** @noinspection PhpUnusedParameterInspection */$index, $label, $name, $checked, $value) {
                                        return Html::tag('label',
                                            Html::radio($name, $checked, ['value' => $value, 'autocomplete' => 'off']) . $label, [
                                                'class' => 'btn btn-primary ' . ($checked ? 'active' : '')
                                            ]
                                        );
                                    }
                                ])->label(Yii::t('node', 'Node addition method'));
                            ?>
                        </div>
                        <div class="col-md-2 pull-right">
                            <div class="pull-right" style="padding-top: 29px">
                                <?= Html::submitButton(Yii::t('app', 'Search'), ['id' => 'spin_btn', 'class' => 'btn bg-light-blue ladda-button', 'data-style' => 'zoom-in']) ?>
                                <?= Html::a(Yii::t('app', 'Reset'), Url::to(['/node/list']), ['class' => 'btn btn-default']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
