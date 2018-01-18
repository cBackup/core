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
 * @var $data  \app\models\DeviceAttributes
 */
?>

<div class="row">
    <?php if (empty($data)): ?>
        <div class="col-md-12">
            <div class="callout callout-info" style="margin-bottom: 0;">
                <p><?= Yii::t('network', 'Attributes for selected device not found.') ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="col-md-12">
            <div class="box box-default box-solid" style="margin-bottom: 0">
                <div class="box-header box-header-narrow text-center">
                    <h3 class="box-title"><?= Yii::t('network', 'List of device attributes') ?></h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-striped table-narrow">
                        <thead>
                            <tr>
                                <th><?= Yii::t('network', 'System OID') ?></th>
                                <th><?= Yii::t('network', 'Hardware rev.') ?></th>
                                <th><?= Yii::t('network', 'System descr.') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $entry): ?>
                                <tr>
                                    <td width="25%">
                                        <?= yii\helpers\Html::a($entry['sysobject_id'], ['device/change-device', 'id' => $entry['id']],[
                                            'title'     => Yii::t('network', 'Change device'),
                                            'data-pjax' => '0',
                                        ]) ?>
                                    </td>
                                    <td width="20%"><?= (isset($entry['hw'])) ? $entry['hw'] : Yii::t('yii', '(not set)') ?></td>
                                    <td><?= (isset($entry['sys_description'])) ? $entry['sys_description'] : Yii::t('yii', '(not set)') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
