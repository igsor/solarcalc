<?php

require_once('init.php');

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());

/** PARAMETERS **/

if (!isset($_GET['id'])) {
    t_argumentError();
}
$id = $db->escape_string($_GET['id']);

/** PROJECT EDIT **/

if (isset($_POST['doDelete'])) {
    // delete project
    $id = $db->escape_string($_POST['id']);
    $db->query("DELETE FROM `project` WHERE `id` = '{$id}'") or fatal_error(mysqli_error($db));
    header("Location: project_overview.php");
} else if (isset($_POST['doProjectEdit'])) {
    $db->query("
        UPDATE `project`
        SET
              `name`                = '" . $db->escape_string($_POST['name']) . "'
            , `description`         = '" . $db->escape_string($_POST['description']) . "'
            , `client_name`         = '" . $db->escape_string($_POST['client_name']) . "'
            , `client_phone`        = '" . $db->escape_string($_POST['client_phone']) . "'
            , `responsible_name`    = '" . $db->escape_string($_POST['responsible_name']) . "'
            , `responsible_phone`   = '" . $db->escape_string($_POST['responsible_phone']) . "'
            , `location`            = '" . $db->escape_string($_POST['location']) . "'
            , `comments`            = '" . $db->escape_string($_POST['comments']) . "'
            , `delivery_date`       = '" . $db->escape_string($_POST['delivery_date']) . "'
        WHERE
            `id`                    = '{$id}'
    ") or fatal_error(mysqli_error($db)); // FIXME: Harden against missing input.
}

/** MODULE ACTION **/

if (isset($_POST['editPanel'])) {
    $fields = array('name', 'description', 'voltage', 'power', 'peak_power', 'price', 'amount');
    $optionals = array('description');
    $_POST['doEdit'] = 1;
    handleModuleAction('project_panel', $fields, $optionals, $db, $_POST);
}

if (isset($_POST['editBattery'])) {
    $fields = array('name', 'description', 'voltage', 'dod', 'loss', 'discharge', 'lifespan', 'capacity', 'max_const_current', 'max_peak_current', 'avg_const_current', 'max_humidity', 'max_temperature', 'price', 'amount');
    $optionals = array('description');
    $_POST['doEdit'] = 1;
    handleModuleAction('project_battery', $fields, $optionals, $db, $_POST);
}

if (isset($_POST['editLoad'])) {
    fix_checkbox_post('sold');
    $fields = array('name', 'description', 'power', 'type', 'voltage', 'price', 'amount', 'daytime', 'nighttime', 'sold');
    $optionals = array('description');
    $_POST['doEdit'] = 1;
    handleModuleAction('project_load', $fields, $optionals, $db, $_POST);
}

if (isset($_POST['editInverter'])) {
    $fields = array('name', 'description', 'voltage', 'loss', 'max_current', 'price', 'amount');
    $optionals = array('description');
    $_POST['doEdit'] = 1;
    handleModuleAction('project_inverter', $fields, $optionals, $db, $_POST);
}

if (isset($_POST['editController'])) {
$fields = array('name', 'description', 'voltage', 'loss', 'max_current', 'price', 'amount');
    $optionals = array('description');
    $_POST['doEdit'] = 1;
    handleModuleAction('project_controller', $fields, $optionals, $db, $_POST);
}

/** PAGE CONTENT **/

// Layout start.
t_start();


?>

<h3>Project metadata</h3>

<form action='' method='POST'>
<?php
$result = $db->query("SELECT * FROM `project` WHERE `id` = '{$id}'") or fatal_error(mysqli_error($db));
t_project_edit('doProjectEdit', 'Save changes', $result->fetch_assoc());
$result->free();
?>
</form>


<h3>Budget</h3>
<?php t_project_budget(project_budget($db, $id)); ?>

<br/>

<?php

function getData($db, $query)
{
    $result = $db->query($query);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
};

t_project_editableModule('Panel', function() use ($db, $id) {
    $data = getData($db, "SELECT * FROM `project_panel` WHERE `project` = '{$id}'");
    t_module_editablePanel($data, 'editPanel', 'editPanelTable');
});

t_project_editableModule('Battery', function() use ($db, $id) {
    $data = getData($db, "SELECT * FROM `project_battery` WHERE `project` = '{$id}'");
    t_module_editableBattery($data, 'editBattery', 'editBatteryTable');
});

t_project_editableModule('Load', function() use ($db, $id) {
    $data = getData($db, "SELECT * FROM `project_load` WHERE `project` = '{$id}'");
    t_module_editableLoad($data, 'editLoad', 'editLoadTable');
});

t_project_editableModule('Controller', function() use ($db, $id) {
    $data = getData($db, "SELECT * FROM `project_controller` WHERE `project` = '{$id}'");
    t_module_editableHardware($data, 'editController', 'editControllerTable');
});

t_project_editableModule('Inverter', function() use ($db, $id) {
    $data = getData($db, "SELECT * FROM `project_inverter` WHERE `project` = '{$id}'");
    t_module_editableHardware($data, 'editInverter', 'editInverterTable');
});

// Layout end.
$db->close();

?>

<h3>Delete project</h3>
<form action='' method='POST' id='deleteForm'>
<input type='hidden' name='id' value='<?php echo $id; ?>' />
<input type='hidden' name='doDelete' value='on' />
<button type='button' onclick='confirmDelete()'>Delete</button>
</form>

<?php

t_end();

?>
