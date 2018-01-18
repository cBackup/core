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
use app\helpers\FormHelper;

/**
 * @var $this       yii\web\View
 * @var $model      app\models\User
 * @var $form       yii\bootstrap\ActiveForm
 */
app\assets\ToggleAsset::register($this);
app\assets\i18nextAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action = $this->context->action->id;

if($action == 'profile') {
    $page_name   = Yii::t('user', 'Profile');
    $this->title = $page_name;
    $this->params['breadcrumbs'][] = ['label' => $page_name];
}
else {
    $page_name   = ($action == 'add') ? Yii::t('user', 'Add user') : Yii::t('user', 'Edit user');
    $this->title = Yii::t('app', 'Users');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration')];
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['/user/list']];
    $this->params['breadcrumbs'][] = ['label' => $page_name];
}

$this->registerJsFile('@web/plugins/clipboard/1.5.16/clipboard.min.js');

/** @noinspection JSUnusedGlobalSymbols */
/** @noinspection JSUnusedLocalSymbols */
$this->registerJs(
    /** @lang JavaScript */
    "
        /** Generate new access token via AJAX */
        $('#generate').click(function() {
          
            var href        = $(this).attr('data-ajax-url'); 
            var token_field = $('#access_token');
            
            $.ajax({
                type: 'POST',
                url: href,
                success: function (data) {
                    /** Check data and fill access_token field */
                    if (isJson(data)) {
                        
                        var json = $.parseJSON(data);
                        token_field.val('');
                        
                        if (json['status'] === 'success') {
                           token_field.val(json['key']);
                        }
                    }
                    /** Show request status */
                    showStatus(data);
                },
                error: function (data) {
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                    token_field.val('');
                }
            });
            
        });
        
        /** Revoke access token */
        $('#revoke').click(function() {
            $('#access_token').val('');
            toastr.success(i18next.t('Token was successfully deleted.<br>Do not forget to save changes!'), '', {timeOut: 5000, progressBar: true, closeButton: true});
        });
        
        /** Copy token to clipboard */
        var clipboard = new Clipboard('#copy');
        
        clipboard.on('success', function(e) {
            if( e.text === '' ) {
                toastr.warning(i18next.t('Empty token, nothing to copy'), '', {timeOut: 5000, progressBar: true, closeButton: true});
            }
            else {
                toastr.success(i18next.t('Token copied to clipboard'), '', {timeOut: 5000, progressBar: true, closeButton: true});
            }
        });
       
        clipboard.on('error', function(e) {
            toastr.warning(i18next.t('Error while copying token'), '', {closeButton: true});
        });

    "
);

?>

<div class="row">
    <div class="col-md-7 col-md-offset-2">
        <div class="box <?= ($model->enabled == 0) ?  "box-danger box-solid" : "box-default" ?>">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i>
                    <?= $page_name . ($model->enabled == 0 ? ' &mdash; ' . Yii::t('user', 'This user is disabled!') : '') ?>
                </h3>
            </div>
            <?php
                $form = ActiveForm::begin([
                    'id'                     => 'user_form',
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
                        echo $form->field($model, 'fullname')->textInput([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'fullname')
                        ]);
                        echo $form->field($model, 'userid')->textInput([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'userid'),
                            'readonly'     => ($action == 'add') ? false : true
                        ]);
                        echo $form->field($model, 'password')->passwordInput([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'password'),
                            'autocomplete' => 'off'
                        ]);
                        echo $form->field($model, 'email')->textInput([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'email'),
                        ]);

                        if (\Yii::$app->user->can('admin') || \Yii::$app->user->can('APICore') || \Yii::$app->user->can('APIReader')) {

                            $template =
                                '
                                    <div class="input-group">
                                        {input}
                                        <div class="input-group-btn">
                                            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li>
                                                    <a id="generate" data-ajax-url="'.Url::to(['/user/generate-token']).'" href="javascript:;">
                                                        '.Yii::t("user", 'Generate access token').'
                                                    </a>
                                                </li>
                                                <li>
                                                    <a id="revoke" href="javascript:;">'.Yii::t("user", 'Revoke access token').'</a>
                                                </li>
                                                <li>
                                                    <a id="copy" href="javascript:;" data-clipboard-target="#access_token" data-clipboard-action="copy">
                                                        '.Yii::t("user", 'Copy access token').'
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                ';

                            echo $form->field($model, 'access_token', ['inputTemplate' => $template])->textInput([
                                'id'           => 'access_token',
                                'class'        => 'form-control',
                                'placeholder'  => Yii::t('user', 'You do not have any token generated yet'),
                                'readonly'     => true
                            ]);
                        }

                        if ($action != 'profile' && \Yii::$app->user->can('admin')) {

                            echo $form->field($model, 'enabled', [
                                'horizontalCheckboxTemplate' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'col-sm-10 col-md-offset-0']
                            ])->checkbox([
                                'data-toggle' => 'toggle',
                                'data-size'   => 'normal',
                                'data-on'     => Yii::t('app', 'Yes'),
                                'data-off'    => Yii::t('app', 'No')
                            ])->label(null, ['class' => 'control-label col-sm-2']);

                        }

                    ?>
                </div>
                <div class="box-footer text-right">
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/user/list'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
