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
            $('[href="' + active + '"]').tab('show');
        }

    }

});

/**
 * @param file
 * @constructor
 */
var Upload = function (file) {
    this.file = file;
};

/**
 * Upload plugin
 */
Upload.prototype.doUpload = function () {

    //noinspection JSUnresolvedVariable
    var $url       = install_url['install_plugin']; // Url are registred in main index view
    var formData   = new FormData();
    var input_name = $("#file_input")[0].name;
    var $btn_lock  = Ladda.create(document.querySelector('#install_btn'));

    formData.append(input_name, this.file);

    $.ajax({
        type: "POST",
        url: $url,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000,
        beforeSend: function () {
            $btn_lock.start();
        },
        success: function (data) {
            if (isJson(data)) {
                var $json = $.parseJSON(data);
                if ($json['status'] === 'success') {
                    $('button.preview-clear').trigger('click');
                    $.pjax.reload({container: '#plugin-grid-pjax', url: $(location).attr('href'), timeout: 10000});
                }
                showStatus(data);
            } else {
                toastr.warning(data, '', {toastClass: 'no-shadow', timeOut: 0, closeButton: true});
            }
        },
        error: function (data) {
            toastr.error(data.responseText, '', {timeOut: 0, closeButton: true});
        }
    }).always(function () {
        $btn_lock.stop();
    });
};

/** Install plugin */
$(document).on('click', "#install_btn", function() {
    var file   = $("#file_input")[0].files[0];
    var upload = new Upload(file);
    upload.doUpload();
});

/** Clear file input field */
$(document).on('click', '.preview-clear', function(){
    $('.preview-filename').val('');
    $('.preview-clear').hide();
    $('.preview-input input:file').val('');
    $(".preview-input-title").text(i18next.t('Browse'));
});

/** Preview selected file */
$(document).on('change', ".preview-input input:file", function (){
    var file   = this.files[0];
    var reader = new FileReader();
    // Set preview image into the popover data-content
    reader.onload = function () {
        $(".preview-input-title").text(i18next.t('Change'));
        $(".preview-clear").show();
        $(".preview-filename").val(file.name);
    };
    reader.readAsDataURL(file);
});
