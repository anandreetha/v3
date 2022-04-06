function createDragAndDrop(uploadUrl) {
    var holder = document.getElementById('holder'),
        tests = {
            filereader: typeof FileReader != 'undefined',
            dnd: 'draggable' in document.createElement('span'),
            formdata: !!window.FormData,
            progress: "upload" in new XMLHttpRequest
        },
        support = {
            filereader: document.getElementById('filereader'),
            formdata: document.getElementById('formdata'),
            progress: document.getElementById('progress')
        },
        fileupload = document.getElementById('upload');

    function readFiles(files) {
        var formData;
        var numberOfImagesRequiredToUpload = files.length;
        // Loop around all files to upload and create a ajax request for each file
        var pageData = $("#alertArea");
        var clientAlert = $("#clientSideAlert");
        var translatedWarning = clientAlert.attr("data-file-size-warning");
        var translatedTypeWarning = clientAlert.attr("data-file-type-warning");

        // Remove previous error message
        $(clientAlert).hide();
        $(clientAlert).empty();

        for (var i = 0; i < files.length; i++) {
            formData = new FormData;
            // Don't attempt to upload invalid file sizes and decrease the total number of images required
            if (files[i].size >= 10485760) {
                $("#clientSideAlert").show(150);
                $(clientAlert).append("<li>" + files[i].name + ' ' + translatedWarning + "</li>");

            } else if (!isValidFileType(files[i].type)) {
                $("#clientSideAlert").show(150);
                $(clientAlert).append("<li>" + files[i].name + ' ' + translatedTypeWarning + "</li>");
            } else {
                if (tests.formdata) formData.append('file[]', files[i]);
                uploadUrl += "/" + type;
                ajaxCall(uploadUrl, formData, numberOfImagesRequiredToUpload, pageData);
            }
        }
    }

    if (tests.dnd) {
        holder.ondragover = function () {
            $("#holder").addClass('hover');
            return false;
        };
        holder.ondrop = function (e) {
            $("#holder").removeClass('hover');
            e.preventDefault();
            readFiles(e.dataTransfer.files);
        }
    } else {
        fileupload.className = 'hidden';
        fileupload.querySelector('input').onchange = function () {
            readFiles(this.files);
        };
    }
}

var type = "images";

function allowedFileTypes(mediaType) {
    type = mediaType;
}

function isValidFileType(filetype) {
    var ext = getExtension(filetype);
    console.log(filetype);
    // Checks the allowed file types for images and for documents
    if (type === "images") {
        switch (ext.toLowerCase()) {
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'png':
                return true;
        }
    } else {
        // Switch for easy expansion later if javascript files are requested at a later date
        switch (ext.toLowerCase()) {
            case 'pdf':
                return true;
        }
    }
    return false;
}

function getExtension(filetype) {
    var parts = filetype.split('/');
    return parts[parts.length - 1];
}