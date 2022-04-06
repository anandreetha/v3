/**
 * Use a button to trigger form submit
 * inside child iFrame
 * ( This will only work for content loaded in the iframe
 * from the same domain )
 * @param iframe String div id
 * @param modalFormName String|undefined form name inside the modal
 * @throws Error
 */
window.submitModal = function (iframe, modalFormName, optFunctionCall) {

    if (iframe == "newWebsiteIframe") {
        $('#modalLoadingBackground').fadeIn(function () {
            $('#modalLoadingSpinner').fadeIn();
            $('#modal-spinner').fadeIn();
        });
    }

    var iframeElement = document.getElementById(iframe).contentWindow.document;

    // We should always have a form inside the modal if we're executing this function
    // Lets support forms without names so we'll just look for the tag <form>
    if (typeof modalFormName === 'undefined') {
        var iframeArray = iframeElement.getElementsByTagName('form');

        if (iframeArray.length > 1) {
            throw new Error('We\'re only expecting 1 form per iframe')
        }

        if (typeof optFunctionCall !== "undefined") {
            return optFunctionCall();
        }
        return iframeArray[0].submit();
    }

    // Look for a form by div ID instead
    iframeElement.getElementById(modalFormName).submit();
};