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
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var $this        yii\web\View
 * @var $out_tables  array
 */
app\assets\OutCustomAsset::register($this);

$this->title = Yii::t('app', 'Task output tables');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Task output tables')];
?>

<div class="row">

    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-header with-border">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'Out tables')?></h3>
            </div>
            <div class="box-body no-padding">
                <ul id="out_table_select" class="nav nav-pills nav-stacked">
                    <?php foreach ($out_tables as $table): ?>
                        <li>
                            <?php
                                echo Html::a($table, 'javascript:void(0);', [
                                    'id'            => $table,
                                    'class'         => 'load-grid-view',
                                    'data-ajax-url' => Url::to(['ajax-render-grid', 'table' => $table])
                                ]);
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <i class="fa fa-table"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'Woker result output') ?></h3>
                <div class="pull-right">
                    <?php
                        echo Html::a('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), 'javascript:void(0);', [
                            'class'         => 'btn btn-sm bg-light-black search-button ladda-button',
                            'data-ajax-url' => Url::to(['ajax-render-search']), /* Dynamic url. Url chages when out table is selected */
                            'data-style'    => 'zoom-in',
                            'style'         => ['display' => 'none']
                        ]);
                        ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <div class="loading" style="display: none; margin: 15px 0 15px 0;">
                    <span style="margin-left: 24%;">
                        <?= Html::img('@web/img/modal_loading.gif', ['alt' => Yii::t('app', 'Loading...')]) ?>
                    </span>
                </div>
                <div id="out_custom_search" style="display: none;"></div>
                <?php Pjax::begin(['id' => 'out-table-pjax', 'enablePushState' => false]); ?>
                    <div class="col-md-12">
                        <div class="callout callout-info" style="margin: 10px 0 10px 0;">
                            <p><?= Yii::t('network', 'Please select out table from list') ?></p>
                        </div>
                    </div>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>

</div>


