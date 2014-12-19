<?php

require_once('init.php');

/** PARAMETERS **/

/** PAGE CONTENT **/

// Layout start.
t_start();

// Database connection.
$db = mysql_connect($DB_HOST, $DB_USER, $DB_PASS) or die(mysql_error());
mysql_select_db($DB_NAME, $db) or die(mysql_error());

// Table query.
$query = "SELECT `id`, `name`, `description`, `client_name`, `location` FROM `project`";
$headers = array(
    'name'        => 'Name'
  , 'description' => 'Description'
  , 'client_name' => 'Client'
  , 'location'    => 'Location'
);

// Execute query and show table.
$result = mysql_query($query, $db) or die(mysql_error());
t_scroll_table($result, $headers, '', null);

// Layout end.
t_end();

?>
