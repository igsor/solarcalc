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
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());

// Edit display.
$editCallback = function($row) use ($db, $mode)
{
    $query = "SELECT * FROM `$mode` WHERE `id` = '{$row['id']}'";
    $result = $db->query($query) or die(mysqli_error($db));
    $data = $result->fetch_assoc();
    $result->free();
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
$result = $db->query($query) or die(mysqli_error($db));
t_scroll_table($result, $headers, $editId, $editCallback);
$result->free();

// Layout end.
$db->close();
t_end();

?>
