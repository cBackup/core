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

/**
 * @var $scheduler_status bool
 * @var $service_status   bool
 * @var $java_server      array
 * @var $java_scheduler   array
 */

echo Html::script(
    /** @lang JavaScript */
    "
        /** Run action on button click via Ajax */
        $('.run_action').click(function() {
            
            var btn           = $(this);
            var ajax_url      = btn.data('ajax-url');
            var services_btns = $('#services_table').find('button');
            var widget        = $('#widget_content');
            
            //noinspection JSUnusedGlobalSymbols
            $.ajax({
                type: 'POST',
                url: ajax_url,
                beforeSend: function() {
                    btn.button('loading');
                    services_btns.prop('disabled',  true);
                },
                success: function (data) {
                    if (isJson(data)) {
                        var json = $.parseJSON(data);
                        
                        if (json['status'] === 'success') {
                            loadServiceWidget();
                        } else {
                            widget.html('<div class=\"callout callout-' + json['status'] + '\" style=\"margin: 10px;\"><p>' + json['msg'] + '</p></div>');
                        }
                    }
                },
                error: function (data) {
                    widget.html('<div class=\"callout callout-danger\" style=\"margin: 10px\">' + data.responseText +'</div>')
                }
            }).always(function() {
                 btn.button('reset');
            });
        });
    ", ['type' => 'text/javascript']
);

?>

<table class="table table-no-outer no-margin" id="services_table">
    <tr class="<?= (!$java_server['init']) ? 'danger text-muted' : '' ?>">
        <td width="70%">
            <?php
                $class = ($service_status) ? 'text-success' : 'text-danger';
                if (!$java_server['init']) {
                    echo Html::tag('span', '<i class="fa fa-warning"></i>', [
                        'class'          => 'text-danger',
                        'data-toggle'    => 'tooltip',
                        'data-placement' => 'left',
                        'data-html'      => 'true',
                        'title'          => $java_server['error']
                    ]);
                }
            ?>
            <span class="label"><i class="fa fa-circle <?= $class ?>"></i></span><?= Yii::t('app', 'Java service is ') ?>
            <span class="<?= $class ?>"><?= ($service_status) ? Yii::t('app', 'running') : Yii::t('app', 'not running') ?></span>
        </td>
        <td>
            <div class="text-center">
                <div class="btn-group">
                    <?php
                        echo Html::button('<i class="fa fa-play"></i>', [
                            'class'             => 'btn btn-success btn-flat btn-xs margin-r-5 run_action',
                            'title'             => Yii::t('app', 'Start'),
                            'disabled'          => (!$java_server['init'] || $service_status),
                            'data-ajax-url'     => Url::to(['/site/ajax-run-service', 'mode' => 0]),
                            'data-loading-text' => '<i class="fa fa-spinner fa-spin"></i>'
                        ]);
                        echo Html::button('<i class="fa fa-stop"></i>', [
                            'class'             => 'btn btn-danger btn-flat btn-xs margin-r-5 run_action',
                            'title'             => Yii::t('app', 'Stop'),
                            'disabled'          => !$service_status,
                            'data-ajax-url'     => Url::to(['/site/ajax-run-service', 'mode' => 1]),
                            'data-loading-text' => '<i class="fa fa-spinner fa-spin"></i>'
                        ]);
                        echo Html::button('<i class="fa fa-refresh"></i>', [
                            'class'             => 'btn btn-warning btn-flat btn-xs margin-r-5 run_action',
                            'title'             => Yii::t('app', 'Restart'),
                            'disabled'          => !$service_status,
                            'data-ajax-url'     => Url::to(['/site/ajax-run-service', 'mode' => 2]),
                            'data-loading-text' => '<i class="fa fa-spinner fa-spin"></i>'
                        ]);
                    ?>
                </div>
            </div>
        </td>
    </tr>
    <tr class="<?= (!$java_scheduler['init']) ? 'danger text-muted' : '' ?>">
        <td width="70%">
            <?php
                $class = ($scheduler_status) ? 'text-success' : 'text-danger';
                if (!$java_scheduler['init']) {
                    echo Html::tag('span', '<i class="fa fa-warning"></i>', [
                        'class'          => 'text-danger',
                        'data-toggle'    => 'tooltip',
                        'data-placement' => 'left',
                        'data-html'      => 'true',
                        'title'          => $java_scheduler['error']
                    ]);
                }
            ?>
            <span class="label"><i class="fa fa-circle <?= $class ?>"></i></span><?= Yii::t('app', 'Java scheduler is ') ?>
            <span class="<?= $class ?>"><?= ($scheduler_status) ? Yii::t('app', 'running') : Yii::t('app', 'not running') ?></span>
        </td>
        <td>
            <div class="text-center">
                <div class="btn-group">
                    <?php
                        echo Html::button('<i class="fa fa-play"></i>', [
                            'class'             => 'btn btn-success btn-flat btn-xs margin-r-5 run_action',
                            'title'             => Yii::t('app', 'Start'),
                            'disabled'          => (!$java_scheduler['init'] || (!$service_status && $java_server['init']) || $scheduler_status),
                            'data-ajax-url'     => Url::to(['network/schedule/ajax-scheduler', 'mode' => 0]),
                            'data-loading-text' => '<i class="fa fa-spinner fa-spin"></i>'
                        ]);
                        echo Html::button('<i class="fa fa-stop"></i>', [
                            'class'             => 'btn btn-danger btn-flat btn-xs margin-r-5 run_action',
                            'title'             => Yii::t('app', 'Stop'),
                            'disabled'          => !$scheduler_status,
                            'data-ajax-url'     => Url::to(['network/schedule/ajax-scheduler', 'mode' => 1]),
                            'data-loading-text' => '<i class="fa fa-spinner fa-spin"></i>'
                        ]);
                        echo Html::button('<i class="fa fa-refresh"></i>', [
                            'class'             => 'btn btn-warning btn-flat btn-xs margin-r-5 run_action',
                            'title'             => Yii::t('app', 'Restart'),
                            'disabled'          => !$scheduler_status,
                            'data-ajax-url'     => Url::to(['network/schedule/ajax-scheduler', 'mode' => 2]),
                            'data-loading-text' => '<i class="fa fa-spinner fa-spin"></i>'
                        ]);
                    ?>
                </div>
            </div>
        </td>
    </tr>
</table>
