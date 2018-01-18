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

/** Set datetimepicker language */
$.extend(true, $.fn.datetimepicker.defaults, {locale: document.documentElement.lang});

/** Load inline script on document ready and after pjax:end */
$(document).on('ready pjax:end', function() {

    /** Open full log message */
    $('a.grid-toggle').click(function(e) {

        e.preventDefault();

        var $msg_id = '#msg_' + this.id.split('_')[1];
        var $target = $($msg_id).find('.full-text');
        var $dots   = $($msg_id).find('.preview .dots');

        if($target.is(':visible')) {
            $(this).children('i').switchClass('fa-caret-square-o-up', 'fa-caret-square-o-down', 0);
            $target.removeClass('expanded');
            $dots.show();
        } else {
            $(this).children('i').switchClass('fa-caret-square-o-down', 'fa-caret-square-o-up', 0);
            $target.addClass('expanded');
            $dots.hide();
        }

        $target.slideToggle();

    });

    /** Enable/Disable expand link on document ready and pjax:end */
    checkExpandLink();

});

/** Enable/Disable expand link on window resize */
$(window).resize(function() {
    checkExpandLink();
});

/** Show search form on button click */
$('.search-button').click(function() {
    $('.scheduler-log-search, .system-log-search, .node-log-search, .mailer-log-search').slideToggle('slow');
    return false;
});

/** Scheduler log search form submit and reload gridview */
$('.scheduler-log-search-form form').submit(function(e) {
    e.stopImmediatePropagation(); // Prevent double submit
    gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
    $.pjax.reload({container:'#scheduler-log-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
    return false;
});

/** System log search form submit and reload gridview */
$('.system-log-search-form form').submit(function(e) {
    e.stopImmediatePropagation(); // Prevent double submit
    gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
    $.pjax.reload({container:'#system-log-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
    return false;
});

/** Node log search form submit and reload gridview */
$('.node-log-search-form form').submit(function(e) {
    e.stopImmediatePropagation(); // Prevent double submit
    gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
    $.pjax.reload({container:'#node-log-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
    return false;
});

/** Mailer log search form submit and reload gridview */
$('.mailer-log-search-form form').submit(function(e) {
    e.stopImmediatePropagation(); // Prevent double submit
    gridLaddaSpinner('spin_btn'); // Show button spinner while search in progress
    $.pjax.reload({container:'#mailer-log-pjax', url: window.location.pathname + '?' + $(this).serialize(), timeout: 10000}); // Reload GridView
    return false;
});

/** Init scheduler from datetimepicker */
$('#from_date').datetimepicker({
    format: 'YYYY-MM-DD HH:mm',
    useCurrent: false,
    showClose: true,
    ignoreReadonly: true
}).on('dp.show', function() {

    var $to   = $('#to_date').val();
    var $date = moment().startOf('day');

    if ($to.length > 0) {
        $date = moment($to).format('YYYY-MM-DD') + '00:00'
    }

    $(this).data('DateTimePicker').defaultDate($date);

}).on('dp.hide', function(e){
    if (e.target.value.length > 0) {
        $('#to_date').data('DateTimePicker').minDate(e.target.value);
    }
});

/** Init scheduler to datetimepicker */
$('#to_date').datetimepicker({
    format: 'YYYY-MM-DD HH:mm',
    useCurrent: false,
    showClose: true,
    ignoreReadonly: true
}).on('dp.show', function() {

    var $from = $('#from_date').val();
    var $date = moment().endOf('day');

    if ($from.length > 0) {
        $date = moment($from).format('YYYY-MM-DD') + '23:59';
    }

    $(this).data('DateTimePicker').defaultDate($date);

}).on('dp.hide', function(e){
    if (e.target.value.length > 0) {
        var $date = moment(e.target.value).add(1, 'days');
        $('#from_date').data('DateTimePicker').maxDate($date).disabledDates([$date]);
    }
});


/** Init system from datetimepicker */
$('#systemFrom_date').datetimepicker({
    format: 'YYYY-MM-DD',
    useCurrent: false,
    ignoreReadonly: true
}).on('dp.hide', function(e){
    if (e.target.value.length > 0) {
        $('#systemTo_date').data('DateTimePicker').minDate(e.target.value);
    }
});

/** Init system to datetimepicker */
$('#systemTo_date').datetimepicker({
    format: 'YYYY-MM-DD',
    useCurrent: false,
    ignoreReadonly: true
}).on('dp.hide', function(e){
    if (e.target.value.length > 0) {
        var $date = moment(e.target.value).add(1, 'days');
        $('#systemFrom_date').data('DateTimePicker').maxDate($date).disabledDates([$date]);
    }
});

/** Clear selected date on button click */
$('.date-clear').click(function() {
    var $dp_id = '#' + $(this)[0].id.split('_')[0] + '_date';
    $($dp_id).data('DateTimePicker').date(null).maxDate(false).minDate(false).disabledDates(false);
});

/** Enable/Disable expand link based ellipsed text */
var checkExpandLink = function () {

    var $overflow_cells = $('.grid-view table.log-table tr td.hide-overflow > div');

    $.each($overflow_cells, function (_, e) {

        var $expand_link = $(e).closest('tr').find('a.gridExpand');

        /** Remove disabled class */
        $expand_link.removeClass('disabled');

        /** Disable expand button if text is not ellipsed */
        if (e.offsetWidth === e.scrollWidth) {
            $expand_link.addClass('disabled');
        }

    });

};
