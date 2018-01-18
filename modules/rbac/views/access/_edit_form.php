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
 * @var $this        yii\web\View
 * @var $model       app\modules\rbac\models\AuthItem
 * @var $form        yii\bootstrap\ActiveForm
 * @var $item_name   string
 * @var $roles       array
 * @var $permissions array
 */
app\assets\Select2Asset::register($this);

$this->registerJs(
    /** @lang JavaScript */
    "
       /** Select2 init */
       $('.select2').select2({
            minimumResultsForSearch: '-1',
            width: '100%'
       });
    ", \yii\web\View::POS_READY
);

$page_name   = Yii::t('rbac', 'Edit item');
$this->title = Yii::t('app', 'Access rights');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['/user/list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Access rights'), 'url' => ['/rbac/access/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];
?>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-plus"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'access_form', 'enableClientValidation' => false]); ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php
                                    echo Html::activeLabel($model, 'name', ['class' => 'control-label']);
                                    echo Html::textInput('', $model->name, ['class' => 'form-control',  'disabled' => true]);
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php
                                    echo Html::activeLabel($model, 'type', ['class' => 'control-label']);
                                    echo Html::textInput('', $item_name, ['class' => 'form-control',  'disabled' => true]);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'description')->textInput([
                                    'class'        => 'form-control',
                                    'placeholder'  => FormHelper::label($model, 'description'),
                                    'style'        => 'resize: vertical'
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <?php if ($model->type == 1):?>
                            <div class="col-md-6">
                                <?php
                                    echo $form->field($model, 'roles')->dropDownList($roles, [
                                        'multiple'         => true,
                                        'class'            => 'select2',
                                        'data-placeholder' => FormHelper::label($model, 'roles'),
                                    ]);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                    echo $form->field($model, 'permissions')->dropDownList($permissions, [
                                        'multiple'         => true,
                                        'class'            => 'select2',
                                        'data-placeholder' => FormHelper::label($model, 'permissions')
                                    ]);
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12">
                                <?php
                                    echo $form->field($model, 'permissions')->dropDownList($permissions, [
                                        'multiple'         => true,
                                        'class'            => 'select2',
                                        'data-placeholder' => FormHelper::label($model, 'permissions')
                                    ]);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/rbac/access'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save changes'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
