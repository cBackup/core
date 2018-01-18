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

/**
 * @var $this              yii\web\View
 * @var $dataProvider      yii\data\ActiveDataProvider
 * @var $searchModel       app\models\search\OutCustomSearch
 */
?>

<?php
    /** @noinspection PhpUnhandledExceptionInspection */
    echo GridView::widget([
        'id'           => 'out-table-grid',
        'tableOptions' => ['class' => 'table table-bordered'],
        'dataProvider' => $dataProvider,
        'afterRow'     => function ($model) use ($searchModel) {/** @var $this yii\web\View */
            return
                '<tr>
                    <td class="grid-expand-row" colspan="5">
                        <div class="grid-expand-div" id='."output_{$model['id']}".'>'
                            .$this->render('_view_output', [
                                'searchModel' => $searchModel,
                                'model'       => $model
                            ]).
                        '</div>
                    </td>
                </tr>';
        },
        'layout'  => '{items}<div class="row"><div class="col-sm-4"><div class="gridview-summary">{summary}</div></div><div class="col-sm-8"><div class="gridview-pager">{pager}</div></div></div>',
        'columns' => [
            [
                'format'         => 'raw',
                'options'        => ['style' => 'width:3%'],
                'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                'value'          => function($model) {
                    return Html::a('<i class="fa fa-caret-square-o-down"></i>', 'javascript:void(0);', [
                        'class'         => 'gridExpand',
                        'title'         => Yii::t('network', 'Show worker output'),
                        'data-div-id'   => '#output_' . $model['id'],
                        'data-multiple' => 'false'
                    ]);
                },
            ],
            [
                'attribute' => 'time',
                'label'     => Yii::t('app', 'Time'),
                'options'   => ['style' => 'width:20%'],
            ],
            [
                'attribute' => 'node_id',
                'label'     => Yii::t('app', 'Node ID'),
                'options'   => ['style' => 'width:11%'],
            ],
            [
                'format'    => 'raw',
                'attribute' => 'node_search',
                'label'     => Yii::t('node', 'Node'),
                'value'     => function($data) {
                    $link = Yii::t('yii', '(not set)');
                    if (!is_null($data['hostname']) || !is_null($data['ip'])) {
                        $text = (!empty($data['hostname'])) ? $data['hostname'] : $data['ip'];
                        $link = Html::a($text, ['/node/view', 'id' => $data['node_id']], ['data-pjax' => '0', 'target' => '_blank']);
                    }
                    return $link;
                }
            ],
            [
                'attribute' => 'hash',
                'label'     => Yii::t('app', 'Hash'),
            ]
        ]
    ]);
?>
