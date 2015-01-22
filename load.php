<?php

require_once('init.php');

/** PARAMETERS **/

// Edit parameter.
$editId = '';
if (key_exists('edit', $_GET)) {
    $editId = $_GET['edit'];
}

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());

// Handle actions.
$fields = array('name', 'description', 'power', 'type', 'voltage', 'price', 'stock');
$optionals = array('description');
if (($newId = handleModuleAction('load', $fields, $optionals, $db, $_POST)) != -1) {
    $editId = $newId;
}

/** PAGE CONTENT **/

// Layout start.
t_start();

// Edit display.
$editCallback = function($row) use ($db)
{
    $query = "SELECT * FROM `load` WHERE `id` = '{$row['id']}'";
    $result = $db->query($query) or die(mysqli_error($db));
    $data = $result->fetch_assoc();
    $result->free();
    t_module_editableLoad([$data], 'doEdit', 'editTable');
};

$addCallback = function()
{
    $data = array_with_defaults(['name', 'description', 'power', 'type', 'voltage', 'price', 'stock']);
    t_module_editableLoad([$data], 'doAdd', 'addTable');
};

// Table query.
$query = "SELECT `id`, `name`, `description`, `power`, `price`, `stock` FROM `load` ORDER BY `name`";
$headers = array(
    'name'        => 'Name'
  , 'description' => 'Description'
  , 'power'       => 'Power' . T_Units::W
  , 'price'       => 'Price' . T_Units::CFA
  , 'stock'       => 'Stock'
);

// Execute query and show table.
$result = $db->query($query) or die(mysqli_error($db));
t_module_list($result, $headers, $editId, $editCallback, $addCallback);
$result->free();

// Layout end.
$db->close();
t_end();

?>
