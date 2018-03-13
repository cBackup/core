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

/** Default variable */
var $body = $('body');
var $ajax_select = $('.select2-ajax');

/** Init select2 */
$('.select2').select2({
    width: '100%'
});

/** Init select2 with clear */
$('.select2-clear').select2({
    minimumResultsForSearch: -1,
    allowClear: true,
    width: '100%'
});

/** Modal loaded event handler */
$body.on('loaded.bs.modal', '.modal', function () {

    /** Init select2 in modal window */
    $('.select2-modal-simple').select2({
        minimumResultsForSearch: -1,
        width: '100%'
    });

    /** Clear all toasts after modal loaded */
    toastr.clear();

});

/** Modal hidden event handler */
$body.on('hidden.bs.modal', '.modal', function () {

    var $toast = $('#toast-container');

    /** Reload select2 after record was added */
    if ($toast.find('.toast-success').is(':visible')) {
        var $selected_task = $('#task_name').val();
        var $update_url    = $('#worker_list').data('update-url');
        updateSelect2($update_url + '&task_name=' + $selected_task, 'worker_list');
    }

    /** Remove errors after modal close */
    $toast.find('.toast-error').fadeOut(1000, function() { $(this).remove(); });

});

/** Form AJAX submit handler */
$body.on('submit', '#worker_form', function () {
    modalFormHandler($(this), 'form_modal', 'save');
    return false;
});

/** Set modal link based on selected task */
$('#task_name').change(function() {

    var $modal_link = $('#modal_link');
    var $modal_url  = $modal_link.data('url');
    var $selected   = $(this).val();

    // noinspection EqualityComparisonWithCoercionJS
    if ($selected != '') {
        $modal_link.attr('href', $modal_url + '&task_name=' + $selected).removeClass('disabled');
    } else {
        $modal_link.addClass('disabled');
    }

}).change();

/** Search nodes via Ajax  */
$ajax_select.select2({
    width: '100%',
    minimumInputLength: 4,
    ajax: {
        url: $ajax_select.data('url'),
        dataType: 'json',
        delay: 250,
        cache: false,
        data: function ($params) {
            return {
                value: $params.term,
                page:  $params.page
            };
        },
        processResults: function ($data, $params) {
            $params.page = $params.page || 1;
            // noinspection JSUnresolvedVariable
            return {
                results: $.map($data, function ($item) {

                    var $hostname;

                    if($item.hasOwnProperty('hostname') && $item.hostname && $item.hostname.length > 0) {
                        $hostname = $item.hostname;
                    }
                    else {
                        $hostname = i18next.t('(not set)');
                    }

                    return {
                        text: $hostname + ' - ' + $item.ip,
                        id:   $item.id
                    }

                }),
                pagination: {
                    more: ($params.page * 30) < $data.total_count
                }
            };
        }
    }
});
