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

/** Default variables */
var $adv_search = $('.search-button');

/** Set datetimepicker language */
$.extend(true, $.fn.datetimepicker.defaults, {locale: document.documentElement.lang});

/** Load grid view via Ajax on button click */
$(document).on('click', '.load-grid-view', function () {

    var $this           = $(this);
    var $loading        = $('.loading');
    var $adv_search_url = $adv_search.data('ajax-url');
    var $url            = $this.data('ajax-url');

    $.ajax({
        type: 'POST',
        url: $url,
        beforeSend: function() {
            $('#out-table-pjax').hide();
            $('#out_custom_search').html('').hide();
            $loading.show();
        },
        success: function (data) {
            $('#out-table-pjax').html(data).show();
            $('#out_table_select').find('li').removeClass('active');
            $('#' + $this.context.id).parent().addClass('active');
            $adv_search.data('ajax-url', $adv_search_url + '&table=' + $this.context.id).show();
            $loading.hide();
        },
        error: function (data) {
            $('#out-table-pjax').html('' +
                ' <div class="callout callout-danger" style="margin: 10px">' +
                    data.responseText + '' +
                '</div>'
            ).show();
            $loading.hide();
        }
    });

});

/** Load search form via Ajax on button click */
$adv_search.click(function() {

    var ajax_url    = $(this).data('ajax-url');
    var $out_search = $('#out_custom_search');

    if ($out_search.html() === '') {
        var btn_lock = Ladda.create(document.querySelector('.search-button'));
        $.ajax({
            type: 'POST',
            url: ajax_url,
            beforeSend: function () {
                btn_lock.start();
            },
            success: function (data) {
                $out_search.html(data).slideDown('slow');
                initSearchFormScripts();
            },
            error: function (data) {
                toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
            }
        }).always(function () {
            btn_lock.stop();
        });
    } else {
        $out_search.slideToggle('slow');
    }

});

/** Out table filter search form submit and grid view reload */
$(document).on('submit', '.out-custom-search-form form', function(e) {
    e.stopImmediatePropagation(); // Prevent double submit
    gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
    $.pjax.reload({container:'#out-table-pjax', url: window.location.pathname + '?' + $(this).serialize(), replace: false, timeout: 10000}); // Reload GridView
    return false;
});

/** Init search form scripts on form load */
var initSearchFormScripts = function() {

    /** Init select2 */
    $('.select2').select2({
        width: '100%',
        minimumResultsForSearch: -1
    });

    /** Init out table from datetimepicker */
    $('#outFrom_date').datetimepicker({
        format: 'YYYY-MM-DD',
        useCurrent: false,
        ignoreReadonly: true
    }).on('dp.hide', function(e){
        if (e.target.value.length > 0) {
            $('#outTo_date').data('DateTimePicker').minDate(e.target.value);
        }
    });

    /** Init out table to datetimepicker */
    $('#outTo_date').datetimepicker({
        format: 'YYYY-MM-DD',
        useCurrent: false,
        ignoreReadonly: true
    }).on('dp.hide', function(e){
        if (e.target.value.length > 0) {
            var date = moment(e.target.value).add(1, 'days');
            $('#outFrom_date').data('DateTimePicker').maxDate(date).disabledDates([date]);
        }
    });

    /** Clear selected date on button click */
    $('.date-clear').click(function() {
        var dp_id = '#' + $(this)[0].id.split('_')[0] + '_date';
        $(dp_id).data('DateTimePicker').date(null).maxDate(false).minDate(false).disabledDates(false);
    });

};
