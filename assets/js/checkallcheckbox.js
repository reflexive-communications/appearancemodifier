/*
 * This application provides the onclick handler of the check all checkboxes on the form feature.
 */
// This function sets the checked properties of the checkboxes on the form
function checkAllCheckboxClickHandler(item) {
    let items = document.querySelectorAll('input[type="checkbox"]');
    for (i = 1; i < items.length; i++) {
        items[i].checked = item.checked;
    }
}
