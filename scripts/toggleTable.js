
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
