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
use yii\helpers\Json;
use yii\bootstrap\ActiveForm;
use yii\helpers\Inflector;

/**
 * @var $this       yii\web\View
 * @var $model      app\models\Plugin
 * @var $form       yii\bootstrap\ActiveForm
 * @var $roles      array
 * @var $form_data  array
 */
app\assets\Select2Asset::register($this);
app\assets\ToggleAsset::register($this);
app\assets\i18nextAsset::register($this);

$this->title = Yii::t('plugin', 'Plugin {0} settings', $model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'System' )];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Plugin manager'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];

$this->registerJs(
/** @lang JavaScript */
    "    
        /** Save active tab to session storage and show necessary tab buttons */
        $('a[data-toggle=tab]').on('shown.bs.tab', function () {
    
            var target = $(this).attr('href');
    
            /** Check if session storage is available */
            if (_supportsSessionStorage) {
                sessionStorage.setItem('active', target);
            }
    
        });
    
        /** Check if session storage is available */
        if (_supportsSessionStorage) {
    
            /** Get active tab from session storage */
            var active = sessionStorage.getItem('active');
    
            /** Set active tab on page reload */
            if (active !== '') {
                $('[href=\"' + active + '\"]').tab('show');
            }
    
        }
    
        /** Init select2 without search */
        $('.select2').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });

        /** Select all checkboxes except toggle */
        var checkboxes = $(':checkbox').not('[data-toggle=\"toggle\"], [data-ignore=\"true\"]');

        /** Init iCheck */
        checkboxes.iCheck({
            determinateClass: 'icheckbox'
        }).on('ifChanged', function (event) {
            $(event.target).trigger('change');
        });

        /** Set iCheck color */
        $.each(checkboxes, function(_, input) {
            var checkbox = $(input);
            var ch_color = checkbox.data('checkboxClass');
            
            if (typeof checkbox.attr('data-checkbox-class') === typeof undefined) {
                ch_color = 'icheckbox_minimal-green';
            }
            
            $(input).parent().addClass(ch_color);
        });
        
        /** Set initial check of radioList */
        $(':input:checked').parent('.btn').addClass('active');
        
        /** Clear warnings */
        $('#submit_btn').click(function() {
            toastr.clear();
            $('input, select, textarea').parent('div').removeClass('has-error');
        });
        
        /** Validate form inputs */
        $('input, select, textarea').on('invalid', function(e) {
            e.preventDefault();
            
            $.each(e.target.validity, function(key, value) {
                if (value === true) {
                   var field = $('#label_' + e.target.id).text();
                   switch (key) {
                       case 'valueMissing':
                           toastr.error(
                               i18next.t('Field <b>{{field}}</b> is required', {field: field}),  '', {timeOut: 0, closeButton: true}
                           );
                       break;
                       case 'patternMismatch':
                            var pattern = e.target.pattern;
                            toastr.error(
                                i18next.t('Field <b>{{field}}</b> does not match pattern {{pattern}}', {field: field, pattern: pattern}), 
                                '', {timeOut: 0, closeButton: true}
                            );
                       break;
                       case 'tooShort':
                            var min_length = e.target.minLength;
                            toastr.error(
                                i18next.t('Minimum length of field <b>{{field}}</b> must be at least {{min_length}} characters', {field: field, min_length: min_length}), 
                                '', {timeOut: 0, closeButton: true}
                            );
                       break;
                       default:
                           toastr.error(
                               i18next.t('Error in field <b>{{field}}</b>. {{error}}', {field: field, error: e.target.validationMessage}), 
                               '', {timeOut: 0, closeButton: true}
                           );
                   }
                   $(e.target.parentElement).addClass('has-error');
                }
            });
        });
        
    "
);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-solid">
            <?php $form = ActiveForm::begin(['id' => 'global_var_form', 'enableClientValidation' => false]); ?>
            <div class="box-body no-padding">
                <div class="col-md-8 no-padding" style="border-right: 1px solid #f4f4f4">
                    <div class="nav-tabs-custom" style="margin-bottom: 0; box-shadow: none;">
                        <ul class="nav nav-tabs">
                            <?php $tabs = array_keys($form_data); ?>
                            <?php foreach ($tabs as $tab_key => $tab): ?>
                                <?php $active = ($tab_key == 0) ? 'active' : ''; ?>
                                <li class="<?= $active ?>">
                                    <a href="#tab_<?= $tab ?>" data-toggle="tab" aria-expanded="false">
                                        <?= $model->plugin::t('general', Inflector::humanize($tab)) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <a href="#tab_metadata" data-toggle="tab" aria-expanded="false"><?= Yii::t('plugin', 'Plugin metadata') ?></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <?php foreach ($tabs as $tab_key => $tab): ?>
                                <?php $active = ($tab_key == 0) ? 'active' : ''; ?>
                                <div class="tab-pane <?= $active ?>" id="tab_<?= $tab ?>">
                                    <?php foreach ($form_data[$tab]['fields'] as $field): ?>
                                        <div class="col-md-12" style="margin-bottom: 5px;">
                                            <div class="row">
                                                <div class="col-md-5 settings-label">
                                                    <span id="label_<?= $field['name'] ?>" class="text-bold">
                                                        <?= $model->plugin::t('general', $field['label']) ?>
                                                    </span><br>
                                                    <small class="text-muted">
                                                        <?= array_key_exists('description', $field) ? $model->plugin::t('general', $field['description']) : '' ?>
                                                    </small>
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <?php
                                                            $options = (array_key_exists('options', $field)) ? $field['options'] : [];
                                                            $options+= ['id' => $field['name']];
                                                            switch ($field['type']) {
                                                                case 'textInput':
                                                                    echo Html::textInput("PluginParams[{$field['name']}]", $model->plugin_params[$field['name']], $options);
                                                                break;
                                                                case 'textarea':
                                                                    echo Html::textarea("PluginParams[{$field['name']}]", $model->plugin_params[$field['name']], $options);
                                                                break;
                                                                case 'checkbox':
                                                                    echo Html::checkbox("PluginParams[{$field['name']}]", $model->plugin_params[$field['name']], $options);
                                                                break;
                                                                case 'dropDownList':
                                                                    /** Translate dropdownlist options */
                                                                    $values = array_map(function ($value) use ($model) {
                                                                        return  $model->plugin::t('general', $value);
                                                                    }, $field['values']);

                                                                    echo Html::dropDownList("PluginParams[{$field['name']}]", $model->plugin_params[$field['name']], $values, $options);
                                                                break;
                                                                case 'toggle':
                                                                    /** Default toggle options*/
                                                                    $defaut_options = [
                                                                        'data-size'   => 'normal',
                                                                        'data-toggle' => 'toggle',
                                                                    ];
                                                                    /** Translate toggle text */
                                                                    $text_options = array_map(function ($value) use ($model) {
                                                                        return  $model->plugin::t('general', $value);
                                                                    }, $field['toggle']);

                                                                    echo Html::checkbox("PluginParams[{$field['name']}]", $model->plugin_params[$field['name']],
                                                                        array_merge($text_options, $defaut_options, $options)
                                                                    );
                                                                break;
                                                                case 'radioList':
                                                                    /** Translate radio options */
                                                                    $values = array_map(function ($value) use ($model) {
                                                                        return  $model->plugin::t('general', $value);
                                                                    }, $field['values']);

                                                                    echo Html::radioList("PluginParams[{$field['name']}]", $model->plugin_params[$field['name']], $values, [
                                                                        'id'          => $field['name'],
                                                                        'class'       => 'btn-group',
                                                                        'data-toggle' => 'buttons',
                                                                        'item'        => function(/** @noinspection PhpUnusedParameterInspection */$index, $label, $name, $checked, $value) {
                                                                            return Html::tag('label',
                                                                                Html::radio($name, $checked, ['value' => $value, 'autocomplete' => 'off']) . $label, [
                                                                                    'class' => 'btn btn-primary'
                                                                                ]
                                                                            );
                                                                        }
                                                                    ]);
                                                                break;
                                                                default:
                                                                    echo Yii::t('plugin', 'Unsupported field type <b>{0}</b>', $field['type']);
                                                            }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="tab-pane" id="tab_metadata">
                                <?php $metadata = Json::decode($model->metadata); ?>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= Yii::t('app', 'Parameter')?></th>
                                            <th><?= Yii::t('app', 'Value')?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($metadata as $key => $meta): ?>
                                            <tr>
                                                <td><?= Yii::t('app', Inflector::camel2words($key)) ?></td>
                                                <td><?= $meta ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 no-padding">
                    <div class="box box-solid no-margin" style="border-radius: 0; box-shadow: none;">
                        <div class="box-header with-border">
                            <h3 class="box-title" style="padding-top: 2px">Global plugin options</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $statuses = [0 => Yii::t('app', 'Disabled'), 1 => Yii::t('app', 'Enabled')];
                                        echo $form->field($model, 'enabled')->dropDownList($statuses, [
                                            'class' => 'select2',
                                        ]);
                                    ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        echo $form->field($model, 'access')->dropDownList($roles, [
                                            'class' => 'select2',
                                        ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer text-right">
                <?= Html::a(Yii::t('app', 'Cancel'), ['index'], ['class' => 'btn btn-sm btn-default margin-r-5']) ?>
                <?= Html::submitButton(Yii::t('app', 'Save changes'), ['id' => 'submit_btn', 'class' => 'btn btn-sm btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
