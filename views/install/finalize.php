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

/** @noinspection JSUnusedLocalSymbols
 *  @var $this \yii\web\View
 */
$this->registerJs(/** @lang JavaScript */'
    $("#finish").on("click", function(e) {
        $(this).button("loading");
        $("a").addClass("disabled");
    });
');

$this->title = Yii::t('install', 'Final step');
?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title">
            <i class="fa fa-smile-o"></i> <?= Yii::t('install', 'Congratulations') ?>
        </h3>
    </div>
    <div class="box-body">
        <ul class="text-justify">
            <li><?= Yii::t('install', "System will be ready to run after you press <span class='text-success text-bolder'>'Finish'</span> button below") ?></li>
            <li>
                <?php
                    /** @noinspection HtmlUnknownTarget */
                    echo Yii::t('install', 'Proceed with setting up networks, devices, tasks and jobs. Questions? <a href="{url}" target="_blank">Here is the documentation</a>', ['url' => 'http://cbackup.readthedocs.io/']);
                ?>
            </li>
            <li><?= Yii::t('install', 'Bug reports can be submitted to github') ?></li>
        </ul>
    </div>
    <div class="box-footer">
        <?=
            Html::a(Yii::t('app', 'Finish'), ['finalize'], [
                'class' => 'btn btn-success pull-right',
                'id'    => 'finish',
                'data'  => [
                    'method' => 'post'
                ]
            ])
        ?>
    </div>
</div>
