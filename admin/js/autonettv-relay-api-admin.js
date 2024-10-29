
function autonettv_checkbox_toggle(source) {
    // Get the form element
    const form = document.getElementById('category_selections');

    // Get all checkbox input elements within the form
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');

    // Loop through checkboxes and mark them as selected
    for (let checkbox of checkboxes) {
        checkbox.checked = true;
    }

}