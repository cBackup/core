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
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\helpers\ConfigHelper;

/**
 * @var $this       yii\web\View
 * @var $config     app\models\Config
 * @var $data       array
 * @var $errors     array
 * @var $backup_put string
 * @var $severities string
 */
app\assets\ToggleAsset::register($this);
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);

$this->title = Yii::t('app', 'System settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'System settings')];

$this->registerJsFile('@web/js/config/script.js', ['depends' => \app\assets\AlphaAsset::class]);

?>

<div class="row">
    <div class="col-md-12">
        <?php Pjax::begin(['id' => 'config-pjax']); ?>
            <?php $form = ActiveForm::begin(['id' => 'config']); ?>

<!---------------------------------------------------- GENERAL BOX ---------------------------------------------------->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <i class="fa fa-cogs"></i>
                        <h3 class="box-title"><?= Yii::t('config', 'Global settings') ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('isolated', $errors, [
                                            'label'       => Yii::t('config', 'Isolated system'),
                                            'description' => Yii::t('config', 'Set "No" if this installation <i>has</i> access to the internet; "Yes" if it is isolated'),
                                            'input'       => Html::checkbox('Config[isolated]', $data['isolated'], [
                                                'id'            => 'cb_isolated',
                                                'data-toggle'   => 'toggle',
                                                'data-size'     => 'normal',
                                                'data-on'       => Yii::t('app', 'Yes'),
                                                'data-off'      => Yii::t('app', 'No'),
                                                'data-onstyle'  => 'danger',
                                                'data-offstyle' => 'success',
                                                'uncheck'       => 0
                                            ]),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('adminEmail', $errors, [
                                            'label'       => Yii::t('config', 'Administrator e-mail'),
                                            'description' => Yii::t('config', 'E-mail address used for major notifications and scheduled reporting'),
                                            'input'       => Html::textInput('Config[adminEmail]', $data['adminEmail'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('logLifetime', $errors, [
                                            'label'       => Yii::t('config', 'Logs lifetime'),
                                            'description' => Yii::t('config', 'System will clear logs older than specified number of days. If days is set to 0 logs will not be cleared'),
                                            'input'       => Html::textInput('Config[logLifetime]', $data['logLifetime'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('nodeLifetime', $errors, [
                                            'label'       => Yii::t('config', 'Nodes lifetime'),
                                            'description' => Yii::t('config', 'System deletes inactive nodes more than specified number of days. If set to 0 nodes will not be deleted'),
                                            'input'       => Html::textInput('Config[nodeLifetime]', $data['nodeLifetime'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('systemLogLevel', $errors, [
                                            'label'       => Yii::t('config', 'System log level'),
                                            'description' => Yii::t('config', 'Change system logging level. We do not recommend set DEBUG log level on production server'),
                                            'input'       => Html::dropDownList('Config[systemLogLevel]', $data['systemLogLevel'], $severities, ['class' => 'select2'])
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('defaultPrependLocation', $errors, [
                                            'label'       => Yii::t('config', 'Default prepend location'),
                                            'description' => Yii::t('config', 'String which will be prepended to actual node locations.'),
                                            'input'       => Html::textInput('Config[defaultPrependLocation]', $data['defaultPrependLocation'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('gitPath', $errors, [
                                            'label'       => Yii::t('config', 'Git path'),
                                            'description' => Yii::t('config', 'Path to the Git executable'),
                                            'input'       => Html::textInput('Config[gitPath]', $data['gitPath'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<!------------------------------------------------------ GIT BOX ------------------------------------------------------>
                <div class="box box-default git-box">
                    <div class="box-header with-border">
                        <i class="fa fa-code-fork"></i>
                        <h3 class="box-title">
                            <?php
                                $git_txt = Yii::t('config', 'Git settings');
                                if (Y::param('git') == 1) {
                                    $git_txt .= '. ' . Yii::t('config', 'Repository status: ');
                                    if ($config::isGitRepo()) {
                                        $git_txt .= Html::tag('span', Yii::t('config', 'initialized'), ['class' => 'label bg-green', 'style' => 'font-weight: normal']);
                                    } else {
                                        $git_txt .= Html::tag('span', Yii::t('config', 'not initialized'), ['class' => 'label bg-red-active', 'style' => 'font-weight: normal']);
                                    }
                                }
                                echo $git_txt;
                            ?>
                        </h3>
                        <div class="box-tools pull-right">
                            <div class="btn-group">
                                <?php
                                    echo Html::a(Yii::t('config', 'Reinit Git settings'), 'javascript:', [
                                        'id'         => 'reinit_git',
                                        'class'      => 'btn btn-primary btn-sm ladda-button ' . ((!$config::isGitRepo() || Y::param('git') == 0) ? 'disabled' : ''),
                                        'data-url'   => Url::to(['ajax-reinit-git-settings']),
                                        'data-style' => 'zoom-in'
                                    ]);
                                    echo Html::a(Yii::t('config', 'Init repository'), 'javascript:', [
                                        'id'         => 'init_repo',
                                        'class'      => 'btn btn-primary btn-sm ladda-button ' . (($config::isGitRepo() || Y::param('git') == 0) ? 'disabled' : ''),
                                        'data-url'   => Url::to(['ajax-init-repo']),
                                        'data-style' => 'zoom-in'
                                    ]);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($backup_put != 'file'): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="callout callout-info">
                                        <?php
                                            $info_msg  = Yii::t('config', 'To unlock GIT UI change task "backup" destination to "File storage".');
                                            echo $info_msg . ' ' . Html::a(Yii::t('config', 'Click here to edit task "backup"'), Url::to(['network/task/edit', 'name' => 'backup']));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('git', $errors, [
                                            'label'       => Yii::t('config', 'Use Git'),
                                            'description' => Yii::t('config', 'Use Git for file-based persistent storage tasks'),
                                            'input'       => Html::checkbox('Config[git]', $data['git'], [
                                                'id'          => 'use_git',
                                                'data-toggle' => 'toggle',
                                                'data-size'   => 'normal',
                                                'data-on'     => Yii::t('app', 'Yes'),
                                                'data-off'    => Yii::t('app', 'No'),
                                                'uncheck'     => 0,
                                                'disabled'    => ($backup_put != 'file') ? true : false
                                            ]),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('gitRemote', $errors, [
                                            'label'       => Yii::t('config', 'Use Git remote'),
                                            'description' => Yii::t('config', 'Execute "git push" to remote repository'),
                                            'input'       => Html::checkbox('Config[gitRemote]', $data['gitRemote'], [
                                                'id'          => 'use_git_remote',
                                                'class'       => 'git',
                                                'data-toggle' => 'toggle',
                                                'data-size'   => 'normal',
                                                'data-on'     => Yii::t('app', 'Yes'),
                                                'data-off'    => Yii::t('app', 'No'),
                                                'uncheck'     => 0
                                            ]),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('gitUsername', $errors, [
                                            'label'       => Yii::t('config', 'Git username'),
                                            'description' => Yii::t('config', 'Username which will be written in git config file'),
                                            'input'       => Html::textInput('Config[gitUsername]', $data['gitUsername'], ['class' => 'form-control git']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('gitEmail', $errors, [
                                            'label'       => Yii::t('config', 'Git email'),
                                            'description' => Yii::t('config', 'E-mail which will be written in git config file'),
                                            'input'       => Html::textInput('Config[gitEmail]', $data['gitEmail'], ['class' => 'form-control git']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                    ConfigHelper::formGroup('gitDays', $errors, [
                                        'label'       => Yii::t('config', 'Git log display period'),
                                        'description' => Yii::t('config', 'For how long period in days will changes be displayed'),
                                        'input'       => Html::textInput('Config[gitDays]', $data['gitDays'], ['class' => 'form-control git']),
                                    ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('gitRepo', $errors, [
                                            'label'       => Yii::t('config', 'Git repository'),
                                            'description' => Yii::t('config', 'Remote git repository if "use git remote" is enabled; the "master" branch on remote must exist'),
                                            'input'       => Html::textInput('Config[gitRepo]', $data['gitRepo'], ['class' => 'form-control git git-remote']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('gitLogin', $errors, [
                                            'label'       => Yii::t('config', 'Git login'),
                                            'description' => Yii::t('config', 'Login is required if "git remote" is enabled'),
                                            'input'       => Html::textInput('Config[gitLogin]', $data['gitLogin'], ['class' => 'form-control git git-remote']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('gitPassword', $errors, [
                                            'label'       => Yii::t('config', 'Git password'),
                                            'description' => Yii::t('config', 'Password is required if "git remote" is enabled'),
                                            'input'       => Html::passwordInput('Config[gitPassword]', $data['gitPassword'], ['class' => 'form-control git git-remote', 'autocomplete' => 'off', 'id' => 'gitPassword']),
                                        ],
                                        '<div class="col-md-8 settings-label">
                                            <span class="text-bold">{label}</span><br>
                                            <small class="text-muted">{description}</small>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group no-error">
                                                <div class="input-group">
                                                    {input}
                                                    <div class="input-group-addon" title="'.Yii::t('app', 'Show password').'" data-container="body" data-placement="bottom" data-toggle="tooltip" >
                                                        '. Html::checkbox('', false, ['id' => 'gitPassword_check', 'class' => 'show-password']) .'
                                                    </div>
                                                </div>
                                            </div>
                                        </div>')
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-md-12 settings-label">
                                        <div class="callout callout-info" style="padding: 0 6px">
                                            <span class="text-bolder"><?= Yii::t('app', 'Reminder') ?></span><br>
                                            <small class="text-muted" style="color: #2c5f82"><?= Yii::t('config', 'Do not forget to <b>reinit git settings</b> after you change and save any data in this section') ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<!---------------------------------------------------- MAILER BOX ----------------------------------------------------->
                <div class="box box-default mailer-box">
                    <div class="box-header with-border">
                        <i class="fa fa-envelope-o"></i><h3 class="box-title"><?= Yii::t('config', 'Mailer settings') ?></h3>
                        <div class="box-tools pull-right">
                            <?=
                                Html::a(Yii::t('config', 'Send test mail'), 'javascript:', [
                                    'id'         => 'send_test_mail',
                                    'class'      => 'btn btn-primary btn-sm ladda-button ' . ((Y::param('mailer') == 0) ? 'disabled' : ''),
                                    'data-url'   => Url::to(['ajax-send-test-mail']),
                                    'data-style' => 'zoom-in'
                                ]);
                            ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailer', $errors, [
                                            'label'       => Yii::t('config', 'Send mail'),
                                            'description' => Yii::t('config', 'Enable mail sending'),
                                            'input'       => Html::checkbox('Config[mailer]', $data['mailer'], [
                                                'id'          => 'use_mailer',
                                                'data-toggle' => 'toggle',
                                                'data-size'   => 'normal',
                                                'data-on'     => Yii::t('app', 'Yes'),
                                                'data-off'    => Yii::t('app', 'No'),
                                                'uncheck'     => 0
                                            ]),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row mtype">
                                    <?=
                                        ConfigHelper::formGroup('mailerType', $errors, [
                                            'label'       => Yii::t('config', 'Mailer type'),
                                            'description' => Yii::t('config', 'Select mailer type for the site email delivery'),
                                            'input'       => Html::checkbox('Config[mailerType]', ($data['mailerType'] == 'smtp') ? true : false, [
                                                'id'            => 'mailer_type',
                                                'data-toggle'   => 'toggle',
                                                'data-size'     => 'normal',
                                                'data-on'       => Yii::t('app', 'SMTP'),
                                                'data-off'      => Yii::t('app', 'Sendmail'),
                                                'data-offstyle' => "primary",
                                                'data-onstyle'  => "primary",
                                                'value'         => 'smtp',
                                                'uncheck'       => 'local'
                                            ]),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerFromEmail', $errors, [
                                            'label'       => Yii::t('config', 'From email'),
                                            'description' => Yii::t('config', 'The email address that will be used to send site email'),
                                            'input'       => Html::textInput('Config[mailerFromEmail]', $data['mailerFromEmail'], ['class' => 'form-control mailer-input common']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerFromName', $errors, [
                                            'label'       => Yii::t('config', 'From name'),
                                            'description' => Yii::t('config', 'Text displayed in the header "From:" field when sending a site email'),
                                            'input'       => Html::textInput('Config[mailerFromName]', $data['mailerFromName'], ['class' => 'form-control mailer-input common']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerSendMailPath', $errors, [
                                            'label'       => Yii::t('config', 'Sendmail Path'),
                                            'description' => Yii::t('config', 'Enter the path to the sendmail program folder on the host server. Flag "-bs" is set by default'),
                                            'input'       => Html::textInput('Config[mailerSendMailPath]', $data['mailerSendMailPath'], ['class' => 'form-control mailer-input local']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerSmtpSslVerify', $errors, [
                                            'label'       => Yii::t('config', 'Disable SSL certificate verify'),
                                            'description' => Yii::t('config', 'Disable OpenSSL certificate verification if certificate verify error occurs'),
                                            'input'       => Html::checkbox('Config[mailerSmtpSslVerify]', $data['mailerSmtpSslVerify'], [
                                                'id'          => 'use_cert_verify',
                                                'data-toggle' => 'toggle',
                                                'data-size'   => 'normal',
                                                'data-on'     => Yii::t('app', 'Yes'),
                                                'data-off'    => Yii::t('app', 'No'),
                                                'uncheck'     => 0
                                            ]),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerSmtpHost', $errors, [
                                            'label'       => Yii::t('config', 'SMTP host'),
                                            'description' => Yii::t('config', 'The name of the SMTP host'),
                                            'input'       => Html::textInput('Config[mailerSmtpHost]', $data['mailerSmtpHost'], ['class' => 'form-control mailer-input smtp']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerSmtpPort', $errors, [
                                            'label'       => Yii::t('config', 'SMTP port'),
                                            'description' => Yii::t('config', 'The port number of the SMTP server cBackup will use to send emails'),
                                            'input'       => Html::textInput('Config[mailerSmtpPort]', $data['mailerSmtpPort'], ['class' => 'form-control mailer-input smtp']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?php
                                        $smtp_security = ['none' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL'];
                                        echo Html::hiddenInput('Config[mailerSmtpSecurity]', $data['mailerSmtpSecurity']);
                                        echo ConfigHelper::formGroup('mailerSmtpSecurity', $errors, [
                                            'label'       => Yii::t('config', 'SMTP security'),
                                            'description' => Yii::t('config', 'The security model of the SMTP server cBackup will use to send emails'),
                                            'input'       => Html::dropDownList('Config[mailerSmtpSecurity]', $data['mailerSmtpSecurity'], $smtp_security, ['id' => 'smtp_security', 'class' => 'select2'])
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerSmtpAuth', $errors, [
                                            'label'       => Yii::t('config', 'SMTP authentication'),
                                            'description' => Yii::t('config', 'Enable if your SMTP Host requires SMTP Authentication'),
                                            'input'       => Html::checkbox('Config[mailerSmtpAuth]', $data['mailerSmtpAuth'], [
                                                'id'          => 'use_smtp_auth',
                                                'data-toggle' => 'toggle',
                                                'data-size'   => 'normal',
                                                'data-on'     => Yii::t('app', 'Yes'),
                                                'data-off'    => Yii::t('app', 'No'),
                                                'uncheck'     => 0
                                            ]),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerSmtpUsername', $errors, [
                                            'label'       => Yii::t('config', 'SMTP username'),
                                            'description' => Yii::t('config', 'The username for access to the SMTP host'),
                                            'input'       => Html::textInput('Config[mailerSmtpUsername]', $data['mailerSmtpUsername'], ['class' => 'form-control mailer-input smtp-auth']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('mailerSmtpPassword', $errors, [
                                            'label'       => Yii::t('config', 'SMTP password'),
                                            'description' => Yii::t('config', 'The password for the SMTP host'),
                                            'input'       => Html::passwordInput('Config[mailerSmtpPassword]', $data['mailerSmtpPassword'], ['class' => 'form-control mailer-input smtp-auth', 'autocomplete' => 'off', 'id' => 'mailerSmtpPassword']),
                                        ],
                                        '<div class="col-md-8 settings-label">
                                            <span class="text-bold">{label}</span><br>
                                            <small class="text-muted">{description}</small>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group no-error">
                                                <div class="input-group">
                                                    {input}
                                                    <div class="input-group-addon" title="'.Yii::t('app', 'Show password').'" data-container="body" data-placement="bottom" data-toggle="tooltip" >
                                                        '. Html::checkbox('', false, ['id' => 'mailerSmtpPassword_check', 'class' => 'show-password']) .'
                                                    </div>
                                                </div>
                                            </div>
                                        </div>')
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<!---------------------------------------------- BACKGROUND SETTINGS BOX ---------------------------------------------->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <i class="fa fa-tasks"></i>
                        <h3 class="box-title"><?= Yii::t('config', 'Background process settings') ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('snmpTimeout', $errors, [
                                            'label'       => Yii::t('config', 'SNMP timeout'),
                                            'description' => Yii::t('config', 'The number of milliseconds until the first timeout'),
                                            'input'       => Html::textInput('Config[snmpTimeout]', $data['snmpTimeout'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('snmpRetries', $errors, [
                                            'label'       => Yii::t('config', 'SNMP retries'),
                                            'description' => Yii::t('config', 'The number of times to retry if timeouts occur'),
                                            'input'       => Html::textInput('Config[snmpRetries]', $data['snmpRetries'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('telnetTimeout', $errors, [
                                            'label'       => Yii::t('config', 'Telnet timeout'),
                                            'description' => Yii::t('config', 'The number of milliseconds until the first timeout'),
                                            'input'       => Html::textInput('Config[telnetTimeout]', $data['telnetTimeout'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('telnetBeforeSendDelay', $errors, [
                                            'label'       => Yii::t('config', 'Telnet before send delay'),
                                            'description' => Yii::t('config', 'The number of milliseconds before sending the next command'),
                                            'input'       => Html::textInput('Config[telnetBeforeSendDelay]', $data['telnetBeforeSendDelay'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('sshTimeout', $errors, [
                                            'label'       => Yii::t('config', 'SSH timeout'),
                                            'description' => Yii::t('config', 'The number of milliseconds until the first timeout'),
                                            'input'       => Html::textInput('Config[sshTimeout]', $data['sshTimeout'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('sshBeforeSendDelay', $errors, [
                                            'label'       => Yii::t('config', 'SSH before send delay'),
                                            'description' => Yii::t('config', 'The number of milliseconds before sending the next command'),
                                            'input'       => Html::textInput('Config[sshBeforeSendDelay]', $data['sshBeforeSendDelay'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('dataPath', $errors, [
                                            'label'       => Yii::t('config', 'Path to storage folder'),
                                            'description' => Yii::t('config', 'Absolute path to the local directory intended to store backup data'),
                                            'input'       => Html::textInput('Config[dataPath]', $data['dataPath'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('threadCount', $errors, [
                                            'label'       => Yii::t('config', 'Thread count'),
                                            'description' => Yii::t('config', 'Amount of concurrent threads for java background workers'),
                                            'input'       => Html::textInput('Config[threadCount]', $data['threadCount'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<!----------------------------------------------- SERVER CREDENTIALS BOX ---------------------------------------------->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <i class="fa fa-database"></i>
                        <h3 class="box-title"><?= Yii::t('config', 'Server credentials') ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('javaServerUsername', $errors, [
                                            'label'       => Yii::t('config', 'SSH login'),
                                            'description' => Yii::t('config', 'Username to authenticate cbackup web core on the server where java daemon is running'),
                                            'input'       => Html::textInput('Config[javaServerUsername]', $data['javaServerUsername'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                    ConfigHelper::formGroup('javaServerPort', $errors, [
                                        'label'       => Yii::t('network', 'SSH port'),
                                        'description' => Yii::t('config', 'Port for SSH connection to the server'),
                                        'input'       => Html::textInput('Config[javaServerPort]', $data['javaServerPort'], ['class' => 'form-control']),
                                    ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('javaServerPassword', $errors, [
                                            'label'       => Yii::t('config', 'SSH password'),
                                            'description' => Yii::t('config', 'Password to authenticate cbackup web core on the server where java daemon is running'),
                                            'input'       => Html::textInput('Config[javaServerPassword]', $data['javaServerPassword'], ['class' => 'form-control', 'autocomplete' => 'off', 'id' => 'javaServerPassword']),
                                        ],
                                        '<div class="col-md-8 settings-label">
                                            <span class="text-bold">{label}</span><br>
                                            <small class="text-muted">{description}</small>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group no-error">
                                                <div class="input-group">
                                                    {input}
                                                    <div class="input-group-addon" title="'.Yii::t('app', 'Show password').'" data-container="body" data-placement="bottom" data-toggle="tooltip" >
                                                        '. Html::checkbox('', false, ['id' => 'javaServerPassword_check', 'class' => 'show-password']) .'
                                                    </div>
                                                </div>
                                            </div>
                                        </div>')
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<!------------------------------------------------ JAVA CREDENTIALS BOX ----------------------------------------------->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <i class="fa fa-database"></i>
                        <h3 class="box-title"><?= Yii::t('config', 'Daemon credentials') ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('javaSchedulerUsername', $errors, [
                                            'label'       => Yii::t('config', 'Java console login'),
                                            'description' => Yii::t('config', 'Username to authenticate in the java daemon console'),
                                            'input'       => Html::textInput('Config[javaSchedulerUsername]', $data['javaSchedulerUsername'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('javaSchedulerPort', $errors, [
                                            'label'       => Yii::t('config', 'Java console port'),
                                            'description' => Yii::t('config', 'On which port java service has opened its terminal'),
                                            'input'       => Html::textInput('Config[javaSchedulerPort]', $data['javaSchedulerPort'], ['class' => 'form-control']),
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <?=
                                        ConfigHelper::formGroup('javaSchedulerPassword', $errors, [
                                            'label'       => Yii::t('config', 'Java console password'),
                                            'description' => Yii::t('config', 'Password to authenticate in the java daemon console'),
                                            'input'       => Html::passwordInput('Config[javaSchedulerPassword]', $data['javaSchedulerPassword'], ['class' => 'form-control', 'autocomplete' => 'off', 'id' => 'javaSchedulerPassword']),
                                        ],
                                        '<div class="col-md-8 settings-label">
                                            <span class="text-bold">{label}</span><br>
                                            <small class="text-muted">{description}</small>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group no-error">
                                                <div class="input-group">
                                                    {input}
                                                    <div class="input-group-addon" title="'.Yii::t('app', 'Show password').'" data-container="body" data-placement="bottom" data-toggle="tooltip" >
                                                        '. Html::checkbox('', false, ['id' => 'javaSchedulerPassword_check', 'class' => 'show-password']) .'
                                                    </div>
                                                </div>
                                            </div>
                                        </div>')
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?= Html::submitButton(Yii::t('app', 'Save changes'), ['class' => 'btn btn-sm btn-primary pull-right']) ?>

            <?php ActiveForm::end(); ?>
        <?php Pjax::end(); ?>
    </div>
</div>
