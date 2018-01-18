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
 * @var $model         app\models\Device
 * @var $form          yii\bootstrap\ActiveForm
 * @var $vendors       array
 * @var $templates     array
 */
?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header modal-default">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            <h4 class="modal-title">
                <i class="fa fa-plus"></i>
                <?= Yii::t('network', 'Add device') ?>
            </h4>
        </div>
        <?php $form = ActiveForm::begin(['id' => 'device_form', 'enableClientValidation' => false]); ?>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <?php
                        echo $form->field($model, 'vendor')->dropDownList($vendors, [
                            'prompt'            => '',
                            'class'             => 'select2',
                            'data-placeholder'  => Yii::t('network', 'Choose vendor')
                        ]);
                    ?>
                </div>
                <div class="col-md-12">
                    <?php
                        echo $form->field($model, 'model')->textInput([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'model'),
                        ]);
                    ?>
                </div>
                <div class="col-md-12">
                    <?php
                        echo $form->field($model, 'auth_template_name')->dropDownList($templates, [
                            'class'            => 'select2',
                            'prompt'           => '',
                            'data-placeholder' => Yii::t('network', 'Choose auth template'),
                        ]);
                    ?>
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
