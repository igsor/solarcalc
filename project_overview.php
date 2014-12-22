<?php

require_once('init.php');

/** PARAMETERS **/

/** PAGE CONTENT **/

// Layout start.
t_start();

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());

// Table query.
$query = "SELECT `id`, `name`, `description`, `client_name`, `location` FROM `project`";
$headers = array(
    'name'        => 'Name'
  , 'description' => 'Description'
  , 'client_name' => 'Client'
  , 'location'    => 'Location'
);

// Execute query and show table.
$result = $db->query($query) or die(mysqli_error($db));
t_scroll_table($result, $headers, '', null);
$result->free();

// Layout end.
$db->close();
t_end();

?>
