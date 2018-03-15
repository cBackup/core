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
use dosamigos\tinymce\TinyMce;
use yii\helpers\Url;

/**
 * @var $this        yii\web\View
 * @var $model       app\models\MailerEvents
 * @var $form        yii\bootstrap\ActiveForm
 * @var $templ_vars  array
 */
app\assets\Select2Asset::register($this);

$page_name   = Yii::t('app', 'Edit template');
$this->title = Yii::t('app', 'Mailer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mailer'), 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];


$this->registerJs(
    /** @lang JavaScript */
    "
        /** Init select2 inputs */
        $('.select2').select2({
            width: '100%',
            minimumResultsForSearch: -1
        });

        /** Show/Hide inputs and variables on change */
        $('#method_select').change(function() {
            var selected = $(this).find(':selected').text();
            
            /** Show/Hide inputs */
            $('.inputs').find('div.input').addClass('hide');
            $('.variables_' + selected).removeClass('hide');
            
            /** Show/hide varibales  */
            $('.output table tr.variables').hide();
            $('.output table tr.method_' + selected).show();
        }).change();
        
        
        /** Process user input */
        $('.input').on('keyup change', function() {
            
            var args   = $('.arg_' + this.id);
            var method = this.id.split('-')[0];
            var inputs = $('.variables_' + method).find('input, select');

            /** Enable/Disable add button based on filled input values */
            if (inputs.length > 0 ) {
                var filled_inputs = inputs.filter(function () {return !!this.value;}).length;
                if (inputs.length === filled_inputs) {
                    $('.' + method + '_variable_btn').removeClass('disabled');
                } else {
                    $('.' + method + '_variable_btn').addClass('disabled'); 
                }
            }
            
            /** Set user entered values */
            args.text($(this).val());
            if ($(this).val().length === 0) {
                args.text(args.data('default'));
            }
            
        });
        
        /** Add template variable to editor */
        $('.add_variable').click(function() {
            tinymce.activeEditor.execCommand('mceInsertContent', false, $.trim(this.text));
        });
    "
);
?>

<div class="row">
    <div class="col-md-9">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-pencil-square-o"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'event_template_form', 'enableClientValidation' => false]); ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'name')->textInput([
                                    'class'    => 'form-control',
                                    'readonly' => true
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'subject')->textInput([
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('mail', 'Enter subject')
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'template')->widget(TinyMce::class, [
                                    'options'  => ['rows' => 6],
                                    'language' => strtok(\Yii::$app->language, '-'),
                                    'clientOptions' => [
                                        'branding'          => false,
                                        'content_style'     => '.mce-content-body {font-size:12px;}',
                                        'forced_root_block' => '',
                                        'plugins'           => [
                                            "advlist autolink lists link charmap print preview anchor",
                                            "searchreplace visualblocks code fullscreen",
                                            "insertdatetime media table contextmenu paste"
                                        ],
                                        'toolbar' => "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify 
                                                     | bullist numlist outdent indent | link image | code | template_variables message_preview",
                                        'setup'   => new yii\web\JsExpression("
                                             function (editor) {
                                                editor.addButton('template_variables', {
                                                    icon: 'books',
                                                    tooltip: '". Yii::t('mail', 'List of template variables') ."',
                                                    onclick: function() {
                                                        $('#template_modal').modal('show');
                                                    }
                                                });
                                                editor.addButton('message_preview', {
                                                    icon: 'preview',
                                                    tooltip: '". Yii::t('mail', 'Message preview') ."',
                                                    onPostRender : function() {
                                                        this.disabled(".(empty($model->template) || empty($model->subject) ? true : false).")
                                                    },
                                                    onclick: function() {
                                                        launchModalWindow('#body_preview_modal', '".Url::to(["ajax-preview-message", "name" => $model->name])."')
                                                    }
                                                });
                                             }
                                        ")
                                    ]
                                ])->label(false);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::a(Yii::t('app', 'Cancel'), ['list'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Apply'), ['class' => 'btn btn-sm btn-primary', 'name' => 'saveandstay']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary', 'name' => 'saveandclose']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Help') ?></h3>
            </div>
            <div class="box-body text-justify">
                <p>
                    <?=
                        Yii::t('mail',
                            'You can use special tags in the mail body. Click on the {button} button on the right side of the editor toolbar to toggle popup with avaialable tags. By pressing button {preview} you can preview message variables without sending mail. There system-wide variables are available for reporting as well as data from particular nodes.', [
                                'button'  => '<button type="button" style="pointer-events: none"><i class="mce-ico mce-i-books"></i></button>',
                                'preview' => '<button type="button" style="pointer-events: none"><i class="mce-ico mce-i-preview"></i></button>'
                        ]);
                    ?>
                </p>
                <p>
                    <?= Yii::t('mail', 'All tags are self-explanatory. Feel free to ask for new variables to add in the reporting subsystem via feature request on our Github or website.') ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Template variables modal -->
<div id="template_modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= Yii::t('mail', 'List of template variables') ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">

                    <!-- Render inputs -->
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-12">
                                <?= Html::dropDownList('method_name', '', array_keys($templ_vars), ['id' => 'method_select', 'class' => 'select2']); ?>
                            </div>
                        </div>
                        <div class="row inputs">
                            <?php foreach ($templ_vars as $method => $params): ?>
                                <?php if (!empty($params['args'])) : ?>
                                    <div class="col-md-12 input hide variables_<?= $method ?>">
                                        <?php foreach ($params['args'] as $key => $value): ?>
                                            <div style="margin-top: 10px;">
                                                <?php
                                                    if (is_array($value)) {
                                                        echo Html::dropDownList("input_{$method}_{$key}", '', array_combine($value, $value), [
                                                            'id'               => "{$method}-{$key}",
                                                            'prompt'           => '',
                                                            'class'            => 'select2 form-control input',
                                                            'data-placeholder' => Yii::t('app', 'Choose') . ' ' . $key,
                                                        ]);
                                                    } else {
                                                        echo Html::textInput("input_{$method}_{$key}", '', [
                                                            'id'          => "{$method}-{$key}",
                                                            'class'       => 'form-control input',
                                                            'placeholder' => Yii::t('app', 'Enter') . ' ' . $key,
                                                        ]);
                                                    }
                                                ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Render variables -->
                    <div class="col-md-8 output">
                        <table class="table table-bordered">
                            <tr>
                                <td><?= Yii::t('app', 'Key') ?></td>
                            </tr>
                            <?php foreach ($templ_vars as $method => $params): ?>
                                <?php foreach ($params['output'] as $output): ?>
                                    <tr class="variables method_<?= $method ?>" style="display: none;">
                                        <td>
                                            <?php
                                                if (!empty($params['args'])) {

                                                    $variables = array_map(
                                                        function ($arg) use ($method) {
                                                            return Html::tag('span', $arg, [
                                                                'class'        => "text-warning arg_{$method}-{$arg}",
                                                                'data-default' => $arg
                                                            ]);
                                                        }, array_keys($params['args'])
                                                    );

                                                    $tag = '{{' . strtoupper($method) . ':' . implode(':', $variables) . '>' . strtoupper($output) . '}}';

                                                }
                                                else {
                                                    $tag = '{{' . strtoupper($method) . '>' . strtoupper($output) . '}}';
                                                }

                                                echo Html::a($tag, 'javascript:void(0);', [
                                                    'class' => "{$method}_variable_btn add_variable " . (!empty($params['args']) ? 'disabled' : ''),
                                                    'title' => Yii::t('app', 'Add variable')
                                                ]);

                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </table>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Body preview modal -->
<div id="body_preview_modal" class="modal fade">
    <div class="modal-dialog modal-lg">
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
