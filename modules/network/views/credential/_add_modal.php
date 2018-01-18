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
 * @var $this       yii\web\View
 * @var $model      app\models\Credential
 * @var $form       yii\bootstrap\ActiveForm
 */
?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header modal-default">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            <h4 class="modal-title"><?= Yii::t('network', 'Add credential') ?></h4>
        </div>
            <?php $form = ActiveForm::begin(['id' => 'credential_form', 'enableClientValidation' => false]); ?>
                <div class="modal-body no-pad-top">

                    <h5 class="heading-hr text-bolder">
                        <i class="icon-user"></i> <?= Yii::t('network', 'General information') ?>
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'name')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'name')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'enable_password')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'enable_password', false)
                                ]);
                            ?>
                        </div>
                    </div>

                    <h5 class="heading-hr text-bolder">
                        <i class="icon-user"></i> <?= Yii::t('network', 'Telnet/SSH information') ?>
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'telnet_login')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'telnet_login')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'telnet_password')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'telnet_password')
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'ssh_login')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'ssh_login')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'ssh_password')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'ssh_password')
                                ]);
                            ?>
                        </div>
                    </div>

                    <h5 class="heading-hr text-bolder">
                        <i class="icon-user"></i> <?= Yii::t('network', 'SNMP information') ?>
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'snmp_read')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'snmp_read')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'snmp_set')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'snmp_set')
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'snmp_version')->dropDownList(\Y::param('snmp_versions'), [
                                    'prompt'           => '',
                                    'class'            => 'select2-modal',
                                    'data-placeholder' => Yii::t('network', 'Choose SNMP version'),
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'snmp_encryption')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'snmp_encryption')
                                ]);
                            ?>
                        </div>
                    </div>

                    <h5 class="heading-hr text-bolder">
                        <i class="icon-user"></i> <?= Yii::t('network', 'Port information') ?>
                    </h5>

                    <div class="row">
                        <div class="col-md-4">
                            <?php
                                echo $form->field($model, 'port_telnet')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'port_telnet')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                                echo $form->field($model, 'port_ssh')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'port_ssh')
                                ]);
                            ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                                echo $form->field($model, 'port_snmp')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'port_snmp')
                                ]);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
                    <?php
                        echo Html::submitButton(Yii::t('app', 'Save changes'), [
                            'id'         => 'save',
                            'class'      => 'btn btn-primary ladda-button',
                            'data-style' => 'zoom-in'
                        ]);
                    ?>
                </div>
            <?php ActiveForm::end(); ?>
    </div>
</div>
