
function toggleVisibility(obj, visibilityMode, defaultVisible)
{
    visibilityMode = visibilityMode || 'inline'; // display style, if visible.
    defaultVisible = defaultVisible || false;    // first-click behaviour. true iff visible on page load.

    if (obj.style.display == "") { // first click
        if (defaultVisible) {
            obj.style.display = "none";
        } else {
            obj.style.display = visibilityMode;
        }
    } else if (obj.style.display == "none" ) {
		obj.style.display = visibilityMode;
	} else {
		obj.style.display = "none";	
	}
}

function toggleConfigOverview(tableHead, table1, table2) {
    // Toggle tables.
    toggleVisibility(document.getElementById(table1), 'table-row', true);
    toggleVisibility(document.getElementById(table2), 'table-row', false);

    // Toggle header class.
    if (tableHead.classList.contains('configurationSelected')) {
        tableHead.classList.remove('configurationSelected');
    } else {
        tableHead.classList.add('configurationSelected');
    }
}

function confirmDelete() {
    if (window.confirm('Please confirm that you absolutely want to delete this item.')) {
        document.getElementById('deleteForm').submit();
    } else {
    }
}


