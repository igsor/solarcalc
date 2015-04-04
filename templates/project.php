<?php

function t_project_editableModule($name, $table_fu)
{
    ?>
        
        <a  onclick="toggleVisibility(document.getElementById('detail<?php echo $name; ?>'), 'table')"><h3 class="project-module-title"><?php echo $name; ?></h3></a>

        <div class="project-module-detail" id="detail<?php echo $name; ?>" style="display: none">
        <?php $table_fu(); ?>
        </div>
        <br/>
    <?php
}

function t_project_loadSummary($cload) {
    $price = array();

    echo '
    <table cellspacing=0 cellpadding=0 class="project-module-summary">
        <tr class="project-module-head">
            <td ' . t_helptext('stock') . '>Product</td>
            <td ' . t_helptext('amount') . '>Amount</td>
            <td ' . t_helptext('daytime') . '>Day time ' . T_Units::H . '</td>
            <td ' . t_helptext('nighttime') . '>Night time ' . T_Units::H . '</td>
            <td ' . t_helptext('power') . '>Power ' . T_Units::W . '</td>
        </tr>';
    
    foreach ($cload as $element) {
        echo '<tr class="project-module-item">';
        echo "<td>{$element['name']}</td>";
        echo "<td class='number'>{$element['amount']}</td>";
        echo "<td class='number'>{$element['dayhours']}</td>";
        echo "<td class='number'>{$element['nighthours']}</td>";
        echo "<td class='number'>{$element['power']}</td>";

        if (isset($element['sell'])) {
            array_push($price, array (
                        "product" => $element["name"],
                        "amount"  => $element["amount"],
                        "price"   => $element["price"],
                        ) );
        }


        echo '</tr>';
    }

    echo '</table>';

    return $price;
}

function t_project_moduleSummary($variable, $string, $database) {
    $Overview = array();
    foreach ($variable[$string] as $value) {
        $query = "SELECT `name` FROM `$string` WHERE `id` = ". $database->escape_string($value['product']);
        $result = $database->query($query) or fatal_error(mysqli_error($database));
        $name = $result->fetch_assoc();
        $result->free();
        array_push($Overview, "<div class='amount'>{$value['amount']}x</div> {$name['name']}");
    };
    echo join('<br/>', $Overview);
}

function t_project_modulePrice($module, $table, $db) {
    foreach ($module as $id => $amount) {
        $query = "SELECT `name`, `price` FROM `{$table}` WHERE `id` = " . $db->escape_string($id);
        $result = $db->query($query) or fatal_error(mysqli_error($db));
        $piece = $result->fetch_assoc();
        $result->free();
        
        echo "<tr class='project-budget-module-item'><td><div class='amount'>{$amount}x</div>{$piece['name']}:</td><td style='text-align:right'>" . cannonical_number($piece['price'] * $amount) . "</td></tr>";
    };
}

function t_project_edit($submitButtonName, $submitButtonValue, $data=null) {
    if ($data === null) {
        $data = array_with_defaults(['name', 'description', 'location', 'client_name', 'client_phone', 'responsible_name', 'responsible_phone', 'delivery_date', 'comments', 'work_allowance', 'material_allowance']);
    }
    ?>
        <table cellspacing=0 cellpadding=0 class="form-table">
          <tr>
            <td class="form-table-key" <?php echo t_helptext('name'); ?>>Name</td>
            <td class="form-table-value"><input type="text" name="name" value="<?php echo $data['name']; ?>" required /></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('description'); ?>>Description</td>
            <td class="form-table-value"><textarea cols=60 rows=5 name="description"><?php echo $data['description']; ?></textarea></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('location'); ?>>Location</td>
            <td class="form-table-value"><input type="text" name="location" value="<?php echo $data['location']; ?>" required /></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('material_allowance'); ?>>Material allowance</td>
            <td class="form-table-value"><input type="number" name="material_allowance" value="<?php echo $data['material_allowance']; ?>" required min="0" pattern="\d+" onBlur="updateBudget(this, 'budget_material')" /></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('work_allowance'); ?>>Work allowance</td>
            <td class="form-table-value"><input type="number" name="work_allowance" value="<?php echo $data['work_allowance']; ?>" required min="0" pattern="\d+" onBlur="updateBudget(this, 'budget_work')" /></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('client_name'); ?>>Client name</td>
            <td class="form-table-value"><input type="text" name="client_name" value="<?php echo $data['client_name']; ?>" required /></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('client_phone'); ?>>Client phone</td>
            <td class="form-table-value"><input type="phone" name="client_phone" value="<?php echo $data['client_phone']; ?>" /></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('responsible_person'); ?>>Responsible person</td>
            <td class="form-table-value"><input type="text" name="responsible_name" value="<?php echo $data['responsible_name']; ?>" required /></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('responsible_phone'); ?>>Responsible phone</td>
            <td class="form-table-value"><input type="phone" name="responsible_phone" value="<?php echo $data['responsible_phone']; ?>" /></td>
          </tr>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('delivery_date'); ?>>Delivery date</td>
            <td class="form-table-value"><input type="date" name="delivery_date" value="<?php echo $data['delivery_date']; ?>" /></td>
          </tr>
          <?php if (key_exists('status', $data)) { ?>
            <tr>
              <td class="form-table-key" <?php echo t_helptext('status'); ?>>Status</td>
              <td class="form-table-value">
                <select name="status" id="project-status">
                  <option value="planned"  <?php echo ($data['status'] == 'planned'  ?' selected':''); ?>>Planned</option>
                  <option value="executing"<?php echo ($data['status'] == 'executing'?' selected':''); ?>>Executing</option>
                  <option value="completed"<?php echo ($data['status'] == 'completed'?' selected':''); ?>>Completed</option>
                  <option value="cancelled"<?php echo ($data['status'] == 'cancelled'?' selected':''); ?>>Cancelled</option>
                </select>
              </td>
            </tr>
          <?php } ?>
          <tr>
            <td class="form-table-key" <?php echo t_helptext('comments'); ?>>Comments</td>
            <td class="form-table-value"><textarea cols=60 rows=5 name="comments"><?php echo $data['comments']; ?></textarea></td>
          </tr>
          <tr>
            <td class="form-table-action" colspan=2><button type="submit" id="<?php echo $submitButtonName; ?>" name="<?php echo $submitButtonName; ?>" value="on"><?php echo $submitButtonValue; ?></button></td>
          </tr>
        </table>
    <?php
}

// Print a budget from data in $budget - an array of an associative array with keys product, amount, price.
function t_project_budget($budget, $work=0, $material=0)
{
    $total = $work + $material;
    ?>
        <table cellspacing=0 cellpadding=0 class="project-module-summary">
         <tr class='project-budget-head'>
          <td<?php echo t_helptext('product'); ?>>Product</td>
          <td<?php echo t_helptext('amount'); ?>>Amount</td>
          <td<?php echo t_helptext('price_per_unit'); ?>>Price per Unit<?php echo T_Units::DOL; ?></td>
          <td<?php echo t_helptext('price'); ?>>Price<?php echo T_Units::DOL; ?></td>
         </tr>
         <?php
             foreach($budget as $data) {
                 $subtotal = $data['price'] * $data['amount'];
                 $total += $subtotal;
                 ?>
                     <tr class='project-budget-item'>
                         <td><?php echo $data['product']; ?></td>
                         <td class='number'><?php echo cannonical_number($data['amount']); ?></td>
                         <td class='number'><?php echo cannonical_number($data['price']); ?></td>
                         <td class='number'><?php echo cannonical_number($subtotal); ?></td>
                     </tr>
                 <?php
             }
         ?>
         <tr class='project-budget-item'>
            <td>Material allowance</td>
            <td class='number'><?php echo cannonical_number(1); ?></td>
            <td class='number'><?php echo cannonical_number($material); ?></td>
            <td id='budget_material' class='number'><?php echo cannonical_number($material); ?></td>
         </tr>
         <tr class='project-budget-item'>
            <td>Work allowance</td>
            <td class='number'><?php echo cannonical_number(1); ?></td>
            <td class='number'><?php echo cannonical_number($work); ?></td>
            <td id='budget_work' class='number'><?php echo cannonical_number($work); ?></td>
         </tr>
         <tr class='project-budget-total'>
          <td>Total</td>
          <td></td>
          <td></td>
          <td id='budget_total' class='number calculation-result'><?php echo cannonical_number($total); ?></td>
         </tr>
        </table>
    <?php
}

function t_project_characteristics($configData, $db)
{
    ?>
    <table cellpadding=0 cellspacing=0>

        <!--======================================== BUSINESS DATA ========================================-->

        <tr>
            <td class="table-key project-data-title" colspan=2>Business data</td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('total_price'); ?>>Total price<?php echo T_Units::DOL; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->totalPrice); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('price_per_kWh'); ?>>Price per kwh<?php echo T_Units::DOL; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->pricePerkWh, 2); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('price_detail'); ?>>Price detail<?php echo T_Units::DOL; ?></td>
            <td class="table-value">
                <table cellspacing=0 cellpadding=0 class='project-budget-module'>
                    <?php
                        t_project_modulePrice($configData->panel, 'project_panel', $db);
                        t_project_modulePrice($configData->battery, 'project_battery', $db);
                        t_project_modulePrice($configData->controller, 'project_controller', $db);
                        t_project_modulePrice($configData->inverter, 'project_inverter', $db);
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('number_of_items'); ?>>Number of items</td>
            <td class="table-value"><?php echo $configData->numItems; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('number_of_items_sold'); ?>>Number of items sold</td>
            <td class="table-value"><?php echo $configData->numItemsSold; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('expected_lifetime'); ?>>Expected lifetime<?php echo T_Units::Y; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->expectedLifetime, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('expected_lifetime_per_module'); ?>>Expected lifetime per Module<?php echo T_Units::Y; ?></td>
            <td class="table-value">NA</td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('module_cost_per_year'); ?>>Module cost per year<?php echo T_Units::DOL; ?></td>
            <td class="table-value">NA</td>
        </tr>

        <!--======================================== POWER DATA ========================================-->

        <tr>
            <td class="table-key project-data-title" colspan=2>Power data</td>
        </tr>

        <!------------------------------------------ POWER MEASURES ------------------------------------------>

        <tr>
            <td class="table-key" <?php echo t_helptext('total_panel_power'); ?>>Total panel power<?php echo T_Units::W; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->panelPower, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('unused_panel_power'); ?>>Unused panel power<?php echo T_Units::W; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->panelReserve, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('consumed_power_day'); ?>>Peak consumed power (day)<?php echo T_Units::W; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->consumedDayPower, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('consumed_power_night'); ?>>Peak consumed power (night)<?php echo T_Units::W; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->consumedNightPower, 1); ?></td>
        </tr>

        <!------------------------------------------ ENERGY MEASURES ------------------------------------------>

        <tr>
            <td class="table-key" <?php echo t_helptext('total_panel_energy'); ?>>Total panel energy<?php echo T_Units::Wh; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->panelEnergy, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('consumed_energy_day'); ?>>Consumed energy (direct)<?php echo T_Units::Wh; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->consumedDayEnergy, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('consumed_energy_battery'); ?>>Consumed energy (via battery)<?php echo T_Units::Wh; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->batteryConsumedEnergy, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('battery_input_energy'); ?>>Battery input energy<?php echo T_Units::Wh; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->batteryInputEnergy, 1); ?></td>
        </tr>

        <!------------------------------------------ BATTERY MEASURES ------------------------------------------>

        <tr>
            <td class="table-key" <?php echo t_helptext('total_battery_capacity'); ?>>Total battery capacity<?php echo T_Units::Ah; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->batteryCapacity, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('consumed_battery_capacity'); ?>>Consumed battery capacity<?php echo T_Units::Ah; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->batteryCapacityConsumed, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('unused_battery_capacity'); ?>>Unused battery capacity<?php echo T_Units::Ah; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->batteryReserve, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('battery_energy_reserve'); ?>>Battery charge energy reserve<?php echo T_Units::Wh; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->batteryEnergyReserve, 1); ?></td>
        </tr>

         <!------------------------------------------ BATTERY MEASURES ------------------------------------------>

        <tr>
            <td class="table-key" <?php echo t_helptext('charge_current'); ?>>Charging current<?php echo T_Units::A; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->batteryChargeCurrent, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('current_day'); ?>>Peak current (day)<?php echo T_Units::A; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->currentDay, 1); ?></td>
        </tr>

        <tr>
            <td class="table-key" <?php echo t_helptext('current_night'); ?>>Peak current (night)<?php echo T_Units::A; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->currentNight, 1); ?></td>
        </tr>

         <!------------------------------------------ TIME MEASURES ------------------------------------------>

        <tr>
            <td class="table-key" <?php echo t_helptext('time_until_fully_charged'); ?>>Time until fully charged (Min/Max/Avg)<?php echo T_Units::H; ?></td>
            <td class="table-value">
                <?php echo cannonical_number($configData->batteryChargeTimeMin, 1); ?> / 
                <?php echo cannonical_number($configData->batteryChargeTimeMax, 1); ?> / 
                <?php echo cannonical_number($configData->batteryChargeTimeAvg, 1); ?>

            </td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('battery_discharge_time'); ?>>Battery discharge time<?php echo T_Units::H; ?></td>
            <td class="table-value"><?php echo cannonical_number($configData->batteryDischargeTime, 1); ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('charge_discharge_diagram'); ?>>Charge / Discharge diagram</td>
            <td class="table-value">NA</td>
        </tr>

        <!--======================================== CIRCUIT DATA ========================================-->

        <tr>
            <td class="table-key project-data-title" colspan=2>Circuit data</td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('input_voltage'); ?>>Input voltage<?php echo T_Units::V; ?></td>
            <td class="table-value"><?php echo $configData->inputVoltage; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('panels_series'); ?>>Panels serie</td>
            <td class="table-value"><?php echo $configData->panelsSerie; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('panels_parallel'); ?>>Panels parallel</td>
            <td class="table-value"><?php echo $configData->panelsParallel; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('batteries_series'); ?>>Batteries serie</td>
            <td class="table-value"><?php echo $configData->batteriesSerie; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('batteries_parallel'); ?>>Batteries parallel</td>
            <td class="table-value"><?php echo $configData->batteriesParallel; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('boostbuck_required'); ?>>Boostbuck required</td>
            <td class="table-value"><?php echo $configData->boostbuck; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('DC_network'); ?>>DC network</td>
            <td class="table-value"><?php echo $configData->hasDC; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('DC_devices'); ?>>DC devices</td>
            <td class="table-value"><?php echo $configData->numDC; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('DC_load'); ?>>DC load<?php echo T_Units::W; ?></td>
            <td class="table-value"><?php echo $configData->loadDC; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('AC_network'); ?>>AC network</td>
            <td class="table-value"><?php echo $configData->hasAC; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('AC_devices'); ?>>AC devices</td>
            <td class="table-value"><?php echo $configData->numAC; ?></td>
        </tr>
        <tr>
            <td class="table-key" <?php echo t_helptext('AC_load'); ?>>AC load<?php echo T_Units::W; ?></td>
            <td class="table-value"><?php echo $configData->loadAC; ?></td>
        </tr>
    </table>
    <?php
}

// EOF //
