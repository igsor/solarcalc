
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

function updateBudget(form_input, target) {
    var subtotal = document.getElementById(target);
    var total = document.getElementById('budget_total');

    var old_total    = parseInt(total.innerHTML);
    var old_subtotal = parseInt(subtotal.innerHTML);
    var new_subtotal = parseInt(form_input.value);
    if (new_subtotal && new_subtotal > 0) {

        // Update budget.
        total.innerHTML  = old_total - old_subtotal + new_subtotal;

        // Budget item.
        subtotal.innerHTML = subtotal.previousElementSibling.innerHTML = new_subtotal;
    }
}

function displayHelp(text) {
    
    var helptext = document.getElementById('helptext');
    
    if (text == '') {
        stat = 'hidden';
    } else {
        stat = 'visible';
    }
        
    helptext.style.visibility = stat;
    helptext.innerHTML = text;
   
}
