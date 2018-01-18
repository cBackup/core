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
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use app\helpers\FormHelper;
use yii2mod\alert\Alert;

/**
 * @var $this         yii\web\View
 * @var $model        app\models\Task
 * @var $form         yii\bootstrap\ActiveForm
 * @var $destinations array
 * @var $out_tables   array
 * @var $table_fields array
 */
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);
app\assets\CreateTableAsset::register($this);
app\assets\i18nextAsset::register($this);
yii2mod\alert\AlertAsset::register($this);

/** @noinspection PhpUndefinedFieldInspection */
$action      = $this->context->action->id;
$page_name   = ($action == 'add') ? Yii::t('network', 'Add task') : Yii::t('network', 'Edit task');
$this->title = Yii::t('app', 'Tasks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tasks'), 'url' => ['/network/task/list']];
$this->params['breadcrumbs'][] = ['label' => $page_name];

/** Check if out table exists */
if (!$model->out_table_exists) {

    $url = yii\helpers\Url::to(['/network/task/ajax-create-table', 'task_name' => $model->name]);

    /** @noinspection PhpUnhandledExceptionInspection */
    echo Alert::widget([
        'useSessionFlash' => false,
        'options' => [
            'timer' => null,
            'type'  => null,
            'html'  => true,
            'title' => "<i class='fa fa-exclamation-triangle'></i> ".Yii::t('app', 'Warning'),
            'text'  => Yii::t('network', 'Cannot find table with name <b>out_{0}</b> <br> Would you like to create table?', $model->name),
            'confirmButtonText' => Yii::t('app', 'Confirm'),
            'cancelButtonText'  => Yii::t('app', 'Cancel'),
            'closeOnConfirm'    => true,
            'showCancelButton'  => true,
            'animation'         => false
        ],
        'callback' => new \yii\web\JsExpression('function(isConfirm) { 
            if (isConfirm) {
                launchModalWindow("#form_modal", "'.$url.'");
            } else {
                $("#put_field").val(null).trigger("change");
            }
        }')
    ]);

}
?>
<?php Pjax::begin(['id' => 'task-form-pjax']); ?>
<div class="row">
    <div class="<?= ($action == 'add') ? 'col-md-8 col-md-offset-2' : 'col-md-8' ?>">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-plus"></i>
                    <?= $page_name . ($model->protected == 1 ? ' &mdash; ' . Yii::t('network', 'Permanent system task') : '') ?>
            </div>
            <?php
                /** @noinspection MissedFieldInspection */
                $form = ActiveForm::begin([
                    'id'                     => 'task_form',
                    'layout'                 => 'horizontal',
                    'enableClientValidation' => false,
                    'fieldConfig' => [
                        'horizontalCssClasses' => [
                            'label'   => 'col-sm-2',
                            'wrapper' => 'col-sm-10'
                        ],
                    ],
                ]);

                /** Store form action */
                echo Html::hiddenInput('action', $action, ['id' => 'form_action']);
            ?>
                <div class="box-body">
                    <?php
                        echo $form->field($model, 'name')->textInput([
                            'class'        => 'form-control',
                            'placeholder'  => FormHelper::label($model, 'name'),
                            'disabled'     => ($action == 'edit') ? true : false
                        ]);
                        echo $form->field($model, 'put')->dropDownList($destinations, [
                            'id'               => 'put_field',
                            'prompt'           => '',
                            'class'            => 'select2',
                            'data-placeholder' => Yii::t('network', 'Choose destinations'),
                            'disabled'         => ($model->name != 'backup' && $model->protected == 1) ? true : false
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
                        if($action == 'edit' && $model->protected == 0) {

                            $warning = (!empty($table_fields['default_fields']))
                                ? Yii::t('network', 'Warning! By deleting task {0} simultaneously will be deleted all task related data.', $model->name) . "\n"
                                : '';

                            echo Html::a(Yii::t('app', 'Delete'), ['delete', 'name' => $model->name], [
                                'class' => 'btn btn-sm btn-danger pull-left',
                                'data' => [
                                    'confirm' => $warning . Yii::t('network', 'Are you sure you want to delete task {0}?', $model->name),
                                    'method'  => 'post'
                                ],
                            ]);
                        }
                    ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['/network/task/list'], ['class' => 'btn btn-sm btn-default margin-r-5']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if($action == 'edit'): ?>
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <i class="fa fa-info-circle"></i>
                    <h3 class="box-title"><?= Yii::t('network', 'Information about table') ?></h3>
                    <?php if (!empty($table_fields['default_fields']) && $model->protected == 0): ?>
                        <div class="box-tools pull-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-wrench"></i>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <?php
                                            echo Html::a('<i class="fa fa-pencil-square-o"></i>' . Yii::t('network', 'Edit table'), [
                                                'ajax-edit-table', 'task_name' => $model->name
                                            ], [
                                                'data-toggle'   => 'modal',
                                                'data-target'   => '#form_modal',
                                            ]);

                                            echo Html::a('<i class="fa fa-trash-o"></i>' . Yii::t('network', 'Delete table'), 'javascript:;', [
                                                'class'            => 'delete-table',
                                                'style'            => 'color: #dd4b39',
                                                'data-confirm-txt' => Yii::t('network', 'Are you sure you want to delete table out_{0}?', $model->name),
                                                'data-ajax-url'    => Url::to(['ajax-delete-table', 'task_name' => $model->name])
                                            ]);
                                        ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (empty($table_fields['default_fields'])): ?>
                    <div class="box-body">
                        <div class="callout callout-info" style="margin-bottom: 0;">
                            <p><?= Yii::t('network', 'Table for task <b>{0}</b> is not found in database.', $model->name) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="box-body no-padding">
                        <table class="table table-bordered">
                            <tr>
                                <th colspan="3"><?= Yii::t('network', 'Table name: out_{0}', $model->name) ?></th>
                            </tr>
                            <?php foreach ($table_fields as $type => $fields): ?>
                                <?php if (!empty($fields)): ?>
                                    <?php $count = count($fields) + 1;?>
                                    <tr>
                                        <th style="vertical-align: middle;" width="50%" rowspan="<?= $count ?>">
                                            <?php
                                                switch ($type) {
                                                    case 'default_fields': echo Yii::t('network', 'Default table fields'); break;
                                                    case 'custom_fields':  echo Yii::t('network', 'Custom table fields');  break;
                                                }
                                            ?>
                                        </th>
                                    </tr>
                                    <?php foreach ($fields as $field): ?>
                                        <tr>
                                            <td><?= $field->name ?></td>
                                            <td>
                                                <?= $field->dbType ?>
                                                <div class="pull-right">
                                                    <?php if($field->isPrimaryKey === true) echo '<i class="fa fa-key"></i>'; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php Pjax::end(); ?>

<!-- form modal -->
<div id="form_modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= Yii::t('app', 'Wait...') ?></h4>
            </div>
            <div class="modal-body">
                <span style="margin-left: 24%;"><?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
            </div>
        </div>
    </div>
</div>
