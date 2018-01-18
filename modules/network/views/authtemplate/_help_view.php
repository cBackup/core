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

/**
 * @var $collapsed boolean
 */
?>

<div class="box box-info <?= ($collapsed) ? 'collapsed-box' : ''?>">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Help') ?></h3>
        <?php if ($collapsed): ?>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="box-body text-justify" style="<?= ($collapsed) ? 'overflow-y: scroll; height:370px;' : '' ?>">
        <p>
            <?= Yii::t('network', 'Auth sequence field represents the authentication sequence for the particular device. The following tags are available for usage in the sequence while being substituted by related values from credentials:') ?>
        </p>
        <table class="table table-condensed">
            <tr>
                <th><?= Yii::t('network', 'Tag') ?></th>
                <th><?= Yii::t('app', 'Description') ?></th>
            </tr>
            <tr>
                <td><code>{{telnet_login}}</code></td>
                <td><?= Yii::t('network', 'Telnet Login') ?></td>
            </tr>
            <tr>
                <td><code>{{telnet_password}}</code></td>
                <td><?= Yii::t('network', 'Telnet Password') ?></td>
            </tr>
            <tr>
                <td><code>{{enable_password}}</code></td>
                <td><?= Yii::t('network', 'Privileged mode password') ?></td>
            </tr>
        </table>
        <br>
        <?= Yii::t('network', 'Also note the following:') ?>
        <ul>
            <li><?= Yii::t('network', 'If you use a tag, it must be the only input in line') ?></li>
            <li><?= Yii::t('network', "Expected value (prompt on green background) is <dfn title=\"'word:' will not match 'PassWord:'\">case-sensitive</dfn>") ?></li>
            <li><?= Yii::t('network', 'You must end the auth sequence with defined CLI prompt, e.g. <code>#</code>, <code>$</code>, <code>&gt;</code> or <code>NMA#</code>, where <i>NMA</i> is hostname. The last line must be terminated with valid prompt symbol from aforementioned list.') ?></li>
        </ul>
    </div>
</div>

<div class="nav-tabs-custom box box-default">
    <ul class="nav nav-tabs pull-right ui-sortable-handle" style="margin-top: -3px">
        <li class="active"><a href="#dlink" data-toggle="tab">D-Link</a></li>
        <li><a href="#arris" data-toggle="tab">Arris Cadant</a></li>
        <li><a href="#cisco" data-toggle="tab">Cisco</a></li>
    </ul>
    <div class="tab-content">
        <div class="active tab-pane" id="dlink">
            <?= Html::tag('pre', "ame:\n{{telnet_login}}\nord:\n{{telnet_password}}\n#") ?>
        </div>
        <div class="tab-pane" id="arris">
            <?= Html::tag('pre', "in:\n{{telnet_login}}\nord:\n{{telnet_password}}\n>\nenable\nord:\n{{enable_password}}\n#") ?>
        </div>
        <div class="tab-pane" id="cisco">
            <?= Html::tag('pre', "in:\n{{telnet_login}}\nord:\n{{telnet_password}}\n>\nena\nord:\n{{enable_password}}\n#") ?>
        </div>
    </div>
</div>
