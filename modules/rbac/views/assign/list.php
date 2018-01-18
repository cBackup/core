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
use cbackup\grid\GroupGridView;

/**
 * @var $this          yii\web\View
 * @var $dataProvider  yii\data\ActiveDataProvider
 * @var $searchModel   app\modules\rbac\models\AuthAssignmentSearch
 * @var $item_types    array
 */
$this->title = Yii::t('app', 'User rights');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration' )];
$this->params['breadcrumbs'][] = ['url' => ['/user/list'], 'label' => Yii::t('app', 'Users')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User rights')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('rbac', 'List of user assignments') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('rbac', 'Add user rights'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'assign-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GroupGridView::widget([
                            'id'           => 'assign-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'mergeColumns' => ['name_search'],
                            'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'name_search',
                                    'value'     => function($data) {
                                        return Html::a($data->user->fullname, ['/rbac/assign/edit', 'userid' => $data->user_id], ['data-pjax' => '0']);
                                    },
                                    'options'   => ['style' => 'width:30%'],
                                ],
                                [
                                    'attribute'     => 'item_name',
                                    'enableSorting' => false
                                ],
                                [
                                    'attribute'     => 'type',
                                    'value'         => 'itemName.authItemReadable',
                                    'filter'        => $item_types
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{delete}',
                                    'buttons'        => [
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('rbac', 'Delete item'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['/rbac/assign/ajax-delete',
                                                    'user_id'   => $model->user_id,
                                                    'item_name' => $model->item_name
                                                ]),
                                                'data-ajax-confirm' => Yii::t('rbac', 'Are you sure you want to delete user assignment {0}?', $model->item_name),
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
