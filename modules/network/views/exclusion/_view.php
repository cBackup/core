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
 *
 * @var $data  \app\models\Node
 */
?>

<div class="row">
    <?php if (is_null($data)): ?>
        <div class="col-md-12">
            <div class="callout callout-info" style="margin-bottom: 0;">
                <p><?= Yii::t('network', 'Specified node was not found in node list.') ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="col-md-8">
            <div class="box box-default box-solid">
                <div class="box-header box-header-narrow text-center">
                    <h3 class="box-title"><?= Yii::t('node', 'Node info') ?></h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-striped table-narrow">
                        <tbody>
                            <tr>
                                <th width="20%"><?= Yii::t('network', 'Hostname') ?></th>
                                <td width="30%"><?= $data->hostname ?></td>
                                <th width="20%"><?= Yii::t('network', 'IP address') ?></th>
                                <td width="30%"><?= $data->ip ?></td>
                            </tr>
                            <tr>
                                <th><?= Yii::t('network', 'Location') ?></th>
                                <td><?= $data->location ?></td>
                                <th><?= Yii::t('network', 'Last seen') ?></th>
                                <td><?= $data->last_seen ?></td>
                            </tr>
                            <tr>
                                <th><?= Yii::t('network', 'Device name') ?></th>
                                <td><?= $data->device->vendor . ' ' . $data->device->model ?></td>
                                <th><?= Yii::t('network', 'Serial') ?></th>
                                <td><?= $data->serial ?></td>
                            </tr>
                            <tr>
                                <th><?= Yii::t('app', 'Description') ?></th>
                                <td><?= $data->sys_description ?></td>
                                <th><?= Yii::t('network', 'MAC address') ?></th>
                                <td><?= \app\helpers\StringHelper::beautifyMac($data->mac) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default box-solid">
                <div class="box-header box-header-narrow text-center">
                    <h3 class="box-title"><?= Yii::t('network', 'Alternative interfaces') ?></h3>
                </div>
                <div class="box-body no-padding">
                    <?php if (empty($data->altInterfaces)): ?>
                        <div class="callout callout-info" style="margin: 10px;">
                            <p><?= Yii::t('network', 'Node does not have alternative interfaces.') ?></p>
                        </div>
                    <?php else: ?>
                        <table class="table table-striped table-narrow">
                            <thead>
                                <tr>
                                    <th><?= Yii::t('network', 'IP address') ?></th>
                                    <th><?= Yii::t('app', 'Description') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data->altInterfaces as $interface): ?>
                                    <tr>
                                        <td><?= $interface['ip'] ?></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
