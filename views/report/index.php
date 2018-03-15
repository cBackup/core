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

use app\helpers\StringHelper;

/**
 * @var $this   yii\web\View
 * @var $disk   array
 */
$this->title = Yii::t('app', 'Reports');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports' )];

$this->registerJsFile('@web/plugins/flot/0.8.3/jquery.flot.pie.min.js', ['depends' => \app\assets\FlotAsset::class]);
$this->registerJs(/** @lang JavaScript */"
var data = [
	{ label: 'Free space',  data: ".$disk['free']."},
	{ label: 'Used space',  data: ".($disk['total'] - $disk['free'])."}
];

function labelFormatter(label, series) {
    return \"<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>\" + label + \"<br/>\" + Math.round(series.percent) + \"%</div>\";
}

//noinspection JSUnresolvedFunction
$.plot('#disk-stats', data, {
    series: {
        pie: {
            show: true,
            radius: 200,
            tilt: 0.4,
            label: {
                show: true,
                radius: 3/4,
                formatter: labelFormatter,
                background: { 
                    opacity: 0.5,
                    color: '#000'
                }
            }
        }
    },
    legend: {
        show: false
    },
    colors: ['#bce8f1', '#ebccd1']
});
");
?>
<div class="row">
    <span style="background-color: #d6e9c6;"></span>
    <span style="background-color: #bce8f1;"></span>
    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-database"></i>
                <h3 class="box-title"><?= Yii::t('app', 'Storage') ?></h3>
            </div>
            <div class="box-body" id="disk-stats" style="height: 200px"></div>
            <div class="box-footer">
                <div class="row">
                    <div class="col-sm-6 col-xs-6">
                        <div class="description-block border-right">
                            <h5 class="description-header"><?= StringHelper::beautifySize($disk['total']) ?></h5>
                            <span class="description-text">TOTAL DISK SIZE</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-6">
                        <div class="description-block">
                            <h5 class="description-header"><?= StringHelper::beautifySize($disk['total'] - $disk['free']) ?></h5>
                            <span class="description-text">USED DISK SPACE</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
