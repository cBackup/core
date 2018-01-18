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
 * @var $data  \app\models\Credential
 */
?>

<div class="row">
    <?php if (is_null($data)): ?>
        <div class="col-md-12">
            <div class="callout callout-info" style="margin-bottom: 0;">
                <p><?= Yii::t('network', 'Specified credential was not found in the list.') ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-default box-solid">
                <div class="box-header box-header-narrow text-center">
                    <h3 class="box-title"><?= Yii::t('network', 'Credential info') ?></h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-striped table-narrow">
                        <tbody>
                            <tr>
                                <th width="25%"><?= Yii::t('app', 'Name') ?></th>
                                <td width="40%"><?= (!empty($data->name)) ? $data->name : Yii::t('yii', '(not set)') ?></td>
                                <th width="15%"><?= Yii::t('network', 'Telnet port') ?></th>
                                <td width="20%"><?= (!empty($data->port_telnet)) ? $data->port_telnet : Yii::t('yii', '(not set)') ?></td>
                            </tr>
                            <tr>
                                <th><?= Yii::t('network', 'SNMP version') ?></th>
                                <td><?= (isset($data->snmp_version)) ? \Y::param('snmp_versions')[$data->snmp_version] : Yii::t('yii', '(not set)') ?></td>
                                <th><?= Yii::t('network', 'SSH port') ?></th>
                                <td><?= (!empty($data->port_ssh)) ? $data->port_ssh : Yii::t('yii', '(not set)') ?></td>
                            </tr>
                            <tr>
                                <th><?= Yii::t('network', 'SNMP encryption') ?></th>
                                <td><?= (!empty($data->snmp_encryption)) ? $data->snmp_encryption : Yii::t('yii', '(not set)') ?></td>
                                <th><?= Yii::t('network', 'SNMP port') ?></th>
                                <td><?= (!empty($data->port_snmp)) ? $data->port_snmp : Yii::t('yii', '(not set)') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
