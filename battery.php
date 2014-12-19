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
$db = mysql_connect($DB_HOST, $DB_USER, $DB_PASS) or die(mysql_error());
mysql_select_db($DB_NAME, $db) or die(mysql_error());

// Edit display.
$editCallback = function($row) use ($db)
{
    $query = "SELECT * FROM `battery` WHERE `id` = '{$row['id']}'";
    $result = mysql_query($query, $db) or die(mysql_error());
    $data = mysql_fetch_assoc($result);
    $header = array(
          'id'                  => 'id'
        , 'name'                => 'Name'
        , 'description'         => 'Description'
        , 'dod'                 => 'Depth of depletion'
        , 'voltage'             => 'Voltage' . T_Units::V
        , 'loss'                => 'Loss'
        , 'discharge'           => 'Discharge'
        , 'lifespan'            => 'Lifespan' . T_Units::Cycles
        , 'capacity'            => 'Capacity' . T_Units::Ah
        , 'price'               => 'Price' . T_Units::CFA
        , 'stock'               => 'Stock'
        , 'max_const_current'   => 'Max. constant current' . T_Units::A
        , 'max_peak_current'    => 'Max. peak current' . T_Units::A
        , 'avg_const_current'   => 'Avg. constant current' . T_Units::A
        , 'max_humidity'        => 'Max. humidity' . T_Units::Percent
        , 'max_temperature'     => 'Max. temperature' . T_Units::DEG

    );

    t_details_table($data, $header);
};

// Table query.
$query = "SELECT `id`, `name`, `description`, `lifespan`, `capacity`, `price`, `stock` FROM `battery`";
$headers = array(
      'name'        => 'Name'
    , 'description' => 'Description'
    , 'lifespan'    => 'Lifespan' . T_Units::H
    , 'capacity'    => 'Capacity' . T_Units::Ah
    , 'price'       => 'Price'    . T_Units::CFA
    , 'stock'       => 'Stock'
);

// Execute query and show table.
$result = mysql_query($query, $db) or die(mysql_error());
t_scroll_table($result, $headers, $editId, $editCallback);

// Layout end.
t_end();

?>
