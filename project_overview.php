<?php

require_once('init.php');

/** PARAMETERS **/

// Edit parameter.
$editId = '';
if (key_exists('edit', $_GET)) {
    $editId = $_GET['edit'];
    // Projects are not edited here. If we get an edit request, we redirect to the special edit page.
    header("Location: project_edit.php?id={$editId}");
}

/** PAGE CONTENT **/

// Layout start.
t_start();

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());

$addCallback = function()
{
    // List modes for project creation
    echo "<a href='project_params.php'>by Load Definition Wizard</a>";
};

// Table query.
$query = "SELECT `id`, `name`, `description`, `client_name`, `location` FROM `project`";
$headers = array(
    'name'        => 'Name'
  , 'description' => 'Description'
  , 'client_name' => 'Client'
  , 'location'    => 'Location'
);

// Execute query and show table.
$result = $db->query($query) or fatal_error(mysqli_error($db));
t_module_list($result, $headers, '', null, $addCallback);
$result->free();

// Layout end.
$db->close();
t_end();

?>
