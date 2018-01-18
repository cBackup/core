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

use app\models\Setting;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

app\assets\LaddaAsset::register($this);
app\assets\Select2Asset::register($this);

/**
 * @var $this          yii\web\View
 * @var $searchModel   app\models\Node
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $networks      array
 * @var $credentials   array
 * @var $devices       array
 * @var $auth_list     array
 */
$query_param = Yii::$app->request->getQueryParam('NodeSearch');
$this->title = Yii::t('node', 'Nodes');
$this->params['breadcrumbs'][] = $this->title;

/** Register JS */
$this->registerJs(
    /** @lang JavaScript */
    "
        /** Init select2 without search */
        $('.select2').select2({
            width: '100%',
            minimumResultsForSearch: -1
        });
        
        /** Init select2 with search */
        $('.select2-search').select2({
            width: '100%',
            allowClear: true
        });

        /** Show search form on button click */
        $('.search-button').click(function() {
            $('.node-search').slideToggle('slow');
            return false;
        });
        
        /** Node search form submit and reload gridview */
        $('.node-search-form form').submit(function(e) {
            e.stopImmediatePropagation(); // Prevent double submit
            gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
            $.pjax.reload({container:'#node-list-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
            return false;
        });
    "
);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border"><i class="fa fa-list"></i>
                <h3 class="box-title box-title-align">
                    <?= Yii::t('node', 'Node list') ?>
                    <?php
                        if (!is_null($query_param) && array_key_exists('credential_id', $query_param) && !array_key_exists('adv_search', $query_param)) {
                            echo Html::tag('span', Yii::t('network', ':: Filtered by record id: <b>{0}</b>', $query_param['credential_id']), ['class' => 'margin-r-5']);
                            echo Html::a('<i class="fa fa-times"></i>', ['node/list'], [
                                'title' => Yii::t('network', 'Clear filter'),
                                'style' => ['color' => '#f39c12']
                            ]);
                        }
                    ?>
                </h3>
                <div class="pull-right">
                    <?= Html::a('<i class="fa fa-search"></i> ' . Yii::t('app', 'Search'), 'javascript:void(0);', ['class' => 'btn btn-sm bg-light-black search-button']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <div class="node-search" style="display: none;">
                    <?php
                        echo $this->render('_search', [
                            'model'       => $searchModel,
                            'networks'    => $networks,
                            'credentials' => $credentials,
                            'auth_list'   => $auth_list,
                            'devices'     => $devices,
                        ]);
                    ?>
                </div>
                <?php Pjax::begin(['id' => 'node-list-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'node-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'attribute' => 'id',
                                    'options'   => ['style' => 'width:7%'],
                                ],
                                [
                                    'attribute' => 'hostname',
                                    'format'    => 'raw',
                                    'value'     => function($data) { /** @var $data \app\models\Node */
                                        $link = (empty($data->hostname)) ? $data->ip : $data->hostname;
                                        return Html::a($link, ['/node/view', 'id' => $data->id], ['data-pjax' => '0']);
                                    }
                                ],
                                'ip',
                                [
                                    'attribute' => 'location',
                                    'value'     => function($data) { /** @var $data \app\models\Node */
                                        $prepend_location = (!is_null($data->prepend_location)) ? $data->prepend_location : Y::param('defaultPrependLocation');
                                        return "{$prepend_location} {$data->location}";
                                    }
                                ],
                                [
                                    'attribute' => 'device_name',
                                    'options'   => ['style' => 'width:17%'],
                                    'value'     => function($data) { /** @var $data \app\models\Node */
                                        return "{$data->device->vendor} {$data->device->model}";
                                    }
                                ],
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'modified',
                                    'value'     => function($data) {
                                        return Yii::$app->formatter->asDatetime($data->modified, 'php:'.Setting::get('datetime'));
                                    },
                                ],
                            ]
                        ]);
                    ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>
