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
/*
 * Click event callback for the rest of the checkboxes.
 * When the item is unchecked, the check all checkbox has to be also unchecked.
 * */
function uncheckCheckAllCheckbox(event) {
	if (event.target.checked) {
		return;
	}
	document.querySelector('#check-all-checkbox-item').checked = false;
}
/*
 * Attach the event handlers to the checkboxes.
 * */
// onload
(function() {
	let items = document.querySelectorAll('input[type="checkbox"]');
	for (i = 1; i < items.length; i++) {
		items[i].addEventListener('click', uncheckCheckAllCheckbox);
	}
})();
