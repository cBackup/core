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
 * @var $show_msg  bool
 * @var $jobs      \app\models\Job[]|array
 */
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span></button>
    <h4 class="modal-title"><?= Yii::t('network', 'Sequence') ?></h4>
</div>
<?php if (empty($jobs)): ?>
    <div class="callout callout-info" style="margin: 10px;">
        <p><?= Yii::t('node', 'Selected task does not have valid worker assigned, or worker does not have jobs') ?></p>
    </div>
<?php else: ?>
    <table class="table">
        <?php if ($show_msg): ?>
            <tr>
                <th colspan="4" class="bg-info">
                    <span style="color: #31708f;">
                        <i class="fa fa-info-circle"></i> <?= Yii::t('node', 'Jobs are inherited from device worker') ?>
                    </span>
                </th>
            </tr>
        <?php endif; ?>
        <tr>
            <th>#</th>
            <th><?= Yii::t('app', 'Name') ?></th>
            <th><?= Yii::t('network', 'Command') ?></th>
            <th><?= Yii::t('app', 'Description') ?></th>
        </tr>
        <?php foreach ($jobs as $job): ?>
        <tr>
            <td><?= $job->sequence_id ?></td>
            <td><?= $job->name ?></td>
            <td><?= $job->command_value ?></td>
            <td><?= $job->description ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
</div>
