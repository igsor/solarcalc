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
$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());
$db->autocommit(false);

/**
 * Traverses $INPUT[{panel, battery, controller, inverter}] and
 * guarantees that each product appears only once. Amounts are
 * summed up, if this is not the case.
 * 
 * Returns a dictionary id => amount.
 */
function unique_product($src, $db) {
    $trg = array();
    foreach($src as $key => $data) {
        if (!isset($data['product']) or !isset($data['amount'])) {
            t_argumentError(); // Fatal, so we stop here.
            continue; // Obsolete but still here as a guard if t_argumentError isn't fatal.
        }

        $id = $db->escape_string($data['product']);
        $amount = $db->escape_string($data['amount']);
        if (key_exists($id, $trg)) {
            $trg[$id] += $amount;
        } else {
            $trg[$id] = $amount;
        }
    }

    return $trg;
}

if (isset($_POST['doCreateProject']))
{
    /******************************* CUSTOM LOADS *******************************/

    foreach($INPUT['custom'] as $key => $values) {
        if (isset($values['save'])) {
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
                          '" . $db->escape_string($values['name']) . "'
                        , '" . $db->escape_string($values['power']) . "'
                        , '" . $db->escape_string($values['type']) . "'
                        , '" . $db->escape_string($values['voltage']) . "'
                        , '" . $db->escape_string($values['price']) . "'
                        , '" . $db->escape_string($values['stock']) . "'
                       )
            ") or fatal_error(mysqli_error($db)); // FIXME: Harden against missing input.

            // Rewrite load structure.
            $custom_id = $db->insert_id;
            $INPUT['load'][$key]['product'] = $custom_id;
        }
    }

    /******************************* PROJECT METADATA *******************************/

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
              '" . $db->escape_string($_POST['project_name']) . "'
            , '" . $db->escape_string($_POST['description']) . "'
            , '" . $db->escape_string($_POST['client_name']) . "'
            , '" . $db->escape_string($_POST['client_phone']) . "'
            , '" . $db->escape_string($_POST['responsible_name']) . "'
            , '" . $db->escape_string($_POST['responsible_phone']) . "'
            , '" . $db->escape_string($_POST['location']) . "'
            , '" . $db->escape_string($_POST['comment']) . "'
            , '" . $db->escape_string($_POST['delivery']) . "'
            , '" . $db->escape_string($_POST['sunhours']) . "'
            )
    ") or fatal_error(mysqli_error($db)); // FIXME: Harden against missing input.

    $project_id = $db->insert_id;

    /******************************* PROJECT DETAILS *******************************/

    //////////// Load ////////////

    if (isset($INPUT['load'])) {
        // Prepare insert/update statements.
        $upd_stock = $db->prepare("UPDATE `load` SET `stock` = `stock` - ? WHERE `id` = ?") or fatal_error(mysqli_error($db));
        $ins_load = $db->prepare("
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
                      '{$project_id}', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ") or fatal_error(mysqli_error($db));

        foreach($INPUT['load'] as $idx => $load_cfg) {
            // Input checking.
            foreach($load_cfg as $key => $value) {
                $load_cfg[$key] = $db->escape_string($value);
            }

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
                ") or fatal_error(mysqli_error($db));
                
                $load_data = $result->fetch_assoc();
                $result->free();
            } else {
                $load_data = $INPUT['custom'][$idx];
                // Input checking.
                foreach($load_data as $key => $value) {
                    $load_data[$key] = $db->escape_string($value); // FIXME: Harden against missing input.
                }
                $load_data['id'] = 'NULL'; // Not available.
                $load_data['description'] = ''; // Not set in form.
            }

            // Insert data.
            $sell = (isset($load_cfg['sell']) ? 1 : 0);
            $ins_load->bind_param('issdsddiddi'
                , $load_data['id']
                , $load_data['name']
                , $load_data['description']
                , $load_data['power']
                , $load_data['type']
                , $load_data['voltage']
                , $load_data['price']
                , $load_cfg['amount']
                , $load_cfg['dayhours']
                , $load_cfg['nighthours']
                , $sell
            ) or fatal_error(mysqli_error($db));
            $ins_load->execute() or fatal_error(mysqli_error($db));

            // Update stock.
            if ($sell and $load_cfg['product'] != 'custom') { // Only if we sell and have the load in the database.
                $upd_stock->bind_param('ii', $load_cfg['amount'], $load_data['id']) or fatal_error(mysqli_error($db));
                $upd_stock->execute() or fatal_error(mysqli_error($db));
            }
        }

        $ins_load->close();
        $upd_stock->close();
    }

    //////////// Panel ////////////

    if (isset($INPUT['panel']) and count($INPUT['panel']) > 0) {
        // Get ids.
        $panel_data = unique_product($INPUT['panel'], $db);
        $panel_id = array_keys($panel_data);

        // NOTE: Probably we're faster to search / insert entries individually (see mysql insert..select man page); But this is a pain in the arse to code.
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
        ") or fatal_error(mysqli_error($db));
    
        // Update stock and amount.
        $upd_stock  = $db->prepare("UPDATE `panel` SET `stock` = `stock` - ? WHERE `id` = ?") or fatal_error(mysqli_error($db));
        $upd_amount = $db->prepare("UPDATE `project_panel` SET `amount` = ? WHERE `panel` = ? AND `project` = $project_id") or fatal_error(mysqli_error($db));
        foreach($panel_data as $id => $amount) {
            // project_id and panel id should be unique as multiple entries of the same panel in the same project just increase the amount (guaranteed by unique_product)

            $upd_amount->bind_param('ii', $amount, $id) or fatal_error(mysqli_error($db));
            $upd_amount->execute() or fatal_error(mysqli_error($db));

            $upd_stock->bind_param('ii', $amount, $id) or fatal_error(mysqli_error($db));
            $upd_stock->execute() or fatal_error(mysqli_error($db));
        }

        $upd_stock->close();
        $upd_amount->close();
    }

    //////////// Battery ////////////

    if (isset($INPUT['battery']) and count($INPUT['battery']) > 0) {
        // Get ids.
        $battery_data = unique_product($INPUT['battery'], $db);
        $battery_id = array_keys($battery_data);

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
        ") or fatal_error(mysqli_error($db));
    
        // Update stock and amount.
        $upd_stock  = $db->prepare("UPDATE `battery` SET `stock` = `stock` - ? WHERE `id` = ?") or fatal_error(mysqli_error($db));
        $upd_amount = $db->prepare("UPDATE `project_battery` SET `amount` = ? WHERE `battery` = ? AND `project` = $project_id") or fatal_error(mysqli_error($db));
        foreach($battery_data as $id => $amount) {
            $upd_amount->bind_param('ii', $amount, $id) or fatal_error(mysqli_error($db));
            $upd_amount->execute() or fatal_error(mysqli_error($db));

            $upd_stock->bind_param('ii', $amount, $id) or fatal_error(mysqli_error($db));
            $upd_stock->execute() or fatal_error(mysqli_error($db));
        }

        $upd_stock->close();
        $upd_amount->close();
    }

    //////////// Inverter ////////////

    if (isset($INPUT['inverter']) and count($INPUT['inverter']) > 0) {
        // Get ids.
        $inverter_data = unique_product($INPUT['inverter'], $db);
        $inverter_id = array_keys($inverter_data);

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
        ") or fatal_error(mysqli_error($db));

        // Update stock and amount.
        $upd_stock  = $db->prepare("UPDATE `inverter` SET `stock` = `stock` - ? WHERE `id` = ?") or fatal_error(mysqli_error($db));
        $upd_amount = $db->prepare("UPDATE `project_inverter` SET `amount` = ? WHERE `inverter` = ? AND `project` = $project_id") or fatal_error(mysqli_error($db));
        foreach($inverter_data as $id => $amount) {
            $upd_amount->bind_param('ii', $amount, $id) or fatal_error(mysqli_error($db));
            $upd_amount->execute() or fatal_error(mysqli_error($db));

            $upd_stock->bind_param('ii', $amount, $id) or fatal_error(mysqli_error($db));
            $upd_stock->execute() or fatal_error(mysqli_error($db));
        }

        $upd_stock->close();
        $upd_amount->close();
    }

    //////////// Controller ////////////

    if (isset($INPUT['controller']) and count($INPUT['controller']) > 0) {
        // Get ids.
        $controller_data = unique_product($INPUT['controller'], $db);
        $controller_id = array_keys($controller_data);

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
        ") or fatal_error(mysqli_error($db));

        // Update stock and amount.
        $upd_stock  = $db->prepare("UPDATE `controller` SET `stock` = `stock` - ? WHERE `id` = ?") or fatal_error(mysqli_error($db));
        $upd_amount = $db->prepare("UPDATE `project_controller` SET `amount` = ? WHERE `controller` = ? AND `project` = $project_id") or fatal_error(mysqli_error($db));
        foreach($controller_data as $id => $amount) {
            $upd_amount->bind_param('ii', $amount, $id) or fatal_error(mysqli_error($db));
            $upd_amount->execute() or fatal_error(mysqli_error($db));

            $upd_stock->bind_param('ii', $amount, $id) or fatal_error(mysqli_error($db));
            $upd_stock->execute() or fatal_error(mysqli_error($db));
        }

        $upd_stock->close();
        $upd_amount->close();
    }

    /******************************* WRAP UP *******************************/

    // Commit all the data at once.
    $db->commit() or fatal_error("TRANSACTION FAILED");

    // Redirect
    header("Location: project_overview.php");
}

/******************************* PAGE OUTPUT *******************************/

// Start the layout.
t_start();

// Create the form.
$budget = array();
?>

<h2>Project summary</h2>

<!-------------------------- LOAD -------------------------->

<h3>Loads</h3>
<?php 
if (isset($INPUT['load']) and isset($INPUT['custom'])) {
    $budget += t_project_loadSummary($INPUT['load'], $INPUT['custom'], $db);
}
?>

<!-------------------------- PANELS -------------------------->

<h3>Panels</h3>

<table cellspacing=0 cellpadding=0 class="project-module-summary">
 <tr class="project-module-head">
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
            $result = $db->query("SELECT `name`, `voltage`, `power`, `price` FROM `panel` WHERE id = '" . $db->escape_string($id) . "'") or fatal_error(mysqli_error($db));
            if ($result->num_rows != 1) { // ID must exist
                continue;
            }
            $data += $result->fetch_assoc();
            $result->free();

            // Table output.
            ?>
                <tr class='project-module-item'>
                    <td><?php echo $data['name']; ?></td>
                    <td class='number'><?php echo $data['amount']; ?></td>
                    <td class='number'><?php echo $data['voltage']; ?></td>
                    <td class='number'><?php echo $data['power']; ?></td>
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

<!-------------------------- BATTERIES -------------------------->

<h3>Batteries</h3>

<table cellspacing=0 cellpadding=0 class="project-module-summary">
 <tr class="project-module-head">
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
            $result = $db->query("SELECT `name`, `voltage`, `capacity`, `dod` * `capacity` AS `ucapacity`, `price` FROM `battery` WHERE id = '" . $db->escape_string($id) . "'") or fatal_error(mysqli_error($db));
            if ($result->num_rows != 1) { // ID must exist
                continue;
            }
            $data += $result->fetch_assoc();
            $result->free();
            
            // Table output.
            ?>
                <tr class='project-module-item'>
                    <td><?php echo $data['name']; ?></td>
                    <td class='number'><?php echo $data['amount']; ?></td>
                    <td class='number'><?php echo $data['voltage']; ?></td>
                    <td class='number'><?php echo $data['capacity']; ?></td>
                    <td class='number'><?php echo $data['ucapacity']; ?></td>
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

<!-------------------------- CONTROLLERS, INVERTERS -------------------------->

<h3>Extra hardware</h3>

<table cellspacing=0 cellpadding=0 class="project-module-summary">
 <tr class="project-module-head">
  <td>Product</td>
  <td>Amount</td>
 </tr>
 <?php
    if (isset($INPUT['inverter'])) {
        foreach($INPUT['inverter'] as $data) {

            // Read database.
            $id = $data['product'];
            $result = $db->query("SELECT `name`, `price` FROM `inverter` WHERE id = '" . $db->escape_string($id) . "'") or fatal_error(mysqli_error($db));
            if ($result->num_rows != 1) { // ID must exist
                continue;
            }
            $data += $result->fetch_assoc();
            $result->free();

            // Table output.
            ?>
                <tr class='project-module-item'>
                    <td><?php echo $data['name']; ?></td>
                    <td class='number'><?php echo $data['amount']; ?></td>
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
            $result = $db->query("SELECT `name`, `price` FROM `controller` WHERE id = '" . $db->escape_string($id) . "'") or fatal_error(mysqli_error($db));
            if ($result->num_rows != 1) { // ID must exist
                continue;
            }
            $data += $result->fetch_assoc();
            $result->free();

            // Table output.
            ?>
                <tr class='project-module-item'>
                    <td><?php echo $data['name']; ?></td>
                    <td class='number'><?php echo $data['amount']; ?></td>
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

<!-------------------------- BUDGET -------------------------->

<h3>Budget</h3>

<table cellspacing=0 cellpadding=0 class="project-module-summary">
 <tr class='project-budget-head'>
  <td>Product</td>
  <td>Amount</td>
  <td>Price per Unit<?php echo T_Units::CFA; ?></td>
  <td>Price<?php echo T_Units::CFA; ?></td>
 </tr>
<?php
    $total = 0;
    foreach($budget as $data) {
        $subtotal = $data['price'] * $data['amount'];
        $total += $subtotal;
        ?>
            <tr class='project-budget-item'>
                <td><?php echo $data['product']; ?></td>
                <td class='number'><?php echo $data['amount']; ?></td>
                <td class='number'><?php echo $data['price']; ?></td>
                <td class='number'><?php echo $subtotal; ?></td>
            </tr>
        <?php
    }
?>
 <tr class='project-budget-total'>
  <td>Total</td>
  <td></td>
  <td></td>
  <td class='number calculation-result'><?php echo $total; ?></td>
 </tr>
</table>

<!-------------------------- PROJECT METADATA -------------------------->

<h2>Project Metadata</h2>
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST">
<?php echo isset($_POST['battery']) ? "<input type='hidden' name='battery' value='{$_POST['battery']}' />" : ''; ?>
<?php echo isset($_POST['panel']) ? "<input type='hidden' name='panel' value='{$_POST['panel']}' />" : ''; ?>
<?php echo isset($_POST['inverter']) ? "<input type='hidden' name='inverter' value='{$_POST['inverter']}' />" : ''; ?>
<?php echo isset($_POST['controller']) ? "<input type='hidden' name='controller' value='{$_POST['controller']}' />" : ''; ?>
<?php echo isset($_POST['load']) ? "<input type='hidden' name='load' value='{$_POST['load']}' />" : ''; ?>
<?php echo isset($_POST['custom']) ? "<input type='hidden' name='custom' value='{$_POST['custom']}' />" : ''; ?>
<?php echo isset($_POST['sunhours']) ? "<input type='hidden' name='sunhours' value='{$_POST['sunhours']}' />" : ''; ?>

<table cellspacing=0 cellpadding=0 class="form-table">
  <tr>
    <td class="form-table-key">Name</td>
    <td class="form-table-value"><input type="text" name="project_name" value="" required /></td>
  </tr>
  <tr>
    <td class="form-table-key">Description</td>
    <td class="form-table-value"><textarea cols=60 rows=5 name="description"></textarea></td>
  </tr>
  <tr>
    <td class="form-table-key">Location</td>
    <td class="form-table-value"><input type="text" name="location" value="" required /></td>
  </tr>
  <tr>
    <td class="form-table-key">Client name</td>
    <td class="form-table-value"><input type="text" name="client_name" value="" required /></td>
  </tr>
  <tr>
    <td class="form-table-key">Client phone</td>
    <td class="form-table-value"><input type="phone" name="client_phone" value="" /></td>
  </tr>
  <tr>
    <td class="form-table-key">Responsible person</td>
    <td class="form-table-value"><input type="text" name="responsible_name" value="" required /></td>
  </tr>
  <tr>
    <td class="form-table-key">Responsible phone</td>
    <td class="form-table-value"><input type="phone" name="responsible_phone" value="" /></td>
  </tr>
  <tr>
    <td class="form-table-key">Delivery date</td>
    <td class="form-table-value"><input type="date" name="delivery" value="" /></td>
  </tr>
  <tr>
    <td class="form-table-key">Comments</td>
    <td class="form-table-value"><textarea cols=60 rows=5 name="comment"></textarea></td>
  </tr>
  <tr>
    <td class="form-table-action" colspan=2><button type="submit" name="doCreateProject" value="Create project">Create project</button></td>
  </tr>
</table>
</form>

<?php
$db->close();
t_end();
?>
