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
 * @var $this \yii\web\View
 */
$this->title = Yii::t('install', 'Requirements');
$errors      = false;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <table class="table compatibility">
                <tr>
                    <th colspan="2">MySQL</th>
                </tr>
                <tr>
                    <td><?= Yii::t('install', '{0} version', ['MySQL']) ?></td>
                    <?php
                        $mysql = \app\helpers\SystemHelper::exec('mysql -V');
                        if( $mysql->exitcode ) {
                            $errors = true;
                            echo '<td class="text-danger"><i class="fa fa-remove"></i></td>';
                        }
                        else {
                            echo '<td class="text-success">'.$mysql->stdout.'</td>';
                        }
                    ?>
                </tr>
                <tr>
                    <th colspan="2">PHP</th>
                </tr>
                <tr>
                    <td><?= Yii::t('install', '{0} version', ['PHP']) ?></td>
                    <?php
                        $phpversion = phpversion();
                        if(version_compare($phpversion, '7.0.0') >= 0):
                    ?>
                        <td class="text-success"><?= $phpversion ?></td>
                    <?php else: ?>
                        <td class="text-danger"><?= $phpversion ?></td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <td><?= Yii::t('install', 'Disabled functions') ?></td>
                    <?php
                        $disabled_funcs = explode(' ', ini_get('disable_functions'));
                        $disabled_error = false;
                        $class          = 'text-success';

                        foreach ($disabled_funcs as $disabled_func) {
                            if(in_array($disabled_func, ['exec', 'shell_exec', 'system'])) {
                                $class  = 'text-danger';
                                $func[] = $disabled_func;
                            }
                        }
                    ?>
                    <td class="<?= $class ?>">
                        <?php if(empty($func)): ?>
                            <i class="fa fa-check"></i>
                        <?php else: ?>
                            <?= join(', ', $func) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><?= Yii::t('install', 'Memory limit') ?></td>
                    <?php
                        $limit = str_replace(array('G', 'M', 'K'), array('000000000', '000000', '000'), ini_get('memory_limit'));
                        if($limit < 128000000):
                    ?>
                        <td class="text-danger"><?= ini_get('memory_limit') ?></td>
                    <?php else: ?>
                        <td class="text-success"><?= ini_get('memory_limit') ?></td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <th colspan="2"><?= Yii::t('install', 'PHP Extensions') ?></th>
                </tr>
                <?php foreach ($extensions as $extension => $value): ?>
                    <tr>
                        <td><?= Yii::t('install', '{0} extension', [$extension]) ?></td>
                        <?php
                            $class = ['text-success', 'fa-check'];
                            if(!$value) {
                                $errors = true;
                                $class  = ['text-danger', 'fa-remove'];
                            }
                        ?>
                        <td class="<?= $class[0] ?>">
                            <i class="fa <?= $class[1] ?>"></i>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="2">Java</th>
                </tr>
                <tr>
                    <?php
                        $java = \app\helpers\SystemHelper::exec('java -version');
                        if( $java->exitcode ):
                            $errors = true;
                    ?>
                        <td>Java</td>
                        <td class="text-danger">
                            <i class="fa fa-remove"></i>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: left;" class="bg-warning text-italic text-danger">
                            <?= Yii::t('install', "Without Java in PATH, system won't be able to start scheduled jobs and workers. Please install Java 8.0.110 or newer to proceed") ?>
                        </td>
                    <?php else: ?>
                        <td colspan="2" class="text-success" style="white-space: normal; text-align: left">
                            <?= $java->stdout.$java->stderr ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <tr>
                    <th colspan="2">Git</th>
                </tr>
                <tr>
                    <td><?= Yii::t('install', '{0} version', ['Git']) ?></td>
                    <?php
                        $git = \app\helpers\SystemHelper::exec('git version');
                        if( $git->exitcode ):
                            $errors = true;
                    ?>
                    <td class="text-danger">
                        <i class="fa fa-remove"></i>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: left; white-space: normal;" class="text-danger bg-warning text-italic">
                        <?= Yii::t('install', "Without Git you won't be able to use the versioning of stored configuration files. However, this is not critical and you may proceed with installation.") ?>
                    </td>
                    <?php else: ?>
                        <td class="text-success">
                            <?= $git->stdout ?>
                        </td>
                    <?php endif; ?>
                </tr>
            </table>
            <div class="box-footer">
                <?= Html::a('&laquo; '.Yii::t('app', 'Back'), ['index'], ['class' => 'btn btn-default pull-left']) ?>
                <?php if(!$errors): ?>
                    <?=
                        Html::a(Yii::t('app', 'Next').' &raquo;', ['requirements'], [
                            'class' => 'btn btn-primary pull-right',
                            'data'  => [
                                'method' => 'post'
                            ]
                        ])
                    ?>
                <?php else: ?>
                    <div class="btn-group pull-right">
                        <button class="btn btn-danger"><?= Yii::t('install', "Erorrs found, can't proceed") ?></button>
                        <button class="btn btn-default" onclick="location.reload();"><i class="fa fa-refresh"></i></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
