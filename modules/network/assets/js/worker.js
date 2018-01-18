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

/** Selector declaration */
var $body     = $('body');
var $job_list;
var $checkbox;
var $opened_list = [];
var $woker_type  = '';

/** Init select2 in main view */
$('.select2').select2({
    minimumResultsForSearch: -1,
    width: '100%'
});

/** Hide control-sidebar on ESC button press */
$(document).keydown(function(e) {
    if (e.keyCode === 27) {
        $('.control-sidebar.sidebar-custom').removeClass('control-sidebar-open');
    }
});

/** Prevent tooltip from repeated showing after redirect */
$(window).focus(function() {
    $('a').focus(function() {
        this.blur();
    });
});

/** Activate controlSidebar after pjax:end  */
$(document).on('pjax:end', function() {
    $.AdminLTE.controlSidebar.activate();
});

/** Click on command to view description */
$(document).on("click", '.sequence-command', function() {

    var $this   = $(this);
    var $url    = $this.data('ajax-url');
    var $result = 'n/d';

    $.ajax({
        type:     'GET',
        dataType: 'json',
        url:      $url,
        success: function (data) {

            // 'null' as a string because it's json_encoded() result of ActiveRecord one()
            if( data !== 'null' ) {
                $result = '';
                $result+= (data.description !== null) ? data.description + ';' : 'no description;';
                $result+= ' ';
                $result+= (data.timeout !== null) ? 'wait for result: <b>' + data.timeout + ' ms</b>;' : '';
                $result+= ' ';
                // noinspection JSUnresolvedVariable
                $result+= (data.table_field !== null) ? 'save to: <b>' + data.table_field + '</b>;' : '';
                $result+= ' ';
                // noinspection JSUnresolvedVariable
                $result+= (data.command_var !== null) ? 'store result in var: <b>' + data.command_var + '</b>;' : '';
                $result+= ' ';
            }

        },
        complete: function () {
            $("#sequence_desc").html($result.replace(/[;\s]+$/mg, ''));
        }
    });

});

$(document).on('ready pjax:end', function() {

    /** Default varibales */
    $checkbox = $('.job-status');
    $job_list = $('.job_list');

    /** Clear info box after pjax */
    $('.start-message').show();
    $('.info').hide();

    /** Make job list  sortable*/
    $job_list.sortable({
        placeholder: 'sort-highlight',
        handle: '.movable',
        forcePlaceholderSize: true,
        zIndex: 999999
    }).disableSelection();

    /** Init iCheck */
    $checkbox.iCheck({
        checkboxClass: 'icheckbox_minimal-green',
        determinateClass: 'icheckbox'
    }).on('ifChanged', function (event) {
        $(event.target).trigger('change');
    });

    /** Worker & job search form submit and reload tree */
    $('.worker-job-search-form form').submit(function(e) {
        e.stopImmediatePropagation(); // Prevent double submit
        gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
        $.pjax.reload({container:'#tree-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
        return false;
    });

    /** Change job status  */
    $checkbox.change(function() {

        var $this  = $(this);
        var $url   = $this.data('ajax-url');
        var $value = $this.is(':checked') ? this.value : '0';

        $.ajax({
            type: 'POST',
            url: $url,
            data: {status: $value},
            success: function (data) {
                $.pjax.reload({container: '#tree-pjax', url: $(location).attr('href'), timeout: 10000});
                showStatus(data);
            },
            error: function (data) {
                toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
            }
        });

    });

    /** Save worker jobs order on button click */
    $('.save-order').on('click', function () {

        var $this      = $(this);
        var $job_li_id = '#jobs_' + $(this).attr('id').split('_')[1];
        var $url       = $(this).data('ajax-url');

        var $list = $($job_li_id).sortable('serialize', {
            attribute: 'id'
        });

        $.ajax({
            type: 'POST',
            url: $url,
            data: $list + '&worker_id=' + $(this).attr('id').split('_')[1],
            beforeSend: function() {
                $this.find('i').switchClass('fa-plus', 'fa-spinner fa-spin', 0);
            },
            success: function (data) {
                $.pjax.reload({container: '#tree-pjax', url: $(location).attr('href'), timeout: 10000});
                showStatus(data);
            },
            error: function (data) {
                $this.find('i').switchClass('fa-spinner fa-spin', 'fa-plus', 0);
                toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
            }
        });

    });

    /** Delete entry via ajax */
    $('.entry-delete').click(function() {

        var $this       = $(this);
        var $url         = $this.data('ajax-url');
        var $confirm_txt = $this.data('confirm-txt');
        var $run_ajax    = false;

        // noinspection EqualityComparisonWithCoercionJS
        if (typeof $confirm_txt !== typeof undefined && $confirm_txt != 'false') {
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
                    $this.find('i').switchClass('fa-trash-o', 'fa-spinner fa-spin', 0);
                },
                success: function (data) {
                    $.pjax.reload({container: '#tree-pjax', url: $(location).attr('href'), timeout: 10000});
                    showStatus(data);
                },
                error: function (data) {
                    $this.find('i').switchClass('fa-spinner fa-spin', 'fa-trash-o', 0);
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            });
        }

    });

    /** View job information */
    $('.job-view, .worker-view').click(function() {

        var $this = $(this);
        var $url  = $this.data('ajax-url');

        $.ajax({
            type: 'POST',
            url: $url,
            beforeSend: function() {
                $this.find('i').switchClass('fa-eye', 'fa-spinner fa-spin', 0);
            },
            success: function (data) {
                $('.start-message').hide();
                $('.info').html(data).show();
                $this.find('i').switchClass('fa-spinner fa-spin', 'fa-eye', 0);
            },
            error: function (data) {
                $this.find('i').switchClass('fa-spinner fa-spin', 'fa-eye', 0);
                toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
            }
        });

    });

    /** After collapse show, push ul id to status, make tools visible array and rotate icon  */
    $job_list.on('show.bs.collapse', function () {
        var $selector = $(this).parent().prev();
        var $ul_id    = $(this).attr('id');
        var $icon     = $selector.find('.toggle').children('i');
        $opened_list.push($ul_id);
        $icon.addClass('rotate up');
        $selector.find('a').not('.visible').css('display', 'inline');
    });


    /** After collapse hide, remove ul id form status array, make tools unvisible and rotate icon  */
    $job_list.on('hide.bs.collapse', function () {
        var $selector = $(this).parent().prev();
        var $ul_id    = $(this).attr('id');
        var $icon     = $selector.find('.toggle').children('i');
        $opened_list.splice($.inArray($ul_id, $opened_list), 1);
        $icon.addClass('rotate').removeClass('up save-up');
        $selector.find('a').not('.visible').css('display', '');
    });

    /** Save list status after pjax update  */
    if (!$.isEmptyObject($opened_list)) {

        /** Remove opened ul id from array if toggle has class disabled */
        $.each($opened_list, function (_, ul_id) {
            var $toggle = $('#toggle_' + ul_id);
            if ($toggle.hasClass('disabled')) {
                var $id = $toggle.attr('id').split('_')[3];
                $opened_list.splice($.inArray('jobs_' + $id, $opened_list), 1);
            }
        });

        /** Show header tools and open list */
        $.each($opened_list, function (_, ul_id) {
            var $toggle = $('#toggle_' + ul_id);
            $toggle.children('i').addClass('save-up');
            $toggle.siblings().css('display', 'inline');
            $('#' + ul_id).addClass('in');
        });

    }

});

$body.on('loaded.bs.modal', '.modal', function () {

    /** Define variables */
    var $dt_search = $('#dt_search');
    var $snmp_job  = $('#job-snmp_request_type');
    var $scrollY   = false;

    /** Init select2 with clear in modal window */
    $('.select2-clear-modal').select2({
        minimumResultsForSearch: -1,
        allowClear: true,
        width: '100%'
    });

    /** Init select2 in modal window */
    $('.select2-modal').select2({
        minimumResultsForSearch: -1,
        width: '100%'
    });

    /** Init simple select2 in modal window */
    $('.select2-modal-simple').select2({
        minimumResultsForSearch: -1,
        width: '100%'
    });

    /** SNMP request type input handler */
    $('input[type=radio][name="Job[snmp_request_type]"]').change(function() {
        // noinspection EqualityComparisonWithCoercionJS
        if (this.value == 'get') {
            $('#job-timeout, #job-snmp_set_value, #snmp_hidden_type').prop('readonly', true).val('');
            $('#job-snmp_set_value_type').prop('disabled', true).val(null).trigger('change');
            $('.field-job-snmp_set_value_type, .field-job-snmp_set_value').removeClass('has-error');
            $('#toast-container').find('div.toast-message:contains("SNMP")').parent().fadeOut(600, function() { $(this).remove(); });
        }
        else {
            // noinspection EqualityComparisonWithCoercionJS
            if (this.value == 'set') {
                $('#job-timeout, #job-snmp_set_value').prop('readonly', false);
                $('#job-snmp_set_value_type').prop('disabled', false);
            }
        }
    });

    /** SNMP value type handler */
    $('#job-snmp_set_value_type').change(function () {
        // noinspection EqualityComparisonWithCoercionJS
        if (this.value == 'null') {
            $('#job-snmp_set_value').prop('readonly', true).val('');
            $('.field-job-snmp_set_value').removeClass('has-error');
            $('#toast-container').find('div.toast-message:contains("SNMP")').parent().fadeOut(600, function() { $(this).remove(); });
        }
        else {
            // noinspection EqualityComparisonWithCoercionJS
            if (this.value != '') {
                $('#job-snmp_set_value').prop('readonly', false);
            }
        }
    }).change();

    /** Clear all toasts after modal loaded */
    toastr.clear();

    /** Toggle */
    $('#job_enable').bootstrapToggle();

    /** Clear info box after modal loaded */
    $('.start-message').show();
    $('.info').hide();

    /** Get worker form "get" value on modal loaded */
    if ($(this).find('#worker_form').length > 0) {
        $woker_type = $('#worker-get').val();
    }

    /** Init dynamic variable dataTable */
    $('#dynamic_var_table').dataTable({
        destroy: true,
        ordering: false,
        deferRender: true,
        scrollY: 110,
        scrollCollapse: true,
        scroller: true,
        paging: false,
        'dom': 't'
    });

    /** Show/Hide dataTable scroll bar */
    if ($dt_search.length > 0 && $snmp_job.length === 0) {
        $scrollY = 141;
    }
    else if ($dt_search.length > 0 && $snmp_job.length > 0) {
        $scrollY = 213;
    }

    /** Init static variable dataTable */
    var dTable = $('#static_var_table').dataTable({
        destroy: true,
        ordering: false,
        deferRender: true,
        scrollY: $scrollY,
        scrollCollapse: true,
        scroller: true,
        paging: false,
        'dom': 't'
    });

    /** Search varibale in static variable dataTable */
    $dt_search.on('keyup change', function(){
        dTable.api().search($(this).val()).draw();
    });

});

$body.on('hidden.bs.modal', '.modal', function () {

    var toast = $('#toast-container');

    /** Reload select2 after record was added */
    if (toast.find('.toast-success, .toast-warning').is(':visible')) {
        $.pjax.reload({container: '#tree-pjax', url: $(location).attr('href'), timeout: 10000});
    }

    /** Remove errors after modal close */
    toast.find('.toast-error').fadeOut(1000, function() { $(this).remove(); });

});

/** Job form AJAX submit handler */
$body.on('submit', '#job_form', function () {
    modalFormHandler($(this), 'job_form_modal_lg', 'save');
    return false;
});

/** Worker form AJAX submit handler */
$body.on('submit', '#worker_form', function () {

    var $this      = $(this);
    var $form_data = $this.serializeArray().reduce(function(m, o){ m[o.name] = o.value; return m;}, {});
    var $get       = $form_data['Worker[get]'];

    // noinspection EqualityComparisonWithCoercionJS
    if ($woker_type != $get && $woker_type != '') {
        swal({
            title: "<i class=\'fa fa-exclamation-triangle\'></i> " + i18next.t("Warning!"),
            text: i18next.t("<b>By changing worker protocol all created jobs will be deleted from worker permanently!</b> Are you sure you want to change protocol?"),
            html: true,
            showCancelButton: true,
            confirmButtonText: i18next.t("Confirm"),
            cancelButtonText: i18next.t("Cancel"),
            closeOnConfirm: true,
            closeOnCancel: true,
            animation: false
        },
        function(isConfirm){
            if (isConfirm) {
                modalFormHandler($this, 'job_form_modal', 'save');
            } else {
                $('#worker-get').val($woker_type).trigger('change');
            }
        });
    } else {
        modalFormHandler($this, 'job_form_modal', 'save');
    }

    return false;

});

/** Add variable to command_value at cursor position */
$body.on('click', '.add_var', function () {
    $('#command_value').insertAtCursor(this.children[0].innerText);
});
