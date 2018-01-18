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
 * @var $this   yii\web\View
 * @var $body   string
 */
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header modal-default">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            <h4 class="modal-title"><?= Yii::t('mail', 'Message preview') ?></h4>
        </div>
        <div class="modal-body">
            <div class="callout callout-info">
                <p><?= Yii::t('mail', 'Attention! This preview is meant only for checking template variables. Message appearance in Your email client may be different.') ?></p>
            </div>
            <?= $body ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
        </div>
    </div>
</div>
