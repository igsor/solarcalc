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

//Edit dislpay.
$editCallback = function($row) use ($db)
{
    $query = "SELECT * FROM `panel` WHERE `id` = '{$row['id']}'";
    $result = mysql_query($query, $db) or die(mysql_error());
    $data = mysql_fetch_assoc($result);
    $header = array(
          'id'          => 'id'
        , 'name'        => 'Name'
        , 'description' => 'Description'
        , 'voltage'     => 'Voltage' . T_Units::V
        , 'power'       => 'Power' . T_Units::W
        , 'peak_power'  => 'Peak power' . T_Units::W
        , 'price'       => 'Price' . T_Units::CFA
        , 'stock'       => 'Stock'
    );

    t_details_table($data, $header);
};

// Table query.
$query = " SELECT `id`, `name`, `description`, `power`, `peak_power`, `price`, `stock` FROM `panel` ";
$headers = array(
      'name'          => 'Name'
    , 'description'   => 'Description'
    , 'power'         => 'Power'      . T_Units::W
    , 'peak_power'    => 'Peak power' . T_Units::W
    , 'price'         => 'Price'      . T_Units::CFA
    , 'stock'         => 'Stock'
);

// Execute query and show table.
$result = mysql_query($query, $db) or die(mysql_error());
t_scroll_table($result, $headers, $editId, $editCallback);

?>

<?php t_end(); ?>
