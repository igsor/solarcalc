<?php


function t_loadTable($load, $custom, $database) {

    $price = array();

    echo '
    <table cellspacing=0 cellpadding=0 class="loadtable">
        <tr class="tablehead">
            <td>Product</td>
            <td>Amount</td>
            <td>Day time <div class="unit" title="Hour">[H]</div></td>
            <td>Night time <div class="unit" title="Hour">[H]</div></td>
            <td>Power <div class="unit" title="Watt">[W]</div><td>
        </tr>';
    
    foreach ($load as $key => $element) {
        echo "<tr class='tablerow'>";
        if ($element["product"] != "custom") {
            $query     = "SELECT  `name`, `power`, `price` FROM `load` WHERE `id` = ". $database->escape_string($element['product']);
            $result    = $database->query($query) or die(mysqli_error($database));
            $data      = $result->fetch_assoc();
            $result->free();
           
            echo "<td>{$data['name']}</td>";
            echo "<td>{$element['amount']}</td>";
            echo "<td>{$element['dayhours']}</td>";
            echo "<td>{$element['nighthours']}</td>";
            echo "<td>{$data['power']}</td>";

            if (isset($element['sell'])) {
                array_push($price, array (
                    "product" => $data['name'],
                    "amount"  => $element["amount"],
                    "price"   => $data['price'],
                    ) );
            }
    
        } else {
    
            echo "<td>{$custom[$key]['name']}</td>";
            echo "<td>{$element['amount']}</td>";
            echo "<td>{$element['dayhours']}</td>";
            echo "<td>{$element['nighthours']}</td>";
            echo "<td>{$custom[$key]['power']}</td>";

            if (isset($element['sell'])) {
                array_push($price, array (
                    "product" => $custom[$key]["name"],
                    "amount"  => $element["amount"],
                    "price"   => $custom[$key]["price"],
                    ) );
            }

            
        }
        echo "</tr>";
    }

    echo '</table>';

    return $price;
};

function t_createOverview($variable, $string, $database) {
    $Overview = array();
    foreach ($variable[$string] as $value) {
        $query = "SELECT `name` FROM `$string` WHERE `id` = ". $database->escape_string($value['product']);
        $result = $database->query($query) or die(mysqli_error($database));
        $name = $result->fetch_assoc();
        $result->free();
        array_push($Overview, "<div class='amount'>{$value['amount']}x</div> {$name['name']}");
    };
    echo join('<br/>', $Overview);
};


function t_priceDetail($variable, $string, $database ) {
    foreach ($variable[$string] as $value) {
        $query = "SELECT `name`,`price` FROM `$string` WHERE `id` = " . $database->escape_string($value['product']);
        $result = $database->query($query) or die(mysqli_error($database));
        $name = $result->fetch_assoc();
        $result->free();

        echo "<tr><td>{$value['amount']}x{$name['name']}:</td><td style='text-align:right'>" . number_format($name['price'], "0", ".", "'") . "</td></tr>";
    };
};

?>
