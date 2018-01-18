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
 * @var $content    string
 */
app\assets\InstallAsset::register($this);
$this->beginPage();
?>
<!DOCTYPE html>
<!--suppress HtmlUnknownTarget -->
<html lang="<?= Yii::$app->language ?>">
<head>

    <meta charset="<?= Yii::$app->charset ?>" />
    <title><?= Html::encode($this->title) ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <?php
        echo Html::csrfMetaTags();
        $this->head();
    ?>

    <link rel="apple-touch-icon" sizes="180x180" href="<?= Yii::getAlias('@web') ?>/../apple-touch-icon.png">
    <link rel="icon" type="image/png" href="<?= Yii::getAlias('@web') ?>/../favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="<?= Yii::getAlias('@web') ?>/../favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="<?= Yii::getAlias('@web') ?>/../manifest.json">
    <link rel="mask-icon" href="<?= Yii::getAlias('@web') ?>/../safari-pinned-tab.svg" color="#5bbad5">
    <meta name="apple-mobile-web-app-title" content="cBackup">
    <meta name="application-name" content="cBackup">
    <meta name="theme-color" content="#ffffff">

</head>
<body class="hold-transition skin-blue sidebar-mini layout-boxed">
<?php $this->beginBody() ?>
<div class="wrapper">

    <header class="main-header">
        <a href="" class="logo">
            <span class="logo-mini"><b>c</b>B</span>
            <span class="logo-lg"><?= Yii::t('install', '<b>c</b>Backup Installation') ?></span>
        </a>
        <nav class="navbar navbar-static-top">
            <a href="" class="sidebar-toggle pull-right" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
        </nav>
    </header>
    <aside class="main-sidebar">
        <section class="sidebar">
            <?php

                /**
                 * Check if current request route is identical to param to
                 * highlight corresponding menu item as 'active'
                 *
                 * @param  array $routes
                 * @return bool
                 */
                $checkRoute = function($routes) {

                    $retval = false;
                    $check  = $this->context->action->uniqueId;

                    if (in_array($check, array_map('trim', $routes, [" \t\n\r\0\x0B/"]))) {
                        $retval = true;
                    }

                    return $retval;

                };

                /**
                 * @param  int $step
                 * @return array
                 */
                $getClass = function($step) {

                    $steps  = ['greeting', 'requirements', 'database', 'integrity', 'finalize'];
                    $index  = Yii::$app->session->get('complete');
                    $cur_ix = array_search($this->context->action->id, $steps);

                    if( $index == null || ($step > $index+1 && $cur_ix != $step)) {
                        return ['class' => 'incomplete'];
                    }

                    return [];

                };

                /** @noinspection PhpUnhandledExceptionInspection */
                echo \yii\widgets\Menu::widget([
                    'encodeLabels'    => false,
                    'options'         => [ 'class' => 'sidebar-menu' ],
                    'items'           => [
                        [
                            'label'   => '<i class="num">0.</i><span>'.Yii::t('install', 'Greeting').'</span>',
                            'url'     => Yii::$app->homeUrl,
                            'active'  => $checkRoute(['install/index']),
                        ],
                        [
                            'label'   => '<i class="num">1.</i><span>'.Yii::t('install', 'Requirements').'</span>',
                            'url'     => ['requirements'],
                            'options' => $getClass(1),
                            'active'  => $checkRoute(['install/requirements']),
                        ],
                        [
                            'label'   => '<i class="num">2.</i><span>'.Yii::t('install', 'System setup').'</span>',
                            'url'     => ['database'],
                            'options' => $getClass(2),
                            'active'  => $checkRoute(['install/database']),
                        ],
                        [
                            'label'   => '<i class="num">3.</i><span>'.Yii::t('install', 'System integrity check').'</span>',
                            'url'     => ['integrity'],
                            'options' => $getClass(3),
                            'active'  => $checkRoute(['install/integrity']),
                        ],
                        [
                            'label'   => '<i class="num">4.</i><span>'.Yii::t('install', 'Finalization').'</span>',
                            'url'     => ['finalize'],
                            'options' => $getClass(4),
                            'active'  => $checkRoute(['install/finalize']),
                        ],
                    ],
                ]);
            ?>
        </section>
    </aside>
    <div class="content-wrapper">
        <section class="content-header">
            <h1><?= $this->title ?></h1>
        </section>
        <section class="content">

            <?php $flashes = Yii::$app->getSession()->getAllFlashes(); ?>
            <?php if( !empty($flashes) ): ?>
                <?php foreach ($flashes as $key => $message): ?>
                    <div class="alert alert-<?= $key ?> alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <?= is_array($message) ? join('<br>', $message) : $message; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $content ?>

        </section>
    </div>
    <footer class="main-footer">
        <div class="pull-right">
            <?= Yii::t('app', 'Version') ?> <b><?= Yii::$app->version ?></b>
        </div>
    </footer>

</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
