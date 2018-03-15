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

use app\models\Install;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this  \yii\web\View
 * @var $model Install
 */
$this->title = Yii::t('install', 'System parameters setup');

$this->registerJsFile('@web/../plugins/jstz/1.0.7/jstz.min.js', ['depends' => \app\assets\InstallAsset::class]);

/** @noinspection JSUnusedLocalSymbols */
$this->registerJs(/** @lang JavaScript */'

    $("w0").on("submit", function(e) {
        $("#submit").button("loading");
    });
    
    // timezone select2
    var tz = $(".select2");
    tz.select2({
        width: "100%" 
    });
    tz.val(jstz.determine().name()).trigger("change");
    
');

$form = ActiveForm::begin([
    'enableClientScript' => false,
    'enableClientValidation' => false,
]);
?>
<div class="box">
    <table class="table check" style="margin-bottom: 0">
        <tr>
            <th><?= Yii::t('network', 'Database') ?></th>
        </tr>
    </table>
    <div class="box-body">
        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'password') ?>
        <?= $form->field($model, 'host')->textInput(['value' => (empty($model->host)) ? 'localhost' : $model->host]) ?>
        <?= $form->field($model, 'port')->textInput(['value' => (empty($model->port)) ? 3306 : $model->port]) ?>
        <?= $form->field($model, 'schema') ?>
    </div>
    <table class="table check" style="margin-bottom: 0">
        <tr>
            <th><?= Yii::t('app', 'Settings') ?></th>
        </tr>
    </table>
    <div class="box-body">
        <?php
            echo $form->field($model, 'timezone')->dropDownList(Install::getTimezoneList(), ['class' => 'select2 form-control']);
            echo $form->field($model, 'gitpath')->textInput();
        ?>
    </div>
    <table class="table check" style="margin-bottom: 0">
        <tr>
            <th><?= Yii::t('install', 'Background service settings') ?></th>
        </tr>
    </table>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6"><?php echo $form->field($model, 'path')->textInput(['value' => (empty($model->path)) ? Yii::$app->basePath.DIRECTORY_SEPARATOR.'data' : $model->path]); ?></div>
            <div class="col-md-6"><?= $form->field($model, 'java_port')->textInput(['value' => (empty($model->java_port)) ? 8437 : $model->java_port]); ?></div>
            <div class="col-md-6"><?= $form->field($model, 'threads')->textInput(['value' => (empty($model->threads)) ? Install::estimatePerformance() : $model->threads]); ?></div>
            <div class="col-md-6"><?= $form->field($model, 'java_username')->textInput(['value' => (empty($model->java_username)) ? 'cbadmin' : $model->java_username]); ?></div>
            <div class="col-md-6">
                <?php
                    /** @noinspection PhpUnhandledExceptionInspection */
                    echo $form->field($model, 'java_password')->textInput(['value' => (empty($model->java_password)) ? str_replace(['_', '-'], '', Yii::$app->security->generateRandomString(9)) : $model->java_password]);
                ?>
            </div>
        </div>
    </div>
    <table class="table check" style="margin-bottom: 0">
        <tr>
            <th><?= Yii::t('config', 'Server credentials') ?></th>
        </tr>
    </table>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6"><?php echo $form->field($model, 'server_login')->textInput(['value' => (empty($model->server_login)) ? 'cbackup' : $model->server_login]); ?></div>
            <div class="col-md-6"><?php echo $form->field($model, 'server_password')->textInput(['value' => (empty($model->server_password)) ? null : $model->server_password]); ?></div>
            <div class="col-md-6"><?php echo $form->field($model, 'server_port')->textInput(['value' => (empty($model->server_port)) ? 22 : $model->server_port]); ?></div>
            <div class="col-md-6"><?php echo $form->field($model, 'systeminit')->dropDownList(['system.d' => 'Systemd', 'init.d' => 'SysVinit'], ['value' => (empty($model->systeminit)) ? 'system.d' : $model->systeminit]); ?></div>
        </div>
    </div>
    <table class="table check" style="margin-bottom: 0">
        <tr>
            <th><?= Yii::t('app', 'User') ?></th>
        </tr>
    </table>
    <div class="box-body">
        <div class="form-group">
            <?php
                echo Html::label('System user');
                echo Html::textInput('sysuser', 'Admin', ['disabled' => true, 'class' => 'form-control']);
            ?>
        </div>
        <?= $form->field($model, 'syspassword')->textInput(['value' => (empty($model->syspassword)) ? '' : $model->syspassword]); ?>
        <?= $form->field($model, 'email')->textInput(['value' => (empty($model->email)) ? '' : $model->email]); ?>
    </div>
    <div class="box-footer">
        <?php
            echo Html::a('&laquo; '.Yii::t('app', 'Back'), ['requirements'], ['class' => 'btn btn-default pull-left']);
            echo Html::submitButton(Yii::t('app', 'Next').' &raquo;', [
                'class' => 'btn btn-primary pull-right',
                'id'    => 'submit',
            ])
        ?>
    </div>
</div>
<?php ActiveForm::end() ?>
