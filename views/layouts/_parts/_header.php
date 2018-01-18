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
use app\helpers\StringHelper;
use app\widgets\MessageWidget;
?>
<header class="main-header">
    <a href="<?= Yii::$app->homeUrl ?>" class="logo">
        <span class="logo-mini"><b>c</b>B</span>
        <span class="logo-lg"><b>c</b>Backup</span>
    </a>
    <nav class="navbar navbar-static-top">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>

        <ul class="nav navbar-nav pull-right">
            <li class="dropdown notifications-menu">
                <?php
                    echo Html::a('<i class="fa fa-hdd-o"></i>', 'javascript:void(0);', [
                        'id'            => 'load_services_menu',
                        'class'         => 'dropdown-toggle',
                        'data-toggle'   => 'dropdown',
                        'data-ajax-url' => \yii\helpers\Url::to(['/site/ajax-render-service'])
                    ]);
                ?>
                <ul class="dropdown-menu dropdown-menu-static pull-right">
                    <li class="header">
                        <?php
                            echo Yii::t('app', 'Daemon status');
                            echo Html::tag('span', '<i class="fa fa-refresh"></i>', [
                                'class'     => 'pull-right',
                                'style'     => ['cursor' => 'pointer'],
                                'title'     => Yii::t('app', 'Refresh'),
                                'onclick'   => 'loadServiceWidget()'
                            ]);
                        ?>
                    </li>
                    <li id="loader">
                        <div style="margin-left: 15%; padding:2.1em 0 2.1em 0">
                            <?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?>
                        </div>
                    </li>
                    <li id="widget_content"></li>
                </ul>
            </li>
            <?php
                /** @noinspection PhpUnhandledExceptionInspection */
                echo MessageWidget::widget();
            ?>
            <li class="dropdown classic-menu-dropdown">
                <a href="javascript:" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <?= /** @noinspection PhpUndefinedFieldInspection */ StringHelper::ucname(Yii::$app->user->identity->userid) ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu pull-right">
                    <li>
                        <a href="<?= \yii\helpers\Url::to(['/user/profile']) ?>"><i class="fa fa-user"></i> <?= Yii::t('user', 'Profile') ?></a>
                    </li>
                    <li>
                        <a href="<?= \yii\helpers\Url::to(['/user/settings']) ?>"><i class="fa fa-cog"></i> <?= Yii::t('app', 'Settings') ?></a>
                    </li>
                    <li>
                        <?php
                            echo Html::a('<span class="fa fa-sign-out"></span> '.Yii::t('user', 'Logout'), ['/user/logout'], [
                                'data' => ['method' => 'post'],
                                'class' => 'nav-link',
                            ]);
                        ?>
                    </li>
                </ul>
            </li>
        </ul>

    </nav>
</header>
