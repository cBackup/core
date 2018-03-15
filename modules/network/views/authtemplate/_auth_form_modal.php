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
 * @var $model         app\models\DeviceAuthTemplate
 * @var $form          yii\bootstrap\ActiveForm
 * @var $vars          array
 */
?>

<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header modal-default">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            <h4 class="modal-title">
                <i class="fa fa-plus"></i>
                <?= Yii::t('network', 'Add auth template') ?>
            </h4>
        </div>
        <?php $form = ActiveForm::begin(['id' => 'deviceauthtemplate_form', 'enableClientValidation' => false]); ?>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-7">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'name')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'name'),
                                ]);
                            ?>
                        </div>
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'auth_sequence', [
                                    'inputTemplate' =>
                                        '<div class="col-md-12" style="padding-left: 0;"> 
                                            <div class="pull-left margin-r-5">
                                                <div class="auth_sequence_helper pull-left" style="background-color: #dcf1d7;"></div>
                                                '. Yii::t('network', 'Prompt (expect)') .'
                                            </div>
                                            <div class="pull-left" style="margin-bottom: 5px">
                                                <div class="auth_sequence_helper pull-left" style="background-color: #ffffff;"></div>
                                                '.Yii::t('network', 'Input data').'
                                            </div>
                                        </div>
                                        {input}',
                                ])->textarea([
                                    'class'       => 'form-control auth_sequence',
                                    'placeholder' => FormHelper::label($model, 'auth_sequence'),
                                    'readonly'    => false
                                ]);
                            ?>
                        </div>
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'description')->textarea([
                                    'class'       => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'description'),
                                    'style'       => 'resize: vertical'
                                ]);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <?= $this->render('_help_view', ['collapsed' => true, 'vars' => $vars]) ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
            <?php
                echo Html::submitButton(Yii::t('app', 'Create'), [
                    'id'         => 'save',
                    'class'      => 'btn btn-primary ladda-button',
                    'data-style' => 'zoom-in'
                ]);
            ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
