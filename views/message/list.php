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
use yii\widgets\Pjax;
use yii\helpers\Url;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $searchModel   app\models\search\MessagesSearch
 * @var $users         array
 */
$this->title = Yii::t('app', 'System messages');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'System' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'System messages')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title"><?= Yii::t('app', 'List of system messages') ?></h3>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'message-pjax']); ?>
                    <?php
                        /** @noinspection MissedFieldInspection, PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'message-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'rowOptions'   => function($model) { /** @var $model \app\models\Messages */
                                return ['class' => (is_null($model->approved)) ? 'warning' : ''];
                            },
                            'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'attribute' => 'created',
                                    'options'   => ['style' => 'width:14%']
                                ],
                                [
                                    'attribute' => 'message',
                                    'format'    => 'raw',
                                ],
                                [
                                    'attribute' => 'approved',
                                    'options'   => ['style' => 'width:14%']
                                ],
                                [
                                    'attribute' => 'approved_by',
                                    'value'     => 'approvedBy.fullname',
                                    'filter'    => $users,
                                    'options'   => ['style' => 'width:20%']
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow', 'style' => 'vertical-align: middle'],
                                    'template'       => '{approve}',
                                    'visible'        => $searchModel::hasUnmarkedMessages(),
                                    'visibleButtons' => [
                                        'approve' => function ($model) { /** @var $model \app\models\Messages */
                                            return (is_null($model->approved)) ? true : false;
                                        },
                                    ],
                                    'buttons'        => [
                                        'approve' => function(/** @noinspection PhpUnusedParameterInspection */$url, $model) { /** @var $data \app\models\Messages */
                                            return Html::a('<i class="fa fa-check"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate updateMessages',
                                                'title'             => Yii::t('app', 'Acknowledge message'),
                                                'style'             => 'color: #00a65a',
                                                'data-ajax-url'     => Url::to(['ajax-approve', 'id' => $model->id]),
                                                'data-update-url'   => Url::to(['message/ajax-update-widget'])
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
