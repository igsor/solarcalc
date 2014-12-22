<?php

require_once('init.php');

// unserialize input
$INPUT = array();
$serialized_keys = array('panel', 'battery', 'inverter', 'controller', 'load', 'custom');
foreach($serialized_keys as $key) {
    if (isset($_POST[$key])) {
        $INPUT[$key] = unserialize($_POST[$key]);
    }
}

// Database connection.
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());
$db->autocommit(false);

if (isset($_POST['doCreateProject']))
{
    // Form submitted; Write database

    /************** CUSTOM LOADS ***************/

    foreach($INPUT['custom'] as $key => $values) {
        if (isset($values['save'])) {
            $stock = isset($values['stock']) ? $INPUT['load'][$key]['amount'] : 0;
            // TODO: Input checking
            $db->query("
                INSERT
                INTO `load` (
                          `name`
                        , `power`
                        , `type`
                        , `voltage`
                        , `price`
                        , `stock`
                        )
                VALUES (
                          '{$values['name']}'
                        , '{$values['power']}'
                        , '{$values['type']}'
                        , '{$values['voltage']}'
                        , '{$values['price']}'
                        , '$stock'
                       )
            ") or die(mysqli_error($db));

            // Rewrite load structure.
            $custom_id = $db->insert_id;
            $INPUT['load'][$key]['product'] = $custom_id;
        }
    }

    /************** PROJECT METADATA ****************/

    // Project metadata.
    $db->query("
        INSERT
        INTO `project` (
              `name`
            , `description`
            , `client_name`
            , `client_phone`
            , `responsible_name`
            , `responsible_phone`
            , `location`
            , `comments`
            , `delivery_date`
            , `sunhours`
            )
        VALUES (
              '{$_POST['project_name']}'
            , '{$_POST['description']}'
            , '{$_POST['client_name']}'
            , '{$_POST['client_phone']}'
            , '{$_POST['responsible_name']}'
            , '{$_POST['responsible_phone']}'
            , '{$_POST['location']}'
            , '{$_POST['comment']}'
            , '{$_POST['delivery']}'
            , '{$_POST['sunhours']}'
            )
    ") or die(mysqli_error($db));

    $project_id = $db->insert_id;

    /*************** PROJECT DETAILS ****************/

    // Load.
    if (isset($INPUT['load'])) {
        foreach($INPUT['load'] as $idx => $load_cfg) { // FIXME: load_cfg input checking.
            if ($load_cfg['product'] != 'custom') {
                // Get load data
                $result = $db->query("
                    SELECT
                          `id`
                        , `name`
                        , `description`
                        , `power`
                        , `type`
                        , `voltage`
                        , `price`
                    FROM
                        `load`
                    WHERE
                        `id` = '" . $db->escape_string($load_cfg['product']) . "'
                ") or die(mysqli_error($db));
                
                $load_data = $result->fetch_assoc();
            } else {
                $load_data = $INPUT['custom'][$idx]; // FIXME: Input checking.
                $load_data['id'] = 'NULL'; // Not available.
                $load_data['description'] = ''; // Not set in form.
            }

            // Insert data.
            $db->query("
                INSERT
                INTO `project_load` (
                      `project`
                    , `load`
                    , `name`
                    , `description`
                    , `power`
                    , `type`
                    , `voltage`
                    , `price`
                    , `amount`
                    , `daytime`
                    , `nighttime`
                    , `sold`
                )
                VALUES (
                      '{$project_id}'
                    , '{$load_data['id']}'
                    , '{$load_data['name']}'
                    , '{$load_data['description']}'
                    , '{$load_data['power']}'
                    , '{$load_data['type']}'
                    , '{$load_data['voltage']}'
                    , '{$load_data['price']}'
                    , '{$load_cfg['amount']}'
                    , '{$load_cfg['dayhours']}'
                    , '{$load_cfg['nighthours']}'
                    , " . (isset($load_cfg['sell']) ? 'TRUE' : 'FALSE') . "
                )
            ") or die(mysqli_error($db));
        }
    }

    // Panel.
    if (isset($INPUT['battery'])) {
        // Get ids.
        $panel_id = array();
        foreach($INPUT['panel'] as $data) {
            array_push($panel_id, $db->escape_string($data['product']));
        }
    
        // FIXME: Probably we're faster to search / insert entries individually (see mysql insert..select man page); But this is a pain in the arse to code.
        // Copy base data.
        $db->query("
            INSERT
            INTO `project_panel` (
                  `project`
                , `panel`
                , `name`
                , `description`
                , `power`
                , `peak_power`
                , `voltage`
                , `price`
                )
            SELECT
                  '{$project_id}'
                , `id`
                , `name`
                , `description`
                , `power`
                , `peak_power`
                , `voltage`
                , `price`
            FROM
                `panel`
            WHERE
                `id` IN (" . join(',', $panel_id) . ")
        ") or die(mysqli_error($db));
    
        // Update amount.
        foreach($INPUT['panel'] as $data) {
            // project_id and panel id should be unique as multiple entries of the same panel just increase the amount
            $db->query("
                UPDATE `project_panel`
                SET
                    `amount` = " . $db->escape_string($data['amount']) . "
                WHERE
                    `panel` = " . $db->escape_string($data['product']) . " AND `project` = $project_id 
            ") or die(mysqli_error($db));
        }
    }

    // Battery.
    if (isset($INPUT['battery'])) {
        // Get ids.
        $battery_id = array();
        foreach($INPUT['battery'] as $data) {
            array_push($battery_id, $db->escape_string($data['product']));
        }
    
        // Copy base data.
        $db->query("
            INSERT
            INTO `project_battery` (
                  `project`
                , `battery`
                , `name`
                , `description`
                , `dod`
                , `voltage`
                , `loss`
                , `discharge`
                , `lifespan`
                , `capacity`
                , `price`
                , `max_const_current`
                , `max_peak_current`
                , `avg_const_current`
                , `max_humidity`
                , `max_temperature`
                )
            SELECT
                  '${project_id}'
                , `id`
                , `name`
                , `description`
                , `dod`
                , `voltage`
                , `loss`
                , `discharge`
                , `lifespan`
                , `capacity`
                , `price`
                , `max_const_current`
                , `max_peak_current`
                , `avg_const_current`
                , `max_humidity`
                , `max_temperature`
            FROM
                `battery`
            WHERE
                `id` IN (" . join(',', $battery_id) . ")
        ") or die(mysqli_error($db));
    
        // Update amount.
        foreach($INPUT['battery'] as $data) {
            $db->query("
                UPDATE `project_battery`
                SET
                    `amount` = " . $db->escape_string($data['amount']) . "
                WHERE
                    `battery` = " . $db->escape_string($data['product']) . " AND `project` = $project_id 
            ") or die(mysqli_error($db));
        }
    }

    // Inverter.
    if (isset($INPUT['inverter'])) {
        // Get ids.
        $inverter_id = array();
        foreach($INPUT['inverter'] as $data) {
            array_push($inverter_id, $db->escape_string($data['product']));
        }

        // Copy base data.
        $db->query("
            INSERT
            INTO `project_inverter` (
                  `project`
                , `inverter`
                , `name`
                , `description`
                , `loss`
                , `voltage`
                , `price`
                , `max_current`
                )
            SELECT
                  '{$project_id}'
                , `id`
                , `name`
                , `description`
                , `loss`
                , `voltage`
                , `price`
                , `max_current`
            FROM
                `inverter`
            WHERE
                `id` IN (" . join(',', $inverter_id) . ")
        ") or die(mysqli_error($db));

        // Update amount.
        foreach($INPUT['inverter'] as $data) {
            $db->query("
                UPDATE `project_inverter`
                SET
                    `amount` = " . $db->escape_string($data['amount']) . "
                WHERE
                    `inverter` = " . $db->escape_string($data['product']) . " AND `project` = $project_id 
            ") or die(mysqli_error($db));
        }
    }

    // Controller.
    if (isset($INPUT['controller'])) {
        // Get ids.
        $controller_id = array();
        foreach($INPUT['controller'] as $data) {
            array_push($controller_id, $db->escape_string($data['product']));
        }

        // Copy base data.
        $db->query("
            INSERT
            INTO `project_controller` (
                  `project`
                , `controller`
                , `name`
                , `description`
                , `loss`
                , `price`
                , `voltage`
                , `max_current`
                )
            SELECT
                  '{$project_id}'
                , `id`
                , `name`
                , `description`
                , `loss`
                , `price`
                , `voltage`
                , `max_current`
            FROM
                `controller`
            WHERE
                `id` IN (" . join(',', $controller_id) . ")
        ") or die(mysqli_error($db));

        // Update amount.
        foreach($INPUT['controller'] as $data) {
            $db->query("
                UPDATE `project_controller`
                SET
                    `amount` = " . $db->escape_string($data['amount']) . "
                WHERE
                    `controller` = " . $db->escape_string($data['product']) . " AND `project` = $project_id 
            ") or die(mysqli_error($db));
        }
    }

    $db->commit() or die("TRANSACTION FAILED");

    // Rewrite POST structure.
    // Remove custom loads which have been added to the database already.
    // FIXME: Actually unnecessary since we redirect.
    if (isset($INPUT['load'])) {
        foreach($INPUT['load'] as $key => $values) {
            if ($values['product'] != 'custom' and isset($INPUT['custom'][$key])) {
                unset($INPUT['custom'][$key]);
            }
        }
    }

    $_POST['custom'] = serialize($INPUT['custom']);
    $_POST['load']   = serialize($INPUT['load']);

    // Redirect
    header("Location: project_overview.php");
}

// Start the layout.
t_start();

// Create the form.
$budget = array();
?>

<h2>Project summary</h2>

<h3>Loads</h3>
<?php 
// FIXME: Switch to MYSQLI interface
$link = mysql_connect($DB_HOST, $DB_USER, $DB_PASS);
mysql_select_db($DB_NAME, $link);
if (isset($INPUT['load']) and isset($INPUT['custom'])) {
    $budget += t_loadtable($INPUT['load'], $INPUT['custom'], $link);
}
?>

<h3>Panels</h3>

<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
  <td>Voltage<?php echo T_Units::V; ?></td>
  <td>Power<?php echo T_Units::W; ?></td>
 </tr>
 <?php
    if (isset($INPUT['panel'])) {
        foreach($INPUT['panel'] as $data) {

            // Read database.
            $id = $data['product'];
            $result = $db->query("SELECT `name`, `voltage`, `power`, `price` FROM `panel` WHERE id = '" . $db->escape_string($id) . "'") or die(mysqli_error($db));
            if ($result->num_rows != 1) { // ID must exist
                continue;
            }
            $data += $result->fetch_assoc();
            $result->free();

            // Table output.
            ?>
                <tr>
                    <td><?php echo $data['name']; ?></td>
                    <td><?php echo $data['amount']; ?></td>
                    <td><?php echo $data['voltage']; ?></td>
                    <td><?php echo $data['power']; ?></td>
                </tr>
            <?php

            // Store budget data.
            array_push($budget, array(
                'product' => $data['name'],
                'amount'  => $data['amount'],
                'price'   => $data['price'],
            ));
        }
    }
 ?>
</table>

<h3>Batteries</h3>

<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
  <td>Voltage<?php echo T_Units::V; ?></td>
  <td>Capacity<?php echo T_Units::Ah; ?></td>
  <td>Usable capacity<?php echo T_Units::Ah; ?></td>
 </tr>
 <?php
    if (isset($INPUT['battery'])) {
        foreach($INPUT['battery'] as $data) {

            // Read database.
            $id = $data['product'];
            $result = $db->query("SELECT `name`, `voltage`, `capacity`, `dod` * `capacity` AS `ucapacity`, `price` FROM `battery` WHERE id = '" . $db->escape_string($id) . "'") or die(mysqli_error($db));
            if ($result->num_rows != 1) { // ID must exist
                continue;
            }
            $data += $result->fetch_assoc();
            $result->free();
            
            // Table output.
            ?>
                <tr>
                    <td><?php echo $data['name']; ?></td>
                    <td><?php echo $data['amount']; ?></td>
                    <td><?php echo $data['voltage']; ?></td>
                    <td><?php echo $data['capacity']; ?></td>
                    <td><?php echo $data['ucapacity']; ?></td>
                </tr>
            <?php

            // Store budget data.
            array_push($budget, array(
                'product' => $data['name'],
                'amount'  => $data['amount'],
                'price'   => $data['price'],
            ));
        }
    }
 ?>
</table>

<h3>Extra hardware</h3>

<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
 </tr>
 <?php
    if (isset($INPUT['inverter'])) {
        foreach($INPUT['inverter'] as $data) {

            // Read database.
            $id = $data['product'];
            $result = $db->query("SELECT `name`, `price` FROM `inverter` WHERE id = '" . $db->escape_string($id) . "'") or die(mysqli_error($db));
            if ($result->num_rows != 1) { // ID must exist
                continue;
            }
            $data += $result->fetch_assoc();
            $result->free();

            // Table output.
            ?>
                <tr>
                    <td><?php echo $data['name']; ?></td>
                    <td><?php echo $data['amount']; ?></td>
                </tr>
            <?php

            // Store budget data.
            array_push($budget, array(
                'product' => $data['name'],
                'amount'  => $data['amount'],
                'price'   => $data['price'],
            ));
        }
    }

    if (isset($INPUT['controller'])) {
        foreach($INPUT['controller'] as $data) {

            // Read database.
            $id = $data['product'];
            $result = $db->query("SELECT `name`, `price` FROM `controller` WHERE id = '" . $db->escape_string($id) . "'") or die(mysqli_error($db));
            if ($result->num_rows != 1) { // ID must exist
                continue;
            }
            $data += $result->fetch_assoc();
            $result->free();

            // Table output.
            ?>
                <tr>
                    <td><?php echo $data['name']; ?></td>
                    <td><?php echo $data['amount']; ?></td>
                </tr>
            <?php

            // Store budget data.
            array_push($budget, array(
                'product' => $data['name'],
                'amount'  => $data['amount'],
                'price'   => $data['price'],
            ));
        }
    }
 ?>
</table>

<h3>Budget</h3>

<table cellspacing=0 cellpadding=0 class="loadtable">
 <tr class="tablehead">
  <td>Product</td>
  <td>Amount</td>
  <td>Price per Unit<?php echo T_Units::CFA; ?></td>
  <td>Price<?php echo T_Units::CFA; ?></td>
 </tr>
<?php
    $total = 0;
    foreach($budget as $data) {
        $total += $subtotal = $data['price'] * $data['amount'];
        ?>
            <tr>
                <td><?php echo $data['product']; ?></td>
                <td><?php echo $data['amount']; ?></td>
                <td><?php echo $data['price']; ?></td>
                <td><?php echo $subtotal; ?></td>
            </tr>
        <?php
    }
?>
 <tr class="tablerow calcresult">
  <td>Total</td>
  <td></td>
  <td></td>
  <td><?php echo $total; ?></td>
 </tr>
</table>

<h2>Project Metadata</h2>
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST">
<input type="hidden" name="battery" value='<?php echo key_exists('battery', $_POST) ? $_POST['battery'] : ''; ?>' />
<input type="hidden" name="panel" value='<?php echo key_exists('panel', $_POST) ? $_POST['panel'] : ''; ?>' />
<input type="hidden" name="inverter" value='<?php echo key_exists('inverter', $_POST) ? $_POST['inverter'] : ''; ?>' />
<input type="hidden" name="controller" value='<?php echo key_exists('controller', $_POST) ? $_POST['controller'] : ''; ?>' />
<input type="hidden" name="load" value='<?php echo key_exists('load', $_POST) ? $_POST['load'] : ''; ?>' />
<input type="hidden" name="custom" value='<?php echo key_exists('custom', $_POST) ? $_POST['custom'] : ''; ?>' />
<input type="hidden" name="sunhours" value='<?php echo key_exists('sunhours', $_POST) ? $_POST['sunhours'] : ''; ?>' />
<table cellspacing=0 cellpadding=0 class="projecttable">
  <tr>
    <td class="tbl_key">Name</td>
    <td class="tbl_value"><input type="text" name="project_name" value="<?php echo key_exists('project_name', $_POST) ? $_POST['project_name'] : ''; ?>" required /></td>
  </tr>
  <tr>
    <td class="tbl_key">Description</td>
    <td class="tbl_value"><textarea cols=60 rows=5 name="description"><?php echo key_exists('description', $_POST) ? $_POST['description'] : ''; ?></textarea></td>
  </tr>
  <tr>
    <td class="tbl_key">Location</td>
    <td class="tbl_value"><input type="text" name="location" value="<?php echo key_exists('location', $_POST) ? $_POST['location'] : ''; ?>" required /></td>
  </tr>
  <tr>
    <td class="tbl_key">Client name</td>
    <td class="tbl_value"><input type="text" name="client_name" value="<?php echo key_exists('client_name', $_POST) ? $_POST['client_name'] : ''; ?>" required /></td>
  </tr>
  <tr>
    <td class="tbl_key">Client phone</td>
    <td class="tbl_value"><input type="phone" name="client_phone" value="<?php echo key_exists('client_phone', $_POST) ? $_POST['client_phone'] : ''; ?>" /></td>
  </tr>
  <tr>
    <td class="tbl_key">Responsible person</td>
    <td class="tbl_value"><input type="text" name="responsible_name" value="<?php echo key_exists('responsible_name', $_POST) ? $_POST['responsible_name'] : ''; ?>" required /></td>
  </tr>
  <tr>
    <td class="tbl_key">Responsible phone</td>
    <td class="tbl_value"><input type="phone" name="responsible_phone" value="<?php echo key_exists('responsible_phone', $_POST) ? $_POST['responsible_phone'] : ''; ?>" /></td>
  </tr>
  <tr>
    <td class="tbl_key">Delivery date</td>
    <td class="tbl_value"><input type="date" name="delivery" value="<?php echo key_exists('delivery', $_POST) ? $_POST['delivery'] : ''; ?>" /></td>
  </tr>
  <tr>
    <td class="tbl_key">Comments</td>
    <td class="tbl_value"><textarea cols=60 rows=5 name="comment"><?php echo key_exists('comment', $_POST) ? $_POST['comment'] : ''; ?></textarea></td>
  </tr>
  <tr class="buttonrow">
    <td colspan=2><input type="submit" name="doCreateProject" value="Create project"></td>
  </tr>
</table>
</form>

<?php t_end(); ?>
