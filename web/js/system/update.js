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

$(document).on('ready pjax:end', function () {

    /** Default variables */
    var $btn_lock;
    var $steps    = [];
    var $cur_step = 0;
    var $step     = 0;
    var $stop     = false;
    var $error    = false;

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
        if (active !== '' && active !== '#update_errors') {
            $('[href="' + active + '"]').tab('show');
        }

    }

    /** Install updates via Ajax */
    $('#install_updates').on('click', function() {

        /** Prevent user from reloading the page */
        window.onbeforeunload = function() { return false; };

        //noinspection JSUnresolvedVariable
        var $urls       = update_urls; // Update urls are registred in main index view
        var $update_btn = $('#check_for_updates');
        $btn_lock       = Ladda.create(document.querySelector('#install_updates'));

        /** Clear toastr messages */
        toastr.clear();

        /** Reset counters */
        $error    = false;
        $cur_step = 0;
        $step     = 0;

        /** Steps array */
        $steps  = [
            {step: 'lock_ui', url : $urls['lock_ui_url'], params:{lock: 1}},
            {step: 'core_update', url : $urls['update_core_url']},
            {step: 'db_update', url : $urls['update_db_url']},
            {step: 'cleanup', url : $urls['cleanup_url']},
            {step: 'finish_update', url : $urls['lock_ui_url'], params:{lock: 0}}
        ];

        /** Reset divs */
        $('#update_errors_tab').parent().addClass('hide');
        $.each($steps, function ($_, $value) {
            $('#' + $value['step']).attr({class: '', style: ''}).addClass('fa fa-stop-circle-o text-warning');
            $('#error_' + $value['step']).hide();
            $('#error_msg_' + $value['step']).html('');
        });

        /** Lock buttons */
        $btn_lock.start();
        $update_btn.addClass('disabled');

        /** Start install process */
        ajaxEventHandler();

    });

    /**
     * Ajax event handler for runnig update methods
     */
    var ajaxEventHandler = function () {

        /** check to make sure there are more requests to make */
        if ($cur_step < $steps.length && $stop === false) {

            var $icon = $('#' + $steps[$cur_step].step);

            /** Set url params if needed */
            if (typeof $steps[$cur_step].params !== typeof undefined && Object.keys($steps[$cur_step].params).length > 0) {
                $steps[$cur_step].params = '&' + $.param($steps[$cur_step].params)
            } else {
                $steps[$cur_step].params = '';
            }

            $.ajax({
                type: 'POST',
                url: $steps[$cur_step].url + $steps[$cur_step].params,
                beforeSend: function() {
                    $icon.attr('class', '').addClass('fa fa-spinner fa-pulse').css({color: '#72afd2;'});
                },
                success  : function (data) {
                    if (isJson(data)) {

                        var $json = $.parseJSON(data);

                        if ($json['status'] === 'true') {
                            $icon.attr({class: '', style: ''}).addClass('fa fa-check text-success');
                            $step = $cur_step;
                            $cur_step++;
                            ajaxEventHandler();
                        } else {
                            errorHandler($icon, data);
                        }

                    } else {
                        errorHandler($icon, data);
                    }
                },
                error: function (data) {
                    errorHandler($icon, data.responseText);
                }
            }).done(function () {
                if ($step === ($steps.length - 1)) {
                    if (!$error) {
                        $.pjax.reload({container: '#live-update-pjax', url: $(location).attr('href'), timeout: 10000});
                    }
                    $stop = false;
                    $('#check_for_updates').removeClass('disabled');

                    /** Unload onbeforeunload event */
                    window.onbeforeunload = function() {return null;};
                }
            }).always(function () {
                if ($step === ($steps.length - 1) && $error === true) {
                    $stop = false;
                    $btn_lock.stop();
                    $('#check_for_updates').removeClass('disabled');
                    /** Unload onbeforeunload event */
                    window.onbeforeunload = function() { return null; };
                }
            });
        }
    };

    /**
     * Error handler
     *
     * @param $icon
     * @param data
     */
    var errorHandler = function ($icon, data) {

        /** Set flags */
        $step  = $cur_step;
        $error = true;
        $icon.attr({class: '', style: ''}).addClass('fa fa-times text-danger');

        /** Skip update process if error occurs, except last step */
        if ($cur_step !== ($steps.length - 1)) {
            $cur_step = 4;
            ajaxEventHandler();
            $stop  = true;
        }

        /** Show error message */
        if (isJson(data)) {
            showError($.parseJSON(data)['msg']);
        } else {
            toastr.error(data, '', {timeOut: 0, closeButton: true});
        }
    };

    /**
     * Show update errors
     *
     * @param {string} $message
     */
    var showError = function ($message) {
        $('#update_errors_tab').parent().removeClass('hide');
        $('#error_' + $steps[$step].step).show();
        $('#error_msg_' + $steps[$step].step).append($message);
    };

});

/** Check for updates via Ajax */
$('#check_for_updates').on('click', function() {

    var $ajax_url    = $(this).data('check-url');
    var $btn_lock    = Ladda.create(document.querySelector('#check_for_updates'));
    var $install_btn = $('#install_updates');

    $.ajax({
        type: 'POST',
        url: $ajax_url,
        beforeSend: function() {
            $btn_lock.start();
            $install_btn.addClass('disabled');
        },
        success: function (data) {
            showStatus(data);
            $.pjax.reload({container: '#live-update-pjax', url: $(location).attr('href'), timeout: 10000});
        },
        error: function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    }).always(function () {
        $btn_lock.stop();
        $install_btn.removeClass('disabled');
    });

});
