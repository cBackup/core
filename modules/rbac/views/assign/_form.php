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
 * @var $this          yii\web\View
 * @var $model         app\modules\rbac\models\AuthAssignment
 * @var $form          yii\bootstrap\ActiveForm
 * @var $users         array
 * @var $roles         array
 * @var $permissions   array
 * @var $locked_rights array
 */
app\assets\Select2Asset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action      = $this->context->action->id;
$page_name   = ($action == 'add') ? Yii::t('rbac', 'Add user rights') : Yii::t('rbac', 'Edit user rights');

$this->title = Yii::t('app', 'User rights');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['/user/list']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Access rights'), 'url' => ['/rbac/assign/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

$this->registerJs(
    /** @lang JavaScript */
    "
       /** Select2 init */
       $('.select2').select2({
           width : '100%'
       });

       /** JSON array of locked system access rights */
       var json = $.parseJSON('$locked_rights');
       
       if (!$.isEmptyObject(json)) {
           /** Lock system access rights */
           $.each(json, function (id, rights) {
               $('#roles_box').find('option[value=' + rights + ']').attr({'locked' : 'locked', 'class' : 'locked-roles'});
               $('#perm_box').find('option[value=' + rights + ']').attr({'locked' : 'locked', 'class' : 'locked-perm'});
           });
            
           /** Move locked system access rights to the beginning of the list */
           $('.locked-roles').prependTo('#roles_box');
           $('.locked-perm').prependTo('#perm_box');
       } 

    ", \yii\web\View::POS_READY
);
?>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'assign_form', 'enableClientValidation' => false]); ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?php
                                    echo $form->field($model, 'user_id')->dropDownList($users, [
                                        'prompt'           => '',
                                        'class'            => 'select2',
                                        'data-placeholder' => Yii::t('rbac', 'Choose user'),
                                        'disabled'         => ($action == 'edit') ? true : false
                                    ]);
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'roles')->dropDownList($roles, [
                                    'id'               => 'roles_box',
                                    'multiple'         => true,
                                    'class'            => 'select2 user-role',
                                    'data-placeholder' => FormHelper::label($model, 'roles'),
                                ]);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo $form->field($model, 'permissions')->dropDownList($permissions, [
                                    'id'               => 'perm_box',
                                    'multiple'         => true,
                                    'class'            => 'select2 user-perm',
                                    'data-placeholder' => FormHelper::label($model, 'permissions')
                                ]);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/rbac/assign'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
