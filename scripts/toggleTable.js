
function tableToggler(tableHead, table1, table2) {
    var smallTable = document.getElementById(table1);
    var largeTable = document.getElementById(table2);


    if ((largeTable.style.display == 'none' || largeTable.style.display == '') && (smallTable.style.display == 'table-row' || smallTable.style.display == '')) {
        largeTable.style.display = 'table-row';
        smallTable.style.display = 'none';
        tableHead.classList.add('configurationSelected');
    } else if (largeTable.style.display == 'table-row' && smallTable.style.display == 'none') {
        largeTable.style.display = 'none';
        smallTable.style.display = 'table-row';
        tableHead.classList.remove('configurationSelected');
    } else {
        smallTable.style.display = 'table-row';
        largeTable.style.display = 'none';
        tableHead.classList.remove('configurationSelected');
    }
}

function toggleAdd() {
    var obj = document.getElementById('addTable');
    if (obj.style.display == '' || obj.style.display == 'none') {
        obj.style.display = 'inline';
    } else {
        obj.style.display = 'none';
    }
}

function confirmDelete() {
    if (window.confirm('Please confirm that you absolutely want to delete this item.')) {
        document.getElementById('deleteForm').submit();
    } else {
    }
}

function toggleDetail(divId) {
	var divObj = document.getElementById(divId);
    if (divObj.style.display == "") {
        // first click
        divObj.style.display = "inline";
    }
	else if (divObj.style.display == "none" ) {
		divObj.style.display = "inline";	
	}
	else {
		divObj.style.display = "none";	
	}
}

