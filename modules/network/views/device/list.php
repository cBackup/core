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
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $this           yii\web\View
 * @var $searchModel    app\models\search\DeviceSearch
 * @var $dataProvider   yii\data\ActiveDataProvider
 * @var $vendors        array
 * @var $unkn_count     integer
 */
$this->title = Yii::t('app', 'Devices');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory' )];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-9">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'Device list') ?></h3>
                <div class="pull-right">
                    <div class="btn-group margin-r-5">
                        <?= Html::a(Yii::t('network', 'View unknown devices'), ['unknown-list'], ['class' => 'btn btn-sm btn-default'])?>
                        <span class="btn btn-sm <?= ($unkn_count > 0) ? 'btn-warning' : 'bg-light-black' ?>" style="cursor: default"><?= $unkn_count ?></span>
                    </div>
                    <?= Html::a(Yii::t('network', 'Add device'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'device-pjax']); ?>
                <?=
                    /** @noinspection PhpUnhandledExceptionInspection */
                    GridView::widget([
                        'id'           => 'device-grid',
                        'tableOptions' => ['class' => 'table table-bordered'],
                        'dataProvider' => $dataProvider,
                        'filterModel'  => $searchModel,
                        'afterRow'     => function($model) { /** @var $model \app\models\Device */
                            $id = "info_{$model->id}";
                            return '<tr><td class="grid-expand-row" colspan="5"><div class="grid-expand-div" id="'.$id.'"></div></td></tr>';
                        },
                        'layout'       => '{items}<div class="row"><div class="col-sm-4"><div class="gridview-summary">{summary}</div></div><div class="col-sm-8"><div class="gridview-pager">{pager}</div></div></div>',
                        'columns' => [
                            [
                                'format'         => 'raw',
                                'options'        => ['style' => 'width:3%'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value'          => function($model) { /** @var $model \app\models\Device */
                                    return Html::a('<i class="fa fa-caret-square-o-down"></i>', 'javascript:;', [
                                        'class'         => 'ajaxGridExpand',
                                        'title'         => Yii::t('app', 'Device attributes'),
                                        'data-ajax-url' => Url::to(['device/ajax-get-device-attributes', 'device_id' => $model->id]),
                                        'data-div-id'   => "#info_{$model->id}",
                                        'data-multiple' => 'false'
                                    ]);
                                },
                            ],
                            'vendor',
                            'model',
                            [
                                'attribute'     => 'auth_template_name',
                            ],
                            [
                                'class'    => \yii\grid\ActionColumn::class,
                                'template' =>'{edit} {delete}',
                                'buttons'  => [
                                    'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Device */
                                        return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/network/device/edit', 'id' => $model->id], [
                                            'title'     => Yii::t('app', 'Edit'),
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Device */
                                        return Html::a('<i class="fa fa-trash-o"></i>', ['/network/device/delete', 'id' => $model->id], [
                                            'title' => Yii::t('app', 'Delete'),
                                            'style' => 'color: #D65C4F',
                                            'data'  =>[
                                                'pjax'      => '0',
                                                'method'    => 'post',
                                                'confirm'   => Yii::t('network', 'Are you sure you want to delete device {0} {1}?', [$model->vendor, $model->model]),
                                                'params'    => ['id' => $model->id],
                                            ]
                                        ]);
                                    },
                                ],
                                'contentOptions' => [
                                    'class' => 'narrow'
                                ]
                            ],
                        ],
                    ]);
                ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'Vendors') ?></h3>
                <div class="box-tools pull-right">
                    <?php
                        echo Html::a('<i class="fa fa-plus"></i>', ['vendor/add'], [
                            'class' => 'btn btn-box-tool',
                            'style' => 'margin-top: 7px;',
                            'title' => Yii::t('app', 'Add')
                        ]);
                    ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table">
                    <?php foreach ($vendors as $vendor): ?>
                        <tr>
                            <td><?= $vendor['name'] ?></td>
                            <td class="narrow">
                                <?php
                                    echo Html::a('<i class="fa fa-pencil-square-o"></i>', ['vendor/edit', 'name' => $vendor['name']],
                                        ['title' => Yii::t('app', 'Edit')]
                                    );
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

</div>
