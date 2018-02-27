<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2018, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
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
 * @var $id     int
 * @var $put    string
 * @var $hash   string|null
 */
?>
<div class="modal-dialog modal-sm">
    <div class="modal-content">
        <div class="modal-header bg-light-blue-active">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">×</span></button>
            <h4 class="modal-title"><?= Yii::t('app', 'Choose end of line format') ?></h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-12">
                    <?php
                        echo Html::a('&nbsp;&nbsp;LF&nbsp;&nbsp;', ['download', 'id' => $id, 'put' => $put, 'hash' => $hash], [
                            'class'   => 'btn btn-primary pull-left',
                            'onclick' => '(function() { $("#download_modal").modal("hide"); })();'
                        ]);
                        echo Html::a('CRLF', ['download', 'id' => $id, 'put' => $put, 'hash' => $hash, 'crlf' => true], [
                            'class'   => 'btn btn-primary pull-right',
                            'onclick' => '(function() { $("#download_modal").modal("hide"); })();'
                        ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
