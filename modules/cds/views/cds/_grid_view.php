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
use cbackup\grid\GroupGridView;

/**
 * @var $this           yii\web\View
 * @var $dataProvider   yii\data\ArrayDataProvider
 * @var $model          app\modules\cds\models\Cds
 * @var $type           string
 */

/** @noinspection PhpUnhandledExceptionInspection */
echo GroupGridView::widget([
    'id'              => 'content-grid',
    'tableOptions'    => ['class' => 'table table-bordered content-table', 'data-ajax-url' => Url::to(['ajax-render-grid', 'type' => $type])],
    'dataProvider'    => $dataProvider,
    'filterModel'     => $model,
    'extraRowColumns' => ['vendor'],
    'extraRowValue'   => function($model) { /** @var $model \app\modules\cds\models\Cds */
        return Yii::t('network', 'Vendor') . ' :: ' . ucfirst($model['vendor']);
    },
    'afterRow'     => function($model) { /** @var $model \app\modules\cds\models\Cds */
        $id = "info_{$model['class']}_{$model['vendor']}";
        return '<tr><td class="grid-expand-row" colspan="5"><div class="grid-expand-div" id="'.$id.'"></div></td></tr>';
    },
    'layout'  => '{items}<div class="row"><div class="col-sm-4"><div class="gridview-summary">{summary}</div></div><div class="col-sm-8"><div class="gridview-pager">{pager}</div></div></div>',
    'columns' => [
        [
            'format'         => 'raw',
            'options'        => ['style' => 'width:36px'],
            'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
            'value'          => function($model) { /** @var $model \app\modules\cds\models\Cds */
                return Html::a('<i class="fa fa-caret-square-o-down"></i>', 'javascript:void(0);', [
                    'class'         => 'ajaxGridExpand',
                    'title'         => Yii::t('app', 'View instalation file'),
                    'data-ajax-url' => Url::to(['ajax-get-install-file', 'path' => $model['file_path']]),
                    'data-div-id'   => "#info_{$model['class']}_{$model['vendor']}",
                    'data-multiple' => 'false'
                ]);
            },
        ],
        [
            'attribute' => 'name',
        ],
        [
            'attribute' => 'protocol',
        ],
        [
            'attribute' => 'class',
        ],
        [
            'class'          => 'yii\grid\ActionColumn',
            'options'        => ['style' => 'width:31px'],
            'template'       => '{save}',
            'buttons'        => [
                'save' => function (/** @noinspection PhpUnusedParameterInspection */$url, $model) use ($type) { /** @var $model \app\modules\cds\models\Cds */
                    return Html::a('<i class="fa fa-save"></i>', 'javascript:void(0);', [
                        'class'             => 'ajaxInstallContent',
                        'title'             => Yii::t('app', 'Add'),
                        'data-ajax-url'     => Url::to(['ajax-install-content',
                            'content' => $type,
                            'vendor'  => $model['vendor'],
                            'class'   => $model['class'],
                        ]),
                    ]);
                },
            ],
        ]
    ]
]);
