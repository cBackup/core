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
 * @var $searchModel   app\models\search\MailerEventsSearch
 * @var $mailDisabled  bool
 */
$this->title = Yii::t('app', 'Mailer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mailer')];
?>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <i class="fa fa-list"></i><h3 class="box-title box-title-align"><?= Yii::t('app', 'List of mailer events') ?></h3>
                <div class="pull-right">
                    <?= Html::a(Yii::t('app', 'Add event'), ['add-event'], ['class' => 'btn btn-sm bg-light-blue']) ?>
                </div>
            </div>
            <div class="box-body no-padding">
                <?php Pjax::begin(['id' => 'mailer-pjax']); ?>
                    <?php
                        /** @noinspection PhpUnhandledExceptionInspection */
                        echo GridView::widget([
                            'id'           => 'mailer-grid',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'dataProvider' => $dataProvider,
                            'filterModel'  => $searchModel,
                            'layout'       => '{items}<div class="row"><div class="col-sm-3"><div class="gridview-summary">{summary}</div></div><div class="col-sm-9"><div class="gridview-pager">{pager}</div></div></div>',
                            'columns' => [
                                [
                                    'format'    => 'raw',
                                    'attribute' => 'name',
                                    'value'     => function($data) { return Html::a($data->name, ['edit-event', 'name' => $data->name], ['data-pjax' => '0']); },
                                    'options'   => ['style' => 'width:25%']
                                ],
                                [
                                    'format'         => 'raw',
                                    'attribute'      => 'subject',
                                    'value'          => 'eventHasSubject',
                                    'contentOptions' => ['class' => 'text-center'],
                                    'enableSorting'  => false,
                                    'filter'         => false,
                                    'options'        => ['style' => 'width:10%']
                                ],
                                [
                                    'format'         => 'raw',
                                    'attribute'      => 'template',
                                    'value'          => 'eventHasTemplate',
                                    'contentOptions' => ['class' => 'text-center'],
                                    'enableSorting'  => false,
                                    'filter'         => false,
                                    'options'        => ['style' => 'width:10%']
                                ],
                                [
                                    'format'         => 'raw',
                                    'attribute'      => 'recipients',
                                    'value'          => 'eventHasRecipients',
                                    'contentOptions' => ['class' => 'text-center'],
                                    'enableSorting'  => false,
                                    'filter'         => false,
                                    'options'        => ['style' => 'width:10%']
                                ],
                                [
                                    'attribute'     => 'description',
                                    'enableSorting' => false,
                                ],
                                [
                                    'class'          => 'yii\grid\ActionColumn',
                                    'contentOptions' => ['class' => 'narrow'],
                                    'template'       => '{recipients} {template} {send_mail} {edit} {delete}',
                                    'buttons'        => [
                                        'recipients' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-address-book-o"></i>', ['edit-event-recipients', 'name' => $model->name], [
                                                'title'     => Yii::t('app', 'Edit recipients'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'template' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-file-text-o"></i>', ['edit-event-template', 'name' => $model->name], [
                                                'title'     => Yii::t('app', 'Edit template'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'send_mail' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) use ($mailDisabled) { /** @var $model \app\models\MailerEvents */
                                            return Html::a('<i class="fa fa-envelope-o"></i>', 'javascript:void(0);', [
                                                'class'         => 'ajaxGridUpdate ' . (is_null($model->subject) || is_null($model->template) || is_null($model->recipients) || $mailDisabled ? 'disabled' : ''),
                                                'title'         => Yii::t('app', 'Send mail'),
                                                'data-ajax-url' => Url::to(['ajax-send-mail', 'task_name' => $model->name]),
                                            ]);
                                        },
                                        'edit' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-pencil"></i>', ['edit-event', 'name' => $model->name], [
                                                'title'     => Yii::t('app', 'Edit'),
                                                'data-pjax' => '0',
                                            ]);
                                        },
                                        'delete' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) {
                                            return Html::a('<i class="fa fa-trash-o"></i>', 'javascript:;', [
                                                'class'             => 'ajaxGridUpdate',
                                                'title'             => Yii::t('app', 'Delete'),
                                                'style'             => 'color: #D65C4F',
                                                'data-ajax-url'     => Url::to(['ajax-delete-event', 'name' => $model->name]),
                                                'data-ajax-confirm' => Yii::t('app', 'Are you sure you want to delete record {0}?', $model->name),
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
