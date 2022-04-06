/**
 * Close a specific modal
 * @param parentModalName
 */
window.closeModal = function(parentModalName) {

    var count = 0;

    var successCheck = setInterval(function(){

        count++;

        if (document.getElementsByClassName('alert-success').length > 0) {
            clearInterval(successCheck);
            return parent.postMessage({
                'type': 'bni-modal-close',
                'parentModal': parentModalName
                },
                // Redirect firefox instead of reload so it doesn't attempt to resubmit form
                navigator.userAgent.search("Firefox") !== -1 ?
                    parent.window.location.replace(parent.window.location.href) :
                    window.location.origin
            )
        }

        // Only check for success 10 times
        if (count === 10) {
            clearInterval(successCheck);
        }

    }, 1000);

};