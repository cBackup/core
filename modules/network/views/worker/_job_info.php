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
 * @var $data  \app\models\Job
 */
?>

<?php if (is_null($data)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="callout callout-info" style="margin-bottom: 0;">
                <p><?= Yii::t('network', 'Specified job was not found.') ?></p>
            </div>
        </div>
    </div>
<?php else: ?>
    <table class="table text-fus">
        <tr>
            <th width="45%"><?= Yii::t('app', 'Name') ?></th>
            <td width="55%"><?= $data->name ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'Worker') ?></th>
            <td><?= $data->worker->name ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'Worker type') ?></th>
            <td><?= strtoupper($data->worker->get) ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'Sequence') ?></th>
            <td><?= $data->sequence_id ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'Timeout') ?></th>
            <td><?= $data->timeout ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'Enable') ?></th>
            <td><?= ($data->enabled == 1) ? Yii::t('app', 'Yes') : Yii::t('app', 'No') ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'SQL table field') ?></th>
            <td><?= (!empty($data->table_field)) ? $data->table_field : Yii::t('yii', '(not set)') ?></td>
        </tr>
        <tr>
            <th><?= (strcasecmp($data->worker->get, 'snmp') == 0) ? Yii::t('network', 'SNMP OID') : Yii::t('network', 'Command'); ?></th>
            <td><?= (!empty($data->command_value)) ? "<span class='text-success'>{$data->command_value}</span>" : Yii::t('yii', '(not set)') ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('network', 'Command variable') ?></th>
            <td><?= (!empty($data->command_var)) ? $data->command_var : Yii::t('yii', '(not set)') ?></td>
        </tr>
        <?php if (strcasecmp($data->worker->get, 'snmp') == 0): ?>
            <tr>
                <th><?= Yii::t('network', 'SNMP request type') ?></th>
                <td><?= strtoupper($data->snmp_request_type) ?></td>
            </tr>
            <?php if (strcasecmp($data->snmp_request_type, 'set') == 0): ?>
                <tr>
                    <th><?= Yii::t('network', 'SNMP value type') ?></th>
                    <td><?= (!empty($data->snmp_set_value_type)) ? $data->snmpSetValueType->description : Yii::t('yii', '(not set)') ?></td>
                </tr>
                <tr>
                    <th><?= Yii::t('network', 'SNMP value') ?></th>
                    <td><?= (!empty($data->snmp_set_value)) ? $data->snmp_set_value : Yii::t('yii', '(not set)') ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (strcasecmp($data->worker->get, 'snmp') != 0): ?>
            <tr>
                <th><?= Yii::t('network', 'CLI custom prompt') ?></th>
                <td><?= (!empty($data->cli_custom_prompt)) ? $data->cli_custom_prompt : Yii::t('yii', '(not set)') ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th><?= Yii::t('app', 'Description') ?></th>
            <td><?= (!empty($data->description)) ? $data->description : Yii::t('yii', '(not set)') ?></td>
        </tr>
    </table>
<?php endif; ?>
