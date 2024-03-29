/**
 * Show overlay
 */
function showOverlay() {
    const overlay = document.getElementById('overlay');
    let inputs = document.getElementsByTagName('input');

    let hasEmptyRequiredInput = false;

    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].classList.contains('required') && (inputs[i].value === '' || inputs[i].value === null)) {
            hasEmptyRequiredInput = true;
            break;
        }
    }

    if (!hasEmptyRequiredInput) {
        overlay.style.display = 'block';
    }
}

/**
 * Add overlay and attach event handlers
 */
(function () {
    let submitButtonsDivs = document.getElementsByClassName('crm-submit-buttons');
    let overlays = document.getElementById('overlay');

    if (overlays) {
        return;
    }

    // If the DOM contains the crm-submit-buttons div, we append the hidden overlay html.
    if (submitButtonsDivs.length > 0) {
        const contentWrapper = document.getElementsByTagName('body');
        let loader = document.createElement('div');
        let overlay = document.createElement('div');

        overlay.id = 'overlay';
        loader.id = 'loader';
        overlay.style.display = 'none';
        overlay.append(loader);
        contentWrapper[0].append(overlay);
    }

    // Normally there is only one of these divs in a view, but it could happen there is more forms in a single page.
    for (let i = 0; i < submitButtonsDivs.length; i++) {
        let submitButtons = submitButtonsDivs[i].getElementsByClassName('crm-form-submit');
        for (let j = 0; j < submitButtons.length; j++) {
            submitButtons[j].addEventListener('click', showOverlay);
        }
    }
})();
