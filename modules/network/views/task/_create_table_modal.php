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

/** @noinspection PhpUndefinedFieldInspection
 *  @var $fields    array
 *  @var $table_exists bool
 *  @var $protected integer
 */
$action     = $this->context->action->id;
$modal_name = ($action == 'ajax-create-table') ? Yii::t('network', 'Add new table') : Yii::t('network', 'Edit table');
?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header modal-default">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            <h4 class="modal-title"><?= $modal_name ?></h4>
        </div>
        <?php if ($protected == 1 || ($action == 'ajax-edit-table' && !$table_exists)): ?>
            <div class="modal-body">
                <div class="callout callout-warning" style="margin-bottom: 0;">
                    <p><?= Yii::t('network', '<b>STOP messing around with link attributes and go play somewhere else!</b>') ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
            </div>
        <?php else: ?>
            <?= Html::beginForm('', 'post', ['id' => 'create_table_form', 'class' => 'form-horizontal']); ?>
                <div class="modal-body">
                    <?php if ($action == 'ajax-edit-table'): ?>
                        <div class="callout callout-warning">
                            <p><?= Yii::t('network', 'Warning! The table will be deleted and recreated. All data will be lost.') ?></p>
                        </div>
                    <?php endif; ?>
                    <div data-role="dynamic-fields">
                        <?php foreach ($fields as $field => $value): ?>
                            <div class="form-group field-create-<?= $field ?>">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <?php
                                            echo Html::textInput("fields[{$field}]", $value, [
                                                'id'          => $field,
                                                'class'       => 'form-control',
                                                'placeholder' => Yii::t('network', 'Enter column name'),
                                            ]);
                                        ?>
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-remove btn-danger" data-role="remove"><i class="fa fa-remove"></i></button>
                                            <button type="button" class="btn btn-primary" data-role="add"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
                    <?php
                        echo Html::submitButton(($action == 'ajax-create-table') ? Yii::t('app', 'Create') : Yii::t('app', 'Save changes'), [
                            'id'         => 'save',
                            'class'      => 'btn btn-primary ladda-button',
                            'data-style' => 'zoom-in'
                        ]);
                    ?>
                </div>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
</div>
