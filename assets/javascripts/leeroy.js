$(document).ready(function () {
    // jQuery('input[type=file]').bind('change', STUDIP.Leeroy.addFile);
    $(function () {
        $('#fileupload').fileupload({
            dataType: 'json',
            add: function (e, data) {
                if (STUDIP.Leeroy.max_file <= 0) {
                    return;
                }

                STUDIP.Leeroy.up();

                STUDIP.Leeroy.file_id += 1;
                data.id = STUDIP.Leeroy.file_id;
                STUDIP.Leeroy.addFile(e, data);
            },

            done: function (e, data) {
                var files = data.result;

                if (typeof files.errors === "object") {
                    var errorTemplateData = {
                        message: json.errors.join("\n")
                    }
                    $('#files_to_upload').before(STUDIP.Leeroy.errorTemplate(errorTemplateData));
                } else {
                    _.each(files, function (file) {
                        var id = $('#files_to_upload tr:first-child').attr('data-fileid');
                        $('#files_to_upload tr[data-fileid=' + id + ']').remove();

                        var templateData = {
                            id: file.id,
                            url: file.url,
                            name: file.name,
                            size: Math.round((file.size / 1024) * 100) / 100,
                            date: file.date,
                            seminar: file.seminar_id
                        }

                        $('#uploaded_files').append(STUDIP.Leeroy.uploadedFileTemplate(templateData));
                    });
                    if (STUDIP.Leeroy.openAnalytic) {
                        window.open(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/leeroy/index/show_analytics/" + STUDIP.Leeroy.handin + "/true", "analytic");
                    }
                }
            },

            progress: function (e, data) {
                var kbs = parseInt(data._progress.bitrate / 8 / 1024);
                var progress = parseInt(data.loaded / data.total * 100, 10);
                var id = $('#files_to_upload tr:first-child').attr('data-fileid');
                $('#files_to_upload tr[data-fileid=' + id + '] progress').val(progress);
                $('#files_to_upload tr[data-fileid=' + id + '] .kbs').html(kbs);
            },

            error: function (xhr, data) {
                var id = $('#files_to_upload tr:first-child').attr('data-fileid');
                $('#files_to_upload tr[data-fileid=' + id + '] td:nth-child(3)')
                    .html('Fehler beim Upload (' + xhr.status + ': ' + xhr.statusText + ')');
                $('#files_to_upload tr[data-fileid=' + id + '] td:nth-child(4)').html('');
                $('#files_to_upload tr[data-fileid=' + id + '] td:nth-child(5)').html('');
                $('#files_to_upload tr[data-fileid=' + id + '] td:nth-child(6)').html('');

                $('#files_to_upload').append($('#files_to_upload tr[data-fileid=' + id + ']').remove());
                STUDIP.Leeroy.down();
            }
        });
    });

    // load templates
    STUDIP.Leeroy.fileTemplate = _.template($("script.file_template").html());
    STUDIP.Leeroy.uploadedFileTemplate = _.template($("script.uploaded_file_template").html());
    STUDIP.Leeroy.errorTemplate = _.template($("script.error_template").html());
    STUDIP.Leeroy.questionTemplate = _.template($("script.confirm_dialog").html());
});

STUDIP.Leeroy = {
    files: {},
    maxFilesize: 0,
    fileTemplate: null,
    uploadedFileTemplate: null,
    errorTemplate: null,
    questionTemplate: null,
    file_id: 0,
    remove_url: null,
    maxFiles: 2000,
    openAnalytic: false,
    handin: -1,

    up: function () {
        STUDIP.Leeroy.maxFiles--;

        if (STUDIP.Leeroy.maxFiles <= 0) {
            $('#add_button').addClass('disabled');
            $('#fileupload').prop("disabled", true);
        }
    },

    down: function () {
        STUDIP.Leeroy.maxFiles++;

        if (STUDIP.Leeroy.maxFiles > 0) {
            $('#add_button').removeClass('disabled');
            $('#fileupload').prop("disabled", false);
        }
    },

    addFile: function (e, data) {
        // this is the first file for the current upload-list

        if (STUDIP.Leeroy.file_id == 1) {
            $('#files_to_upload').html('');
        }

        $('#upload_button').removeClass('disabled');

        var file = data.files[0];
        STUDIP.Leeroy.files[data.id] = data;

        var templateData = {
            id: data.id,
            name: file.name,
            error: file.size > STUDIP.Leeroy.maxFilesize,
            size: Math.round((file.size / 1024) * 100) / 100
        }

        $('#files_to_upload').append(STUDIP.Leeroy.fileTemplate(templateData));

        if (file.type == 'image/png'
            || file.type == 'image/jpg'
            || file.type == 'image/gif'
            || file.type == 'image/jpeg') {

            var img = new Image();

            var reader = new FileReader();

            reader.onload = function (e) {
                img.src = e.target.result;
            }

            reader.readAsDataURL(file);

            $('#files_to_upload tr:last-child td:first-child').append(img);
        }
    },

    removeUploadFile: function (id) {
        var files = STUDIP.Leeroy.files[id];
        delete STUDIP.Leeroy.files[id];
        STUDIP.Leeroy.max_file++;

        _.each(files, function (file) {
            if (file.jqXHR) {
                file.jqXHR.abort();
            }
        });

        $('#files_to_upload tr[data-fileid=' + id + ']').remove();

        STUDIP.Leeroy.down();
    },

    removeFile: function (seminar_id, id) {
        $.ajax(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/leeroy/" + STUDIP.Leeroy.remove_url + "_remove/" + id + "?cid=" + seminar_id, {
            type: "post",
            error: function (xhr) {
                var json = $.parseJSON(xhr.responseText);
            }
        }).done(function () {
            STUDIP.Leeroy.down();
            $('#uploaded_files tr[data-fileid=' + id + ']').remove();
        });
    },

    upload: function () {
        // do nothing if upload has been disabled
        if ($('upload_button').hasClass('disabled')) {
            return;
        }

        // set upload as disabled
        $('#upload_button').addClass('disabled');

        // upload each file separately to allow max filesize for each file
        _.each(STUDIP.Leeroy.files, function (data) {
            if (data.files[0].size > 0 && data.files[0].size <= STUDIP.Leeroy.maxFilesize) {
                data.submit();
            }
        });

        STUDIP.Leeroy.files = {};
        STUDIP.Leeroy.file_id = 0;
    },

    createQuestion: function (question, link) {
        var questionTemplateData = {
            question: question,
            confirm: link
        }

        $('#Leeroy').append(STUDIP.Leeroy.questionTemplate(questionTemplateData));
    },

    closeQuestion: function () {
        $('#Leeroy .modaloverlay').remove();
    }
};
