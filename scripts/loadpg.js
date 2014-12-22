
/* GLOBALS */

// Counter of how many rows generated (in total)
var loadMaxCnt = 0;
var loadStock = new Object();

// onchange event handler for products
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
    //var inputs = Array.concat(row.getElementsByTagName('input').concat(row.getElementsByTagName('select'));
    var inputs = row.getElementsByTagName('input')
    for(var i=0; i<inputs.length; i++) {
        inputs[i].name = inputs[i].name.replace('%i', loadMaxString);
        if (inputs[i].type != 'checkbox') { // Require only if visible
            inputs[i].required = true;
        }
    }

    // FIXME: I can't believe there's no way to do this...
    var selects = row.getElementsByTagName('select');
    for(var i=0; i<selects.length; i++) {
        selects[i].name = selects[i].name.replace('%i', loadMaxString);
    }

    // Set select correctly
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

// Show the custom load table for a row
function addLoadCustom(row)
{
    var tbl = row.getElementsByTagName('table')[0];
    tbl.rows[0].cells[1].children[0].value = ''; // FIXME: Dep. on HTML
    tbl.style.display = 'table-row';
}

// Hide the custom load table for a row
function delLoadCustom(row)
{
    var tbl = row.getElementsByTagName('table')[0];
    var name = tbl.rows[0].cells[1].children[0]; // FIXME: Dep. on HTML
    if (name.value == '') { // Set the name to a non-empty value to match required attribute
        name.value = ' ';
    }
    tbl.style.display = 'none';
}

// Check if the stock covers the requested number of pieces
function checkLoadStock(row)
{
    // FIXME: Traversing object tree still seems very ugly to me.
    var selector = row.children[0].children[0];
    var sold   = row.children[4].children[0].checked;
    var amount = parseInt(row.children[1].children[0].value);
    var stock = 0;
    
    if (selector.value == 'custom') {
        stock = parseInt(row.children[0].children[1].children[0].children[5].children[1].children[0].value); // Yayyyyy, too much fun here :)
    } else {
        stock = parseInt(loadStock[selector.value]);
    }

    var wpar = row.children[4];
    if (sold && stock < amount) {
        // Show warning unless present
        if (wpar.children.length == 1) {
            var warn = document.getElementById('stockWarning').cloneNode(true);
            warn.removeAttribute('id');
            wpar.appendChild(warn);
            warn.style.display='inline';
        }
    } else {
        // Remove warning if present
        if (wpar.children.length > 1) {
            wpar.removeChild(wpar.children[1]); // FIXME: Ugly but works
        }
    }
}

// EOF //
