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
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\Pjax;

/**
 * @var $this           yii\web\View
 * @var $origin_info    array
 * @var $environment    bool
 * @var $message        string
 * @var $database       string
 * @var $giturl         string
 * @var $service        array|null
 */
app\assets\UpdateAsset::register($this);

$this->title = Yii::t('update', 'cBackup update');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'System' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('update', 'cBackup update')];

/** @var  $options */
$options = [
    'lock_ui_url'     => Url::to(['ajax-lock-system']),
    'update_core_url' => Url::to(['ajax-update-core']),
    'update_db_url'   => Url::to(['ajax-update-database']),
    'cleanup_url'     => Url::to(['ajax-cleanup'])
];

/** Register update urls */
$this->registerJs(/** @lang JavaScript */" var update_urls = " . Json::htmlEncode($options) . ";", View::POS_HEAD);

/** @noinspection JSUnusedLocalSymbols */
$this->registerJs(/** @lang JavaScript */"
    
    var path = $('#path');
    var span = $('.path');
    span.text(path.val());
    
    path.keyup(function() {
        span.text(path.val());
    });
    
    $('#init-update').click(function(e) {
        $(this).button('loading');
    });
    
", View::POS_READY);

/** @noinspection CssUnusedSymbol */
$this->registerCss(/** @lang CSS */ '
    .nav-tabs-custom > .nav-tabs > li.error {
        border-top-color: #f56954;
    }
');
?>

<div class="row">
    <?php if (!$environment): ?>
        <div class="col-md-12">
            <?php if(!Y::param('isolated')): ?>
            <div class="callout callout-danger">
                <?php
                    echo $message;
                    if( stripos($message, 'not a git repo') !== false ) {
                        echo Html::a(Yii::t('update', 'Initialize updater'), ['update/init'], [
                            'class' => 'btn btn-primary',
                            'id'    => 'init-update',
                            'style' => 'text-decoration: none; position: absolute; top: 8px; right: 25px; '
                        ]);
                    }
                ?>
            </div>
            <?php endif; ?>
            <div class="box box-default">
                <div class="box-header with-border">
                    <i class="fa fa-hand-grab-o"></i>
                    <h3 class="box-title"><?= Yii::t('update', 'Manual update') ?></h3>
                </div>
                <div class="box-body">
                    <?php echo $this->render('_manual', ['giturl' => $giturl, 'database' => $database]); ?>
                </div>
            </div>
        </div>
    <?php elseif(Y::param('isolated')): ?>
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <i class="fa fa-hand-grab-o"></i>
                    <h3 class="box-title"><?= Yii::t('update', 'Manual update') ?></h3>
                </div>
                <div class="box-body">
                    <?php echo $this->render('_manual', ['giturl' => $giturl, 'database' => $database]); ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#live_update" data-toggle="tab" aria-expanded="true">
                            <i class="fa fa-globe"></i> <?= Yii::t('update', 'Live update') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#update_via_file" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-hand-grab-o"></i> <?= Yii::t('update', 'Manual update') ?>
                        </a>
                    </li>
                    <li class="hide error">
                        <a href="#update_errors" id="update_errors_tab" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-exclamation-triangle"></i> <?= Yii::t('network', 'Update errors') ?>
                        </a>
                    </li>
                    <li class="pull-right">
                        <div class="tab-links tasks-schedule-links">
                            <?php
                                echo Html::a('<i class="fa fa-refresh"></i> ' . Yii::t('update', 'Check for updates'), 'javascript:void(0);', [
                                    'id'             => 'check_for_updates',
                                    'class'          => 'btn btn-sm bg-light-blue ladda-button',
                                    'data-check-url' => Url::to(['ajax-check-updates']),
                                    'data-style'     => 'zoom-in'
                                ]);
                            ?>
                        </div>
                    </li>
                </ul>
                <div class="tab-content no-padding">
                    <div class="tab-pane active" id="live_update">
                        <?php Pjax::begin(['id' => 'live-update-pjax', 'enablePushState' => false]); ?>
                            <div class="row">
                                <?php if (version_compare($origin_info['version'], Yii::$app->version, '>')): ?>
                                    <?php if (!$service['init']): ?>
                                        <div class="col-md-12">
                                            <div class="callout callout-warning" style="margin: 10px 5px 0 5px;">
                                                <p><i class="fa fa-warning"></i> <?= Yii::t('update', 'Can not determine service status. Please make sure that Java service is not running before updating cBackup.') ?></p>
                                            </div>
                                        </div>
                                    <?php elseif ($service['init'] && $service['status']): ?>
                                        <div class="col-md-12">
                                            <div class="callout callout-warning" style="margin: 10px 5px 0 5px;">
                                                <p><i class="fa fa-warning"></i> <?= Yii::t('update', 'Java service is running. Please shut down Java service and reload this page before updating cBackup.') ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-12">
                                        <div class="callout callout-info" style="margin: 10px 5px 0 5px;">
                                            <p><?= Yii::t('update', '<b>New update was found.</b> Note that after update, all files which were changed manually will be deleted. Make sure you save your file changes before update.') ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="margin-top: 10px">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <td colspan="2">
                                                        <span class="text-bold"><?= Yii::t('update', 'Update info') ?></span>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="width: 45%"><?= Yii::t('update', 'Installed cBackup version') ?></td>
                                                    <td><?= Yii::$app->version ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= Yii::t('update', 'Latest cBackup version') ?></td>
                                                    <td><?= $origin_info['version'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= Yii::t('update', 'Update source') ?></td>
                                                    <td><?= Yii::t('config', 'Git repository') ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= Yii::t('update', 'Additional information') ?></td>
                                                    <td><?= $origin_info['message'] ?></td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2">
                                                        <div class="pull-right">
                                                            <?php
                                                               echo Html::button(Yii::t('update', 'Install updates'), [
                                                                   'id'         => 'install_updates',
                                                                   'class'      => 'btn btn-sm bg-light-blue ladda-button',
                                                                   'disabled'   => ($service['init'] && $service['status']) ? true : false,
                                                                   'data-style' => 'expand-left',
                                                                   'data-pjax'  => '0'
                                                               ])
                                                            ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="col-md-6" style="margin-top: 10px">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <td colspan="2">
                                                        <span class="text-bold"><?= Yii::t('update', 'Update process') ?></span>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?= Yii::t('update', 'Entering maintenance mode') ?></td>
                                                    <td class="text-center" style="width: 30%">
                                                        <i id="lock_ui" class="fa fa-stop-circle-o text-warning"></i>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?= Yii::t('update', 'Updating web core files') ?></td>
                                                    <td class="text-center">
                                                        <i id="core_update" class="fa fa-stop-circle-o text-warning"></i>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?= Yii::t('update', 'Updating database') ?></td>
                                                    <td class="text-center">
                                                        <i id="db_update" class="fa fa-stop-circle-o text-warning"></i>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?= Yii::t('update', 'Cache and assets cleanup') ?></td>
                                                    <td class="text-center">
                                                        <i id="cleanup" class="fa fa-stop-circle-o text-warning"></i>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?= Yii::t('update', 'Finishing update process') ?></td>
                                                    <td class="text-center">
                                                        <i id="finish_update" class="fa fa-stop-circle-o text-warning"></i>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="col-md-12">
                                        <div class="callout callout-info" style="margin: 10px 5px 10px 5px;">
                                            <p><?= Yii::t('update', 'No new updates found. Your system is up-to-date. System version <b>{0}</b>', Yii::$app->version) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php Pjax::end(); ?>
                    </div>
                    <div class="tab-pane" id="update_via_file" style="padding: 1em 1em 4px 0;">
                        <?php echo $this->render('_manual', ['giturl' => $giturl, 'database' => $database]); ?>
                    </div>
                    <div class="tab-pane" id="update_errors" style="padding: 0 10px 10px 10px;">
                        <div id="error_lock_ui" style="display: none;">
                            <?= Yii::t('update', 'Lock UI errors') ?>
                            <?= Html::tag('pre', '', ['id' => 'error_msg_lock_ui']) ?>
                        </div>
                        <div id="error_core_update" style="display: none;">
                            <?= Yii::t('update', 'Web core update errors') ?>
                            <?= Html::tag('pre', '', ['id' => 'error_msg_core_update']) ?>
                        </div>
                        <div id="error_db_update" style="display: none;">
                            <h4><?= Yii::t('update', 'Database update errors') ?></h4>
                            <?= Html::tag('pre', '', ['id' => 'error_msg_db_update']) ?>
                        </div>
                        <div id="error_cleanup" style="display: none;">
                            <h4><?= Yii::t('update', 'Resources cleanup errors') ?></h4>
                            <?= Html::tag('pre', '', ['id' => 'error_msg_cleanup']) ?>
                        </div>
                        <div id="error_finish_update" style="display: none;">
                            <h4><?= Yii::t('update', 'Finish update errors') ?></h4>
                            <?= Html::tag('pre', '', ['id' => 'error_msg_finish_update']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
