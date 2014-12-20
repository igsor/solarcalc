
// GLOBAL
var loadMaxCnt = 0;

function changeLoadProduct(obj, row)
{
    if (obj.options[obj.selectedIndex].value == "remove") {
        delLoadProduct(row);
    } else if (obj.options[obj.selectedIndex].value == "custom") {
        addLoadCustom(row);
    } else {
        delLoadCustom(row);
    }
}

// Add a row for a new product
function addLoadProduct(obj)
{
    // Guard against invalid selection
    if (obj.selectedIndex == 0) {
        return;
    }

    // Create the new element
    var tbody = document.getElementById('products').children[0];
    var row = document.getElementById('tpl_product').cloneNode(true);
    var loadMaxString = loadMaxCnt.toString();

    // Set ids, names, values and alike
    row.setAttribute('id', 'product-' + loadMaxString);
    //var inputs = Array.apply(row.getElementsByTagName('input'), row.getElementsByTagName('select'));
    var inputs = row.getElementsByTagName('input')
    for(var i=0; i<inputs.length; i++) {
        inputs[i].name = inputs[i].name.replace('%i', loadMaxString);
        if (inputs[i].type != 'checkbox') {
            inputs[i].required = true;
        }
    }

    // FIXME: I can't believe there's no way to do this...
    var selects = row.getElementsByTagName('select');
    for(var i=0; i<selects.length; i++) {
        selects[i].name = selects[i].name.replace('%i', loadMaxString);
    }

    var rsel = row.getElementsByTagName('select')[0] // FIXME: Also very ugly
    rsel.selectedIndex = obj.selectedIndex;
    obj.selectedIndex = 0;

    // Show the new row
    tbody.insertBefore(row, tbody.children[tbody.children.length - 1]);
    row.style.display = 'table-row';

    // Add the custom form, if selected
    if (rsel.options[rsel.selectedIndex].value == 'custom') {
        addLoadCustom(row);
    }

    loadMaxCnt++; // Increment GLOBAL counter
}

// Remove a product row
function delLoadProduct(obj)
{
    obj.parentNode.removeChild(obj);
}

// Show the custom load table
function addLoadCustom(row)
{
    var tbl = row.getElementsByTagName('table')[0];
    tbl.rows[0].cells[1].children[0].value = ''; // FIXME: Dep. on HTML
    tbl.style.display = 'table-row';
}

// Hide the custom load table
function delLoadCustom(row)
{
    var tbl = row.getElementsByTagName('table')[0];
    var name = tbl.rows[0].cells[1].children[0]; // FIXME: Dep. on HTML
    if (name.value == '') {
        name.value = ' ';
    }
    tbl.style.display = 'none';
}

// EOF //
