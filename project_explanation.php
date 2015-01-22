<?php

require_once("init.php");

$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());

t_start();

t_end();
$db->close();
?>
