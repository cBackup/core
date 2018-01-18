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
use app\assets\AlphaAsset;

/**
 * @var $this       \yii\web\View
 * @var $content    string
 */
AlphaAsset::register($this);
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

    <link rel="apple-touch-icon" sizes="180x180" href="<?= Yii::getAlias('@web') ?>/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="<?= Yii::getAlias('@web') ?>/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="<?= Yii::getAlias('@web') ?>/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="<?= Yii::getAlias('@web') ?>/manifest.json">
    <link rel="mask-icon" href="<?= Yii::getAlias('@web') ?>/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="apple-mobile-web-app-title" content="cBackup">
    <meta name="application-name" content="cBackup">
    <meta name="theme-color" content="#ffffff">

</head>
<body class="hold-transition <?= /** @noinspection PhpUndefinedFieldInspection */ $this->context->action->id ?>-page">
<?php $this->beginBody() ?>
    <?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
