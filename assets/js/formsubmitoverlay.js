function addOnClickEvent () {
    let items = document.getElementsByClassName('crm-form-submit');
    for (let i = 0; i < items.length; i++) {
        items[i].addEventListener('click', addOverLay);
    }
}

function addOverLay() {
    const contentWrapper = document.getElementById('crm-main-content-wrapper');
    contentWrapper.innerHTML +=
        '<div id="overlay">' +
        '    <div id="loader"></div>' +
        '</div>';
    // After js added the overlay with the spinner, we submit the form
    contentWrapper.firstElementChild.submit();
}

addOnClickEvent();
