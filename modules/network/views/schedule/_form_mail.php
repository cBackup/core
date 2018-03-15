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
use launcherx\cronui\CronUI;

/**
 * @var $this          yii\web\View
 * @var $model         app\models\ScheduleMail
 * @var $form          yii\bootstrap\ActiveForm
 * @var $events_list   array
 */
app\assets\Select2Asset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action      = $this->context->action->id;
$page_name   = ($action == 'add-mail-schedule') ? Yii::t('network', 'Add scheduled mail event') : Yii::t('network', 'Edit scheduled mail event');

$this->title = Yii::t('app', 'Schedules');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Schedules'), 'url' => ['/network/schedule/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

$this->registerJs(
    /** @lang JavaScript */
    "
        /** Init select2 */
        $('.select2').select2({
            width: '100%'
        });
    "
);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa <?= ($action == 'add-mail-schedule') ? 'fa-plus' : 'fa-pencil-square-o' ?>"></i> <?= $page_name ?>
                </h3>
            </div>
            <?php $form = ActiveForm::begin(['id' => 'schedule_mail_form', 'enableClientValidation' => false]); ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'event_name')->dropDownList($events_list, [
                                    'prompt'           => '',
                                    'class'            => 'select2',
                                    'data-placeholder' => Yii::t('network', 'Choose task'),
                                    'disabled'         => ($action == 'edit-mail-schedule') ? true : false
                                ]);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $form->field($model, 'schedule_cron', ['enableLabel' => false])->widget(CronUI::class, [
                                    'options'       => ['class' => 'form-control'],
                                    'pluginOptions' => [
                                        'dropDownMultiple'   => true,
                                        'dropDownStyled'     => true,
                                        'dropDownStyledFlat' => true
                                    ]
                                ]);
                            ?>
                        </div>
                    </div>
                </div>

                <div class="box-footer text-right">
                    <?php
                        if($action == 'edit-mail-schedule') {
                            echo Html::a(Yii::t('app', 'Delete'), ['delete-mail-schedule', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-danger pull-left',
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->event_name),
                                    'method'  => 'post',
                                ],
                            ]);
                        }
                    ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/network/schedule/list'], ['class' => 'btn btn-sm btn-default margin-r-5']) ?>
                    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
