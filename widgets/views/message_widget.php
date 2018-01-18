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
 * @var $data array
 */
?>

<li class="dropdown notifications-menu system-message-widget">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell-o"></i>
        <?php if ($data['count'] > 0): ?>
            <span class="label label-warning"><?= $data['count'] ?></span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu pull-right">
        <li class="header"><?= Yii::t('app', 'System messages') ?></li>
        <li>
            <table class="table table-hover" style="margin-bottom: 0">
                <?php if ($data['count'] > 0): ?>
                    <?php foreach ($data['messages'] as $entry): ?>
                        <tr>
                            <td width="35%"><?= $entry['created'] ?></td>
                            <td><div><?= $entry['message'] ?></div></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3"><?= Yii::t('app', 'There are no new system messages.') ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </li>
        <li class="footer">
            <?= \yii\helpers\Html::a(Yii::t('app', 'View all messages'), ['/message']) ?>
        </li>
    </ul>
</li>
