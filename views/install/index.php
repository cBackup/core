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

/**
 * @var $this \yii\web\View
 */
$this->title = Yii::t('install', 'Installation');

$path = session_save_path();
$wacc = \app\models\Install::checkWorldAccess();
$url  = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].Yii::$app->request->baseUrl;

/** @noinspection JSUnusedLocalSymbols */
$this->registerJs(/** @lang JavaScript */'
    $("#language").change(function(e){
        window.location = $(location).attr("pathname") + "?lang=" + $(this).val();
    });
');

if(!is_writable($path)):
?>
    <div class="alert alert-danger">
        Unable to proceed - session_save_path in '<?= $path ?>' is not writable for user '<?= get_current_user() ?>'
    </div>
<?php elseif(is_null($wacc)): ?>
    <div class="alert alert-danger">
        No PHP cURL extension found, please install and enable to proceed
    </div>
<?php elseif($wacc === true): ?>
    <div class="alert alert-danger">
        <b>Unable to proceed - your application protected locations are world-accessible.</b> Your web server should
        be configured to deny access to files and folders on two levels higher than <code>install</code> folder. Use
        following locations for test, <code>they should not be accessible</code>:
        <ul>
            <li><?= Html::a("$url/../../install/schema.sql", "$url/../../install/schema.sql", ['target' => "_blank"]); ?></li>
            <li><?= Html::a("$url/../../yii.bat", "$url/../../yii.bat", ['target' => "_blank"]); ?></li>
            <li><?= Html::a("$url/../../README.md", "$url/../../README.md", ['target' => "_blank"]); ?></li>
            <li><?= Html::a("$url/../../bin/cbackup.jar", "$url/../../bin/cbackup.jar", ['target' => "_blank"]); ?></li>
        </ul>
    </div>
    <div class="btn-group pull-right">
        <button class='btn btn-danger'><?= Yii::t('install', 'Erorrs found, can\'t proceed') ?></button>
        <button class="btn btn-primary" onclick="location.reload();"><i class="fa fa-refresh"></i></button>
    </div>
<?php else: ?>
    <?php if(mb_stripos(PHP_OS, 'Linux') === false): ?>
        <div class="callout callout-warning">
            <?= Yii::t('help', "We don't officially support cBackup in non-Linux environment yet. Use it at own and sole discretion.") ?>
        </div>
    <?php endif; ?>
    <?php \yii\widgets\ActiveForm::begin(['options' => ['class' => 'form-horizontal']]) ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-body">
                    <p class="text-justify">
                        <?php
                            /** @noinspection HtmlUnknownTarget */
                            echo Yii::t('install', 'Welcome to cBackup installation script. During the setup process we will check if system meets specific requirements and deploy the initial database structure and scheduled jobs. Please check the <a href="{url}" target="_blank"> documentation</a> to complete the configuration after the installation is finished.', ['url' => 'http://cbackup.rtfd.io'])
                        ?>
                    </p>
                    <div class="form-group">
                        <label for="language" class="col-sm-2 control-label"><?= Yii::t('app', 'Language') ?></label>
                        <div class="col-sm-10">
                            <select name="language" id="language" class="form-control">
                                <option value="en-US" <?php if(Yii::$app->session->get('language') == 'en-US') echo 'selected' ?>>English</option>
                                <option value="ru-RU" <?php if(Yii::$app->session->get('language') == 'ru-RU') echo 'selected' ?>>Русский</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::submitButton(Yii::t('app', 'Next').' &raquo;', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>
    <?php \yii\widgets\ActiveForm::end() ?>
<?php endif; ?>
