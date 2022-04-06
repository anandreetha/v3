var totalImagesUploaded = 0;
var message;

function updateRemainingFilesToUpload(page, numberOfImagesRequiredToUpload) {
    $(page).find("#activeConnections").show();
    $(page).find("#activeConnections").text(multiUploadMessage + " " + (totalImagesUploaded) + "/" + numberOfImagesRequiredToUpload);
    $(page).find("#active").text($.active);
}

function ajaxCall(uploadUrl, data, numberOfImagesRequiredToUpload, page) {
    $.ajax({
        type: 'POST',
        url: uploadUrl,
        data: data,
        enctype: 'multipart/form-data',
        processData: false,  // tell jQuery not to process the data
        contentType: false,  // tell jQuery not to set contentType
        success: function (data) {
            message = $(data).find("#alertBox");
            isNotSuccessful = message.hasClass("alert alert-danger");
            errorList = $(page).find("#errorMessagelist");
            successList = $(page).find("#successMessagelist");
            oldMessage = $(page).find("#alertBox");

            if(!isNotSuccessful){
                totalImagesUploaded++;
                $(successList).append("<li>" + message.text() + "</li>");
                $(successList).show();
            } else {
                $(errorList).append("<li>" + message.text() + "</li>" );
            }
            if (page !== 'undefined') {
                updateRemainingFilesToUpload(page, numberOfImagesRequiredToUpload);

            }
            // If all files have been uploaded then refresh the page, active connections should be one
            if ($.active === 1) {

                if(errorList.children().length > 0){
                    oldMessage.hide();
                    $(errorList).show();
                } else {
                    $(oldMessage).replaceWith(message);
                }
                setTimeout(function() {
                    location.reload();
                }, 3000);
            }
        }
    });

    updateRemainingFilesToUpload(page, numberOfImagesRequiredToUpload);
}
