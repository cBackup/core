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

$(document).on('ready pjax:end', function() {

    var git           = $('#use_git');
    var git_remote    = $('#use_git_remote');
    var mailer        = $('#use_mailer');
    var mailer_type   = $('#mailer_type');
    var mailer_auth   = $('#use_smtp_auth');
    var mailer_verify = $('#use_cert_verify');

    $('.select2').select2({
        minimumResultsForSearch: '-1',
        width: '100%'
    });

    $().add(git).add(git_remote).add(mailer).add(mailer_auth).add(mailer_verify).bootstrapToggle();

    /** Git controls */
    if( !git.is(':checked') ) {
        $('input.git').prop('readonly', true);
        git_remote.attr('disabled', true).change();
    }

    if( !git_remote.is(':checked') ) {
        $('input.git-remote').prop('readonly', true);
    }

    git.change(function() {
        $('input.git').prop('readonly', !$(this).is(':checked'));
        git_remote.attr('disabled', !$(this).is(':checked')).change();
        if (typeof git_remote.attr('disabled') !== typeof undefined && git_remote.attr('disabled').length > 0) {
            $('input.git-remote').prop('readonly', !$(this).is(':checked'));
        }
    });

    git_remote.change(function() {
        $('input.git-remote').prop('readonly', !$(this).is(':checked'));
    });

    $().add(git).add(git_remote).change(function() {
        $('.git-box .form-group.has-error .help-block').remove();
        $('.git-box .form-group.has-error').toggleClass('has-error', 'no-error', '0');
    });


    /** Mailer controls */
    mailer.change(function() {
        mailer_type.attr('disabled', !$(this).is(':checked')).change();
        $('input.mailer-input.common').prop('readonly', !$(this).is(':checked'));
        if (typeof mailer_type.attr('disabled') !== typeof undefined && mailer_type.attr('disabled').length > 0) {
            mailer_verify.attr('disabled', !$(this).is(':checked')).change();
            mailer_auth.attr('disabled', !$(this).is(':checked')).change();
            $('input.mailer-input.smtp, input.mailer-input.smtp-auth, input.mailer-input.local').prop('readonly', !$(this).is(':checked'));
            $('#smtp_security').prop('disabled', !$(this).is(':checked'));
        }
    }).change();

    mailer_type.change(function() {
        $('input.mailer-input.local').prop('readonly', $(this).is(':checked'));
        $('input.mailer-input.smtp').prop('readonly', !$(this).is(':checked'));
        $('#smtp_security').prop('disabled', !$(this).is(':checked'));
        mailer_verify.attr('disabled', !$(this).is(':checked')).change();
        mailer_auth.attr('disabled', !$(this).is(':checked')).change();
        if (typeof mailer_auth.attr('disabled') !== typeof undefined && mailer_auth.attr('disabled').length > 0) {
            $('input.smtp-auth').prop('readonly', !$(this).is(':checked'));
        }
    }).change();


    if( !mailer.is(':checked') ) {
        $('input.mailer-input.local').prop('readonly', true);
    }

    /** SMTP auth */
    if( !mailer_auth.is(':checked') ) {
        $('input.mailer-input.smtp-auth').prop('readonly', true);
    }

    mailer_auth.change(function() {
        $('input.mailer-input.smtp-auth').prop('readonly', !$(this).is(':checked'));
    });

    $().add(mailer).add(mailer_auth).change(function() {
        $('.mailer-box .form-group.has-error .help-block').remove();
        $('.mailer-box .form-group.has-error').toggleClass('has-error', 'no-error', '0');
    });

    /** Reinit git settings on button click via Ajax */
    $('#reinit_git, #send_test_mail').click(function() {

        var url      = $(this).data('url');
        var btn_lock = Ladda.create(document.querySelector('#' + $(this)[0].id));
        toastr.clear();

        //noinspection JSUnusedGlobalSymbols
        $.ajax({
            type: 'POST',
            url: url,
            beforeSend: function() {
                btn_lock.start();
            },
            success: function (data) {
                if (isJson(data)) {
                    showStatus(data);
                } else {
                    toastr.warning(data, '', {timeOut: 0, closeButton: true});
                }
            },
            error: function (data) {
                toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
            }
        }).always(function(){
            btn_lock.stop();
        });

    });

    /** Init git repo on button click via Ajax */
    $('#init_repo').click(function() {

        var url      = $(this).data('url');
        var btn_lock = Ladda.create(document.querySelector('#init_repo'));
        toastr.clear();

        //noinspection JSUnusedGlobalSymbols
        $.ajax({
            type: 'POST',
            url: url,
            beforeSend: function() {
                btn_lock.start();
            },
            success: function (data) {
                if (isJson(data)) {
                    showStatus(data);
                } else {
                    toastr.warning(data, '', {timeOut: 0, closeButton: true});
                }

                $.pjax.reload({container: '#config-pjax', url: $(location).attr('href'), timeout: 10000});
            },
            error: function (data) {
                toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
            }
        }).always(function(){
            btn_lock.stop();
        });

    });

    /** Checkbox for password show/hide */
    var checkbox = $('.show-password');

    checkbox.change(function() {

        var input_id = $(this)[0].id.split('_')[0];

        if ($(this).is(':checked')) {
            $('#' + input_id).attr('type', 'text');
        }
        else {
            $('#' + input_id).attr('type', 'password');
        }

    }).change();

    checkbox.iCheck({
        checkboxClass: 'icheckbox_minimal-red'
    }).on('ifChanged', function (event) {
        $(event.target).trigger('change');
    });

});
