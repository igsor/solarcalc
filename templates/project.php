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

function t_project_loadSummary($load, $custom, $database) {
    $price = array();

    echo '
    <table cellspacing=0 cellpadding=0 class="project-module-summary">
        <tr class="project-module-head">
            <td>Product</td>
            <td>Amount</td>
            <td>Day time ' . T_Units::H . '</td>
            <td>Night time ' . T_Units::H . '</td>
            <td>Power ' . T_Units::W . '</td>
        </tr>';
    
    foreach ($load as $key => $element) {
        echo '<tr class="project-module-item">';
        if ($element["product"] != "custom") {
            $query     = "SELECT  `name`, `power`, `price` FROM `load` WHERE `id` = ". $database->escape_string($element['product']);
            $result    = $database->query($query) or fatal_error(mysqli_error($database));
            $data      = $result->fetch_assoc();
            $result->free();
           
            echo "<td>{$data['name']}</td>";
            echo "<td class='number'>{$element['amount']}</td>";
            echo "<td class='number'>{$element['dayhours']}</td>";
            echo "<td class='number'>{$element['nighthours']}</td>";
            echo "<td class='number'>{$data['power']}</td>";

            if (isset($element['sell'])) {
                array_push($price, array (
                    "product" => $data['name'],
                    "amount"  => $element["amount"],
                    "price"   => $data['price'],
                    ) );
            }
    
        } else {
    
            echo "<td>{$custom[$key]['name']}</td>";
            echo "<td class='number'>{$element['amount']}</td>";
            echo "<td class='number'>{$element['dayhours']}</td>";
            echo "<td class='number'>{$element['nighthours']}</td>";
            echo "<td class='number'>{$custom[$key]['power']}</td>";

            if (isset($element['sell'])) {
                array_push($price, array (
                    "product" => $custom[$key]["name"],
                    "amount"  => $element["amount"],
                    "price"   => $custom[$key]["price"],
                    ) );
            }

            
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

function t_project_modulePrice($variable, $string, $database) {
    foreach ($variable[$string] as $value) {
        $query = "SELECT `name`,`price` FROM `$string` WHERE `id` = " . $database->escape_string($value['product']);
        $result = $database->query($query) or fatal_error(mysqli_error($database));
        $name = $result->fetch_assoc();
        $result->free();
        
        echo "<tr class='project-budget-module-item'><td><div class='amount'>{$value['amount']}x</div>{$name['name']}:</td><td style='text-align:right'>" . number_format($name['price'] * $value["amount"], "0", ".", "'") . "</td></tr>";
    };
}

function t_project_edit($submitButtonName, $submitButtonValue, $data=null) {
    if ($data === null) {
        $data = array_with_defaults(['name', 'description', 'location', 'client_name', 'client_phone', 'responsible_name', 'responsible_phone', 'delivery_date', 'comments', 'work_allowance', 'material_allowance']);
    }
    ?>
        <table cellspacing=0 cellpadding=0 class="form-table">
          <tr>
            <td class="form-table-key">Name</td>
            <td class="form-table-value"><input type="text" name="name" value="<?php echo $data['name']; ?>" required /></td>
          </tr>
          <tr>
            <td class="form-table-key">Description</td>
            <td class="form-table-value"><textarea cols=60 rows=5 name="description"><?php echo $data['description']; ?></textarea></td>
          </tr>
          <tr>
            <td class="form-table-key">Location</td>
            <td class="form-table-value"><input type="text" name="location" value="<?php echo $data['location']; ?>" required /></td>
          </tr>
          <tr>
            <td class="form-table-key">Material allowance</td>
            <td class="form-table-value"><input type="number" name="material_allowance" value="<?php echo $data['material_allowance']; ?>" required min="0" pattern="\d+" onBlur="updateBudget(this, 'budget_material')" /></td>
          </tr>
          <tr>
            <td class="form-table-key">Work allowance</td>
            <td class="form-table-value"><input type="number" name="work_allowance" value="<?php echo $data['work_allowance']; ?>" required min="0" pattern="\d+" onBlur="updateBudget(this, 'budget_work')" /></td>
          </tr>
          <tr>
            <td class="form-table-key">Client name</td>
            <td class="form-table-value"><input type="text" name="client_name" value="<?php echo $data['client_name']; ?>" required /></td>
          </tr>
          <tr>
            <td class="form-table-key">Client phone</td>
            <td class="form-table-value"><input type="phone" name="client_phone" value="<?php echo $data['client_phone']; ?>" /></td>
          </tr>
          <tr>
            <td class="form-table-key">Responsible person</td>
            <td class="form-table-value"><input type="text" name="responsible_name" value="<?php echo $data['responsible_name']; ?>" required /></td>
          </tr>
          <tr>
            <td class="form-table-key">Responsible phone</td>
            <td class="form-table-value"><input type="phone" name="responsible_phone" value="<?php echo $data['responsible_phone']; ?>" /></td>
          </tr>
          <tr>
            <td class="form-table-key">Delivery date</td>
            <td class="form-table-value"><input type="date" name="delivery_date" value="<?php echo $data['delivery_date']; ?>" /></td>
          </tr>
          <?php if (key_exists('status', $data)) { ?>
            <tr>
              <td class="form-table-key">Status</td>
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
            <td class="form-table-key">Comments</td>
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
          <td>Product</td>
          <td>Amount</td>
          <td>Price per Unit<?php echo T_Units::DOL; ?></td>
          <td>Price<?php echo T_Units::DOL; ?></td>
         </tr>
         <?php
             foreach($budget as $data) {
                 $subtotal = $data['price'] * $data['amount'];
                 $total += $subtotal;
                 ?>
                     <tr class='project-budget-item'>
                         <td><?php echo $data['product']; ?></td>
                         <td class='number'><?php echo number_format($data['amount'], "0", ".", "'"); ?></td>
                         <td class='number'><?php echo number_format($data['price'], "0", ".", "'"); ?></td>
                         <td class='number'><?php echo number_format($subtotal, "0", ".", "'"); ?></td>
                     </tr>
                 <?php
             }
         ?>
         <tr class='project-budget-item'>
            <td>Material allowance</td>
            <td class='number'><?php echo number_format(1, "0", ".", "'"); ?></td>
            <td class='number'><?php echo number_format($material, "0", ".", "'"); ?></td>
            <td id='budget_material' class='number'><?php echo number_format($material, "0", ".", "'"); ?></td>
         </tr>
         <tr class='project-budget-item'>
            <td>Work allowance</td>
            <td class='number'><?php echo number_format(1, "0", ".", "'"); ?></td>
            <td class='number'><?php echo number_format($work, "0", ".", "'"); ?></td>
            <td id='budget_work' class='number'><?php echo number_format($work, "0", ".", "'"); ?></td>
         </tr>
         <tr class='project-budget-total'>
          <td>Total</td>
          <td></td>
          <td></td>
          <td id='budget_total' class='number calculation-result'><?php echo number_format($total, "0", ".", "'"); ?></td>
         </tr>
        </table>
    <?php
}

// EOF //
