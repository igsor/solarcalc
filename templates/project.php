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

function t_project_modulePrice($variable, $string, $database ) {
    foreach ($variable[$string] as $value) {
        $query = "SELECT `name`,`price` FROM `$string` WHERE `id` = " . $database->escape_string($value['product']);
        $result = $database->query($query) or fatal_error(mysqli_error($database));
        $name = $result->fetch_assoc();
        $result->free();

        echo "<tr class='project-budget-module-item'><td><div class='amount'>{$value['amount']}x</div>{$name['name']}:</td><td style='text-align:right'>" . number_format($name['price'], "0", ".", "'") . "</td></tr>";
    };
}

