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
use yii2mod\alert\Alert;

/** @noinspection PhpUndefinedFieldInspection
 *  @var $this        yii\web\View
 *  @var $model       app\models\Exclusion
 *  @var $form        yii\bootstrap\ActiveForm
 *  @var $data        \app\models\Node
 */
$action      = $this->context->action->id;
$page_name   = ($action == 'add') ? Yii::t('network', 'Add exclusion') : Yii::t('network', 'Edit exclusion');

$this->title = Yii::t('app', 'Exclusions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('node', 'Nodes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Exclusions'), 'url' => ['/network/exclusion/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

if ($model->show_warning) {
    /** @noinspection PhpUnhandledExceptionInspection */
    echo Alert::widget([
        'useSessionFlash' => false,
        'options' => [
            'timer' => null,
            'type'  => null,
            'html'  => true,
            'title' => "<i class='fa fa-exclamation-triangle'></i> ".Yii::t('app', 'Warning'),
            'text'  => Yii::t('network', 'Are you sure you want to exclude IP-address <b>{0}</b> from discovery and provisioning procedures?', $model->ip),
            'confirmButtonText' => Yii::t('app', 'Confirm'),
            'cancelButtonText'  => Yii::t('app', 'Cancel'),
            'closeOnConfirm'    => true,
            'showCancelButton'  => true,
            'animation'         => false
        ],
        'callback' => new \yii\web\JsExpression('function(isConfirm) { 
            if (isConfirm) {
                $("#save_on_warning").val("1");
                $("#exclusion_form").submit();
            }  
        }')
    ]);
}

?>

<div class="row">
    <div class="col-md-8">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php
                $form = ActiveForm::begin([
                    'id'                     => 'exclusion_form',
                    'layout'                 => 'horizontal',
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label'   => 'col-sm-2',
                            'wrapper' => 'col-sm-10'
                        ],
                    ],
                ]);
                echo $form->field($model, 'save_on_warning')->hiddenInput(['id' => 'save_on_warning'])->label(false);
            ?>
                <div class="box-body">
                    <?php
                        echo $form->field($model, 'ip')->textInput([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'ip'),
                            'readonly'     => ($action == 'edit') ? true : false
                        ]);
                        echo $form->field($model, 'description')->textarea([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'description'),
                            'style'        => 'resize: vertical'
                        ]);
                    ?>
                </div>
                <div class="box-footer text-right">
                    <?php
                        if($action == 'edit') {
                            echo Html::a(Yii::t('app', 'Delete'), \yii\helpers\Url::to(['delete', 'ip' => $model->ip]), [
                                'class' => 'btn btn-sm btn-danger pull-left',
                                'data' => [
                                    'confirm' => Yii::t('network', 'Are you sure you want to delete exclusion {0}?', $model->ip),
                                    'method' => 'post',
                                    'params' => [
                                        'ip' => $model->ip,
                                    ],
                                ],
                            ]);
                        }
                    ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/network/exclusion'], ['class' => 'btn btn-sm btn-default']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-info <?= ($action == 'edit') ? 'collapsed-box' : '' ?>">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Help') ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-<?= ($action == 'edit') ? 'plus' : 'minus' ?>"></i></button>
                </div>
            </div>
            <div class="box-body text-justify">
                <?= Yii::t('network', "Added IP-addresses will be excluded from discovery and provisioning procedures. E.g. if there's a network 192.168.10.0/24 but you want to skip particular device from being discovered and polled, add its IP-address in this classificator.") ?>
                <br><br>
                <?= Yii::t('network', "If you add to exclusion classificator <i>existing</i> node or interface, node will be marked as excluded in list and detailed view and won't get called by any task nor process.") ?>
            </div>
        </div>

        <?php if($action == 'edit' && !empty($data)): ?>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('node', 'Node information') ?></h3>
                <div class="box-tools pull-right">
                    <a href="<?= \yii\helpers\Url::to(['/node/view', 'id' => $data->id]) ?>" class="btn btn-primary btn-sm"><?= Yii::t('network', 'Open node') ?></a>
                </div>
            </div>
            <table class="box-body table">
                <tr>
                    <th><?= Yii::t('network', 'Hostname') ?></th>
                    <td><?= $data->hostname ?></td>
                </tr>
                <tr>
                    <th><?= Yii::t('network', 'IP address') ?></th>
                    <td><?= $data->ip ?></td>
                </tr>
                <tr>
                    <th><?= Yii::t('network', 'Location') ?></th>
                    <td><?= $data->location ?></td>
                </tr>
                <tr>
                    <th><?= Yii::t('network', 'Last seen') ?></th>
                    <td><?= $data->last_seen ?></td>
                </tr>
                <tr>
                    <th><?= Yii::t('network', 'Device name') ?></th>
                    <td><?= $data->device->vendor . ' ' . $data->device->model ?></td>
                </tr>
                <tr>
                    <th><?= Yii::t('network', 'Serial') ?></th>
                    <td><?= $data->serial ?></td>
                </tr>
                <tr>
                    <th><?= Yii::t('app', 'Description') ?></th>
                    <td><?= $data->sys_description ?></td>
                </tr>
                <tr>
                    <th><?= Yii::t('network', 'MAC address') ?></th>
                    <td><?= app\helpers\StringHelper::beautifyMac($data->mac) ?></td>
                </tr>
            </table>
            <?php if(!empty($data->altInterfaces)): ?>
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('network', 'Alternative interfaces') ?></h3>
            </div>
            <table class="box-body table">
                <thead>
                    <tr>
                        <th><?= Yii::t('network', 'IP address') ?></th>
                        <th><?= Yii::t('app', 'Description') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data->altInterfaces as $altInterface): ?>
                    <tr>
                        <td><?= $altInterface['ip'] ?></td>
                        <td>&nbsp;</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>
