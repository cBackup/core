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
 * @var $searchModel   app\models\search\CustomNodeSearch
 * @var $data          array
 * @var $devices_list  array
 * @var $networks_list array
 * @var $tasks_list    array
 */
app\assets\Select2Asset::register($this);
app\assets\LaddaAsset::register($this);

$this->title = Yii::t('network', 'Assign task [{0}]', $searchModel->task_name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Task assignments')];

$this->registerJs(
    /** @lang JavaScript */
    "
        /** Select2 init */
        $('.select2').select2({
           minimumResultsForSearch: -1,
           width : '100%'
       });

       /** Select2 with clear init */
       $('.select2-clear').select2({
           minimumResultsForSearch: -1,
           allowClear: true,
           width : '100%'
       });
       
       /** Select2 with search */
       $('.select2-search').select2({
           width : '100%'
       });
       
       /** Select with minimum and clear init */
       $('.select2-min').select2({
           minimumInputLength: 4,
           allowClear: true,
           width: '100%'
       });
       
       /** Node search form submit and reload gridview */
        $('.node-search-form form').submit(function(e) {
            e.stopImmediatePropagation(); // Prevent double submit
            gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
            $.pjax.reload({container:'#node-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
            return false;
        });
        
        /** Init JS on document:ready and pjax:end */
        $(document).on('ready pjax:end', function() {
            
            /** Init iCheck */
            $('.set-node-box').iCheck({ 
                checkboxClass: 'icheckbox_minimal-green'
            }).on('ifChanged', function (event) {
                $(event.target).trigger('change');
            });
            
            /** Check/uncheck all nodes on page */
            $('#check_all_box').change(function() {
                if ($('#check_all_box').is(':checked')) {
                    $('.check_node_box').prop('checked', true).iCheck('update');
                } else {
                    $('.check_node_box').prop('checked', false).iCheck('update');
                }
            });

            /** Check/uncheck check all box based on node checked values */
            $('.check_node_box').change(function() {
                var input = $('.check_node_box');
                if(input.length === input.filter(':checked').length){
                    $('#check_all_box').prop('checked', true).iCheck('update');
                } else {
                    $('#check_all_box').prop('checked', false).iCheck('update');
                }
            }).change();
            
        });
        
        /** Submit assign form */
        $(document).on('submit', '#assign_form', function (e) {
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
                    $.pjax.reload({container: '#node-pjax', url: $(location).attr('href'), timeout: 10000});
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
                        <p><?= Yii::t('network', 'Sorry but you can\'t assign nodes to task: <b>{0}</b>!', $searchModel->task_name) ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="box-header with-border">
                    <i class="fa fa-filter"></i><h3 class="box-title"><?= Yii::t('network', 'Filter records')?></h3>
                </div>
                <div class="box-body no-padding">
                    <?php
                        echo $this->render('_adv_search', [
                            'model'         => $searchModel,
                            'devices_list'  => $devices_list,
                            'networks_list' => $networks_list
                        ]);
                    ?>
                <div class="box-inner-separator">
                    <div class="box-header">
                        <i class="fa fa-list"></i><h3 class="box-title"><?= Yii::t('node', 'Node list')?></h3>
                    </div>
                </div>
                <?php Pjax::begin(['id' => 'node-pjax']); ?>
                    <?php if (empty($data)): ?>
                        <div class="callout callout-info" style="margin: 15px;">
                            <p><?= Yii::t('network', 'Nothing to show here') ?></p>
                        </div>
                    <?php else: ?>
                        <?php $form = ActiveForm::begin(['id' => 'assign_form', 'action' => ['ajax-assign-nodes'], 'enableClientValidation' => false]); ?>
                            <table class="table table-bordered" style="margin-bottom: 0">
                                <thead>
                                    <tr>
                                        <th width="3%">
                                            <?= Html::checkbox('check_all', false, ['id' => 'check_all_box', 'class' => 'set-node-box']) ?>
                                        </th>
                                        <th width="15%"><?= Yii::t('network', 'Network')?></th>
                                        <th width="30%"><?= Yii::t('network', 'Hostname')?></th>
                                        <th width="15%"><?= Yii::t('network', 'IP address')?></th>
                                        <th width="30%"><?= Yii::t('network', 'Device')?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $key => $node): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                    echo Html::hiddenInput("NodeTasks[{$node['id']}][task_name]", $searchModel->task_name);
                                                    echo Html::hiddenInput("NodeTasks[{$node['id']}][set_node]", '0');
                                                    echo Html::checkbox("NodeTasks[{$node['id']}][set_node]", $node['node_has_task'],[
                                                       'class' => 'set-node-box check_node_box'
                                                    ]);
                                                ?>
                                            </td>
                                            <td><?= (!empty($node['network']['network'])) ? $node['network']['network'] : Yii::t('yii', '(not set)') ?></td>
                                            <td><?= (!empty($node['hostname'])) ? $node['hostname'] : Yii::t('yii', '(not set)') ?></td>
                                            <td><?= $node['ip'] ?></td>
                                            <td><?= "{$node['device']['vendor']} - {$node['device']['model']}" ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="5">
                                            <?php
                                                echo Html::submitButton(Yii::t('network', 'Assign nodes'), [
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
