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
use yii\widgets\Pjax;

yii\grid\GridViewAsset::register($this);
app\assets\LaddaAsset::register($this);

/**
 * @var $this   yii\web\View
 * @var $types  array
 * @var $mtime  string
 */
$this->title = Yii::t('app', 'Content delivery system');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'System')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Content delivery system')];

/** Register JS */
$this->registerJs(
    /** @lang JavaScript */
    "    
        /** Load grid view via Ajax on button click */
        $(document).on('click', '.load-grid-view', function () {
        
            var link    = $(this);
            var loading = $('.loading');
            var url     = link.data('ajax-url');
            
            //noinspection JSUnusedGlobalSymbols
            $.ajax({
                type: 'POST',
                url: url,
                beforeSend: function() {
                    $('#content-pjax').hide();
                    loading.show();
                },
                success: function (data) {
                    $('#content-pjax').html(data).show();
                    $('#content_select').find('li').removeClass('active');
                    $('#' + link.context.id).parent().addClass('active');
                    loading.hide();
                },
                error: function (data) {
                    $('#content-pjax').html('' +
                        ' <div class=\"callout callout-danger\" style=\"margin: 10px\">' +
                            data.responseText + '' +
                        '</div>'
                    ).show();
                    loading.hide();
                }
            });
        
        });
        
        /** Filter grid data */
        $(document).on('keyup.yiiGridView', '.table .filters input', function(){
            var table = $(this).closest('table');
            var table_id = '#' + table.parent().attr('id');
            var ajax_url = table.data('ajax-url');
            
            $(table_id).yiiGridView({
                filterUrl: ajax_url,
                filterSelector: table_id + '-filters input, select '
            });
        });
        
        /** Install content via Ajax */
        $(document).on('click', '.ajaxInstallContent', function() {
           
            var link       = $(this);
            var ajax_url   = link.data('ajax-url');
            var update_url = link.closest('table').find(' th > a').eq(0).attr('href').replace(/&sort.*?(?=&|$)/, '');
            var link_icon  = link.find('i').attr('class').split(' ')[1];
            
            //noinspection JSUnusedGlobalSymbols
            $.ajax({
                type: 'POST',
                url: ajax_url,
                beforeSend : function() {
                    link.find('i').switchClass(link_icon, 'fa-cog fa-spin', 0);
                    link.closest('table').find('a').addClass('disabled')
                },
                success: function (data) {
                    $.pjax.reload({container: '#content-pjax', url: update_url, replace: false, timeout: 10000});
                    showStatus(data);
                },
                error: function (data) {
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            }).always(function () {
                link.find('i').switchClass('fa-cog fa-spin', link_icon, 0);
                link.closest('table').find('a').removeClass('disabled');
            });
            
        });
        
        /** Update content via Ajax */
        $(document).on('click', '#update_content', function() {
        
            var ajax_url    = $(this).data('update-url');
            var btn_lock    = Ladda.create(document.querySelector('#update_content'));
            
            //noinspection JSUnusedGlobalSymbols
            $.ajax({
                type: 'POST',
                url: ajax_url,
                beforeSend: function() {
                    btn_lock.start();
                },
                success: function () {
                    location.reload();
                },
                error: function (data) {
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            }).always(function () {
                btn_lock.stop();
            });
        
        });
        
    "
);

/** @noinspection CssUnusedSymbol */
$this->registerCss(/** @lang CSS */'
    code {background-color: transparent;}
    
    table.content-table {
        table-layout: fixed;
    }
');
?>

<div class="row">
    <?php if (!empty($types)): ?>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('app', 'Categories')?></h3>
                </div>
                <div class="box-body no-padding">
                    <ul id="content_select" class="nav nav-pills nav-stacked">
                        <?php foreach ($types as $type): ?>
                            <li>
                                <?php
                                    echo Html::a(Yii::t('app', ucfirst($type)), 'javascript:void(0);', [
                                        'id'            => $type,
                                        'class'         => 'load-grid-view',
                                        'data-ajax-url' => Url::to(['ajax-render-grid', 'type' => $type])
                                    ]);
                                ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php if($mtime): ?>
            <div class="box box-default">
                <div class="box-body">
                    <?= Yii::t('app', 'Last update: {0}', $mtime) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="col-md-<?= (!empty($types)) ? '9' : '12' ?>">
        <div class="box box-primary">
            <div class="box-header with-border">
                <i class="fa fa-table"></i><h3 class="box-title box-title-align"><?= Yii::t('app', 'Available content') ?></h3>
                <div class="pull-right">
                    <?php
                        echo Html::a('<i class="fa fa-refresh"></i> ' . Yii::t('app', 'Update data'), 'javascript:void(0);', [
                            'id'              => 'update_content',
                            'class'           => 'btn btn-sm bg-light-blue ladda-button',
                            'data-update-url' => Url::to(['ajax-update-content']),
                            'data-style'      => 'expand-right'
                        ]);
                    ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <div class="loading" style="display: none; margin: 15px 0 15px 0;">
                    <span style="margin-left: 24%;">
                        <?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?>
                    </span>
                </div>
                <?php Pjax::begin(['id' => 'content-pjax', 'enablePushState' => false]); ?>
                    <div class="col-md-12">
                        <div class="callout callout-info" style="margin: 10px 0 10px 0;">
                            <?php if (!empty($types)): ?>
                                <p><?= Yii::t('app', 'Please select category') ?></p>
                            <?php else: ?>
                                <p><?= Yii::t('app', 'No content found. Please press <b>"Update data"</b>') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
