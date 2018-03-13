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
use yii\widgets\Pjax;

/**
 * @var $this          yii\web\View
 * @var $form          yii\bootstrap\ActiveForm
 * @var $dataProvider  yii\data\ArrayDataProvider
 * @var $searchModel   app\models\search\CustomDeviceSearch
 * @var $data          array
 * @var $tasks_list    array
 * @var $vendors_list  array
 */
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);

$this->title = Yii::t('network', 'Assign task [{0}] with worker [{1}]', [$searchModel->task_name, $searchModel->worker_name]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Task assignments')];

$this->registerJs(
/** @lang JavaScript */
    "
        /** Select2 init */
        $('.select2').select2({
           minimumResultsForSearch: -1
        });

        /** Submit form on filter change */
        $('.filter-inputs').change(function() {
              $('#device_assign_search_form').submit();
        });
        
        /** Process submit request */
        $('#device_assign_search_form').submit(function(e) {
            e.stopImmediatePropagation(); // Prevent double submit
            boxSpinner($('body'), $(this));
            $.pjax.reload({container:'#device-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
            return false;
        });

        /** Prevent tooltip from repeated showing after redirect */
        $(window).focus(function() {
            $('a').focus(function() {
                this.blur();
            });
        });
        
        /** Init JS on document:ready and pjax:end */
        $(document).on('ready pjax:end', function() {
            
            /** Init iCheck */
            $('.set-device-box').iCheck({ 
                checkboxClass: 'icheckbox_minimal-green'
            }).on('ifChanged', function (event) {
                $(event.target).trigger('change');
            });
            
            /** Check/uncheck vendor specific check all box based on device checked values */
            $('.device-check-box').change(function() {
                
                var box_class = this.classList[0];
                var vendor    = box_class.split('_')[0];
                var input     = $('.' + box_class);
                
                if(input.length === input.filter(':checked').length){
                    $('#' + vendor + '_check_all_box').prop('checked', true).iCheck('update');
                } else {
                    $('#' + vendor + '_check_all_box').prop('checked', false).iCheck('update');
                }
                
                /** Disable check all box if at least one vendor specific device box is disabled */
                if (input.is(':disabled')) {
                    $('#' + vendor + '_check_all_box').prop('disabled', true).iCheck('update');
                }
                
            }).change();
            
            /** Check/uncheck all vendor specific devices */
            $('.check-all-box').change(function() {
                var box_name = this.id.split('_')[0];
                if ($('#' + box_name + '_check_all_box').is(':checked')) {
                    $('.' + box_name + '_check_box').prop('checked', true).iCheck('update');
                } else {
                    $('.' + box_name + '_check_box').prop('checked', false).iCheck('update');
                }
            });
            
        });
        
        /** Submit assign form */
        $(document).on('submit', '#device_assign_form', function (e) {
            e.stopImmediatePropagation(); // Prevent double submit
            
            var form     = $(this);
            var btn_lock = Ladda.create(document.querySelector('#assign_btn'));
            
            //noinspection JSUnusedGlobalSymbols
            /** Submit form */
            $.ajax({
                url    : form.attr('action'),
                type   : 'post',
                data   : form.serialize(),
                beforeSend: function() {
                    btn_lock.start();
                },
                success: function (data) {
                    if (isJson(data)) {
                        showStatus(data);
                    } else {
                        toastr.warning(data, '', {timeOut: 0, closeButton: true});
                    }
                    $.pjax.reload({container: '#device-pjax', url: $(location).attr('href'), timeout: 10000});
                },
                error : function (data) {
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            }).always(function(){
                btn_lock.stop();
            });
            
            return false;
        });
        
    "
);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <?php if (!in_array($searchModel->task_name, $tasks_list)): ?>
                <div class="box-header with-border">
                    <i class="icon fa fa-warning"></i><h3 class="box-title"><?= Yii::t('app', 'Sorry...')?></h3>
                </div>
                <div class="box-body no-padding">
                    <div class="callout callout-warning" style="margin: 15px;">
                        <p><?= Yii::t('network', 'Sorry but you can\'t assign devices to task: <b>{0}</b>!', $searchModel->task_name) ?></p>
                    </div>
                </div>
            <?php else: ?>

                <div class="box-header with-border">
                    <i class="fa fa-list"></i><h3 class="box-title"><?= Yii::t('network', 'Device list')?></h3>
                    <div class="box-tools pull-right">
                        <?php
                             $form = ActiveForm::begin([
                                 'id'      => 'device_assign_search_form',
                                 'options' => ['class' => 'form-inline dashboard-search'],
                                 'action'  => ['adv-device-assign'],
                                 'method'  => 'get',
                                 'enableClientValidation' => false
                             ]);

                             echo Html::hiddenInput('task_name', $searchModel->task_name);
                             echo Html::hiddenInput('worker_id', $searchModel->worker_id);

                             echo $form->field($searchModel, 'vendor')->dropDownList($vendors_list, [
                                 'class'  => 'select2 filter-inputs',
                                 'prompt' => Yii::t('network', 'All vendors'),
                                 'style'  => 'width: 165px'
                             ])->label(false);

                             echo Html::tag('span', '', ['class'=> 'margin-r-5']);

                             echo $form->field($searchModel, 'page_size')->dropDownList(\Y::param('page_size'), [
                                 'class' => 'select2 filter-inputs',
                                 'style' => 'width: 60px'
                             ])->label(false);

                            ActiveForm::end();
                        ?>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <?php Pjax::begin(['id' => 'device-pjax']); ?>
                        <?php if (empty($data)): ?>
                            <div class="callout callout-info" style="margin: 15px;">
                                <p><?= Yii::t('network', 'Nothing to show here') ?></p>
                            </div>
                        <?php else: ?>
                            <?php $form = ActiveForm::begin(['id' => 'device_assign_form', 'action' => ['ajax-assign-devices'], 'enableClientValidation' => false]); ?>
                            <table class="table table-bordered" style="margin-bottom: 0">
                                <thead>
                                    <tr>
                                        <th width="3%"></th>
                                        <th width="25%"><?= Yii::t('network', 'Model')?></th>
                                        <th width="72%"><?= Yii::t('network', 'Auth Sequence')?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $vendor => $devices):?>
                                        <tr style="background-color: #f9f9f9; font-weight: 600;">
                                            <td>
                                                <?php
                                                    echo Html::checkbox('', false, [
                                                        'id'    => $vendor . '_check_all_box',
                                                        'class' => 'set-device-box check-all-box'
                                                    ]);
                                                ?>
                                            </td>
                                            <td colspan="2"><?= Yii::t('network', 'Vendor') . " :: {$vendor}" ?></td>
                                        </tr>
                                        <?php foreach ($devices as $device): ?>
                                            <tr>
                                                <td>
                                                    <?php
                                                        echo Html::hiddenInput("DeviceTasks[{$device['id']}][task_name]", $searchModel->task_name);
                                                        echo Html::hiddenInput("DeviceTasks[{$device['id']}][worker_id]", $searchModel->worker_id);
                                                        echo Html::hiddenInput("DeviceTasks[{$device['id']}][set_device]", '0');
                                                        echo Html::checkbox("DeviceTasks[{$device['id']}][set_device]", $device['has_selected_task'], [
                                                            'class'    => $vendor . '_check_box set-device-box device-check-box',
                                                            'disabled' => !empty($device['other_device_task']) ? true : false
                                                        ]);
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        $warning = '';
                                                        if (!empty($device['other_device_task'])) {
                                                            $message = Yii::t('network',
                                                                'Device is already assigned <br>task: {0}<br>worker: {1}', [
                                                                    $device['other_device_task']['task_name'],
                                                                    $device['other_device_task']['worker_name']
                                                                ]
                                                            );
                                                            $warning = Html::a('<i class="fa fa-warning"></i>', [
                                                                'adv-device-assign',
                                                                'task_name' => $device['other_device_task']['task_name'],
                                                                'worker_id' => $device['other_device_task']['worker_id']
                                                            ], [
                                                                'class'          => 'margin-r-5 text-danger',
                                                                'data-pjax'      => '0',
                                                                'data-toggle'    => 'tooltip',
                                                                'data-placement' => 'top',
                                                                'data-html'      => 'true',
                                                                'title'          => Html::tag('div', $message, ['style' => 'text-align:left; white-space:pre; max-width:none;']),
                                                                'target'         => '_blank'
                                                            ]);
                                                        }
                                                        echo $warning . $device['model'];
                                                    ?>
                                                </td>
                                                <td><?= $device['auth_template_name'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="5">
                                            <?php
                                                echo Html::submitButton(Yii::t('network', 'Assign devices'), [
                                                    'id'         => 'assign_btn',
                                                    'class'      => 'btn btn-sm btn-primary ladda-button',
                                                    'data-style' => 'zoom-in'
                                                ]);
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="box-footer">
                                <div class="col-md-3">
                                    <div class="summary">
                                        <?= Yii::t('network', 'Showing <b>{0}</b> of <b>{1}</b>.', [$dataProvider->getCount(), $dataProvider->getTotalCount()]) ?>
                                    </div>
                                </div>
                                <div class="box-tools pull-right">
                                    <?php
                                        /** @noinspection PhpUnhandledExceptionInspection */
                                        echo \yii\widgets\LinkPager::widget([
                                            'pagination' => $dataProvider->pagination,
                                            'options' => [
                                                'class' => 'pagination pagination-sm inline'
                                            ]
                                        ]);
                                    ?>
                                </div>
                            </div>
                            <?php ActiveForm::end(); ?>
                        <?php endif; ?>
                    <?php Pjax::end(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
