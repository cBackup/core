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
 * @var $this       \yii\web\View
 * @var $response   array
 */
$this->registerCss(/** @lang CSS */'
    pre {
        width: 100%;
        overflow: auto;
    }
');
?>
<table class="table table-bordered" style="margin-bottom: 0;">
    <tbody>
        <tr>
            <th width="20%"><?= Yii::t('app', 'Commit hash') ?></th>
            <th width="43%"><?= Yii::t('app', 'Commit message') ?></th>
            <th width="15%"><?= Yii::t('app', 'User') ?></th>
            <th width="20%"><?= Yii::t('app', 'Date') ?></th>
            <th width="2%"></th>
        </tr>
        <tr>
            <td><?= $response['meta'][0] ?></td>
            <td><?= $response['meta'][1] ?></td>
            <td><?= $response['meta'][2] ?></td>
            <td><?= preg_replace("/(\s+\+\d*)/i", '', $response['meta'][3]) ?></td>
            <td>
                <?php
                    echo Html::a('<i class="fa fa-history"></i>', null, [
                        'data-toggle' => 'modal',
                        'data-target' => '#git_log_modal',
                        'style'       => 'cursor:pointer;',
                        'title'       => Yii::t('app', 'History')
                    ])
                ?>
            </td>
        </tr>
    </tbody>
</table>

<?php if (!empty($response['diff'])): ?>
    <div class="div-scroll">
        <?= $response['diff'] ?>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="callout callout-info" style="margin-top: 10px">
                <p><?= Yii::t('node', 'Selected GIT file revision is equal to current backup file. Please choose another file revision if possible.')?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
