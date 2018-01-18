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

/** @noinspection PhpUndefinedFieldInspection
 *  @var $this          yii\web\View
 *  @var $model         app\models\Vendor
 *  @var $form          yii\bootstrap\ActiveForm
 */
$action      = $this->context->action->id;
$page_name   = ($action == 'add') ? Yii::t('network', 'Add vendor') : Yii::t('network', 'Edit vendor');

$this->title = Yii::t('app', 'Devices');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Devices'), 'url' => ['/network/device/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

?>

<div class="row">
    <div class="<?= (!array_key_exists($model->name, \Y::param('java_factory'))) ? 'col-md-8 col-md-offset-2' : 'col-md-8'?>">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'vendor_form', 'enableClientValidation' => false]); ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'name')->textInput([
                                    'class'            => 'form-control',
                                    'placeholder' => FormHelper::label($model, 'name'),
                                ]);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?php
                        if($action == 'edit') {
                            echo Html::a(Yii::t('app', 'Delete'), ['vendor/delete', 'name' => $model->name], [
                                'class' => 'btn btn-sm btn-danger pull-left',
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->name),
                                    'method'  => 'post',
                                ],
                            ]);
                        }
                    ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/network/device/list'], ['class' => 'btn btn-sm btn-default margin-r-5']) ?>
                    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if (array_key_exists($model->name, \Y::param('java_factory'))): ?>
        <div class="col-md-4">
            <div class="box box-danger">
                <div class="box-header with-border bg-red-gradient">
                    <i class="fa fa-warning"></i>
                    <h3 class="box-title box-title-align"><?= Yii::t('app', 'Warning') ?></h3>
                </div>
                <div class="box-body text-justify">
                    <p><?= Yii::t('network', 'The name of selected vendor is used for the specific implementation of the protocols. By changing vendor name, the standard version will be used.')?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
