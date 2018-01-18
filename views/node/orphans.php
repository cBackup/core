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
use yii\grid\GridView;
use app\models\Setting;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 */
$this->title = Yii::t('app', 'Orphans');
$this->params['breadcrumbs'][] = ['label' => Yii::t('node', 'Nodes')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Orphans')];
?>

<div class="row">
    <div class="col-md-8">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('node', 'List of orphans') ?></h3>
            </div>
            <div class="box-body no-padding">
                <?php
                    /** @noinspection PhpUnhandledExceptionInspection */
                    echo GridView::widget([
                        'id'           => 'orphans-grid',
                        'tableOptions' => ['class' => 'table table-bordered'],
                        'dataProvider' => $dataProvider,
                        'layout'  => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                        'columns' => [
                            [
                                'attribute' => 'hostname',
                                'format'    => 'raw',
                                'options'   => ['style' => 'width:30%'],
                                'value'     => function($data) { /** @var $data \app\models\Node */
                                    $link = (empty($data->hostname)) ? $data->ip : $data->hostname;
                                    return Html::a($link, ['/node/view', 'id' => $data->id]);
                                }
                            ],
                            'ip',
                            [
                                'attribute' => 'device_name',
                                'value'     => function($data) { /** @var $data \app\models\Node */
                                    return "{$data->device->vendor} {$data->device->model}";
                                }
                            ],
                            [
                                'format'    => 'raw',
                                'attribute' => 'created',
                                'value'     => function($data) { /** @var $data \app\models\Node */
                                    return Yii::$app->formatter->asDatetime($data->created, 'php:'.Setting::get('datetime'));
                                },
                            ],
                            [
                                'class'          => 'yii\grid\ActionColumn',
                                'contentOptions' => ['class' => 'narrow'],
                                'template'       => '{assign_task}',
                                'buttons'        => [
                                    'assign_task' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Node */
                                        return Html::a('<i class="fa fa-plus-square-o"></i>', ['/network/assigntask/assign-node-task', 'id' => $model->id], [
                                            'title'  => Yii::t('network', 'Assign task to node'),
                                            'target' => '_blank'
                                        ]);
                                    },
                                ],
                            ]
                        ]
                    ]);
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-info-circle"></i> <?= Yii::t('app', 'Information') ?>
                </h3>
            </div>
            <div class="box-body text-justify">
                <p><?= Yii::t('node', 'This is the list of nodes not assigned to any task. If node exists in exclusion list, node will not appear in orphans list. Please note that although this interface allows to assign node to a specific task, this is not the most efficient way to do it. If you have more than 5 orphans please use "Advanced node assign" interface.') ?></p>
            </div>
        </div>
    </div>

</div>

