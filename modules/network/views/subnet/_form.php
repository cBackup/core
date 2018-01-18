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
 * @var $this       yii\web\View
 * @var $model      app\models\Network
 * @var $form       yii\bootstrap\ActiveForm
 * @var $cred_list  array
 */
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);
app\assets\ToggleAsset::register($this);
app\assets\i18nextAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action = $this->context->action->id;

$page_name   = ($action == 'add') ? Yii::t('network', 'Add subnet') : Yii::t('network', 'Edit subnet');
$this->title = Yii::t('app', 'Networks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Subnets'), 'url' => ['/network/subnet/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

/** @noinspection JSUnusedLocalSymbols */
$this->registerJs(
    /** @lang JavaScript */
    "

        /** Default variable */
        var body  = $('body');

        /** Init select2 */
        $('.select2').select2({
            width: '100%'
        });
        
        /** Modal loaded event handler */
        body.on('loaded.bs.modal', '.modal', function () {
           
            /** Init select2 in modal window */
            $('.select2-modal').select2({
                minimumResultsForSearch: -1,
                width: '100%'
            });
            
            /** Clear all toasts after modal loaded */
            toastr.clear();
            
            /** Toggle */
            $('#credential-enable').bootstrapToggle();

        });
        
        /** Modal hidden event handler */
        body.on('hidden.bs.modal', '.modal', function (e) {

            var toast = $('#toast-container');
            
            /** Reload select2 after record was added */
            if (toast.find('.toast-success').is(':visible')) {
                var update_url = $('#credential_id').data('update-url');
                updateSelect2(update_url, 'credential_id');
            }
            
            /** Remove errors after modal close */
            toast.find('.toast-error, .toast-warning').fadeOut().remove();
            
        });
        
        /** Form AJAX submit handler */
        body.on('submit', '#credential_form', function () {
            modalFormHandler($(this), 'credential_form_modal', 'save');
            return false;
        });
        
    "
);

?>

<div class="row">
    <div class="col-md-9">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php
                $form = ActiveForm::begin([
                    'id'                     => 'subnet_form',
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
            ?>
                <div class="box-body">
                    <?php
                        echo $form->field($model, 'network')->textInput([
                            'class'        => 'form-control',
                            'placeholder'  => Yii::t('network', 'Subnet address')
                        ]);

                        echo $form->field($model, 'credential_id', [
                            'inputTemplate' =>
                                '
                                    <div class="input-group">
                                        {input}
                                        <div class="input-group-btn">
                                            '.Html::a('<i class="fa fa-plus-square-o"></i>', yii\helpers\Url::to(['/network/credential/ajax-add-credential']), [
                                                'class'         => 'btn btn-default',
                                                'title'         => Yii::t('network', 'Add credential'),
                                                'data-toggle'   => 'modal',
                                                'data-target'   => '#credential_form_modal',
                                                'data-backdrop' => 'static',
                                                'data-keyboard' => 'false'
                                            ]).'
                                        </div>
                                    </div>
                                '
                        ])->dropDownList($cred_list, [
                            'id'               => 'credential_id',
                            'class'            => 'select2',
                            'prompt'           => '',
                            'data-placeholder' => Yii::t('network', 'Choose credential'),
                            'data-update-url'  => Url::to(['/network/credential/ajax-get-credentials'])
                        ]);

                        echo $form->field($model, 'discoverable', [
                            'horizontalCheckboxTemplate' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                            'wrapperOptions' => ['class' => 'col-sm-10 col-md-offset-0']
                        ])->checkbox([
                            'data-toggle' => 'toggle',
                            'data-size'   => 'normal',
                            'data-on'     => Yii::t('app', 'Yes'),
                            'data-off'    => Yii::t('app', 'No')
                        ])->label(null, ['class' => 'control-label col-sm-2']);

                        echo $form->field($model, 'description')->textarea([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'description'),
                            'style'        => 'resize: vertical'
                        ]);
                    ?>
                </div>
                <div class="box-footer text-right">
                    <?php
                        if($action == 'edit') {
                            echo Html::a(Yii::t('app', 'Delete'), \yii\helpers\Url::to(['delete', 'id' => $model->id]), [
                                'class' => 'btn btn-sm btn-danger pull-left',
                                'data' => [
                                    'confirm' => Yii::t('network', 'Are you sure you want to delete subnet {0}?', $model->network),
                                    'method' => 'post',
                                    'params' => [
                                        'id' => $model->id,
                                    ],
                                ],
                            ]);
                        }
                    ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/network/subnet'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= Yii::t('app', 'Hints') ?>
                </h3>
            </div>
            <div class="box-body text-justify">
                <dl>
                    <dt><?= Yii::t('network', 'Subnet address') ?></dt>
                    <dd><?= Yii::t('network', 'Subnet address must be in CIDR format.<br>Example: 192.168.0.0/26') ?></dd>
                    <dt><?= Yii::t('network', 'Credential name') ?></dt>
                    <dd>
                        <?= Yii::t('network', 'Credentials for created network contain authentication data set for discovering and polling devices in it. Make sure, you have created required credentials set beforehand. If you have not done it, you can') ?>
                        <?=
                            Html::a(Yii::t('network', 'create credentials set'), yii\helpers\Url::to(['/network/credential/ajax-add-credential']), [
                                'data-toggle'   => 'modal',
                                'data-target'   => '#credential_form_modal',
                                'data-backdrop' => 'static',
                                'data-keyboard' => 'false'
                            ])
                        ?>.
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- credential modal -->
<div id="credential_form_modal" class="modal fade">
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
