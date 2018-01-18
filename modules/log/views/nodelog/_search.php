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
 * @var $this        yii\web\View
 * @var $model       app\models\LogNode
 * @var $form        yii\widgets\ActiveForm
 * @var $users       array
 * @var $severities  array
 * @var $actions     array
 */
?>

<div class="node-log-search-form">
    <div class="row">
        <div class="col-md-12">
            <?php $form = ActiveForm::begin(['action' => ['list'], 'method' => 'get', 'enableClientValidation' => false]); ?>
                <div class="search-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php
                                echo $form->field($model, 'date_from', [
                                    'inputTemplate' =>
                                        '<div class="input-group">
                                            {input}
                                            <div class="input-group-btn">
                                                <a href="javascript:;" id="systemFrom_clear" class="btn btn-default date-clear" title="'.Yii::t('app', 'Clear date').'">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            </div>
                                        </div>'
                                ])->textInput([
                                    'id'          => 'systemFrom_date',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('log', 'Pick date/time'),
                                    'readonly'    => true,
                                    'style'       => 'background-color: white; cursor: pointer;'
                                ]);
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                                echo $form->field($model, 'date_to', [
                                    'inputTemplate' =>
                                        '<div class="input-group">
                                            {input}
                                            <div class="input-group-btn">
                                                <a href="javascript:;" id="systemTo_clear" class="btn btn-default date-clear" title="'.Yii::t('app', 'Clear date').'">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            </div>
                                        </div>'
                                ])->textInput([
                                    'id'          => 'systemTo_date',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('log', 'Pick date/time'),
                                    'readonly'    => true,
                                    'style'       => 'background-color: white; cursor: pointer;'
                                ]);
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                                echo $form->field($model, 'userid')->dropDownList($users, [
                                    'prompt' => Yii::t('log', 'All users'),
                                    'class'  => 'select2-search',
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'severity')->dropDownList($severities, [
                                    'prompt' => Yii::t('log', 'All levels'),
                                    'class'  => 'select2',
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'action')->dropDownList($actions, [
                                    'prompt' => Yii::t('log', 'All actions'),
                                    'class'  => 'select2',
                                ]);
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php
                                echo $form->field($model, 'node_params')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('network', 'Enter node hostname or IP')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-1">
                            <?php
                                echo $form->field($model, 'page_size')->dropDownList(\Y::param('page_size'), ['class' => 'select2']);
                            ?>
                        </div>
                        <div class="col-md-2">
                            <div class="pull-right" style="padding-top: 30px">
                                <?= Html::submitButton(Yii::t('app', 'Search'), ['id' => 'spin_btn', 'class' => 'btn bg-light-blue ladda-button', 'data-style' => 'zoom-in']) ?>
                                <?= Html::a(Yii::t('app', 'Reset'), yii\helpers\Url::to(['/log/nodelog/list']), ['class' => 'btn btn-default']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
