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
use yii\helpers\Inflector;

/**
 * @var $searchModel  app\models\search\OutCustomSearch
 * @var $model        array
 */
?>

<div class="nav-tabs-custom" style="margin-bottom: 0;">
    <ul class="nav nav-tabs">
        <?php foreach ($searchModel->custom_fields as $li_key => $li_field): ?>
            <?php $active = ($li_key == 0) ? 'active' : ''; ?>
            <li class="<?= $active ?>">
                <a href="#tab_<?= "{$li_field->name}_{$model['id']}" ?>" data-toggle="tab" aria-expanded="false"><?= $li_field->name ?></a>
            </li>
        <?php endforeach; ?>
        <?php if (array_key_exists('raw_data', $model)): ?>
            <li>
                <a href="#tab_raw_data_<?= $model['id'] ?>" data-toggle="tab" aria-expanded="false">
                    <?= Yii::t('network', Inflector::humanize('raw_data')) ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <?php foreach ($searchModel->custom_fields as $tab_key => $tab_field): ?>
            <?php $active = ($tab_key == 0) ? 'active' : ''; ?>
            <div class="tab-pane <?= $active ?>" id="tab_<?= "{$tab_field->name}_{$model['id']}" ?>">
                <?php if (!empty($model[$tab_field->name])): ?>
                    <?= Html::tag('pre', $model[$tab_field->name]) ?>
                <?php else: ?>
                    <div class="callout callout-info" style="margin: 5px">
                        <p><?= Yii::t('network', 'Job did not return result')?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (array_key_exists('raw_data', $model)): ?>
            <div class="tab-pane" id="tab_raw_data_<?= $model['id'] ?>">
                <?= Html::tag('pre', $model['raw_data']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
