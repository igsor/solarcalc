
/************************** GLOBALS **************************/

// Counter of how many rows generated (in total)
var loadMaxCounter = 0;
var loadStock = new Object();

/************************** HELPERS **************************/

// Helper function to get all form elements of a node
function getFormElements(parent) {
    return [].slice.call(parent.getElementsByTagName('input'))
           .concat([].slice.call(parent.getElementsByTagName('select')))
           .concat([].slice.call(parent.getElementsByTagName('textarea')))
           ;
}

// Remove a product row.
function delLoadProduct(idx)
{
    var row = document.getElementById('product-' + idx);
    row.parentNode.removeChild(row);
}

// Show the custom load table for a row
function addLoadCustom(idx)
{
    // Create the node.
    var custom = document.getElementById('tpl_custom').cloneNode(true);

    // Set ids and names.
    custom.id = 'custom-' + idx;
    var inputs = getFormElements(custom);
    for(var i=0; i<inputs.length; i++) { // FIXME: Could be replaced by for..each or for..of but need to check which is present
        inputs[i].name = inputs[i].name.replace('%i', idx);
        inputs[i].id   = inputs[i].id.replace('%i', idx);
    }

    // Show the table.
    document.getElementById('product-' + idx).cells[0].appendChild(custom);
    custom.style.display = 'table-row';

    // Check stock.
    checkLoadStock(custom);
}

// Hide the custom load table for a row
function delLoadCustom(idx)
{
    var custom = document.getElementById('custom-' + idx);
    if (custom != null) {
        custom.parentNode.removeChild(custom);
    }
}

/************************** EVENT HANDLERS **************************/

// Entrypoint for products' onChange event.
function changeLoadProduct(evt)
{
    // Retrieve the globally unique row index from the id.
    var idx = this.id.match(/\d+/)[0];
    //var idx = evt.target.id.match(/\d+/)[0]; // Alternatively, grab it from the event.

    // Decide on and execute the action.
    if (this.options[this.selectedIndex].value == "remove") {
        delLoadProduct(idx);
    } else if (this.options[this.selectedIndex].value == "custom") {
        addLoadCustom(idx);
    } else {
        delLoadCustom(idx);
    }
}

// Add a row for a new product. Called upon the adder's onChange event.
function addLoadProduct()
{
    // Get the select node.
    var addSelect = document.getElementById('pselector');

    // Guard against invalid selection.
    if (addSelect.selectedIndex == 0) {
        return;
    }

    // Create the new node. 
    var row = document.getElementById('tpl_product').cloneNode(true);
    var loadMaxString = loadMaxCounter.toString();

    // Set ids and names.
    row.id = 'product-' + loadMaxString;
    var inputs = getFormElements(row);
    for(var i=0; i<inputs.length; i++) { // FIXME: Could be replaced by for..each or for..of but need to check which is present
        inputs[i].name = inputs[i].name.replace('%i', loadMaxString);
        inputs[i].id   = inputs[i].id.replace('%i', loadMaxString);
    }

    // Show the new row.
    var tbody = document.getElementById('products').children[0];
    tbody.insertBefore(row, tbody.children[tbody.children.length - 2]);
    row.style.display = 'table-row';

    // Set correct select options.
    var productSelect = document.getElementById('pselect-' + loadMaxString);
    productSelect.selectedIndex = addSelect.selectedIndex;
    addSelect.selectedIndex = 0;
    productSelect.addEventListener('change', changeLoadProduct, true);

    // Add the custom form, if requested.
    if (productSelect.options[productSelect.selectedIndex].value == 'custom') {
        addLoadCustom(loadMaxString);
    }

    // Increment global counter.
    loadMaxCounter++; 

    // Check stock.
    checkLoadStock(row);
}

// Check if the stock covers the requested number of pieces
function checkLoadStock(obj)
{
    // Get the globally unique row index from the object's id.
    var idx = obj.id.match(/\d+/)[0];

    // Get some objects.
    var selector = document.getElementById('pselect-' + idx);
    var sold   = document.getElementById('sell-' + idx);
    var amount = parseInt(document.getElementById('amount-' + idx).value);

    // Get the stock.
    var stock = 0;
    if (selector.value == 'custom') {
        stock = parseInt(document.getElementById('cstock-' + idx).value);
    } else {
        stock = parseInt(loadStock[selector.value]);
    }

    // Check stock.
    if (sold.checked && stock < amount) {
        // Show warning unless present.
        if (document.getElementById('stockWarning-' + idx) == null) {
            var warn = document.getElementById('stockWarning').cloneNode(true);
            warn.id = 'stockWarning-' + idx;
            sold.parentNode.nextElementSibling.appendChild(warn);
            warn.style.display='inline';
        }
    } else {
        // Remove warning if present.
        var warn = document.getElementById('stockWarning-' + idx);
        if (warn != null) {
            warn.parentNode.removeChild(warn);
        }
    }
}

// EOF //
