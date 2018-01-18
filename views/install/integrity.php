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
 *  @var $this      \yii\web\View
 *  @var $database  string
 *  @var $locations array
 *  @var $ssh       string
 */
$this->registerJs(/** @lang JavaScript */'
    $("#next").on("click", function(e) {
        $(this).button("loading");
        $("a").addClass("disabled");
    });
');

$this->title = Yii::t('install', 'System integrity check');
$errors      = false;
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <table class="table compatibility" style="margin-bottom: 0">
                <tr>
                    <th colspan="2">
                        <?= Yii::t('network', 'Database') ?>
                    </th>
                </tr>
                <tr>
                    <td><?= Yii::t('install', 'Connection') ?></td>
                    <?php if(empty($database)): ?>
                        <td class="text-success"><i class="fa fa-check"></i></td>
                    <?php else: ?>
                        <td class="text-danger" style="text-align: right;">
                            <?php
                                echo $database;
                                $errors = true;
                            ?>
                        </td>
                    <?php endif; ?>
                </tr>
            </table>
            <table class="table compatibility" style="margin-bottom: 0">
                <tr>
                    <th colspan="2">
                        SSH
                    </th>
                </tr>
                <tr>
                    <td><?= Yii::t('install', 'Connection') ?></td>
                    <?php if(empty($ssh)): ?>
                        <td class="text-success"><i class="fa fa-check"></i></td>
                    <?php else: ?>
                        <td class="text-danger" style="text-align: right;">
                            <?php
                                echo $ssh;
                                $errors = true;
                            ?>
                        </td>
                    <?php endif; ?>
                </tr>
            </table>
            <table class="table compatibility">
                <tr>
                    <th colspan="4">
                        <?= Yii::t('install', 'Directory and file permissions') ?>
                    </th>
                </tr>
                <tr class="text-bold" style="background-color: #f7f7f7">
                    <td><?= Yii::t('node', 'Path') ?></td>
                    <td style="width: 5%;" class="text-center">R</td>
                    <td style="width: 5%;" class="text-center">W</td>
                    <td style="width: 5%; text-align: center !important;">X</td>
                </tr>
                <?php foreach ($locations as $location): ?>
                    <?php if(is_null($location['path'])) continue; ?>
                    <tr>
                        <?php
                            echo "<td><code>{$location['path']}</code> ".Yii::t('install', 'should be'). ' ';
                            echo ($location['writable']) ? Yii::t('install', 'writable') : Yii::t('install', 'non-writable');
                            echo ', ';
                            if( !is_array($location['executable']) ) {
                                echo ($location['executable']) ? Yii::t('install', 'executable') : Yii::t('install', 'non-executable');
                            }
                            else {
                                foreach ($location['executable'] as $os => $executable) {
                                    if( mb_stripos(PHP_OS, $os) !== false ) {
                                        echo ($executable) ? Yii::t('install', 'executable') : Yii::t('install', 'non-executable');
                                    }
                                }
                            }
                            echo "</td>";
                            foreach ($location['errors'] as $error) {
                                if( $error === true ) {
                                    echo '<td class="text-danger" style="text-align: center !important;"><i class="fa fa-remove"></i></td>';
                                    $errors = true;
                                }
                                else {
                                    echo '<td class="text-success" style="text-align: center !important;"><i class="fa fa-check"></i></td>';
                                }
                            }
                        ?>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="box-footer">
                <?= Html::a('&laquo; '.Yii::t('app', 'Back'), ['database'], ['class' => 'btn btn-default pull-left']) ?>
                <?php
                    if(!$errors) {
                        echo Html::a(Yii::t('app', 'Next').' &raquo;', ['integrity'], [
                            'class' => 'btn btn-primary pull-right',
                            'id'    => 'next',
                            'data' => [
                                'method' => 'post'
                            ]
                        ]);
                    }
                    else {
                        echo '<div class="btn-group pull-right">';
                        echo Html::button(Yii::t('install', 'Erorrs found, can\'t proceed'), ['class' => 'btn btn-danger']);
                        echo '<button class="btn btn-default" onclick="location.reload();"><i class="fa fa-refresh"></i></button>';
                        echo '</div>';
                    }
                ?>
            </div>
        </div>
    </div>
</div>
