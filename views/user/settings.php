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
use yii\widgets\ActiveForm;

/**
 * @var $this         yii\web\View
 * @var $data         array
 */
app\assets\ToggleAsset::register($this);

$this->title = Yii::t('user', 'Profile settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Profile settings')];
?>

<?php $form = ActiveForm::begin(['id' => 'settings']); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <i class="fa fa-cogs"></i>
                    <h3 class="box-title"><?= Yii::t('user', 'Interface personalization') ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-8 settings-label">
                                    <span class="text-bold"><?= Yii::t('user', 'Collapsed sidebar') ?></span><br>
                                    <small class="text-muted"><?= Yii::t('user', 'Display compact main menu sidebar with hover menu') ?></small>
                                </div>
                                <div class="col-md-4">
                                    <?php
                                        echo Html::checkbox('Setting[sidebar_collapsed]', $data['sidebar_collapsed'], [
                                            'data-toggle' => 'toggle',
                                            'data-size'   => 'normal',
                                            'uncheck'     => 0,
                                            'data-on'     => Yii::t('app', 'On'),
                                            'data-off'    => Yii::t('app', 'Off')
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-8 settings-label">
                                    <span class="text-bold"><?= Yii::t('app', 'Language') ?></span><br>
                                    <small class="text-muted"><?= Yii::t('user', 'cBackup system interface language') ?></small>
                                </div>
                                <div class="col-md-4">
                                    <?php
                                        echo Html::dropDownList('Setting[language]', $data['language'], [
                                            'en-US' => 'English',
                                            'ru-RU' => 'Русский'
                                        ], [
                                            'class' => 'form-control',
                                        ])
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 1em;">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-8 settings-label">
                                    <span class="text-bold"><?= Yii::t('app', 'Short date format') ?></span><br>
                                    <small class="text-muted">
                                        <?php
                                            /** @noinspection HtmlUnknownTarget */
                                            echo Yii::t('app', 'Date, month, year label formatting, see <a href="{url}" target="_blank">PHP date()</a> arguments', ['url' => 'http://docs.php.net/manual/en/function.date.php']);
                                        ?>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <?php
                                        echo Html::textInput('Setting[date]', $data['date'], [
                                            'class' => 'form-control',
                                        ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-8 settings-label">
                                    <span class="text-bold"><?= Yii::t('app', 'Full date format') ?></span><br>
                                    <small class="text-muted">
                                        <?php
                                            /** @noinspection HtmlUnknownTarget */
                                            echo Yii::t('app', 'Date, month, year and timestamp label formatting, see <a href="{url}" target="_blank">PHP date()</a> arguments', ['url' => 'http://docs.php.net/manual/en/function.date.php'])
                                        ?>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <?php
                                        echo Html::textInput('Setting[datetime]', $data['datetime'], [
                                            'class' => 'form-control',
                                        ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <?= Html::submitButton(Yii::t('app', 'Save changes'), ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>
