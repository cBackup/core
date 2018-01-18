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

/**
 * @var $this         yii\web\View
 * @var $model        app\models\search\OutCustomSearch
 * @var $form         yii\widgets\ActiveForm
 * @var $table        string
 */
?>

<div class="out-custom-search-form">
    <div class="row">
        <div class="col-md-12">
            <?php $form = ActiveForm::begin(['action' => ['ajax-render-grid', 'table' => $table], 'method' => 'get', 'enableClientValidation' => false]); ?>
                <div class="search-body">
                    <div class="row">
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'date_from', [
                                    'inputTemplate' =>
                                        '<div class="input-group">
                                            {input}
                                            <div class="input-group-btn">
                                                <a href="javascript:void(0);" id="outFrom_clear" class="btn btn-default date-clear" title="'.Yii::t('app', 'Clear date').'">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            </div>
                                        </div>'
                                ])->textInput([
                                    'id'          => 'outFrom_date',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('log', 'Pick date/time'),
                                    'readonly'    => true,
                                    'style'       => 'background-color: white; cursor: pointer;'
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'date_to', [
                                    'inputTemplate' =>
                                        '<div class="input-group">
                                            {input}
                                            <div class="input-group-btn">
                                                <a href="javascript:void(0);" id="outTo_clear" class="btn btn-default date-clear" title="'.Yii::t('app', 'Clear date').'">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            </div>
                                        </div>'
                                ])->textInput([
                                    'id'          => 'outTo_date',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('log', 'Pick date/time'),
                                    'readonly'    => true,
                                    'style'       => 'background-color: white; cursor: pointer;'
                                ]);
                            ?>
                        </div>
                        <div class="col-md-2">
                            <?php
                                echo $form->field($model, 'node_id')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('app', 'Enter') . ' ' . Yii::t('app', 'Node ID')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                                echo $form->field($model, 'node_search')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('network', 'Enter node hostname or IP')
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
                        <?php $custom_field_count = count($model->custom_fields); ?>
                        <?php foreach ($model->custom_fields as $field): ?>
                            <div class="<?= ($custom_field_count > 1 ) ? "col-md-3" : "col-md-6"?>">
                                <?php
                                    echo $form->field($model, $field->name)->textInput([
                                        'class'       => 'form-control',
                                        'placeholder' => Yii::t('app', 'Enter') . ' ' . $field->name
                                    ]);
                                ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-md-3 pull-right" style="margin-top: 30px; margin-bottom: 10px">
                            <div class="pull-right">
                                <?php
                                    echo Html::submitButton(Yii::t('app', 'Search'), [
                                        'id'         => 'spin_btn',
                                        'class'      => 'btn bg-light-blue ladda-button margin-r-5',
                                        'data-style' => 'zoom-in'
                                    ]);
                                    echo Html::a(Yii::t('app', 'Reset'), 'javascript:void(0)', [
                                        'id'            => $table,
                                        'class'         => 'btn btn-default load-grid-view',
                                        'data-ajax-url' => yii\helpers\Url::to(['ajax-render-grid', 'table' => $table])
                                    ]);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
