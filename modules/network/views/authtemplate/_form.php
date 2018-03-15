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
 * @var $this   yii\web\View
 * @var $model  app\models\DeviceAuthTemplate
 * @var $form   yii\bootstrap\ActiveForm
 * @var $vars   array
 */

/** @noinspection PhpUndefinedFieldInspection */
$action    = $this->context->action->id;
$page_name = ($action == 'add') ? Yii::t('network', 'Add auth template') : Yii::t('network', 'Edit auth template');

$this->title = Yii::t('app', 'Device auth templates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Device auth templates'), 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

// Because firefox has 9 years of open bug with unsupported 'background-attachment: local' for textareas
$this->registerJsFile('/js/plugins/autosize.min.js', ['depends' => \app\assets\AlphaAsset::class]);
$this->registerJs(/** @lang JavaScript */"autosize($('textarea'));");
?>

<div class="row">
    <div class="col-md-7">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php
                $form = ActiveForm::begin([
                    'id'                     => 'auth_templates_form',
                    'layout'                 => 'horizontal',
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label'   => 'col-sm-4',
                            'wrapper' => 'col-sm-8'
                        ],
                    ],
                ]);
            ?>
                <div class="box-body">
                    <?php
                        echo $form->field($model, 'name')->textInput([
                            'class'       => 'form-control',
                            'placeholder' => FormHelper::label($model, 'name'),
                            'disabled'    => ($action == 'edit') ? true : false
                        ]);
                        echo $form->field($model, 'auth_sequence', [
                            'inputTemplate' =>
                                '<div class="pull-left margin-r-5">
                                    <div class="auth_sequence_helper pull-left" style="background-color: #dcf1d7;"></div>
                                    '. Yii::t('network', 'Prompt (expect)') .'
                                </div>
                                <div class="pull-left" style="margin-bottom: 5px">
                                    <div class="auth_sequence_helper pull-left" style="background-color: #ffffff;"></div>
                                    '.Yii::t('network', 'Input data').'
                                </div>
                                {input}',
                        ])->textarea([
                            'class'       => 'form-control auth_sequence',
                            'placeholder' => FormHelper::label($model, 'auth_sequence'),
                            'readonly'    => false
                        ]);
                        echo $form->field($model, 'description')->textarea([
                            'class'       => 'form-control',
                            'placeholder' => FormHelper::label($model, 'description'),
                            'style'       => 'resize: vertical'
                        ]);
                    ?>
                </div>
                <div class="box-footer text-right">
                    <?php
                        if($action == 'edit') {
                            echo Html::a(Yii::t('app', 'Delete'), ['delete', 'name' => $model->name], [
                                'class' => 'btn btn-sm btn-danger pull-left',
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->name),
                                    'method'  => 'post'
                                ],
                            ]);
                        }
                    ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['list'], ['class' => 'btn btn-sm btn-default margin-r-5']) ?>
                    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save changes'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="col-md-5">
       <?= $this->render('_help_view', ['collapsed' => false, 'vars' => $vars]) ?>
    </div>
</div>
