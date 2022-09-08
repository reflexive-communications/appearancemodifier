function addOnClickEvent() {
    let submitButtonsDivs = document.getElementsByClassName('crm-submit-buttons');

    // If the DOM contains the crm-submit-buttons div, we append the hidden overlay html.
    if (submitButtonsDivs.length > 0) {
        const contentWrapper = document.getElementsByTagName('body');
        contentWrapper[0].innerHTML +=
            '<div id="overlay" style="display: none">' +
            '    <div id="loader"></div>' +
            '</div>';
    }

    // Normally there is only one of these divs in a view, but it could happen there is more forms in a single page.
    for (let i = 0; i < submitButtonsDivs.length; i++) {
        let submitButtons = submitButtonsDivs[i].getElementsByClassName('crm-form-submit');
        for (let j = 0; j < submitButtons.length; j++) {
            submitButtons[i].addEventListener('click', showOverlay);
        }
    }
}

function showOverlay() {
    const overlay = document.getElementById('overlay');
    overlay.style.display = "block";
}

addOnClickEvent();
