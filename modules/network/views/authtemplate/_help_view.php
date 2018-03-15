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
 * @var $vars      array
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
            <?= /** @noinspection HtmlUnknownTarget */ Yii::t('network', 'Auth sequence field represents the authentication sequence for the particular device. The following tags are available for usage in the sequence while being substituted by related values from <a href="{0}">credentials</a>:', \yii\helpers\Url::to(['/network/credential'])) ?>
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
                <td class="text-left"><?= Yii::t('network', 'Privileged mode password') ?></td>
            </tr>
            <?php foreach($vars as $var => $description): ?>
                <tr>
                    <td><code><?= $var ?></code></td>
                    <td><?= Yii::t('network', $description) ?></td>
                </tr>
            <?php endforeach; ?>
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
    <ul class="nav nav-tabs nav-justified ui-sortable-handle tabs-scroll disable-multirow" style="margin-top: -3px">
        <li><a href="#ssh_mikrotik" data-toggle="tab">SSH Mikrotik</a></li>
        <li><a href="#ssh_cisco" data-toggle="tab">SSH Cisco</a></li>
        <li><a href="#ssh_hp" data-toggle="tab">SSH HP</a></li>
        <li><a href="#telnet_dlink" data-toggle="tab">Telnet D-Link</a></li>
        <li><a href="#telnet_arris" data-toggle="tab">Telnet Arris</a></li>
        <li class="active"><a href="#telnet_cisco" data-toggle="tab">Global Cisco</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" id="ssh_mikrotik">
            <div class="row">
                <div class="col-md-6">
                    <?= Html::tag('pre', "#") ?>
                </div>
                <div class="col-md-6 text-justify" style="padding-right: 22px;">
                    <?= Yii::t('network', 'You can define only <code>#</code> as initial expect after successful authentication.') ?>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="ssh_cisco">
            <div class="row">
                <div class="col-md-6">
                    <?= Html::tag('pre', ">\nena\nord:\n{{enable_password}}\n#") ?>
                </div>
                <div class="col-md-6 text-justify" style="padding-right: 22px;">
                    <?= Yii::t('network', 'Authentication sequence for plain SSH doesn\'t need (and will ignore) <code>{{telnet...}}</code> tags, so you may want not to use them in the template at all, starting with expect for privileged mode if necessary.') ?>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="ssh_hp">
            <div class="row">
                <div class="col-md-6">
                    <?= Html::tag('pre', "Press any key to continue\n%%SEQ(ENTER)%%\n>\nena\nord:\n{{enable_password}}\n#") ?>
                </div>
                <div class="col-md-6 text-justify" style="padding-right: 22px;">
                    <?= Yii::t('network', 'This is an example for device that uses to "press any key" or to to press certain key sequence (e.g. Juniper asks for CTRL+Y). You may use predefined tags to do so.') ?>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="telnet_dlink">
            <?= Html::tag('pre', "ame:\n{{telnet_login}}\nord:\n{{telnet_password}}\n#") ?>
        </div>
        <div class="tab-pane" id="telnet_arris">
            <?= Html::tag('pre', "in:\n{{telnet_login}}\nord:\n{{telnet_password}}\n>\nenable\nord:\n{{enable_password}}\n#") ?>
        </div>
        <div class="tab-pane active" id="telnet_cisco">
            <div class="row">
                <div class="col-md-6">
                    <?= Html::tag('pre', "in:\n{{telnet_login}}\nord:\n{{telnet_password}}\n>\nena\nord:\n{{enable_password}}\n#") ?>
                </div>
                <div class="col-md-6 text-justify" style="padding-right: 22px;">
                    <p>
                        <?= Yii::t('network', 'As you know, SSH credentials are passed when the connection is established. Therefore you may want to use only prompt and <code>{{enable_password}}</code> if necessary or <code>%%SEQ</code> tags for sending sequences.') ?>
                    </p>
                    <p>
                        <?= Yii::t('network', 'Both <code>{{telnet_login}}</code> and <code>{{telnet_ password}}</code> tags with corresponding prompts will be ignored, so you still may use the same authentication template for Telnet and SSH devices.') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
