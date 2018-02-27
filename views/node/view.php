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
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\widgets\Pjax;
use app\models\Setting;
use app\helpers\StringHelper;
use app\helpers\GridHelper;
use app\models\Exclusion;

/**
 * @var $this           yii\web\View
 * @var $data           app\models\Node
 * @var $credential     app\models\Credential
 * @var $task_info      app\models\Task
 * @var $int_provider   yii\data\ArrayDataProvider
 * @var $commit_log     array
 * @var $templates      array
 * @var $plugins        object
 * @var $exclusion      boolean
 * @var $networks       array
 * @var $credentials    array
 */
app\assets\NodeAsset::register($this);
app\assets\DataTablesBootstrapAsset::register($this);
app\assets\ScrollingTabsAsset::register($this);

$title       = empty($data->hostname) ? $data->ip : $data->hostname;
$empty_task  = [false, false]; // empty array for defining empty task tab
$this->title = Yii::t('node', 'Node') . ' ' . $title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('node', 'Nodes'), 'url' => ['/node']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-6">
        <div class="box">
            <?php Pjax::begin(['id' => 'node-info-pjax', 'enablePushState' => false]); ?>
                <div class="box-header with-border">
                    <i class="fa fa-globe"></i>
                    <h3 class="box-title"><?= Yii::t('node', 'Node information') ?></h3>
                    <div class="box-tools pull-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <?= Yii::t('app', 'Actions') ?> <i class="fa fa-angle-down"></i>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <?=
                                        Html::a(Yii::t('node', 'Backup now'), 'javascript:void(0);', [
                                            'id'            => 'run_node_action',
                                            'data-ajax-url' => Url::to(['/node/ajax-backup-node',
                                                'node_id'   => $data->id,
                                                'device_id' => $data->device_id
                                            ])
                                        ])
                                    ?>
                                </li>
                                <li>
                                    <?php
                                        $protect_msg = ($data->protected == 0) ? Yii::t('node', 'Protect node') : Yii::t('node', 'Unprotect node');
                                        echo Html::a($protect_msg, 'javascript:void(0);', [
                                            'id'               => 'run_node_action',
                                            'data-pjax-reload' => 'true',
                                            'data-ajax-url'    => Url::to(['/node/ajax-protect-node',
                                                'node_id'        => $data->id,
                                                'protect_status' => ($data->protected == 1) ? 0 : 1
                                            ])
                                        ])
                                    ?>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <?=
                                        Html::a(Yii::t('node', 'Edit node'), ['/node/edit', 'id' => $data->id], [
                                            'class'     => ($data->manual == 0) ? 'disabled' : '',
                                            'data-pjax' => '0',
                                        ])
                                    ?>
                                </li>
                                <li>
                                    <?=
                                        Html::a(Yii::t('node', 'Delete node'), ['/node/delete', 'id' => $data->id], [
                                            'class'     => 'text-danger',
                                            'data-pjax' => '0',
                                            'data'      => [
                                                'method'  => 'post',
                                                'confirm' => Yii::t('node', 'Are you sure you want to delete node {0}?', [$data->ip]),
                                            ]
                                        ])
                                    ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <table class="table node-info">
                    <tr>
                        <th><?= Yii::t('network', 'Hostname') ?></th>
                        <td>
                            <?php
                                $host_text = Yii::t('yii', '(not set)');
                                if (isset($data->hostname)) {
                                    $lock = '';
                                    if ($data->protected == 1) {
                                        $lock = Html::tag('span', '<i class="fa fa-lock"></i>', [
                                            'class'          => 'margin-r-5 text-danger',
                                            'data-toggle'    => 'tooltip',
                                            'data-placement' => 'top',
                                            'data-html'      => 'true',
                                            'title'          => Yii::t('node', 'This node is marked as protected')
                                        ]);
                                    }
                                    $host_text = $lock . $data->hostname;
                                }
                                echo $host_text;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('network', 'IP address') ?></th>
                        <td>
                            <?= $data->ip ?>
                            <div class="pull-right">
                                <a href="telnet://<?= $data->ip ?>" class="btn btn-xs btn-default" style="line-height: 13px;">telnet</a>
                                <a href="http://<?= $data->ip ?>" target="_blank" class="btn btn-xs btn-default" style="line-height: 13px;">http</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?= Yii::t('app', 'Credentials') . Html::a(' <i class="fa fa-pencil-square-o"></i>', 'javascript:void(0);', ['id' => 'open_select']); ?>
                        </th>
                        <td>
                            <div id="credentials_text">
                                <?php
                                    if (!is_null($data->network_id)) {
                                        $credential_text = $data->network->credential->name . ' ' . Html::tag('i', '', [
                                            'class'          => 'fa fa-info-circle',
                                            'data-toggle'    => 'tooltip',
                                            'data-placement' => 'top',
                                            'title'          => Yii::t('node', 'Inherited from network')
                                        ]);
                                    }

                                    if (!is_null($data->credential_id)) {
                                        $credential_text = $data->credential->name;
                                    }

                                    echo Html::a($credential_text, 'javascript:void(0);', ['id' => 'show_credentials_tab']);
                                ?>
                            </div>
                            <div id="credentials_select" class="hidden">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <?php
                                            echo Html::a('<i class="fa fa-times"></i>', 'javascript:void(0);', [
                                                'id'    => 'clear_credentials_select',
                                                'class' => 'btn btn-default',
                                                'title' => Yii::t('node', 'Clear node credentials'),
                                            ])
                                        ?>
                                    </span>
                                    <?php
                                        echo Html::dropDownList('', $data->credential_id, $credentials, [
                                            'id'               => 'credentials_select_box',
                                            'class'            => 'select2-small',
                                            'prompt'           => '',
                                            'data-placeholder' => Yii::t('node', 'Choose node credentials')
                                        ]);
                                    ?>
                                    <div class="input-group-btn">
                                        <?php
                                            echo Html::a('<i class="fa fa-check"></i>', 'javascript:void(0);', [
                                                'id'           => 'set_credentials',
                                                'class'        => 'btn bg-light-blue ladda-button',
                                                'title'        => Yii::t('node', 'Set node credentials'),
                                                'data-set-url' => Url::to(['ajax-set-node-credentials', 'node_id' => $data->id]),
                                                'data-style'   => 'zoom-in'
                                            ]);
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('network', 'Network') ?></th>
                        <td>
                            <?php
                                $network_text = Yii::t('yii', '(not set)');
                                $net_override  = '';

                                if (isset($data->network)) {
                                    if (!is_null($data->credential_id)) {
                                        $net_override = Html::tag('span', '<i class="fa fa-info-circle"></i>', [
                                            'class'          => 'margin-r-5',
                                            'data-toggle'    => 'tooltip',
                                            'data-placement' => 'top',
                                            'title'          => Yii::t('node', 'Network credentials are overwritten'),
                                            'style'          => ['color' => '#3c8dbc', 'cursor' => 'pointer']
                                        ]);
                                    }
                                    $network_text = $data->network->network . ' ' . $net_override;
                                }

                                echo $network_text;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('network', 'MAC address') ?></th>
                        <td><?= (isset($data->mac)) ? StringHelper::beautifyMac($data->mac) : Yii::t('yii', '(not set)') ?></td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('network', 'Device') ?></th>
                        <td><?= $data->device->vendor . ' ' . $data->device->model ?></td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('network', 'Serial') ?></th>
                        <td><?= (isset($data->serial)) ? $data->serial : Yii::t('yii', '(not set)') ?></td>
                    </tr>
                    <tr>
                        <th>
                            <?= Yii::t('network', 'Prepend location') . Html::a(' <i class="fa fa-pencil-square-o"></i>', 'javascript:void(0);', ['id' => 'open_input']); ?>
                        </th>
                        <td>
                            <div id="prepend_location_text">
                                <?php
                                    $prepend_location = (!is_null($data->prepend_location)) ? $data->prepend_location : Y::param('defaultPrependLocation');
                                    echo (!is_null($prepend_location)) ? $prepend_location : Yii::t('yii', '(not set)');
                                ?>
                            </div>
                            <div id="prepend_location_input" class="hidden">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <?php
                                            echo Html::a('<i class="fa fa-times"></i>', 'javascript:void(0);', [
                                                'id'    => 'clear_input',
                                                'class' => 'btn btn-default',
                                                'title' => Yii::t('network', 'Clear prepend location'),
                                            ])
                                        ?>
                                    </span>
                                    <?php
                                        echo Html::textInput('', $data->prepend_location, [
                                            'id'          => 'prepend_box',
                                            'class'       => 'form-control',
                                            'placeholder' => Yii::t('network', 'Enter node prepend location')
                                        ]);
                                    ?>
                                    <div class="input-group-btn">
                                        <?php
                                            echo Html::a('<i class="fa fa-check"></i>', 'javascript:void(0);', [
                                                'id'           => 'set_prepend_location',
                                                'class'        => 'btn bg-light-blue ladda-button',
                                                'title'        => Yii::t('network', 'Add prepend location'),
                                                'data-set-url' => Url::to(['ajax-set-prepend-location', 'node_id' => $data->id]),
                                                'data-style'   => 'zoom-in'
                                            ]);
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('network', 'Location') ?></th>
                        <td><?= (isset($data->location)) ? $data->location : Yii::t('yii', '(not set)') ?></td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('app', 'Description') ?></th>
                        <td><?= (isset($data->sys_description)) ? $data->sys_description : Yii::t('yii', '(not set)') ?></td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('app', 'Contact') ?></th>
                        <td><?= (isset($data->contact)) ? $data->contact : Yii::t('yii', '(not set)') ?></td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('node', 'Last seen') ?></th>
                        <td>
                            <?php /** @noinspection PhpUnhandledExceptionInspection */
                                echo (strtotime($data->last_seen) != false) ?  Yii::$app->formatter->asDatetime($data->last_seen, 'php:'.Setting::get('datetime')) : Yii::t('yii', '(not set)')
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('node', 'Last modified') ?></th>
                        <td>
                            <?php /** @noinspection PhpUnhandledExceptionInspection */
                                echo (isset($data->modified)) ? Yii::$app->formatter->asDatetime($data->modified, 'php:'.Setting::get('datetime')) : Yii::t('yii', '(not set)')
                            ?>
                        </td>
                    </tr>
                </table>
            <?php Pjax::end(); ?>
        </div>
    </div>
    <div class="col-md-6">
        <?php if($exclusion): ?>
            <div class="callout callout-warning" style="margin-bottom: 10px">
                <p>
                    <?php
                        echo Yii::t('node', /** @lang text */ 'This node exists in the <a href="{url}">exclusion list</a>, therefore it won\'t be processed by any workers', [
                            'url' => Url::to(['/network/exclusion/list'])
                        ]);
                    ?>
                </p>
            </div>
        <?php endif; ?>
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs tabs-scroll disable-multirow">
                <li class="active"><a href="#tab_1" data-toggle="tab"><?= Yii::t('app', 'Events') ?></a></li>
                <li><a href="#tab_2" data-toggle="tab"><?= Yii::t('node', 'Subinterfaces') ?></a></li>
                <li><a href="#tab_3" data-toggle="tab"><?= Yii::t('app', 'Tasks') ?></a></li>
                <?php if(Yii::$app->user->can('admin')): ?>
                    <li><a href="#tab_4" data-toggle="tab"><?= Yii::t('app', 'Credentials') ?></a></li>
                    <li><a href="#tab_5" data-toggle="tab"><?= Yii::t('network', 'Auth Sequence') ?></a></li>
                <?php endif; ?>
            </ul>
            <div class="tab-content no-padding">
                <div class="tab-pane active" id="tab_1">
                    <?php
                        $full_log = array_merge($data->logNodes, $data->logSchedulers);
                        ArrayHelper::multisort($full_log, 'time', SORT_DESC);
                        $full_log = array_slice($full_log, 0, 11);
                    ?>
                    <?php if (!empty($full_log)): ?>
                        <table class="table ellipsis">
                            <thead>
                                <tr>
                                    <th class="narrow"><?= Yii::t('app', 'Time') ?></th>
                                    <th class="narrow"><?= Yii::t('log', 'Severity') ?></th>
                                    <th><?= Yii::t('app', 'Message') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($full_log as $node_log): ?>
                                    <tr>
                                        <td class="narrow">
                                            <?php /** @noinspection PhpUnhandledExceptionInspection */
                                                echo Yii::$app->formatter->asDatetime($node_log->time, 'php:'.Setting::get('datetime'))
                                            ?>
                                        </td>
                                        <td class="narrow"><?= GridHelper::colorSeverity($node_log->severity) ?></td>
                                        <td class="hide-overflow"><?= $node_log->message ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-right">
                                        <?= Html::a(Yii::t('log', "View all nodes' logs"), ['/log/nodelog/list'], ['target' => '_blank']) ?>
                                        <?= Html::tag('span', '|', ['style' => 'padding: 0 3px 0 3px'])?>
                                        <?= Html::a(Yii::t('log', 'View all scheduler logs'), ['/log/scheduler/list'],  ['target' => '_blank']) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="callout callout-info" style="margin: 10px">
                                    <p><?= Yii::t('log', 'No logs found in database')?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane" id="tab_2">
                    <?php if (!empty($int_provider->getModels())): ?>
                        <?php Pjax::begin(['id' => 'alt-int-pjax', 'enablePushState' => false]); ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="30%"><?= Yii::t('network', 'IP address') ?></th>
                                        <th width="20%"><?= Yii::t('app', 'Type') ?></th>
                                        <th><?= Yii::t('app', 'Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background-color: whitesmoke;">
                                        <td><?= $data->ip ?></td>
                                        <td><?= Yii::t('node', 'Primary') ?></td>
                                        <td></td>
                                    </tr>
                                    <?php foreach ($int_provider->getModels() as $interface): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                    $warning   = '';
                                                    $exclusion = Exclusion::exists($interface['ip']);
                                                    if ($exclusion) {
                                                        $warning = Html::tag('span', '<i class="fa fa-warning"></i>', [
                                                            'class'          => 'margin-r-5 text-danger',
                                                            'data-toggle'    => 'tooltip',
                                                            'data-placement' => 'top',
                                                            'data-html'      => 'true',
                                                            'title'          => Yii::t('node', 'This IP is listed in exclusions')
                                                        ]);
                                                    }
                                                    echo $warning . $interface['ip'];
                                                ?>
                                            </td>
                                            <td><?= Yii::t('node', 'Secondary') ?></td>
                                            <td style="padding-top: 4px; padding-bottom: 3px;">
                                                <div class="input-group input-group-sm">
                                                    <div class="alt_actions">
                                                        <?php
                                                            $actions = [
                                                                'set_primary'      => Yii::t('node', 'Set as primary'),
                                                                'add_exclusion'    => Yii::t('node', 'Add to exclusions'),
                                                                'remove_exclusion' => Yii::t('node', 'Remove from exclusions')
                                                            ];
                                                            $disabled = ($exclusion)
                                                                ? ['set_primary' => ['disabled' => true], 'add_exclusion' => ['disabled' => true]]
                                                                : ['remove_exclusion' => ['disabled' => true]];
                                                            echo Html::dropDownList('', '', $actions, [
                                                                'class'   => 'select2-small alt_actions_select',
                                                                'options' => $disabled
                                                            ]);
                                                        ?>
                                                    </div>
                                                    <div class="subnets hide">
                                                        <?php
                                                            echo Html::dropDownList('', '', $networks, [
                                                                'prompt'           => '',
                                                                'class'            => 'select2-small-search network_select',
                                                                'data-placeholder' => Yii::t('network', 'Choose network'),
                                                            ]);
                                                        ?>
                                                    </div>
                                                    <div class="input-group-btn">
                                                        <?php
                                                            echo Html::a('<i class="fa fa-times"></i>', 'javascript:void(0);', [
                                                                'class' => 'btn btn-warning close-input hide',
                                                                'title' => Yii::t('node', 'Close subnet list')
                                                            ]);
                                                            echo Html::a('<i class="fa fa-check"></i>', 'javascript:void(0);', [
                                                                'id'            => 'run_action_' . substr(md5(uniqid(mt_rand(), true)), 10) ,
                                                                'class'         => 'btn bg-light-blue ladda-button run_alt_action',
                                                                'title'         => Yii::t('node', 'Run action'),
                                                                'data-ajax-url' => Url::to(['ajax-run-interface-action']),
                                                                'data-style'    => 'zoom-in',
                                                                'data-params'   => Json::encode([
                                                                    'node_id' => $data->id,
                                                                    'alt_ip'  => $interface['ip']
                                                                ])
                                                            ]);
                                                        ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if ($int_provider->pagination->pageCount > 1): ?>
                                        <tr>
                                            <td colspan="3">
                                                <div class="pull-right">
                                                    <?php /** @noinspection PhpUnhandledExceptionInspection */
                                                        echo \yii\widgets\LinkPager::widget([
                                                            'pagination'     => $int_provider->pagination,
                                                            'maxButtonCount' => 5,
                                                            'options' => [
                                                                'class' => 'pagination pagination-sm inline',
                                                            ]
                                                        ]);
                                                    ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php Pjax::end() ?>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="callout callout-info" style="margin: 10px">
                                    <p><?= Yii::t('node', 'No alt interfaces found in database')?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane" id="tab_3">
                    <table class="table">
                        <?php if(isset($data->device->tasksHasDevices) && !empty($data->device->tasksHasDevices)): ?>
                            <tr>
                                <th colspan="5" class="bg-info"><?= Yii::t('node', 'Device-related workers') ?></th>
                            </tr>
                            <tr>
                                <th><?= Yii::t('network', 'Task') ?></th>
                                <th><?= Yii::t('network', 'Worker') ?></th>
                                <th><?= Yii::t('network', 'Protocol') ?></th>
                                <th><?= Yii::t('network', 'Destination') ?></th>
                                <th class="narrow"><?= Yii::t('network', 'Jobs') ?></th>
                            </tr>
                            <?php foreach ($data->device->tasksHasDevices as $model): ?>
                                <tr>
                                    <td><?= Html::a($model->task_name, ['/network/task/edit', 'name' => $model->task_name], []) ?></td>
                                    <td><?= $model->worker->name ?></td>
                                    <td><?= mb_strtoupper($model->worker->protocol->name) ?></td>
                                    <td><?= isset($model->taskName->destination->name) ? $model->taskName->destination->name : '' ?></td>
                                    <td class="narrow text-right">
                                        <?php
                                            $device_worker_link = '';
                                            if (!empty($model->worker->jobs)) {
                                                $device_worker_link = Html::a('<i class="fa fa-eye"></i>',
                                                    ['/network/job/ajax-get-jobs', 'worker_id' => $model->worker_id],
                                                    ['class' => 'btn btn-xs btn-default', 'data-toggle' => 'modal', 'data-target' => '#job_modal']
                                                );
                                            }
                                            echo $device_worker_link;
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php $empty_task[0] = true; ?>
                        <?php endif; ?>
                        <?php if(isset($data->tasksHasNodes) && !empty($data->tasksHasNodes)): ?>
                        <tr>
                            <th colspan="5" class="bg-info"><?= Yii::t('node', 'Node-related tasks') ?></th>
                        </tr>
                        <tr>
                            <th><?= Yii::t('network', 'Task') ?></th>
                            <th><?= Yii::t('network', 'Worker') ?></th>
                            <th><?= Yii::t('network', 'Protocol') ?></th>
                            <th><?= Yii::t('network', 'Destination') ?></th>
                            <th class="narrow"><?= Yii::t('network', 'Jobs') ?></th>
                        </tr>
                        <?php foreach ($data->tasksHasNodes as $model): ?>
                        <tr>
                            <td><?= Html::a($model->task_name, ['/network/task/edit', 'name' => $model->task_name], []) ?></td>
                            <td><?= isset($model->worker->name) ? $model->worker->name : Yii::t('app', 'inherited') ?></td>
                            <td><?= isset($model->worker->protocol->name) ? strtoupper($model->worker->protocol->name) : Yii::t('app', 'inherited') ?></td>
                            <td><?= isset($model->taskName->destination->name) ? $model->taskName->destination->name : '' ?></td>
                            <td class="narrow text-right">
                                <?php
                                    echo Html::a('<i class="fa fa-eye"></i>', ['/network/job/ajax-get-jobs',
                                        'task_name' => $model->task_name,
                                        'device_id' => $data->device_id,
                                        'worker_id' => $model->worker_id
                                    ], [
                                        'class'       => 'btn btn-xs btn-default',
                                        'data-toggle' => 'modal',
                                        'data-target' => '#job_modal'
                                    ]);
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <?php $empty_task[1] = true; ?>
                        <?php endif; ?>
                        <?php if(!in_array(false, $empty_task)): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="callout callout-info" style="margin: 10px">
                                        <p><?= Yii::t('node', 'No tasks found in database')?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </table>
                </div>
                <?php if(Yii::$app->user->can('admin')): ?>
                <div class="tab-pane" id="tab_4">
                    <table class="table table-no-outer">
                        <tr>
                            <th><?= Yii::t('network', 'Ports') ?></th>
                            <td colspan="3">
                                <?php
                                    echo "SNMP({$credential->port_snmp}); Telnet({$credential->port_telnet}); SSH({$credential->port_ssh})";
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('node', 'Credential ID') ?></th>
                            <td><?= Html::a($credential->id, ['/network/credential/edit', 'id' => $credential->id]) ?></td>
                            <th><?= Yii::t('app', 'Name') ?></th>
                            <td><?= Html::a($credential->name, ['/network/credential/edit', 'id' => $credential->id]) ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('network', 'Privileged mode') ?></th>
                            <td>
                                <?php
                                    if(preg_match('/^ena?(?:ble)?$/i', $data->device->authTemplateName->auth_sequence)) {
                                        echo '<span class="tip cursor-question dashed-underline" data-toggle="tooltip" data-html="true" data-title="'.Yii::t('app', 'Password').':<br>'.$credential->enable_password.'">'.Yii::t('app', 'Yes').'</span>';
                                    }
                                    else {
                                        echo Yii::t('app', 'No');
                                    }
                                ?>
                            </td>
                            <th><?= Yii::t('node', 'Credential source') ?></th>
                            <td><?= (!is_null($data->credential_id)) ? Yii::t('node', 'Node') : Yii::t('app', 'Networks') ?></td>
                        </tr>
                        <tr>
                            <th>Telnet <?= mb_strtolower(Yii::t('app', 'Login')) ?></th>
                            <td><?= $credential->telnet_login ?></td>
                            <th>Telnet <?= mb_strtolower(Yii::t('app', 'Password')) ?></th>
                            <td><?= Html::passwordInput('telnet_password', $credential->telnet_password, ['readonly' => 'readonly', 'class' => 'showhide_pass', 'autocomplete' => 'off']) ?></td>
                        </tr>
                        <tr>
                            <th>SSH <?= mb_strtolower(Yii::t('app', 'Login')) ?></th>
                            <td><?= $credential->ssh_login ?></td>
                            <th>SSH <?= mb_strtolower(Yii::t('app', 'Password')) ?></th>
                            <td><?= Html::passwordInput('ssh_password', $credential->ssh_password, ['readonly' => 'readonly', 'class' => 'showhide_pass', 'autocomplete' => 'off']) ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('network', 'SNMP version') ?></th>
                            <td><?= Y::param('snmp_versions')[$credential->snmp_version] ?></td>
                            <th><?= Yii::t('network', 'SNMP encryption') ?></th>
                            <td><?= empty($credential->snmp_encryption) ? Yii::t('app', 'No') : $credential->snmp_encryption ?></td>
                        </tr>
                        <tr>
                            <th>SNMP <?= mb_strtolower(Yii::t('network', 'Read community')) ?></th>
                            <td><?= $credential->snmp_read ?></td>
                            <th>SNMP <?= mb_strtolower(Yii::t('network', 'Set community')) ?></th>
                            <td><?= Html::passwordInput('snmp_set', $credential->snmp_set, ['readonly' => 'readonly', 'class' => 'showhide_pass', 'autocomplete' => 'off']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane" id="tab_5" style="padding: 10px">
                    <?php Pjax::begin(['id' => 'auth-template-pjax', 'enablePushState' => false]); ?>
                        <?php if (is_null($data->auth_template_name)): ?>
                            <div class="callout callout-info" style="margin-bottom: 10px;">
                                <p><?= Yii::t('network', 'Selected node use default device auth template - <b>{0}</b>', $data->device->auth_template_name) ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="input-group" style="margin-bottom: 10px ">
                            <span class="input-group-btn">
                                <?php
                                    echo Html::a('<i class="fa fa-plus-square-o"></i>', ['/network/authtemplate/ajax-add-template'], [
                                        'class'         => 'btn btn-default',
                                        'title'         => Yii::t('network', 'Add auth template'),
                                        'data-toggle'   => 'modal',
                                        'data-target'   => '#job_modal',
                                        'data-backdrop' => 'static',
                                    ])
                                ?>
                            </span>
                            <?php
                                $selected_template = (!is_null($data->auth_template_name)) ? $data->auth_template_name : '';
                                echo Html::dropDownList('', $selected_template, $templates, [
                                    'id'                 => 'auth_template_list',
                                    'class'              => 'select2-normal',
                                    'prompt'             => '',
                                    'data-placeholder'   => Yii::t('network', 'Please select auth template'),
                                    'data-allow-clear'   => 'true',
                                    'data-url'           => Url::to(['network/device/ajax-auth-template-preview']),
                                    'data-update-url'    => Url::to(['network/device/ajax-update-templates']),
                                    'data-default-value' => $data->device->auth_template_name
                                ]);
                            ?>
                            <div class="input-group-btn">
                                <?php
                                    echo Html::a('<i class="fa fa-check"></i>', 'javascript:void(0);', [
                                        'id'              => 'update_node_auth',
                                        'class'           => 'btn bg-light-blue ladda-button',
                                        'title'           => Yii::t('network', 'Update node auth template'),
                                        'data-update-url' => Url::to(['ajax-set-auth-template', 'node_id' => $data->id]),
                                        'data-style'      => 'zoom-in'
                                    ]);
                                ?>
                            </div>
                        </div>

                        <!-- Show error if occurs -->
                        <div id="display_error"></div>

                        <div id="auth_template_preview">
                            <div class="pull-left margin-r-5" style="margin-bottom: 1px">
                                <div class="auth_sequence_helper pull-left" style="background-color: #dcf1d7;"></div>
                                <?= Yii::t('network', 'Prompt (expect)') ?>
                            </div>
                            <div class="pull-left" style="margin-bottom: 5px">
                                <div class="auth_sequence_helper pull-left" style="background-color: #ffffff;"></div>
                                <?= Yii::t('network', 'Input data') ?>
                            </div>
                            <?php
                                $template = (!is_null($data->auth_template_name)) ? $data->authTemplateName->auth_sequence : $data->device->authTemplateName->auth_sequence;
                                echo Html::textarea('', $template, [
                                    'id'       => 'auth_textarea',
                                    'class'    => 'form-control auth_sequence',
                                    'readonly' => true,
                                    'style'    => 'resize: none'
                                ])
                            ?>
                        </div>
                    <?php Pjax::end(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xs-12">

        <div id="nav_tabs" class="nav-tabs-custom">
            <ul class="nav nav-tabs tabs-scroll disable-multirow">
                <li class="active"><a href="#backup_tab" data-toggle="tab"><?= Yii::t('node', 'Actual configuration backup') ?></a></li>
                <?php foreach ($plugins as $plugin): ?>
                    <?php if ($plugin->plugin_params['widget_enabled'] == '1'): ?>
                        <li>
                            <?php
                                echo Html::a($plugin->plugin::t('general', Inflector::humanize($plugin->name)), "#tab_{$plugin->name}", [
                                    'class'            => 'load-widget',
                                    'data-toggle'      => 'tab',
                                    'data-widget-url'  => Url::to(['ajax-load-widget', 'node_id' => $data->id, 'plugin' => $plugin->name]),
                                    'data-plugin-name' => $plugin->name
                                ]);
                            ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="pull-right">
                    <a href="javascript:void(0);" id="tab_expand_btn" class="text-muted"><i class="fa fa-expand"></i></a>
                </li>
            </ul>
            <!-- Tab with all information about selected device config backup -->
            <div class="tab-content no-padding">
                <div class="tab-pane active table-responsive" id="backup_tab">
                    <?php if (!empty($data->outBackups)): ?>
                        <table class="table table-bordered" style="margin-bottom: 0;">
                            <tr>
                                <th>ID</th>
                                <th><?= Yii::t('node', 'Last modified') ?></th>
                                <th class="hidden-sm hidden-xs"><?= Yii::t('app', 'Hash') ?></th>
                                <th class="hidden-sm hidden-xs"><?= Yii::t('node', 'Path') ?></th>
                                <th><?= Yii::t('app', 'Size') ?></th>
                                <th><?= Yii::t('app', 'Actions') ?></th>
                            </tr>
                            <tr>
                                <td><?= $data->outBackups[0]->id ?></td>
                                <td>
                                    <?php /** @noinspection PhpUnhandledExceptionInspection */
                                        echo Yii::$app->formatter->asDatetime($data->outBackups[0]->time, 'php:'.Setting::get('datetime'))
                                    ?>
                                </td>
                                <td class="hidden-sm hidden-xs"><?= $data->outBackups[0]->hash ?></td>
                                <td class="hidden-sm hidden-xs">
                                    <?php
                                        $com_status  = 'disabled';
                                        $com_hash    = '';
                                        $conf_exists = '';
                                        $disabled    = '';
                                        $path        = Yii::t('network', 'Database');
                                        $file_path   = DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . "{$data->id}.txt";

                                        /** Show file path if put is set to file */
                                        if ($task_info->put == 'file') {
                                            $path = Html::tag('span', '<i class="fa fa-folder-open-o"></i>', [
                                                'class'          => 'text-info cursor-question',
                                                'title'          => Y::param('dataPath'),
                                                'data-toggle'    => 'tooltip',
                                                'data-placement' => 'left',
                                            ]) . $file_path;
                                        }

                                        /** Check if config exists in database or file */
                                        if (($task_info->put == 'file' && !file_exists(Y::param('dataPath') . $file_path)) ||
                                            ($task_info->put == 'db'   && is_null($data->outBackups[0]->config))) {

                                            $conf_exists = Html::tag('i', '', [
                                                'class'               => 'fa fa-warning text-danger cursor-question',
                                                'title'               => Yii::t('node', 'Configuration not found'),
                                                'data-toggle'         => 'tooltip',
                                                'data-placement'      => 'right',
                                            ]);

                                            $disabled = 'disabled';
                                        }

                                        /** Check if file commits exists in GIT repo */
                                        if ($task_info->put == 'file' && is_array($commit_log) && array_key_exists('0', $commit_log) ) {
                                            $com_hash   = $commit_log[0][0];
                                            $com_status = '';
                                        }

                                        echo Html::tag('span', $path, ['class' => 'margin-r-5']) . $conf_exists;
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $file_size = '&mdash;';
                                        if ($task_info->put == 'file' && file_exists(Y::param('dataPath') . $file_path)) {
                                            $file_size = StringHelper::beautifySize(filesize(Y::param('dataPath') . $file_path));
                                        }
                                        echo $file_size;
                                    ?>
                                </td>
                                <td class="narrow">
                                    <?php
                                        echo Html::a('<i class="fa fa-eye"></i> ' .Yii::t('app', 'View'), '#config_content', [
                                            'id'          => 'show_config',
                                            'class'       => 'btn btn-xs btn-default margin-r-5 ' . $disabled,
                                            'data-toggle' => "collapse",
                                            'data-parent' => '#accordion',
                                            'data-url'    => Url::to(['ajax-load-config']),
                                            'data-params' => json_encode([
                                                'node_id' => $data->id,
                                                'put'     => $task_info->put,
                                            ])
                                        ]);

                                        echo Html::a('<i class="fa fa-copy"></i> ' . Yii::t('app', 'Copy'), 'javascript:void(0);', [
                                            'id'    => 'copy_config',
                                            'class' => 'btn btn-xs btn-default margin-r-5 disabled',
                                            'data-clipboard-action' => 'copy'
                                        ]);

                                        if ($task_info->put == 'file') {
                                            echo Html::a('<i class="fa fa-book"></i> ' . Yii::t('app', 'History'), '#diff_content', [
                                                'id'          => 'show_history',
                                                'class'       => 'btn btn-xs btn-default margin-r-5 ' . "{$disabled} {$com_status}",
                                                'data-toggle' => "collapse",
                                                'data-parent' => '#accordion',
                                                'data-url'    => Url::to(['ajax-load-file-diff']),
                                                'data-params' => json_encode([
                                                    'node_id' => $data->id,
                                                    'hash'    => $com_hash,
                                                ])
                                            ]);
                                        }

                                        echo Html::a('<i class="fa fa-download"></i> ' . Yii::t('app', 'Download'), Url::to(['ajax-download',
                                            'id'   => $data->id,
                                            'put'  => $task_info->put,
                                            'hash' => null
                                        ]), [
                                            'class'         => 'btn btn-xs btn-default ' . $disabled,
                                            'title'         => Yii::t('app', 'Download'),
                                            'data-dismiss'  => 'modal',
                                            'data-toggle'   => 'modal',
                                            'data-target'   => '#download_modal',
                                            'data-backdrop' => 'static',
                                            'data-keyboard' => 'false'
                                        ]);
                                    ?>
                                </td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <div class="callout callout-info" style="margin: 10px;">
                            <p><?= Yii::t('node', 'Configuration not found') ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="panel-group" id="accordion" style="margin-bottom: 0">
                        <div class="panel panel-no-border panel-default">
                            <div id="config_content" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <?php
                                        echo Html::tag('iframe', '' , [
                                            'id'    => 'config_iframe',
                                            'style' => 'width: 100%; font-family: monospace; border: 1px solid silver; background: #efefef;',
                                            'src'   => '#'
                                        ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-no-border panel-default" style="margin-top: 0">
                            <div id="diff_content" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <span class="loader" style="margin-left: 35%;">
                                        <?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?>
                                    </span>
                                    <div id="file_diff"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php foreach ($plugins as $plugin): ?>
                    <?php if ($plugin->plugin_params['widget_enabled'] == '1'): ?>
                        <div class="tab-pane" id="tab_<?= $plugin->name ?>">
                            <div id="widget_loading_<?= $plugin->name ?>" style="padding: 30px 0 30px 0;">
                                <span class="loader" style="margin-left: 32%;">
                                    <?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?>
                                </span>
                            </div>
                            <div id="widget_content_<?= $plugin->name ?>" style="padding: 10px;"></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- form modal -->
<div id="job_modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
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

<!-- form modal -->
<div id="download_modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-light-blue-active">
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

<!-- git log modal -->
<?php if (is_array($commit_log) && !empty($commit_log)): ?>
    <div id="git_log_modal" class="modal fade" tabindex="-1" style="z-index: 999999;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light-blue-active">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title"><?= Yii::t('app', 'Last {0} entries of commit history', Y::param('gitDays')) ?></h4>
                </div>
                <div class="modal-body no-padding">

                    <!-- Commit history placeholder -->
                    <div id="commit_history_placeholder" style="padding: 15px 0 15px 34%;">
                        <?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?>
                    </div>

                    <!-- Commit history dataTable -->
                    <div id="commit_history" class="row hidden">
                        <div class="col-xs-12">
                            <table id="commit_history_table" class="table ellipsis" style="margin-bottom: 0;">
                                <thead>
                                    <tr>
                                        <th width="20%"><?= Yii::t('app', 'Commit hash') ?></th>
                                        <th width="42%"><?= Yii::t('app', 'Commit message') ?></th>
                                        <th width="15%"><?= Yii::t('app', 'User') ?></th>
                                        <th width="20%"><?= Yii::t('app', 'Date') ?></th>
                                        <th width="3%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commit_log as $log): ?>
                                        <tr>
                                            <td><?= $log[0] ?></td>
                                            <td class="hide-overflow"><?= $log[1] ?></td>
                                            <td><?= $log[2] ?></td>
                                            <td><?= preg_replace("/(\s+\+\d*)/i", '', $log[3]) ?></td>
                                            <td style="white-space: nowrap">
                                                <?php
                                                    echo Html::a('<i class="fa fa-save"></i>', Url::to(['ajax-download',
                                                        'id'   => $data->id,
                                                        'put'  => $task_info->put,
                                                        'hash' => $log[0],
                                                    ]), [
                                                        'style'         => 'cursor:pointer;',
                                                        'title'         => Yii::t('app', 'Download'),
                                                        'data-dismiss'  => 'modal',
                                                        'data-toggle'   => 'modal',
                                                        'data-target'   => '#download_modal',
                                                        'data-backdrop' => 'static',
                                                        'data-keyboard' => 'false'
                                                    ]);
                                                    echo '&nbsp;';
                                                    echo Html::a('<i class="fa fa-eye"></i>', 'javascript:;', [
                                                        'class'       => 'reload_history',
                                                        'data-url'    => Url::to(['ajax-load-file-diff']),
                                                        'style'       => 'cursor:pointer;',
                                                        'title'       => Yii::t('app', 'View'),
                                                        'data-params' => json_encode([
                                                            'node_id' => $data->id,
                                                            'hash'    => $log[0],
                                                        ])
                                                    ]);
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
