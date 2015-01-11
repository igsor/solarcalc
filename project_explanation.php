<?php

require_once("init.php");

// Argument checking
if (!isset($_POST['load']) or !isset($_POST['sunhours'])) {
    t_argumentError();
}

// POST cleanup
if (!isset($_POST['custom'])) {
    $_POST['custom'] = array();
}

$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());

t_start();



<?php
$db->close();
t_end();
?>
