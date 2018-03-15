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
use faryshta\widgets\JqueryTagsInput;

/**
 * @var $this        yii\web\View
 * @var $model       app\models\MailerEvents
 * @var $form        yii\bootstrap\ActiveForm
 */
$page_name   = Yii::t('app', 'Edit recipients');
$this->title = Yii::t('app', 'Mailer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mailer'), 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

?>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-pencil-square-o"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php
                $form = ActiveForm::begin([
                    'id'                     => 'mail_event_recipients_form',
                    'layout'                 => 'horizontal',
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label'   => 'col-sm-2',
                            'wrapper' => 'col-sm-10'
                        ],
                    ],
                    'options' => [
                        'onkeypress' => 'return event.keyCode != 13;', /** Prevent form submitting on enter */
                    ]
                ]);
            ?>
            <div class="box-body">
                <?php
                    echo $form->field($model, 'name')->textInput([
                        'class'    => 'form-control',
                        'readonly' => true
                    ]);
                    echo $form->field($model, 'recipients', ['errorOptions' => ['encode' => false]])->widget(JqueryTagsInput::class, [
                        'clientOptions' => [
                            'width'       => '100%',
                            'height'      => 'auto',
                            'delimiter'   => ';',
                            'defaultText' => Yii::t('mail', 'Enter recipients'),
                            'onAddTag'    => new yii\web\JsExpression("
                                function(tag) { 
                                   var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,6})?$/;
                                   if (!emailReg.test(tag)) {
                                       $(this).removeTag(tag);
                                       $('#' + this.id  + '_tag').addClass('not_valid').val(tag).focus();
                                   }
                                }
                            ")
                        ],
                    ]);
                ?>
            </div>
            <div class="box-footer text-right">
                <?= Html::a(Yii::t('app', 'Cancel'), ['list'], ['class' => 'btn btn-sm btn-default']) ?>
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
