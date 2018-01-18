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
 * @var $searchModel   app\modules\rbac\models\AuthItemSearch
 * @var $item_types    array
 */
$this->title = Yii::t('app', 'Access rights');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Administration' )];
$this->params['breadcrumbs'][] = ['url' => ['/user/list'], 'label' => Yii::t('app', 'Users')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Access rights')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('rbac', 'List of authorization items') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('rbac', 'Add item'), ['add'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'access-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'access-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'name',
                                    'value'     => function($data) { return Html::a($data->name, ['/rbac/access/edit', 'name' => $data->name], ['data-pjax' => '0']); },
                                ],
                                [
                                    'attribute'     => 'type',
                                    'value'         => 'authItemReadable',
                                    'filter'        => $item_types
                                ],
                                [
                                    'attribute'     => 'description',
                                    'enableSorting' => false,
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{edit} {delete}',
                                    'buttons'        => [
                                        'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-pencil-square-o"></i>', ['/rbac/access/edit', 'name' => $model->name], [
                                                'title'     => Yii::t('rbac', 'Edit item'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('rbac', 'Delete item'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['/rbac/access/ajax-delete', 'name' => $model->name]),
                                                'data-ajax-confirm' => Yii::t('rbac', 'Are you sure you want to delete authorization item {0}?', $model->name),
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
