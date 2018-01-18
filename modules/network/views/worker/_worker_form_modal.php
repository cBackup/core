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

/** @noinspection PhpUndefinedFieldInspection
 *  @var $this          yii\web\View
 *  @var $model         app\models\Worker
 *  @var $form          yii\bootstrap\ActiveForm
 *  @var $protocols     array
 *  @var $tasks         array
 */
$action     = $this->context->action->id;
$modal_name = (!empty($model->task_name)) ? Yii::t('network', 'Add worker to {0}', $model->task_name) : Yii::t('network', 'Add worker');

if ($action == 'ajax-edit-worker') {
    $modal_name = Yii::t('network', 'Edit worker of {0}', $model->task_name);
}
?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header modal-default">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            <h4 class="modal-title">
                <i class="fa <?= ($action == 'ajax-add-worker') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $modal_name ?>
            </h4>
        </div>
        <?php
            $form = ActiveForm::begin([
                'id'                     => 'worker_form',
                'layout'                 => 'horizontal',
                'enableClientValidation' => false,
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label'   => 'col-sm-2',
                        'wrapper' => 'col-sm-10'
                    ],
                ],
            ]);
        ?>
            <div class="modal-body">
                <?php
                    if (empty($model->task_name)) {
                        echo $form->field($model, 'task_name')->dropDownList($tasks, [
                            'prompt'           => '',
                            'class'            => 'select2-modal-simple',
                            'data-placeholder' => Yii::t('network', 'Choose task')
                        ])->label(Yii::t('network', 'Task'));
                    }
                    echo $form->field($model, 'name')->textInput([
                        'class'        => 'form-control',
                        'placeholder'  => FormHelper::label($model, 'name'),
                    ]);
                    echo $form->field($model, 'get')->dropDownList($protocols, [
                        'prompt'           => '',
                        'class'            => 'select2-modal-simple',
                        'data-placeholder' => Yii::t('network', 'Choose protocol')
                    ]);
                    echo $form->field($model, 'description')->textarea([
                        'class'        => 'form-control',
                        'placeholder'  => FormHelper::label($model, 'description'),
                        'style'        => 'resize: vertical'
                    ]);
                ?>
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
