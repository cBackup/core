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
use yii\helpers\Url;

/**
 * @var $this       yii\web\View
 * @var $model      app\models\DeviceAttributes
 * @var $form       yii\bootstrap\ActiveForm
 * @var $devices    array
 */
app\assets\Select2Asset::register($this);
app\assets\i18nextAsset::register($this);
app\assets\LaddaAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action    = $this->context->action->id;
$page_name = ($action == 'add-unknown-device') ? Yii::t('network', 'Recognize device') : Yii::t('network', 'Change device');

if ($action == 'add-unknown-device') {
    $this->title = Yii::t('app', 'Unknown devices');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory')];
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Devices'), 'url' => ['device/list']];
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Unknown devices'), 'url' => ['device/unknown-list']];
} else {
    $this->title = Yii::t('app', 'Device attributes');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory')];
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Devices'), 'url' => ['device/list']];
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Device attributes')];
}

$this->params['breadcrumbs'][] = $page_name;

$this->registerJs(
    /** @lang JavaScript */
    "
        /** Init select2 */
        $('.select2').select2({
            width: '100%'
        });
        
        /** Modal auth template add form AJAX submit handler */
        $(document).on('submit', '#device_form', function () {
            modalFormHandler($(this), 'form_modal', 'save');
            return false;
        });
        
        /** Modal loaded event handler */
        $(document).on('loaded.bs.modal', '.modal', function () {
            /** Init select2 */
            $('.select2').select2({
                width: '100%'
            });
        });

        /** Modal hidden event handler */
        $(document).on('hidden.bs.modal', '.modal', function () {
            
            var toast = $('#toast-container');
            
            /** Reload select2 after record was added */
            if (toast.find('.toast-success, .toast-warning').is(':visible')) {
                var update_url = $('#devices_list').data('update-url');
                updateSelect2(update_url, 'devices_list');
            }
            
            /** Remove errors after modal close */
            toast.find('.toast-error').fadeOut(1000, function() { $(this).remove(); });
        
        });
        
        
    "
);
?>

<div class="row">
    <div class="col-md-9 col-md-offset-2">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add-unknown-device') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php
                /** @noinspection MissedFieldInspection */
                $form = ActiveForm::begin([
                    'id'                     => 'unknown_form',
                    'layout'                 => 'horizontal',
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'errorOptions' => ['encode' => false],
                        'horizontalCssClasses' => [
                            'label'   => 'col-sm-2',
                            'wrapper' => 'col-sm-10'
                        ],
                    ],
                ]);
                echo Html::activeHiddenInput($model, 'unkn_id');
            ?>
            <div class="box-body">
                <?php
                    echo $form->field($model, 'device_id', [
                        'inputTemplate' =>
                            '<div class="input-group">
                                {input}
                                <div class="input-group-btn">
                                    '.Html::a('<i class="fa fa-plus-square-o"></i>', ['/network/device/ajax-add-device'], [
                                        'class'         => 'btn btn-default',
                                        'title'         => Yii::t('network', 'Add auth template'),
                                        'data-toggle'   => 'modal',
                                        'data-target'   => '#form_modal',
                                        'data-backdrop' => 'static',
                                    ]).'
                                </div>
                            </div>'
                    ])->dropDownList($devices, [
                        'id'               => 'devices_list',
                        'class'            => 'select2',
                        'prompt'           => '',
                        'data-placeholder' => Yii::t('network', 'Choose device'),
                        'data-update-url'  => Url::to(['device/ajax-update-devices'])
                    ]);
                    echo $form->field($model, 'sysobject_id')->textInput([
                        'class'       => 'form-control',
                        'placeholder' => Yii::t('yii', '(not set)'),
                        'readonly'    => true
                    ]);
                    echo $form->field($model, 'hw')->textInput([
                        'class'       => 'form-control',
                        'placeholder' => Yii::t('yii', '(not set)'),
                        'readonly'    => true
                    ]);
                    echo $form->field($model, 'sys_description')->textarea([
                        'class'       => 'form-control',
                        'placeholder' => Yii::t('yii', '(not set)'),
                        'style'       => 'resize: vertical',
                        'readonly'    => true
                    ]);
                ?>
            </div>
            <div class="box-footer text-right">
                <?php
                    if ($action == 'change-device') {
                        echo Html::a(Yii::t('app', 'Delete'), ['device/delete-attributes', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger pull-left',
                            'data' => [
                                'confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->sysobject_id),
                                'method'  => 'post',
                            ],
                        ]);
                    }
                ?>
                <?= Html::a(Yii::t('app', 'Cancel'), ($model->isNewRecord) ? ['device/unknown-list'] : ['device/list'], ['class' => 'btn btn-sm btn-default']) ?>
                <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Form modal -->
<div id="form_modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= Yii::t('app', 'Wait...') ?></h4>
            </div>
            <div class="modal-body">
                <span style="margin-left: 24%;"><?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
