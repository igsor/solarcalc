<?php

require_once('init.php');

/** PARAMETERS **/

// Mode parameter.
$mode = 'controller';
if (key_exists('mode', $_GET) and $_GET['mode'] == 'inverter') {
    $mode = 'inverter';
}

// Edit parameter.
$editId = '';
if (key_exists('edit', $_GET)) {
    $editId = $_GET['edit'];
}


/** PAGE CONTENT **/

// Layout start.
t_start();

echo "
<form action='' method='get'>
Select display: <select name='mode'>
<option value='controller'" . ($mode == 'controller'?' selected':'') . ">Controller</option>
<option value='inverter'" . ($mode == 'inverter'?' selected':'') . ">Inverter</option>
</select>
<input type=submit value='Go'>
</form>
";

// Database connection.
$db = mysql_connect($DB_HOST, $DB_USER, $DB_PASS) or die(mysql_error());
mysql_select_db($DB_NAME, $db) or die(mysql_error());

// Edit display.
$editCallback = function($row) use ($db, $mode)
{
    $query = "SELECT * FROM `$mode` WHERE `id` = '{$row['id']}'";
    $result = mysql_query($query, $db) or die(mysql_error());
    $data = mysql_fetch_assoc($result);
    $header = array(
          'id'            => 'id'
        , 'name'          => 'Name'
        , 'description'   => 'Description'
        , 'loss'          => 'Loss'
        , 'voltage'       => 'Voltage' . T_Units::V
        , 'max_current'   => 'Max. current' . T_Units::A
        , 'price'         => 'Price' . T_Units::CFA
        , 'stock'         => 'Stock'
    );

    t_details_table($data, $header);
};

// Table query.
$query = "SELECT `id`, `name`, `description`, `price`, `stock` FROM `$mode`";
$headers = array(
    'name'        => 'Name'
  , 'description' => 'Description'
  , 'price'       => 'Price' . T_Units::CFA
  , 'stock'       => 'Stock'
);

// Execute query and show table.
$result = mysql_query($query, $db) or die(mysql_error());
t_scroll_table($result, $headers, $editId, $editCallback);

// Layout end.
t_end();

?>
