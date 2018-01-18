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
app\assets\Select2Asset::register($this);
app\assets\ToggleAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action = $this->context->action->id;

if ($action == 'add') {
    $page_name   = Yii::t('network', 'Add credential');
    $box_checked = true;
} else {
    $page_name   = Yii::t('network', 'Edit credential');
    $box_checked = false;
}

$this->title = Yii::t('app', 'Credentials');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Credentials'), 'url' => ['/network/credential/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

$this->registerJs(/** @lang JavaScript */"
    var checkbox = $('.show-password');
    var ajaxUrl  = '".\yii\helpers\Url::to(['/network/credential/ajax-test'])."';
");

$this->registerJs(
    /** @lang JavaScript */
    "
        /** Init select2 */
        $('.select2').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });
        
        /** Autocomplete */
        $('#login_prompt').autocomplete({
            minLength: 0,
            source: [ 'username:', 'name:', 'Username:', 'UserName:', 'Login:' ]
        }).on('focus dblclick', function() {
            $(this).autocomplete('search');
        });
        $('#password_prompt').autocomplete({
            minLength: 0,
            source: [ 'password:', 'pass:', 'ord:', 'PassWord:', 'Password:' ]
        }).on('focus dblclick', function() {
            $(this).autocomplete('search');
        });
        $('#enable_prompt').autocomplete({
            minLength: 0,
            source: [ ':', '>' ]
        }).on('focus dblclick', function() {
            $(this).autocomplete('search');
        });
        $('#enable_success').autocomplete({
            minLength: 0,
            source: [ '#' ]
        }).on('focus dblclick', function() {
            $(this).autocomplete('search');
        });
        $('#main_prompt').autocomplete({
            minLength: 0,
            source: [ '#', '>' ]
        }).on('focus dblclick', function() {
            $(this).autocomplete('search');
        });
        
        /** Init iCheck */
        checkbox.iCheck({ 
            checkboxClass: 'icheckbox_minimal-red' 
        }).on('ifChanged', function (event) { 
            $(event.target).trigger('change');
        });

        /** Change password input type on checkbox change */
        checkbox.change(function() {
            var input_id = $(this)[0].id.split('_')[0];
            
            if ($(this).is(':checked')) {
                $('#' + input_id).attr('type', 'text');
            } else {
                $('#' + input_id).attr('type', 'password');
            }
        }).change();
        
        /** Run credentials test */
        $('#test_credentials').on('submit', function(event){
            
            var btn = $(this).find('button');
            btn.button('loading');
            event.preventDefault();
            
            /** Clear errors */
            $(this).find('.has-error').removeClass('has-error');
            toastr.clear();
            
            //noinspection JSUnresolvedVariable
            $.ajax({
                url:      ajaxUrl,
                method:   'post',
                cache:    false,
                data:     $('#credential_form, #test_credentials').serialize(),
                dataType: 'json'
            }).done(function(data) {
                $.each(data, function(key, value) {
                    var suffix = (value === 1) ? 'check text-success' : 'times text-danger';
                    if( value === 2 ) {
                        suffix = 'minus';                        
                    }
                    $('#'+key).html('<i class=\"fa fa-'+suffix+'\"></i>');
                });
            }).fail(function(data, textStatus, xhr ) {
                var errorText = xhr + ': ';
                try {
                    
                    var errors = $.parseJSON(data.responseText);
                    errorText += '<ul>';
                    
                    $.each(errors, function(key, values) {
                        errorText += '<li>' + values.join(', ') + '</li>';
                        $('.field-' + key).addClass('has-error');
                    });
                    
                    errorText += '</ul>';
                    
                } 
                catch(exception) {
                    errorText += data.responseText;
                }
                toastr.error(errorText, '', {timeOut: 5000, progressBar: true, closeButton: true});
            }).always(function(){
                btn.button('reset');
            });
                        
        });
    "
);

?>

<div class="row">
    <div class="<?= ($action == 'edit' && (isset($model->telnet_login) || isset($model->ssh_login) || isset($model->snmp_read))) ? 'col-md-9' : 'col-md-12' ?>">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'credential_form', 'enableClientValidation' => false]); ?>
                <div class="box-body">
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
                                echo $form->field($model, 'enable_password', [
                                    'inputTemplate' =>
                                        '
                                            <div class="input-group">
                                                {input}
                                                <div class="input-group-addon" title="'.Yii::t('app', 'Show password').'" data-container="body" data-placement="bottom" data-toggle="tooltip" >
                                                    '. Html::checkbox('', $box_checked, ['id' => 'enablePassword_check', 'class' => 'show-password']) .'
                                                </div>
                                            </div>
                                        '
                                ])->passwordInput([
                                    'id'           => 'enablePassword',
                                    'class'        => 'form-control',
                                    'autocomplete' => 'off',
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
                                echo $form->field($model, 'telnet_password', [
                                    'inputTemplate' =>
                                        '
                                            <div class="input-group">
                                                {input}
                                                <div class="input-group-addon" title="'.Yii::t('app', 'Show password').'" data-container="body" data-placement="bottom" data-toggle="tooltip" >
                                                    '. Html::checkbox('', $box_checked, ['id' => 'telnetPassword_check', 'class' => 'show-password']) .'
                                                </div>
                                            </div>
                                        '
                                ])->passwordInput([
                                    'id'           => 'telnetPassword',
                                    'class'        => 'form-control',
                                    'autocomplete' => 'off',
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
                                echo $form->field($model, 'ssh_password', [
                                    'inputTemplate' =>
                                        '
                                            <div class="input-group">
                                                {input}
                                                <div class="input-group-addon" title="'.Yii::t('app', 'Show password').'" data-container="body" data-placement="bottom" data-toggle="tooltip" >
                                                    '. Html::checkbox('', $box_checked, ['id' => 'sshPassword_check', 'class' => 'show-password']) .'
                                                </div>
                                            </div>
                                        '
                                ])->passwordInput([
                                    'id'           => 'sshPassword',
                                    'class'        => 'form-control',
                                    'autocomplete' => 'off',
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
                                    'class'            => 'select2',
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
                <div class="box-footer text-right">
                    <?php
                        if($action == 'edit') {
                            echo Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-danger pull-left',
                                'data' => [
                                    'confirm' => Yii::t('network', 'Are you sure you want to delete credential {0}?', $model->name),
                                    'method'  => 'post'
                                ],
                            ]);
                        }
                    ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/network/credential'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?php if($action == 'edit' && (isset($model->telnet_login) || isset($model->ssh_login) || isset($model->snmp_read))): ?>
    <div class="col-md-3">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-question"></i> <?= Yii::t('network', 'Test credential') ?>
                </h3>
            </div>
            <div class="box-body text-justify">
                <p>
                    <?= Yii::t('network', 'You can test current credentials set against the hostname to see if everything works correct.') ?>
                </p>
                <form id="test_credentials" class="form-horizontal">
                    <?php $hidden = (!empty($model->telnet_login) || !empty($model->telnet_password)) ? '' : 'hidden' ?>
                    <div class="form-group field-login_prompt <?= $hidden ?>">
                        <div class="col-xs-12">
                            <?php
                                echo Html::textInput('login_prompt', '', [
                                    'id'          => 'login_prompt',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('network', 'Telnet login (username) prompt')
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="form-group field-password_prompt <?= $hidden ?>">
                        <div class="col-xs-12">
                            <?php
                                echo Html::textInput('password_prompt', '', [
                                    'id'          => 'password_prompt',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('network', 'Telnet password prompt')
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="form-group field-main_prompt <?= $hidden ?>">
                        <div class="col-xs-12">
                            <?php
                                echo Html::textInput('main_prompt', '', [
                                    'id'          => 'main_prompt',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('network', 'Telnet main prompt')
                                ]);
                            ?>
                        </div>
                    </div>
                    <?php $hidden = (!empty($model->enable_password)) ? '' : 'hidden' ?>
                    <div class="col-xs-6 <?= $hidden ?>">
                        <div class="form-group field-enable_prompt" style="padding-right: 3px">
                            <?php
                                echo Html::textInput('enable_prompt', '', [
                                    'id'          => 'enable_prompt',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('network', 'Enable prompt')
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="col-xs-6 <?= $hidden ?>">
                        <div class="form-group field-enable_success" style="padding-left: 3px">
                            <?php
                                echo Html::textInput('enable_success', '', [
                                    'id'          => 'enable_success',
                                    'class'       => 'form-control',
                                    'placeholder' => Yii::t('network', 'Enable success')
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="form-group field-ip">
                        <div class="col-xs-12">
                            <div class="input-group">
                                <?php
                                    echo Html::textInput('ip', '', [
                                        'id'           => 'input_ip',
                                        'class'        => 'form-control',
                                        'placeholder'  => Yii::t('network', 'Test IP-address'),
                                    ]);
                                ?>
                                <span class="input-group-btn">
                                    <?php
                                        echo Html::submitButton('<i class="fa fa-play"></i>', [
                                            'class'             => 'btn btn-primary btn-flat',
                                            'data-loading-text' => '<i class="fa fa-spinner fa-spin"></i>'
                                        ]);
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <table class="table table-tests">
                <tbody>
                    <tr class="<?= (empty($model->telnet_login) && empty($model->telnet_password)) ? 'hidden' : '' ?>">
                        <td>Telnet</td>
                        <td class="text-right" id="test-telnet">
                            <i class="fa fa-minus"></i>
                        </td>
                    </tr>
                    <tr class="<?= (empty($model->ssh_login) || empty($model->ssh_password)) ? 'hidden' : '' ?>">
                        <td>SSH</td>
                        <td class="text-right" id="test-ssh">
                            <i class="fa fa-minus"></i>
                        </td>
                    </tr>
                    <tr class="<?= empty($model->enable_password) ? 'hidden' : '' ?>">
                        <td>Enable password</td>
                        <td class="text-right" id="test-enable">
                            <i class="fa fa-minus"></i>
                        </td>
                    </tr>
                    <tr class="<?= (empty($model->snmp_read)) ? 'hidden' : '' ?>">
                        <td>SNMP read</td>
                        <td class="text-right" id="test-snmpget">
                            <i class="fa fa-minus"></i>
                        </td>
                    </tr>
                    <tr class="<?= (empty($model->snmp_set)) ? 'hidden' : '' ?>">
                        <td>SNMP write</td>
                        <td class="text-right" id="test-snmpset">
                            <i class="fa fa-minus"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
