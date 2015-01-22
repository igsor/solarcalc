<?php

require_once('init.php');

/** PARAMETERS **/

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());

if (isset($_POST['doDelete'])) {
    // delete project
    $id = $db->escape_string($_POST['id']);
    $db->query("DELETE FROM `project` WHERE `id` = '{$id}'") or fatal_error(mysqli_error($db));
    header("Location: project_overview.php");
}

/** PAGE CONTENT **/

// Layout start.
t_start();

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

$id = $db->escape_string($_GET['id']);
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

<form action='' method='POST' id='deleteForm'>
<input type='hidden' name='id' value='<?php echo $id; ?>' />
<input type='hidden' name='doDelete' value='on' />
<input type='button' value='DEL' onclick='confirmDelete()'>
</form>

<?php

t_end();

?>
