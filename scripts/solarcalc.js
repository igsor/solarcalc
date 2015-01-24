
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

function toggleConfigOverview(tableHead, tblShortId, tblLongId) {
    var tblShort = document.getElementById(tblShortId);
    var tblLong = document.getElementById(tblLongId);

    // Toggle tables.
    toggleVisibility(tblShort, 'table-row', true);
    toggleVisibility(tblLong, 'table-row', false);

    // Toggle header class.
    if (tableHead.classList.contains('selected')) {
        tableHead.classList.remove('selected');
        tblShort.parentNode.classList.remove('selected')

    } else {
        tableHead.classList.add('selected');
        tblShort.parentNode.classList.add('selected')
    }
}

function confirmDelete() {
    if (window.confirm('Please confirm that you absolutely want to delete this item.')) {
        document.getElementById('deleteForm').submit();
    } else {
    }
}


