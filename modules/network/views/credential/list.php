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
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\grid\GridView;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $searchModel   app\models\search\NetworkSearch
 */
$this->title = Yii::t('app', 'Credentials');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Inventory' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Credentials')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('network', 'List of credentials') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('network', 'Add credential'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'credential-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'credential-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'afterRow'     => function($model) { /** @var $model \app\models\Credential */
                                $id = 'credential_' . $model->id;
                                return '<tr><td class="grid-expand-row" colspan="9"><div class="grid-expand-div" id="'.$id.'"></div></td></tr>';
                            },
                            'layout'  => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'         => 'raw',
                                    'options'        => ['style' => 'width:3%'],
                                    'contentOptions' => ['class' => 'text-center'],
                                    'value'          => function($model) { /** @var $model \app\models\Credential */
                                        return Html::a('<i class="fa fa-caret-square-o-down"></i>', 'javascript:;', [
                                            'class'         => 'ajaxGridExpand',
                                            'title'         => Yii::t('network', 'View credential info'),
                                            'data-ajax-url' => Url::to(['/network/credential/ajax-get-credential', 'id' => $model->id]),
                                            'data-div-id'   => '#credential_' . $model->id,
                                            'data-multiple' => 'false'
                                        ]);
                                    },
                                ],
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'name',
                                    'options'   => ['style' => 'width:15%'],
                                    'value'     => function($data) { /** @var $data \app\models\Credential */
                                        return Html::a($data->name, ['/network/credential/edit', 'id' => $data->id], [
                                            'data-pjax' => '0',
                                            'title'     => Yii::t('network', 'Edit credential')
                                        ]);
                                    },
                                ],
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'network_ip',
                                    'value'     => function($model){ /** @var $model \app\models\Credential*/ return $model->renderNetworkList(); },
                                    'options'   => ['style' => 'width:13%']
                                ],
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'node_name',
                                    'value'     => function($model){ /** @var $model \app\models\Credential*/ return $model->renderNodeList(); },
                                    'options'   => ['style' => 'width:21%']
                                ],
                                [
                                    'attribute'     => 'snmp_read',
                                ],
                                [
                                    'attribute'     => 'snmp_set',
                                ],
                                [
                                    'attribute'     => 'telnet_login',
                                ],
                                [
                                    'attribute'     => 'ssh_login',
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{edit} {delete}',
                                    'buttons'        => [
                                        'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Credential */
                                            return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/network/credential/edit', 'id' => $model->id], [
                                                'title'     => Yii::t('app', 'Edit'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $model \app\models\Credential */
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['/network/credential/ajax-delete', 'id' => $model->id]),
                                                'data-ajax-confirm' => Yii::t('network', 'Are you sure you want to delete credential {0}?', $model->name),
                                            ]);
                                        },
                                    ],
                                ]
                            ],
                        ]);
                    ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>

