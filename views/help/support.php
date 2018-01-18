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
 * @var $this    yii\web\View
 * @var $phpinfo array
 */
app\assets\ToggleAsset::register($this);

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Help'), 'url' => ['/help']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'About'), 'url' => ['/help/about']];
$this->params['breadcrumbs'][] = Yii::t('help', 'Create support bundle');
$this->title = Yii::t('help', 'Create support bundle');
?>
<div class="row">
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Create') ?></h3>
            </div>
            <div class="box-body">
                <?= Html::beginForm('', 'post', ['class' => 'form-inline']) ?>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar">0%</div>
                    </div>
                    <?php
                        echo Html::hiddenInput('encryption', '0');
                        echo Html::checkbox('encryption', true, [
                            'class' => 'pull-left',
                            'data-on' => Yii::t('help', 'Encrypted'),
                            'data-off' => Yii::t('help', 'Plaintext'),
                            'data-size' => 'normal',
                            'data-toggle' => 'toggle',
                            'value' => '1',
                        ]);
                        echo Html::submitButton(Yii::t('help', 'Create and download'), ['class' => 'btn btn-success pull-right'])
                    ?>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Information') ?></h3>
            </div>
            <div class="box-body text-justify">
                <?php
                    /** @noinspection HtmlUnknownTarget */
                    echo Yii::t('help', 'You can submit your issue <a href="{url}" target="_blank">here</a> providing detailed description and attaching actual support bundle file. Upon your choise the bundle file can be encrypted or plain-text.', ['url' => 'https://github.com/cBackup/main/issues']);
                ?>
                <br><br>
                <?= Yii::t('help', 'The support bundle is the encrypted (by default) file that contains data necessary for support and issue diagnostic. It does not contain any private data sucha as passwords, backups or its fragments or configuration snapshots. Bundle contains server software versions and fingerprints, database data flow statistics, database and filebase sizes.') ?>
            </div>
        </div>
    </div>
</div>
