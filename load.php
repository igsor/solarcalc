<?php

require_once('init.php');

/** PARAMETERS **/

// Edit parameter.
$editId = '';
if (key_exists('edit', $_GET)) {
    $editId = $_GET['edit'];
}


/** PAGE CONTENT **/

// Layout start.
t_start();

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());

// Edit display.
$editCallback = function($row) use ($db)
{
    $query = "SELECT * FROM `load` WHERE `id` = '{$row['id']}'";
    $result = $db->query($query) or die(mysqli_error($db));
    $data = $result->fetch_assoc();
    $result->free();
    $header = array(
          'id'            => 'id'
        , 'name'          => 'Name'
        , 'description'   => 'Description'
        , 'power'         => 'Power' . T_Units::W
        , 'type'          => 'Type'
        , 'voltage'       => 'Voltage' . T_Units::V
        , 'price'         => 'Price' . T_Units::CFA
        , 'stock'         => 'Stock'
    );

    t_details_table($data, $header);
};

// Table query.
$query = "SELECT `id`, `name`, `description`, `power`, `price`, `stock` FROM `load`";
$headers = array(
    'name'        => 'Name'
  , 'description' => 'Description'
  , 'power'       => 'Power' . T_Units::W
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
