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

var config_content = $('#config_content');
var diff_content   = $('#diff_content');
var git_modal      = $('#git_log_modal');
var config_text    = '';

$(document).on('ready pjax:end', function() {
    /** Init select2 */
    $('.select2-normal').select2({
        width: '100%'
    });

    /** Init small select2 without search */
    $(".select2-small").select2({
        width: '100%',
        minimumResultsForSearch: -1,
        containerCssClass: ':all:'
    });

    /** Init small select2 with search */
    $(".select2-small-search").select2({
        width: '100%',
        containerCssClass: ':all:'
    });

});

/** Load config file in iframe on demand */
$(document).on('click', '#show_config', function () {
    if (config_content.is(':hidden')) {
        $.ajax({
            url    : $(this).data('url'),
            type   : 'post',
            data   : $(this).data('params'),
            success: function (data) {
                config_text = $(data).text();
                var iframe  = $('#config_iframe');
                iframe.contents().find('html').html(data);
                /** Calculate iframe height */
                var i_body = iframe[0].contentWindow.document.body;
                var height = i_body.offsetHeight + 40;
                iframe[0].style.height = height + 'px';
            },
            error : function (data) {
                toastr.error(data.responseText, '', {toastClass: 'no-shadow', timeOut: 0, closeButton: true});
            }
        });
    }

});

/** Load git log for current node */
$(document).on('click', '#show_history, .reload_history', function () {
    if (diff_content.is(':hidden') || this.className === 'reload_history') {
        $.ajax({
            url    : $(this).data('url'),
            type   : 'post',
            data   : $(this).data('params'),
            beforeSend : function() {
                $('.loader').show();
                $('#file_diff').hide();
                if (git_modal.is(':visible')) {
                    git_modal.modal('hide');
                }
            },
            success: function (data) {
                $('#file_diff').html(data).show();
                $('.loader').hide();
            },
            error : function (data) {
                toastr.error(data.responseText, '', {toastClass: 'no-shadow', timeOut: 0, closeButton: true});
            }
        });
    }

});

/** Enable/Disabled copy button on config showing/hiding */
config_content.on('shown.bs.collapse hide.bs.collapse', function() {
    $('#copy_config').toggleClass('disabled');
});

/** show/hide passwords */
$('.showhide_pass').on('focus mouseover', function(){
    this.type = 'text';
}).on('blur mouseout', function(){
    this.type = 'password';
});

/** Copy config from iframe to clipboard */
var clipboard = new Clipboard('#copy_config', {
        text: function() {
            return config_text;
        }
    });

clipboard.on('success', function() {
    toastr.success(i18next.t('Configuration successfully copied'), '', {timeOut: 5000, progressBar: true, closeButton: true});
});

clipboard.on('error', function() {
    toastr.warning(i18next.t('An error occurred while copying configuration'), '', {closeButton: true});
});

/** Expand/Compress tab panel */
$(document).on('click', '#tab_expand_btn', function () {
    var nav_tabs = $('#nav_tabs');

    if (nav_tabs.hasClass('box-fullscreen')) {
        $(this).children('i').switchClass('fa-compress', 'fa-expand', 0);
    } else {
        $(this).children('i').switchClass('fa-expand', 'fa-compress', 0);
    }

    nav_tabs.toggleClass('box-fullscreen');

    /** Refresh scrolling tabs */
    $('.tabs-scroll').scrollingTabs('refresh');

});

/** Get auth template on select change via Ajax */
$(document).on('change', '#auth_template_list', function () {

    var auth_name = ($(this).val() !== '') ? $(this).val() : $(this).data('default-value');
    var ajax_url  = $(this).data('url') + '&name=' + auth_name;

    $.ajax({
        type: 'POST',
        url: ajax_url,
        beforeSend: function() {
            $('#auth_template_preview').block({
                message: '<i style="color:#72afd2;" class="fa fa-spinner fa-3x fa-pulse"></i>',
                css: {backgroundColor: 'none', border: 'none'},
                overlayCSS: {backgroundColor: 'none',cursor: 'default'}
            });
        },
        success: function (data) {
            if (isJson(data)) {
                $('#auth_template_preview').show();
                $('#auth_textarea').val($.parseJSON(data));
            }
            $('#display_error').html('').hide();
        },
        error: function (data) {
            $('#auth_template_preview').hide();
            $('#display_error').html('' +
                '<div class="callout callout-danger" style="margin-bottom: 0;">' +
                data.responseText + '' +
                '</div>'
            ).show();
        }
    }).always(function() {
        $('#auth_template_preview').unblock();
    });

});

/** Set node auth template via Ajax */
$(document).on('click', '#update_node_auth', function () {

    var auth_name = $('#auth_template_list').val();
    var ajax_url  = $(this).data('update-url') + '&name=' + auth_name;
    var btn_lock  = Ladda.create(document.querySelector('#update_node_auth'));

    $.ajax({
        type: 'POST',
        url: ajax_url,
        beforeSend: function() {
            btn_lock.start();
        },
        success: function (data) {
            showStatus(data);
            $.pjax.reload({container: '#auth-template-pjax', url: $(location).attr('href'), timeout: 10000});
        },
        error: function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    }).always(function () {
        btn_lock.stop();
    });

});

/** Load widget on demand via Ajax */
$(document).on('click', '.load-widget', function () {

    var $ajax_url    = $(this).data('widget-url');
    var $plugin_name = $(this).data('plugin-name');

    $.ajax({
        type: 'POST',
        url: $ajax_url,
        beforeSend: function() {
            $('#widget_loading_' + $plugin_name).show();
            $('#widget_content_' + $plugin_name).html('').hide();
        },
        success: function (data) {

            if (isJson(data)) {

                var $json    = $.parseJSON(data);
                var $content = '';

                if ($json['status'] === 'success') {
                    $content = $json['data'];
                } else {
                    $content = '<div class="callout callout-danger" style="margin: 0;"><p>' + $json['data'] + '</p></div>'
                }

                $('#widget_loading_' + $plugin_name).hide();
                $('#widget_content_' + $plugin_name).html($content).show();
            }

        },
        error: function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    });

});

/** Modal auth template add form AJAX submit handler */
$(document).on('submit', '#deviceauthtemplate_form', function () {
    modalFormHandler($(this), 'job_modal', 'save');
    return false;
});

/** Modal hidden event handler */
$(document).on('hidden.bs.modal', '#job_modal', function () {

    var toast = $('#toast-container');

    /** Reload select2 after record was added */
    if (toast.find('.toast-success, .toast-warning').is(':visible')) {
        var update_url = $('#auth_template_list').data('update-url');
        updateSelect2(update_url, 'auth_template_list');
    }

    /** Remove errors after modal close */
    toast.find('.toast-error').fadeOut(1000, function() { $(this).remove(); });

});

/** Show/Hide prepend location input */
$(document).on('click', '#open_input', function () {
    $('#prepend_location_text').toggleClass('hidden');
    $('#prepend_location_input').toggleClass('hidden');
});

/** Clear prepend location input value */
$(document).on('click', '#clear_input', function () {
    $('#prepend_box').val('');
});

/** Set node prepend location via Ajax */
$(document).on('click', '#set_prepend_location', function () {

    var ajax_url = $(this).data('set-url')+ '&prepend_location=' + $('#prepend_box').val();
    var btn_lock = Ladda.create(document.querySelector('#set_prepend_location'));

    $.ajax({
        type: 'POST',
        url: ajax_url,
        beforeSend: function() {
            btn_lock.start();
        },
        success: function (data) {
            showStatus(data);
            $.pjax.reload({container: '#node-info-pjax', url: $(location).attr('href'), timeout: 10000});
        },
        error: function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    }).always(function () {
        btn_lock.stop();
    });

});

/** Show/Hide credentials select box */
$(document).on('click', '#open_select', function () {
    $('#credentials_text').toggleClass('hidden');
    $('#credentials_select').toggleClass('hidden');
});

/** Clear credentials select box */
$(document).on('click', '#clear_credentials_select', function () {
    $('#credentials_select_box').val('').trigger('change');
});

/** Show credentials tab */
$(document).on('click', '#show_credentials_tab', function () {
    $('[href="#tab_4"]').tab('show');
});

/** Set node credentials via Ajax */
$(document).on('click', '#set_credentials', function () {

    var ajax_url  = $(this).data('set-url')+ '&credential_id=' + $('#credentials_select_box').val();
    var btn_lock  = Ladda.create(document.querySelector('#set_credentials'));
    var $in_group = $(this).closest('.input-group');

    $.ajax({
        type: 'POST',
        url: ajax_url,
        beforeSend: function() {
            btn_lock.start();
        },
        success: function (data) {
            if (isJson(data)) {
                var json = $.parseJSON(data);
                if (json['status'] === 'success') {
                    location.reload();
                } else{
                    showStatus(data);
                    $in_group.parent().addClass('has-error');
                }
            }
        },
        error: function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    }).always(function () {
        btn_lock.stop();
    });

});


/** Hide subnet select box on click */
$(document).on('click', '.close-input', function () {
    toastr.clear();
    var $in_group = $(this).closest('.input-group');
    $in_group.find('.alt_actions').removeClass('hide');
    $in_group.find('.subnets, .close-input').addClass('hide').removeClass('has-error');
    $in_group.find('select.network_select').val(null).trigger('change');
});

/** Run alt interface action via Ajax */
$(document).on('click', '.run_alt_action', function () {

    var $btn_id   = $(this).attr('id');
    var ajax_url  = $(this).data('ajax-url');
    var $params   = $(this).data('params');
    var $in_group = $(this).closest('.input-group');
    var $btn_lock = Ladda.create(document.querySelector('#' + $btn_id));

    $params['action_type'] = $in_group.find('select.alt_actions_select').val();
    $params['network_id']  = $in_group.find('select.network_select').val();

    $.ajax({
        type: 'POST',
        url: ajax_url,
        data: $params,
        beforeSend: function() {
            $btn_lock.start();
        },
        success: function (data) {
            if (isJson(data)) {
                var json = $.parseJSON(data);
                if (json['status'] === 'success') {
                    location.reload();
                } else if(json['error_type'] === 'wrong_subnet') {
                    showStatus(data);
                    $in_group.find('.alt_actions').addClass('hide');
                    $in_group.find('.subnets, .close-input').removeClass('hide').addClass('has-error');
                } else {
                    showStatus(data);
                }
            }
        },
        error: function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    }).always(function () {
        $btn_lock.stop();
    });

});

/** Run node backup via Ajax */
$(document).on('click', '#run_node_action', function () {

    var $this       = $(this);
    var ajax_url    = $this.data('ajax-url');
    var pjax_reload = $this.data('pjax-reload');

    $.ajax({
        type: 'POST',
        url: ajax_url,
        beforeSend: function() {
            $this.addClass('disabled');
            $this.prepend('<i class="fa fa-spinner fa-spin"></i>');
        },
        success: function (data) {
            showStatus(data);
            if (typeof pjax_reload !== typeof undefined && pjax_reload === true) {
                $.pjax.reload({container: '#node-info-pjax', url: $(location).attr('href'), timeout: 10000});
            }
        },
        error: function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    }).always(function () {
        $this.removeClass('disabled');
        $this.find('i').remove();
    });

});

/** Document ready scripts */
$(document).on('ready', function() {

    /** Init commit history dataTable */
    $('#commit_history_table').dataTable({
        ordering: false,
        deferRender: true,
        scrollY: 600,
        scrollCollapse: true,
        scroller: true,
        paging: false,
        'dom': 't'
    });

    /** Init tab scroll */
    $('.tabs-scroll').scrollingTabs({
        disableScrollArrowsOnFullyScrolled: true,
        scrollToTabEdge: true
    }).on('ready.scrtabs', function() {
        $('.tabs-scroll').removeClass('disable-multirow').parent().addClass('nav-tabs-custom');
    });

    /** Refresh scrolling tabs on window resize */
    $(window).resize(function() {
        $('.tabs-scroll').scrollingTabs('refresh');
    });

});

/** Show commit history table when modal window is visible */
$(document).on('shown.bs.modal', '#git_log_modal', function () {

    $('#commit_history_placeholder').hide();
    $('#commit_history').removeClass('hidden');
    $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();

});
