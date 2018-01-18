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

/** Method declarations */
var toastrAlert;
var launchModalWindow;
var loadServiceWidget;

/** Default variables */
var $body          = $('body');
var $locked_values = [];

/**
 * Custom JavaScript will be initiated on document ready
 */
$(function() {

    /** Show GridView spinner after link click */
    $body.on('click', '.grid-view:not(.tab-grid-view) a', function() {
        boxSpinner($body, $(this));
    });

    /** Show GridView spinner after select change or keydown */
    $body.on('change keydown', '.grid-view:not(.tab-grid-view)', function() {
        boxSpinner($body, $(this));
    });

    /** Show Tab GridView spinner after link click */
    $body.on('click', '.grid-view.tab-grid-view a', function() {
        tabSpinner($body, $(this));
    });

    /** Show Tab GridView spinner after select change or keydown */
    $body.on('change keydown', '.grid-view.tab-grid-view', function() {
        tabSpinner($body, $(this));
    });

    /** Modal clear on close */
    $body.on('hidden.bs.modal', '.modal', function (e) {
        $(e.target).removeData('bs.modal');
    });

    /** Default popover init */
    $("[data-toggle=popover]").popover().click(function(e) {
        e.preventDefault()
    });

    /** Prevent closing dropdown on click */
    $('ul.dropdown-menu.dropdown-menu-static').on('click', function (e) {
        e.stopPropagation();
    });

    /** Custom popover click handler */
    $body.on('click', '[data-toggle=popover][data-click-handler=custom]', function (e) {
        $(this).popover('toggle');
        e.preventDefault();
    });

    /** Close custom popover when click on body */
    $body.on('click', function (e) {
        $('[data-toggle="popover"][data-click-handler=custom]').each(function () {
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                (($(this).popover('hide').data('bs.popover')||{}).inState||{}).click = false;
            }

        });
    });

    /** Set active tab by link click */
    $body.on('click', '.set-active-tab', function () {
        if (_supportsSessionStorage) {

            var $tab_id = $(this).data('target');
            var $open   = $(this).data('open');

            /** Save active tab */
            sessionStorage.setItem('active', '#' + $tab_id);

            /** If data-open is set open window in new tab */
            if (typeof $open !== typeof undefined && $open != false) {
                window.open(this.href, $open);
                return false;
            }
        }
    });

    /**
     * Show full label text in tooltip when it is ellipsed
     */
    $body.on('mouseenter', 'form:not(.form-horizontal) .form-group label', function(){

        var $this       = $(this);
        var scrollWidth = this.scrollWidth;

        /** Workaround for Microsoft browsers. */
        if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0 || navigator.userAgent.indexOf('Edge/') > 0) {
            scrollWidth = scrollWidth - 1;
        }

        if (this.offsetWidth < scrollWidth && !$this.attr('title')) {
            $this.attr('data-original-title', $this.text());
            $this.tooltip('show');
        }
    }).on('mouseleave', 'form:not(.form-horizontal) .form-group label', function () {
        $(this).removeAttr('data-toggle data-original-title')
    });

    /**
     * Show table cell full text in title box when it is ellipsed
     */
    $body.on('mouseenter', 'table tr td:last-child:not(.grid-expand-row) > div, table.ellipsis tr td.hide-overflow', function(){

        var $this       = $(this);
        var scrollWidth = this.scrollWidth;

        /** Workaround for Microsoft browsers. */
        if (navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > 0 || navigator.userAgent.indexOf('Edge/') > 0) {
            scrollWidth = scrollWidth - 1;
        }

        if (this.offsetWidth < scrollWidth && !$this.attr('title')) {
            var $orig_text = $.trim($this.text());
            var $matches   = $orig_text.match(/\n/g);
            var $text      = (typeof $matches !== typeof null && $matches.length <= 1) ? $orig_text.replace(/(.{100})/g, "$1\n") : $orig_text;
            $this.css('cursor', 'help');
            $this.attr('title', $text);
        }
    }).on('mouseleave', 'table tr td:last-child:not(.grid-expand-row) > div, table.ellipsis tr td.hide-overflow', function () {
        $(this).removeAttr('title style');
    });

    /**
     * Update GridView data via Ajax
     * Can be used ONLY with GridView
     *
     * Usage:
     *  Html::a('Text', 'javascript:;', [
     *      'class'             => 'ajaxGridUpdate',
     *      'data-ajax-url'     => Url::to(['link', 'param' => '']),
     *      'data-ajax-confirm' => 'Confirm text',
     *      'data-pjax-reload'  => 'true'
     *  ]);
     *
     *  If data-ajax-confirm is not set or set to false, confirm dialog will not be shown
     *  If pjax-reload is not set or set to true GridView must be wrapped in Pjax::widget()
     *
     */
    $body.on('click', '.ajaxGridUpdate', function () {

        var $grid_id      = $(this).closest('div').attr('id');
        var $ajax_url     = $(this).attr('data-ajax-url');
        var $confirm_text = $(this).attr('data-ajax-confirm');
        var $pjax_reload  = $(this).attr('data-pjax-reload');
        var $pjax_id      = $('#' + $grid_id).parent().attr('id');
        var $run_ajax     = false;

        /** Don't show confirm if data-ajax-confirm is not set or set to false */
        if (typeof $confirm_text !== typeof undefined && $confirm_text !== false) {
            if (confirm($confirm_text)) {
                $run_ajax = true
            }
        } else {
            $run_ajax = true;
        }

        if ($run_ajax) {
            $.ajax({
                type: 'POST',
                url: $ajax_url,
                success: function (data) {
                    if (typeof $pjax_reload === typeof undefined || $pjax_reload === 'true') {
                        $.pjax.reload({container: '#' + $.trim($pjax_id), url: $(location).attr('href'), timeout: 10000});
                        showStatus(data);
                    } else {
                        location.reload();
                    }
                },
                error: function (data) {
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            });
        }

    });

    /**
     * Gridview expandable row handler
     *
     * Method use AJAX to get content
     * For how to use this handler see cBackup documentation
     */
    $body.on('click', '.ajaxGridExpand', function () {

        var $this     = $(this);
        var $grid     = $('#' + $this.closest('div').attr('id'));
        var $target   = $($this.attr('data-div-id'));
        var $ajax_url = $this.attr('data-ajax-url');
        var $multiple = $this.attr('data-multiple');
        var $expanded = $grid.find('.expanded');

        /** Close previous expanded row if multiple flag set to false */
        if (typeof $multiple !== typeof undefined && $multiple == 'false') {

            var data_id = $expanded.attr('id');

            if (typeof data_id !== typeof undefined) {
                $grid.find('a[data-div-id="#' + data_id + '"]').children('i').switchClass('fa-caret-square-o-up', 'fa-caret-square-o-down', 0);
                $expanded.slideUp();
                $expanded.removeClass('expanded');
            }

        }

        if ($target.is(':visible')) {
            $(this).children('i').switchClass('fa-caret-square-o-up', 'fa-caret-square-o-down', 0);
            $target.removeClass('expanded');
            $target.slideUp();
        } else {
            $.ajax({
                type: 'POST',
                url: $ajax_url,
                beforeSend: function() {
                    $this.children('i').switchClass('fa-caret-square-o-down', 'fa-cog fa-spin', 0);
                },
                success: function (data) {
                    $this.children('i').switchClass('fa-cog fa-spin', 'fa-caret-square-o-up', 0);
                    $target.addClass('expanded');
                    $target.html(data).slideDown();
                },
                error: function (data) {
                    $this.children('i').switchClass('fa-cog fa-spin', 'fa-caret-square-o-down', 0);
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            });
        }

    });

    /**
     * Gridview expandable row handler (no AJAX)
     *
     * Method do not use AJAX to get content.
     * Content is already rendered on page, method only expand hidden row.
     * For how to use this handler see cBackup documentation
     */
    $body.on('click', '.gridExpand', function () {

        var $this     = $(this);
        var $grid     = $('#' + $this.closest('div').attr('id'));
        var $target   = $($this.attr('data-div-id'));
        var $multiple = $this.attr('data-multiple');
        var $expanded = $grid.find('.expanded');

        /** Close previous expanded row if multiple flag set to false */
        if (typeof $multiple !== typeof undefined && $multiple == 'false') {

            var data_id = $expanded.attr('id');

            if (typeof data_id !== typeof undefined) {
                $grid.find('a[data-div-id="#' + data_id + '"]').children('i').switchClass('fa-caret-square-o-up', 'fa-caret-square-o-down', 0);
                $expanded.slideUp();
                $expanded.removeClass('expanded');
            }

        }

        if ($target.is(':visible')) {
            $(this).children('i').switchClass('fa-caret-square-o-up', 'fa-caret-square-o-down', 0);
            $target.removeClass('expanded');
            $target.slideUp();
        } else {
            $this.children('i').switchClass('fa-caret-square-o-down', 'fa-caret-square-o-up', 0);
            $target.addClass('expanded');
            $target.slideDown();
        }

    });

    /**
     * Select2 4.x locked item workaround
     * Developers for some reason removed locked item feature
     */
    $body.on('select2:unselecting', 'select[multiple]', function(e) {

        $(e.target).data('unselecting', true);
        var $select = $(e.params.args.data.element);

        if ($select.attr('locked')) {
            $(e.target.nextSibling).find('ul > li.select2-selection__choice[title="'+ $select.val() +'"]').css({
                'background-color': '#d33724',
                'border-color': '#d33724'
            }).children().remove('span');
            $locked_values.push($select.val());
            return false;
        }
    }).on('select2:open', function(e) {
        var $target = $(e.target);
        if ($target.data('unselecting')) {
            $target.removeData('unselecting');
            $target.select2('close');
        }
    }).on('change', function(e) {
        if (!$.isEmptyObject($locked_values)) {
            $.each($locked_values, function (id, rights) {
                $(e.target.nextSibling).find('ul > li.select2-selection__choice[title="'+ rights +'"]').css({
                    'background-color': '#d33724',
                    'border-color': '#d33724'
                }).children().remove('span');
            });
        }
    });

    /**
     * Update message widget when message is marked as done
     */
    $body.on('click', '.updateMessages', function () {
        var $ajax_url = $(this).data('update-url');
        $body.on('pjax:end.pjax-mes-update-handler', function () {
            $.ajax({
                type: 'POST',
                url: $ajax_url,
                success: function (data) {
                    $('ul.nav.navbar-nav > li.notifications-menu.system-message-widget').replaceWith(data);
                },
                error: function (data) {
                    toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
                }
            });
            $body.off('pjax:end.pjax-mes-update-handler'); // Unset .on handler when pjax ended work
        });
    });

    /** Render back to top buttom */
    var $slideToTop = $('<div />');

    $slideToTop.html('<i class="fa fa-chevron-up"></i>');

    $slideToTop.css({
        position          : 'fixed',
        bottom            : '5px',
        right             : '25px',
        width             : '40px',
        height            : '40px',
        color             : '#eee',
        'line-height'     : '40px',
        'text-align'      : 'center',
        'background-color': '#222d32',
        cursor            : 'pointer',
        'border-radius'   : '5px',
        'z-index'         : '9999999',
        opacity           : '.7',
        'display'         : 'none'
    });

    $slideToTop.on('mouseover', function () {
        $(this).css('opacity', '1');
    });

    $slideToTop.on('mouseout click', function () {
        $(this).css('opacity', '.7')
    });

    $('.wrapper').append($slideToTop);

    $(window).scroll(function () {
        if ($(window).scrollTop() >= 150) {
            if (!$($slideToTop).is(':visible')) {
                $($slideToTop).fadeIn(500)
            }
        } else {
            $($slideToTop).fadeOut(500)
        }
    });

    $($slideToTop).click(function () {
        $('html, body').animate({
            scrollTop: 0
        }, 500)
    });

});

/**
 *  Check if data is JSON string
 *
 * @param   {string} data
 * @returns {boolean}
 */
var isJson = function(data){
    try {
        $.parseJSON(data);
        return true;
    } catch (e) {
        return false;
    }
};

/**
 * Check if browser session storage is enabled
 *
 * @returns {boolean}
 */
var _supportsSessionStorage = (function() {
    return !!window.sessionStorage
        && $.isFunction(sessionStorage.getItem)
        && $.isFunction(sessionStorage.setItem)
        && $.isFunction(sessionStorage.removeItem);
}());

/**
 * Show message after GridView update
 *
 * @param {string} data
 */
var showStatus = function(data) {

    /** Close already opened toastrs and alerts */
    $('.alert').remove();
    toastr.clear();

    if (isJson(data)) {

        var $json = $.parseJSON(data);

        switch ($json['status']) {
            case 'success':
                toastr.success($json['msg'], '', {timeOut: 5000, progressBar: true, closeButton: true});
            break;
            case 'warning':
                toastr.warning($json['msg'], '', {timeOut: 5000, progressBar: true, closeButton: true});
                break;
            case 'error':
                toastr.error($json['msg'], '', {timeOut: 0, closeButton: true});
            break;
        }
    }
};

/**
 * GridView spinner handler
 *
 * @param {object} $container
 * @param {object} $context
 */
var boxSpinner = function ($container, $context) {

    var $icon       = $context.parents('.box').find('.box-header').children('i');
    var $icon_class = $icon.attr('class').split(' ')[1];

    $container.on('pjax:start.pjax-box-handler', function() {
        $icon.switchClass($icon_class, 'fa-spinner fa-spin', 0);
        $context.closest('.grid-view th a').addClass('disabled');
    });

    $container.on('pjax:end.pjax-box-handler', function() {
        $icon.switchClass('fa-spinner fa-spin', $icon_class, 0);
        $container.off('pjax:start.pjax-box-handler pjax:end.pjax-box-handler'); // Unset .on handler when pjax ended work
    });
};

/**
 * Tab GridView spinner handler
 *
 * @param {object} $container
 * @param {object} $context
 */
var tabSpinner = function ($container, $context) {

    var $tab_id  = $context.closest('div.tab-pane.active').attr('id');
    var $icon        = $("[href='#" + $tab_id + "']").children('i');
    var $icon_class  = $icon.attr('class').split(' ')[1];

    $container.on('pjax:start.pjax-tab-handler', function() {
        $icon.switchClass($icon_class, 'fa-spinner fa-spin', 0);
        $context.closest('.grid-view th a').addClass('disabled');
    });

    $container.on('pjax:end.pjax-tab-handler', function() {
        $icon.switchClass('fa-spinner fa-spin', $icon_class, 0);
        $container.off('pjax:start.pjax-tab-handler pjax:end.pjax-tab-handler'); // Unset .on handler when pjax ended work
    });
};

/**
 * Show toastr with translation.
 * Method waits unit i18next translation json is fully loaded.
 *
 * Note: Method will work only if i18nextAsset is registred.
 *
 * Usage:
 *  - Simple:   toastrAlert('success', 'Hello, translate me!')
 *  - Advanced: toastrAlert('success', 'Hello {{name}}, translate me!', {name: 'Vasja'})
 *
 * @param {string} level
 * @param {string} text
 * @param {object} [params] This parameter is optimal, meant for passing dynamic variables, see i18next documentation
 */
toastrAlert = function (level, text, params) {
    i18next.on('loaded', function () {
        switch (level) {
            case 'success':
                toastr.success(i18next.t(text, params), '', {timeOut: 5000, progressBar: true, closeButton: true});
                break;
            case 'warning':
                toastr.warning(i18next.t(text, params), '', {timeOut: 5000, progressBar: true, closeButton: true});
                break;
            case 'error':
                toastr.error(i18next.t(text, params), '', {timeOut: 0, closeButton: true});
                break;
        }
    });
};


/**
 * GridView Ladda button spinner
 * Can be used ONLY in GridView with PJAX
 *
 * @param {string} $btn_id
 */
var gridLaddaSpinner = function ($btn_id) {

    /** Create ladda button */
    var $ladda = Ladda.create( document.querySelector( '#' + $btn_id ) );

    $body.on('pjax:start.pjax-ladda-handler', function() {
        $ladda.start();
    });

    $body.on('pjax:end.pjax-ladda-handler', function() {
        $ladda.stop();
        $body.off('pjax:start.pjax-ladda-handler pjax:end.pjax-ladda-handler'); // Unset .on handler when pjax ended work
    });

};

/**
 * Submit form from modal window via AJAX
 *
 * @param {object} $form_obj Fisrt part of form ID MUST contain model name Exmaple: credential_form, where credential -> model name
 * @param {string} $modal_id
 * @param {string} $btn_id
 */
var modalFormHandler = function ($form_obj, $modal_id, $btn_id) {

    var $form     = $form_obj;
    var $btn_lock = Ladda.create(document.querySelector('#' + $btn_id));
    var $model    = $form.attr('id').split('_')[0];

    /** Clear error */
    $form.find('.has-error').removeClass('has-error');
    toastr.clear();

    /** Submit form */
    $.ajax({
        url    : $form.attr('action'),
        type   : 'post',
        data   : $form.serialize(),
        beforeSend: function() {
            $btn_lock.start();
        },
        success: function (data) {
            if (isJson(data)) {

                var json = $.parseJSON(data);
                $btn_lock.stop();

                if (json['status'] === 'validation_failed') {
                    $.each(json['error'], function(id, msg) {
                        $('.field-' + $model + '-' + id + ', .field-' + id).addClass('has-error').find('.help-block').remove();
                        toastr.error(msg, '', {toastClass: 'no-shadow', timeOut: 0, closeButton: true});
                    });
                    return false;
                }

                $('#' + $modal_id).modal('toggle');
                showStatus(data);

            } else {
                toastr.warning(data, '', {toastClass: 'no-shadow', timeOut: 0, closeButton: true});
                $btn_lock.stop();
            }
        },
        error : function (data) {
            toastr.error(data.responseText, '', {toastClass: 'no-shadow', timeOut: 0, closeButton: true});
            $btn_lock.stop();
        }
    });

};

/**
 * Update select2 content via Ajax
 *
 * @param {string} $url
 * @param {string} $select_id
 */
var updateSelect2 = function ($url, $select_id) {

    var $select_box      = $('#' + $select_id);
    var $selected_option = '';
    var $empty_option    = '';
    var $option          = '';

    $.ajax({
        url    : $url,
        type   : 'post',
        beforeSend: function () {
            $selected_option = $select_box.find('option:selected').val();
            $empty_option    = $select_box.find('option[value=""]');
            $select_box.empty().prop('disabled', true);
            $select_box.select2({
                width: '100%',
                data: [{ id: '0', text: i18next.t('Wait...') }]
            });
        },
        success: function (data) {

            if (isJson(data)) {
                if ($empty_option.length > 0) {
                    $option = $('<option/>', { value: "", text: $empty_option.text() });
                }
                $select_box.empty().select2({width: '100%', data: $.parseJSON(data)}).prop('disabled', false);
                $select_box.prepend($option).val($selected_option).trigger('change');
            } else {
                toastr.warning(data, '', {timeOut: 0, closeButton: true});
            }

        },
        error : function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    });

};

/**
 * Launch modal window via Ajax
 *
 * @param {string} $modal_id
 * @param {string} $url
 */
launchModalWindow = function ($modal_id, $url) {
    $.ajax({
        type: 'POST',
        url:  $url,
        beforeSend: function() {
            $($modal_id).modal({backdrop: 'static', keyboard: false});
        },
        success: function (data) {
            $($modal_id).html(data);
        },
        error: function (data) {
            $('.modal-body').html('' +
                '<div class="alert alert-danger fade in" style="margin-bottom: 0;">' +
                    data.responseText + '' +
                '</div>'
            );
        }
    });
};

/** Launch service menu */
$body.on('click', '#load_services_menu',  function() {
    if (!$(this).parent().hasClass('open')) {
        loadServiceWidget($(this));
    }
});

/**
 * Load service widget via Ajax
 */
loadServiceWidget = function () {

    var $context  = $('#load_services_menu');
    var $ajax_url = $context.data('ajax-url');
    var $loader   = $('#loader');
    var $widget   = $('#widget_content');

    $.ajax({
        type: 'POST',
        url: $ajax_url,
        beforeSend: function() {
            $loader.show();
            $widget.hide();
        },
        success: function (data) {
            if (isJson(data)) {

                var json    = $.parseJSON(data);
                var content = '';

                if (json['status'] === 'success') {
                    content = json['data'];
                } else {
                    content = '<div class="callout callout-danger" style="margin: 10px;"><p>' + json['data'] + '</p></div>'
                }

                $loader.hide();
                $widget.html(content).show();
            }
        },
        error: function (data) {
            $widget.html('<div class="callout callout-danger" style="margin: 10px">' + data.responseText + '' + '</div>').show();
            $loader.hide();
        }
    });

};

/**
 * Insert text at cursor position
 *
 * @param {string} value
 */
$.fn.extend({
    insertAtCursor: function(value){
        return this.each(function() {
            if (document.selection) {
                //For browsers like Internet Explorer
                this.focus();
                var sel = document.selection.createRange();
                sel.text = value;
                this.focus();
            }
            else if (this.selectionStart || this.selectionStart === '0') {
                //For browsers like Firefox and Webkit based
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos)+value+this.value.substring(endPos,this.value.length);
                this.focus();
                this.selectionStart = startPos + value.length;
                this.selectionEnd = startPos + value.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += value;
                this.focus();
            }
        })
    }
});
