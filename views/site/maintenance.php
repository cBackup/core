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

/** @var $this yii\web\View                   */
/** @noinspection PhpUndefinedFieldInspection */
$this->context->layout = 'plain';
$this->title = Yii::t('app', 'Maintenance');
$this->registerCss(/** @lang CSS */ '
    body {background-color: #d2d6de;}
    h1 { font-size: 45px;}
');
?>

<div class="lockscreen-wrapper">
    <div class="lockscreen-logo">
        <span><i class="fa fa-cogs fa-5x" style="color: #575757" aria-hidden="true"></i></span>
    </div>
</div>

<div class="help-block text-center">
    <h1><?= Yii::t("app", "Sorry, we're down for maintenance.") ?></h1>
</div>
<div class="help-block text-center">
    <h3><?= Yii::t("app", "We'll be back shortly.") ?></h3>
</div>
