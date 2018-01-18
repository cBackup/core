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
 * @var $this         yii\web\View
 * @var $phpinfo      array
 * @var $SERVER       $_SERVER
 * @var $perms        array
 * @var $extensions   array
 * @var $plugins      \app\models\Plugin[]
 */
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Help'), 'url' => ['/help']];
$this->params['breadcrumbs'][] = Yii::t('app', 'About');
$this->title = Yii::t('app', 'About');
?>
<div class="row">
    <div class="col-md-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#tab_1" data-toggle="tab"><?= Yii::t('app', 'System') ?></a>
                </li>
                <li>
                    <a href="#tab_2" data-toggle="tab"><?= Yii::t('app', 'Diagnostics') ?></a>
                </li>
                <li>
                    <a href="#tab_3" data-toggle="tab">$_SERVER</a>
                </li>
                <li>
                    <a href="#tab_4" data-toggle="tab">PHP info</a>
                </li>
                <li>
                    <a href="#tab_5" data-toggle="tab"><?= Yii::t('help', 'Licenses') ?></a>
                </li>
                <li class="dropdown pull-right tabdrop">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-ellipsis-v"></i>&nbsp;<i class="fa fa-angle-down"></i></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="https://github.com/cBackup/main/issues" target="_blank"><?= Yii::t('help', 'Submit issue') ?></a>
                        </li>
                        <li>
                            <a href="<?= \yii\helpers\Url::to(['/help/support']) ?>"><?= Yii::t('help', 'Create support bundle') ?></a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade active in" id="tab_1">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th colspan="3" class="bg-info"><?= Yii::t('app', 'Generic info') ?></th>
                        </tr>
                        <tr>
                            <th><?= Yii::t('app', 'Parameter') ?></th>
                            <th><?= Yii::t('app', 'Value') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><?= Yii::t('app', 'cBackup version') ?></td>
                            <td colspan="2"><?= Yii::$app->version ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('app', 'Environment') ?></td>
                            <td colspan="2">
                                <?php
                                    $class = (YII_ENV_DEV || YII_ENV_TEST) ? 'red' : 'regular';
                                    echo "<span class='text-$class'>".YII_ENV."</span>";
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('app', 'Debug mode') ?></td>
                            <td colspan="2">
                                <?php
                                    echo (YII_DEBUG) ? "<span class='text-red'>".Yii::t('app', 'Yes')."</span>" : Yii::t('app', 'No')
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'Server platform') ?></td>
                            <td colspan="2"><?= php_uname("s") . ' ' . php_uname("r") ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'Framework') ?></td>
                            <td colspan="2">Yii <?= Yii::getVersion() ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'Framework database driver') ?></td>
                            <td colspan="2"><?= Yii::$app->db->driverName ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'Database server version') ?></td>
                            <td colspan="2"><?= (new \yii\db\Query())->select('version()')->scalar(); ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'Database client version') ?></td>
                            <td colspan="2"><?= mysqli_get_client_info() ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'PHP version') ?></td>
                            <td colspan="2"><?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'Web server') ?></td>
                            <td colspan="2"><?= $SERVER['SERVER_SOFTWARE'] ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'PHP interface') ?></td>
                            <td colspan="2"><?= php_sapi_name() ?></td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'Java version') ?></td>
                            <td colspan="2">
                                <?php
                                    $java = \app\models\Sysinfo::getJavaVersion();
                                    echo (is_null($java)) ? '<span class="text-red">' . \Yii::t('app', 'not found') . '</span>' :  $java;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?= Yii::t('help', 'Git version') ?></td>
                            <td colspan="2">
                                <?php
                                    $git = \app\models\Sysinfo::getGitVersion();
                                    echo (is_null($git)) ? '<span class="text-red">' . \Yii::t('app', 'not found') . '</span>' : $git;
                                ?>
                            </td>
                        </tr>
                    <?php if(!empty($plugins)): ?>
                        </tbody>
                        <thead>
                        <tr>
                            <th colspan="3" class="bg-info"><?= Yii::t('app', 'Plugins') ?></th>
                        </tr>
                        <tr>
                            <th><?= Yii::t('app', 'Plugin') ?></th>
                            <th><?= Yii::t('app', 'Description') ?></th>
                            <th class="narrow"><?= Yii::t('app', 'Enabled') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($plugins as $plugin): ?>
                            <tr>
                                <td><?= $plugin->name ?></td>
                                <td><?= $plugin->description ?></td>
                                <td class="narrow">
                                    <?php
                                        if($plugin->enabled) {
                                            echo Html::tag('span', Yii::t('app', 'Yes'), ['class' => 'label pull-right bg-green']);
                                        } else {
                                            echo Html::tag('span', Yii::t('app', 'No'), ['class' => 'label pull-right bg-red']);
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade in" id="tab_2">
                    <?php if( mb_stripos(PHP_OS, 'Linux') === false ) : ?>
                        <div class="alert alert-warning">
                            <p><?= Yii::t('help', "We don't officially support cBackup in non-Linux environment yet. Use it at own and sole discretion.") ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if(\app\models\Install::checkWorldAccess()): ?>
                        <div class="alert alert-danger">
                            <p>
                                <?php
                                    /** @noinspection HtmlUnknownTarget */
                                    echo Yii::t('help', 'Web server is configured incorrectly, no data outside of ./web folder should be accessible. E.g.: <a href="{url}">readme.md</a>', ['url' => "./../README.md"]);
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <table class="table">
                        <tr class="info text-bolder">
                            <td colspan="4"><?= Yii::t('install', 'Directory and file permissions') ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('node', 'Path') ?></th>
                            <th class="text-right">R</th>
                            <th class="text-right">W</th>
                            <th class="text-right">X</th>
                        </tr>
                        <?php foreach ($perms as $perm): ?>
                            <?php if(is_null($perm['path'])) continue; ?>
                            <tr>
                                <?php
                                    echo "<td><code>{$perm['path']}</code> ".Yii::t('install', 'should be'). ' ';
                                    echo ($perm['writable']) ? Yii::t('install', 'writable') : Yii::t('install', 'non-writable');
                                    echo ', ';
                                    if( !is_array($perm['executable']) ) {
                                        echo ($perm['executable']) ? Yii::t('install', 'executable') : Yii::t('install', 'non-executable');
                                    }
                                    else {
                                        foreach ($perm['executable'] as $os => $executable) {
                                            if( mb_stripos(PHP_OS, $os) !== false ) {
                                                echo ($executable) ? Yii::t('install', 'executable') : Yii::t('install', 'non-executable');
                                            }
                                        }
                                    }
                                    echo "</td>";
                                    foreach ($perm['errors'] as $error) {
                                        if( $error === true ) {
                                            echo '<td class="text-danger" style="text-align: right !important;"><i class="fa fa-remove"></i></td>';
                                            $errors = true;
                                        }
                                        else {
                                            echo '<td class="text-success" style="text-align: right !important;"><i class="fa fa-check"></i></td>';
                                        }
                                    }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <table class="table">
                        <tr class="info text-bolder">
                            <td colspan="2"><?= Yii::t('install', 'PHP Extensions') ?></td>
                        </tr>
                        <?php foreach ($extensions as $extension => $value): ?>
                        <tr>
                            <td><?= $extension ?> extension</td>
                            <?php
                                if($value) {
                                    echo '<td class="text-success text-right"><i class="fa fa-check"></i></td>';
                                }
                                else {
                                    echo '<td class="text-danger text-right"><i class="fa fa-remove"></i></td>';
                                }
                            ?>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="tab-pane fade" id="tab_3">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th><?= Yii::t('app', 'Key') ?></th>
                            <th><?= Yii::t('app', 'Value') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($SERVER as $key => $val): ?>
                            <tr>
                                <td><?= $key ?></td>
                                <td>
                                    <?php
                                    if( is_array($val) ) {
                                        print_r($val);
                                    }
                                    else {
                                        echo $val;
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="tab_4">
                    <table class="table table-hover">
                        <?php foreach($phpinfo as $key=>$val): ?>
                            <thead>
                            <tr class="info">
                                <th colspan="2"><b><?= $key ?></b></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($val as $param => $data): ?>
                                <?php if( is_int($param) ): ?>
                                    <tr class="active">
                                        <td colspan="2">
                                            <?= strip_tags($data) ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?= $param ?></td>
                                        <td>
                                            <?php echo is_array($data) ? $data[0] : $data ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </tbody>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div class="tab-pane fade" id="tab_5">
                    <dl>
                        <dt>cBackup</dt>
                        <dd>
                            cBackup [siː ˈbækʌp] &mdash; network equipment configuration backup software that is published
                            under the GNU <a href="http://opensource.org/licenses/AGPL-3.0" target="_blank"> Affero
                            General Public License</a> (AGPLv3).<br>Copyright 2017 &copy; <a href="http://cbackup.me" target="_blank">
                            cBackup Team</a>: Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
                        </dd>
                        <dt>Template</dt>
                        <dd>
                            <a href="https://github.com/almasaeed2010/AdminLTE" target="_blank">AdminLTE</a> by
                            <a href='https://almsaeedstudio.com/' target='_blank'>Almsaeed Studio</a> under
                            <a href="https://github.com/almasaeed2010/AdminLTE/blob/master/LICENSE" target="_blank">MIT
                            license</a>, incapsulates jQuery, jQueryUI and Bootstrap.
                        </dd>
                        <dt>Yii Framework</dt>
                        <dd>
                            <a href="http://yiiframework.com" target="_blank">Yii 2</a> &mdash; high-performance PHP
                            framework under <a href="http://www.yiiframework.com/license/" target="_blank">BSD License</a>.
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
