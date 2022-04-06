/**
 * We want to close the modals when the user clicks save and if there are no errors
 * The issue we have is that the content such as forms displayed inside the modal are iframes
 * which means we don't have scope from the modal that the save button has been clicked.
 * So we need to use post message to sent data from the modal content to the modal body.
 */
window.addEventListener('message', function(event){

    // If we don't have the expected data name then just bail
    if (event.data.type !== 'bni-modal-close') return;

    // Not really needed anymore but we'll hold onto it in case we need to do some specific modal stuff
    if (typeof event.data.parentModal === 'undefined') return;

    location.reload();

}, false);