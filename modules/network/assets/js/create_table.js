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

var $body = $('body');
var $put_field    = $('#put_field');
var $previous_put = '';

/** Load js on ready and pjax:end */
$(document).on('ready pjax:end', function() {

    /** Select2 init */
    $('.select2').select2({
        minimumResultsForSearch: '-1',
        allowClear: true,
        width: '100%'
    });

    /** Delete entry via ajax */
    $('.delete-table').click(function() {

        var $this        = $(this);
        var $url         = $this.data('ajax-url');
        var $confirm_txt = $this.data('confirm-txt');
        var $run_ajax    = false;

        if (typeof $confirm_txt !== typeof undefined && $confirm_txt !== 'false') {
            if (confirm($confirm_txt)) {
                $run_ajax = true
            }
        } else {
            $run_ajax = true;
        }

        if ($run_ajax) {
            $.ajax({
                type: 'POST',
                url: $url,
                beforeSend: function() {
                    $this.closest('div.box .box-header').children('i').switchClass('fa-info-circle', 'fa-spinner fa-spin', 0);
                },
                success: function (data) {
                    $.pjax.reload({container: '#task-form-pjax', url: $(location).attr('href'), timeout: 10000});
                    showStatus(data);
                },
                error: function (data) {
                    $this.closest('div.box .box-header').children('i').switchClass('fa-spinner fa-spin', 'fa-info-circle', 0);
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            });
        }

    });

    /** Store previous put value */
    $previous_put = $put_field.val();

    /** Show alert if type changed */
    $('#put_field').change(function () {
        var $form_action = $('#form_action').val();
        if ($previous_put !== this.value && $previous_put !== '' && $form_action !== 'add') {
            swal({
                title: '<i class="fa fa-exclamation-triangle"></i> ' + i18next.t('Warning!'),
                text: i18next.t('<b>By changing task destination all saved data will be lost!</b></br>Are you sure you want to change task destination?'),
                html: true,
                showCancelButton: true,
                confirmButtonText: i18next.t('Confirm'),
                cancelButtonText: i18next.t('Cancel'),
                closeOnConfirm: true,
                closeOnCancel: true,
                animation: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    $('#task_form').submit();
                } else {
                    $put_field.val($previous_put).trigger('change');
                }
            });
        }
    });

});

/** Remove form-group on button click */
$body.on('click', '[data-role=dynamic-fields] .input-group [data-role=remove]',function(e) {
    e.preventDefault();
    $(this).closest('.form-group').slideUp('fast', function() { $(this).remove(); });
});

/** Add form-group on button click */
$body.on('click','[data-role=dynamic-fields] .input-group [data-role=add]', function(e) {

    e.preventDefault();

    var $container       = $(this).closest('[data-role=dynamic-fields]');
    var $new_field_group = $container.children().filter('.form-group:last-child').clone();
    var $field_counter   = $new_field_group.find('input').attr('id').split('_')[1];
    var $next_field      = parseInt($field_counter) + 1;

    $new_field_group.attr('class', 'form-group field-create-field_' + $next_field);

    $new_field_group.find('input').each(function(){
        $(this).attr({
            id: 'field_' + $next_field,
            name: 'fields[field_' + $next_field + ']'
        }).val('');
    });

    $container.append($new_field_group);
    $container.children().last().hide().slideDown('fast');
    $container.children().last().find('input').focus();

});

/** Modal hidden event handler */
$body.on('hidden.bs.modal', '.modal', function () {

    var $toast = $('#toast-container');

    /** Reload page with pjax */
    if ($toast.find('.toast-success').is(':visible')) {

        var $params = {};

        /** Get all entered data before pjax reload */
        $body.find('form#task_form input:not([type=hidden]), select, textarea').each(function () {
            var $this = $(this);
            var $name = $this[0].name.match(/\[(.*?)]/);
            $params[$name[1]] = $this[0].value;
        });

        /** Reload page with entered data */
        $.pjax.reload({container: '#task-form-pjax', url: $(location).attr('href'), type: 'POST', data:{params: $params}, timeout: 10000});
    }

    /** Remove errors after modal close */
    $toast.find('.toast-error, .toast-warning').fadeOut(1000, function() { $(this).remove(); });

});

/** Create table form AJAX submit handler */
$body.on('submit', '#create_table_form', function () {
    modalFormHandler($(this), 'form_modal', 'save');
    return false;
});
