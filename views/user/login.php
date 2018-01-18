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

/**
 * @var $this yii\web\View
 * @var $form yii\bootstrap\ActiveForm
 * @var $model app\models\LoginForm
 */
app\assets\AlphaAsset::register($this);

$this->title = Yii::t('user', 'Sign in');
$this->registerJs(/** @lang JavaScript */ "
    $('input').iCheck({ checkboxClass: 'icheckbox_minimal-blue' });
    $('#loginform-username').focus();
");
?>
<div class="login-box">
    <div class="login-logo"><b>C</b>Backup</div>
    <div class="login-box-body">

        <?php
            $form = ActiveForm::begin([
                'id'                     => 'login-form',
                'enableClientValidation' => false
            ]);
        ?>

            <div class="form-group has-feedback" style="margin-top: 1em;">
                <?=
                    $form->field($model, 'username', [
                        'template' => "{input}\n{error}"
                    ])->textInput([
                        'class'        => 'form-control',
                        'placeholder'  => Yii::t('app', 'Username'),
                        'autocomplete' => 'off',
                    ])
                ?>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <?=
                    $form->field($model, 'password', [
                        'template' => "{input}\n{error}",
                    ])->passwordInput([
                        'class'        => 'form-control',
                        'placeholder'  => Yii::t('app', 'Password'),
                        'autocomplete' => 'off',
                    ])
                ?>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8">
                    <?=
                        $form->field($model, 'rememberMe')->checkbox([
                            'labelOptions' => ['class' => 'rememberme'],
                            'label'        => Yii::t('user', 'Remember me'),
                        ]);
                    ?>
                </div>
                <div class="col-xs-4">
                    <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary btn-block btn-flat']); ?>
                </div>
            </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
