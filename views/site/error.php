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
 * @var $this       yii\web\View
 * @var $name       string
 * @var $message    string
 * @var $exception  \yii\base\Exception
 */
$this->title           = $name;

/** @noinspection PhpUndefinedFieldInspection */
$this->context->layout = 'plain';

?>
<section class="content">

    <div class="error-page">
        <h2 class="headline text-red">
            <?php
                if( isset($exception->statusCode) ) {
                    echo $exception->statusCode;
                }
                else {
                    echo 'ERR';
                }
            ?>
        </h2>

        <div class="error-content">
            <h3><i class="fa fa-warning text-red"></i> <?= Html::encode($exception->getMessage()) ?></h3>
            <p>
                <?= Yii::t('app', 'An error occurred while the Web server was processing your request') ?>.<br>
                <?= Yii::t('app', 'Please contact us if you think this is a system error. Thank you') ?>.
            </p>
        </div>
    </div>

</section>
