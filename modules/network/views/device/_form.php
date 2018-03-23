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
 * @var $this          yii\web\View
 * @var $model         app\models\Device
 * @var $form          yii\widgets\ActiveForm
 * @var $vendors       array
 * @var $templates     array
 */
app\assets\Select2Asset::register($this);
app\assets\i18nextAsset::register($this);
app\assets\LaddaAsset::register($this);
app\assets\ScrollingTabsAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action      = $this->context->action->id;
$page_name   = ($action == 'add') ? Yii::t('network', 'Add device') : Yii::t('network', 'Edit device');

$this->title = Yii::t('app', 'Devices');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Devices'), 'url' => ['/network/device/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

// Because firefox has 9 years of open bug with unsupported 'background-attachment: local' for textareas
$this->registerJsFile('@web/js/plugins/autosize.min.js', ['depends' => \app\assets\AlphaAsset::class]);

$this->registerJs(/** @lang JavaScript */
    "
        /** Init select2 */
        $('.select2').select2({
            width: '100%'
        });
        
        /** Get auth template on select change via Ajax */
        $('#auth_template_list').change(function() {
            
            var loading    = $('.loading');
            var auth_name = $(this).val();
            var ajax_url  = $(this).data('url') + '&name=' + auth_name;
            
            if (auth_name.length > 0) {
                //noinspection JSUnusedGlobalSymbols
                $.ajax({
                    type: 'POST',
                    url: ajax_url,
                    beforeSend: function() {
                        $('#auth_template_preview, #preview_placeholder').hide();
                        loading.show();
                    },
                    success: function (data) {
                        if (isJson(data)) {
                            $('#auth_textarea').val($.parseJSON(data));
                            $('#auth_template_preview').show();
                            loading.hide();
                        }
                    },
                    error: function (data) {
                        $('#preview_placeholder').html('' +
                            '<div class=\"callout callout-danger\" style=\"margin-bottom: 0;\">' +
                                data.responseText + '' +
                            '</div>'
                        ).show();
                        loading.hide();
                    }
                });
            }
            
        }).change();
        
        /** Modal auth template add form AJAX submit handler */
        $(document).on('submit', '#deviceauthtemplate_form', function () {
            modalFormHandler($(this), 'form_modal', 'save');
            return false;
        });
        
        /** Modal hidden event handler */
        $(document).on('hidden.bs.modal', '.modal', function () {
            
            var toast = $('#toast-container');
            
            /** Reload select2 after record was added */
            if (toast.find('.toast-success, .toast-warning').is(':visible')) {
                var update_url = $('#auth_template_list').data('update-url');
                updateSelect2(update_url, 'auth_template_list');
            }
            
            /** Remove errors after modal close */
            toast.find('.toast-error').fadeOut(1000, function() { $(this).remove(); });
        
        });
        
        /** Modal shown event handler */
        $(document).on('shown.bs.modal', '.modal', function () {
            
            var tabs = $('.tabs-scroll');
            
            /** Destroy plugin to prevet tabs duplicating */
            tabs.scrollingTabs('destroy');
            
            /** Init tab scroll */
            tabs.scrollingTabs({
                disableScrollArrowsOnFullyScrolled: true,
                scrollToTabEdge: true
            }).on('ready.scrtabs', function() {
                $('.tabs-scroll').find('li > a').css({color: '#444', 'padding-top': '15px'})
            });
            
            /** Auto resize textarea */
            autosize($('textarea'));
            
        });
        
    "
);
?>

<div class="row">
    <div class="col-md-8">
        <?php
        /** @noinspection MissedFieldInspection */
        $form = ActiveForm::begin([
                'id'                     => 'device_form',
                'layout'                 => 'horizontal',
                'enableClientValidation' => false,
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label'   => 'col-sm-3',
                        'wrapper' => 'col-sm-9'
                    ],
                ],
            ]);
        ?>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <div class="box-body">
                <?php
                    echo $form->field($model, 'vendor')->dropDownList($vendors, [
                        'prompt'            => '',
                        'class'             => 'select2',
                        'data-placeholder'  => Yii::t('network', 'Choose vendor')
                    ]);
                    echo $form->field($model, 'model')->textInput([
                        'class'        => 'form-control',
                        'placeholder'  => FormHelper::label($model, 'model'),
                    ]);
                    echo $form->field($model, 'auth_template_name', [
                        'inputTemplate' =>
                            '<div class="input-group">
                                {input}
                                <div class="input-group-btn">
                                    '.Html::a('<i class="fa fa-plus-square-o"></i>', ['/network/authtemplate/ajax-add-template'], [
                                        'class'         => 'btn btn-default',
                                        'title'         => Yii::t('network', 'Add auth template'),
                                        'data-toggle'   => 'modal',
                                        'data-target'   => '#form_modal',
                                        'data-backdrop' => 'static',
                                    ]).'
                                </div>
                            </div>'
                    ])->dropDownList($templates, [
                        'id'               => 'auth_template_list',
                        'class'            => 'select2',
                        'prompt'           => '',
                        'data-placeholder' => Yii::t('network', 'Choose auth template'),
                        'data-url'         => Url::to(['ajax-auth-template-preview']), // Dynamic url. Url changes when template is selected
                        'data-update-url'  => Url::to(['ajax-update-templates'])
                    ]);
                ?>
            </div>
            <div class="box-footer text-right">
                <?php
                    echo $model->isNewRecord ? '' : Html::a(Yii::t('app', 'Delete'), ['/network/device/delete', 'id' => $model->id], [
                        'class' => 'btn btn-sm btn-danger pull-left',
                        'data'  => [
                            'method'    => 'post',
                            'confirm'   => Yii::t('network', 'Are you sure you want to delete device {0} {1}?', [$model->vendor, $model->model]),
                            'params'    => ['id' => $model->id],
                        ]
                    ]);
                    /** @noinspection PhpUndefinedFieldInspection */
                    echo Html::a(Yii::t('app', 'Cancel'), [$this->context->defaultAction], ['class' => 'btn btn-sm btn-default']);
                    echo '&nbsp;';
                    echo Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']);
                ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="col-md-4">
        <?php if (!is_null(\Y::param('java_factory.' . $model->vendor)) && in_array($model->model, \Y::param('java_factory.' . $model->vendor))): ?>
            <div class="box box-warning">
                <div class="box-header with-border">
                    <i class="fa fa-warning"></i>
                    <h3 class="box-title box-title-align"><?= Yii::t('app', 'Warning') ?></h3>
                </div>
                <div class="box-body">
                    <p><?= Yii::t('network', 'The name of selected model is used for the specific implementation of the protocols. By changing vendor name, the standard version will be used.')?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-eye"></i><h3 class="box-title"><?= Yii::t('network', 'Auth template preview')?></h3>
            </div>
            <div class="box-body">
                <div class="loading" style="display: none; margin: 10px 0 10px 0;">
                    <?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?>
                </div>
                <div id="auth_template_preview" style="display: none;">
                    <div class="col-sm-12">
                        <div class="pull-left margin-r-5" style="margin-bottom: 1px">
                            <div class="auth_sequence_helper pull-left" style="background-color: #dcf1d7;"></div>
                            <?= Yii::t('network', 'Prompt (expect)') ?>
                        </div>
                        <div class="pull-left" style="margin-bottom: 5px">
                            <div class="auth_sequence_helper pull-left" style="background-color: #ffffff;"></div>
                            <?= Yii::t('network', 'Input data') ?>
                        </div>
                        <?php
                            echo Html::textarea('', '', [
                                'id'       => 'auth_textarea',
                                'class'    => 'form-control auth_sequence',
                                'readonly' => true,
                                'style'    => 'resize: none'
                            ])
                        ?>
                    </div>
                </div>
                <div id="preview_placeholder">
                    <div class="callout callout-info" style="margin-bottom: 0;">
                        <p><?= Yii::t('network', 'Please select auth template') ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Form modal -->
<div id="form_modal" class="modal fade">
    <div class="modal-dialog modal-xl">
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
