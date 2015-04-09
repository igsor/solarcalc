<?php

/*** Project status state machine: See docs/project_states.t ***/

require_once('init.php');

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());

/** PARAMETERS **/

if (!isset($_GET['id'])) {
    t_argumentError();
}
$id = $db->escape_string($_GET['id']);

// Get status.
$status_row = $db->query("SELECT `status` FROM `project` WHERE `id` = {$id}") or fatal_error(mysqli_error($db));
if ($status_row->num_rows != 1) {
    t_argumentError();
}
$status = $status_row->fetch_assoc()['status'];

/** PROJECT EDIT **/

if (isset($_POST['doDelete'])) {
    if ($status == $STATUS_PLANNED || $status == $STATUS_CANCELLED) { // Only deletable in (P, X)
        // delete project
        $id = $db->escape_string($_POST['id']);
        $db->query("DELETE FROM `project` WHERE `id` = '{$id}'") or fatal_error(mysqli_error($db));
        header("Location: project_overview.php");
    } else {
        t_argumentError();
    }

} else if (isset($_POST['doProjectEdit'])) {
    // Check state transition
    $next_status = $db->escape_string($_POST['status']);
    if ($status != $next_status) {
        // Check transition validity
        switch($status) {
            case $STATUS_PLANNED: // Allowed successor states: (E, X)
                if ($next_status == $STATUS_COMPLETED) {
                    t_argumentError();
                }
                break;
            case $STATUS_EXECUTING: // Allowed successor states: (P, C, X)
                break;
            case $STATUS_COMPLETED: // Allowed successor states: (E)
                if ($next_status != $STATUS_EXECUTING) {
                    t_argumentError();
                }
                break;
            case $STATUS_CANCELLED: // Allowed successor states: (P)
                if ($next_status != $STATUS_PLANNED) {
                    t_argumentError();
                }
                break;
            default:
                t_programmingError();
        }

        // Transition action
        $trans_op = NULL;
        if ($status == $STATUS_PLANNED && $next_status == $STATUS_EXECUTING) {
            // Remove project amount from stock
            $trans_op = '-';
        } else if ($status == $STATUS_EXECUTING && ($next_status == $STATUS_PLANNED || $next_status == $STATUS_CANCELLED)) {
            // Add project amount to stock
            $trans_op = '+';
        }

        if ($trans_op != NULL) {
            // Modules always sold
            foreach(['panel', 'battery', 'controller', 'inverter'] as $module) {
                $db->query("UPDATE
                        `{$module}` as `module`
                        INNER JOIN `project_{$module}` as `project` ON `project`.`{$module}` = `module`.`id`
                    SET
                        `module`.`stock` = `module`.`stock` {$trans_op} `project`.`amount`
                    WHERE
                        `project`.`project` = '{$id}'
                ") or fatal_error(mysqli_error($db));
            }

            // Modules sold optionally
            foreach(['load'] as $module) {
                $db->query("UPDATE
                        `{$module}` as `module`
                        INNER JOIN `project_{$module}` as `project` ON `project`.`{$module}` = `module`.`id`
                    SET
                        `module`.`stock` = `module`.`stock` {$trans_op} `project`.`amount`
                    WHERE
                        `project`.`project` = '{$id}'
                        AND `project`.`sold` = TRUE
                ") or fatal_error(mysqli_error($db));
            }
        }

        // State update
        $db->query("UPDATE `project` SET `status` = '{$next_status}' WHERE `id` = '{$id}'") or fatal_error(mysqli_error());
    }

    if ($status == $STATUS_PLANNED || $status == $STATUS_EXECUTING) { // Only editable in (P, E)
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
                , `work_allowance`      = '" . $db->escape_string($_POST['work_allowance']) . "'
                , `material_allowance`  = '" . $db->escape_string($_POST['material_allowance']) . "'
            WHERE
                `id`                    = '{$id}'
        ") or fatal_error(mysqli_error($db)); // FIXME: Harden against missing input.
    } // Data can still be sent in (C, X) because the status is always editable.
}

/** MODULE ACTION **/

if ($status == $STATUS_PLANNED) { // Only editable in P
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
}

/** PAGE CONTENT **/

// Layout start.
t_start();


?>

<h3>Project metadata</h3>

<form action='' method='POST' id='projectMetadata'>
<?php
$status = isset($next_status) ? $next_status : $status; // Update status as well
$result = $db->query("SELECT * FROM `project` WHERE `id` = '{$id}'") or fatal_error(mysqli_error($db));
t_project_edit('doProjectEdit', 'Save changes', $result->fetch_assoc());
$result->free();
?>
</form>
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


t_project_editableModule('Characteristics', function() use ($db, $id) {

    // Get the configuration data.
    $config_modules = ['panel', 'battery', 'controller', 'inverter'];
    foreach($config_modules as $module) {
        $result = $db->query(
            "SELECT `id`, `amount` FROM `project_{$module}` WHERE `project` = '{$id}}'"
        ) or fatal_error(mysqli_error($db));
        $data[$module] = [];
        while($item = $result->fetch_assoc()) {
            $data[$module][$item['id']] = $item['amount'];
        }
        $result->free();
    }

    // Get the cannonical load.
    $cload = getData($db,"SELECT *, `daytime` as 'dayhours', `nighttime` as 'nighthours' FROM `project_load` WHERE `project` = '{$id}'");

    // Get sun hours.
    $result = $db->query("SELECT `sunhours` FROM `project` WHERE `id` = '{$id}'") or fatal_error(mysqli_error($db));
    $sunhours = $result->fetch_row()[0];
    $result->free();

    // Get the derived data.
    $derived_data = new ConfigurationData(
        $db,
        $data['battery'],
        $data['panel'],
        $data['controller'],
        $data['inverter'],
        $cload,
        $sunhours,
        'project_'
    );

    // Print the characteristics table.
    t_project_characteristics($derived_data, $db);
});

t_project_editableModule('Budget', function() use ($db, $id) {
    // Get data from project.
    $result = $db->query(" SELECT `work_allowance`, `material_allowance` FROM `project` WHERE `id` = '{$id}' ") or fatal_error(mysqli_error($db));

    $data = $result->fetch_assoc();
    $result->free();

    t_project_budget(project_budget($db, $id), $data['work_allowance'], $data['material_allowance']);
});

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

// Delete button.
t_project_editableModule('Delete project', function() use ($id) {
    ?>
        <form action='' method='POST' id='deleteForm'>
            <input type='hidden' name='id' value='<?php echo $id; ?>' />
            <input type='hidden' name='doDelete' value='on' />
            <button type='button' id='deleteButton' onclick='confirmDelete()'>Delete</button>
        </form>
    <?php
});


// Disable metadata (except status) in states (C, X)
if ($status == $STATUS_COMPLETED || $status == $STATUS_CANCELLED) {
    ?>
        <script>
            var meta_elements = document.getElementById('projectMetadata').elements;
            for(var i=0; i<meta_elements.length; i++)
                meta_elements[i].disabled = true;

            document.getElementById('project-status').disabled = false;
            document.getElementById('doProjectEdit').disabled = false;

        </script>
    <?php
}

// Disable configuration in states (E, C, X)
if ($status != $STATUS_PLANNED) {
    ?>
        <script>
            var module_forms = document.getElementsByClassName('module-form');
            for(var i=0; i<module_forms.length; i++) {
                var module_elements = module_forms[i].elements;
                for(var j=0; j<module_elements.length; j++)
                    module_elements[j].disabled = true;
            }

        </script>
    <?php
}

// Disable delete button in states (E, C)
if ($status == $STATUS_EXECUTING || $status == $STATUS_COMPLETED) {
    ?>
        <script>
            document.getElementById('deleteButton').disabled = true;
        </script>
    <?php
}

// Disable illegal state transitions
echo '<script>';
switch($status) {
    case $STATUS_PLANNED:
        echo "document.getElementById('project-status').options[2].disabled = true;"; // completed
        break;
    case $STATUS_EXECUTING:
        break;
    case $STATUS_COMPLETED:
        echo "document.getElementById('project-status').options[0].disabled = true;"; // planning
        echo "document.getElementById('project-status').options[3].disabled = true;"; // cancelled
        break;
    case $STATUS_CANCELLED:
        echo "document.getElementById('project-status').options[1].disabled = true;"; // executing
        echo "document.getElementById('project-status').options[2].disabled = true;"; // completed
        break;
}
echo '</script>';

// Layout end.
$db->close();

t_end();

?>
