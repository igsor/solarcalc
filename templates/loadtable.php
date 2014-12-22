<?php


function t_loadTable($load, $custom, $db) {

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
            $query     = "SELECT  `name`, `power`, `price` FROM `load` WHERE `id` = '{$element['product']}'";
            $result    = mysql_query($query, $db) or die(mysql_error());
            $name      = mysql_fetch_assoc($result);
            $loadname  = $name["name"];
            $loadpower = $name["power"];
            $loadprice = $name["price"];
           
            echo "<td>$loadname</td>";
            echo "<td>{$element['amount']}</td>";
            echo "<td>{$element['dayhours']}</td>";
            echo "<td>{$element['nighthours']}</td>";
            echo "<td>$loadpower</td>";

            if (key_exists("sell",$element)) {
                array_push($price, array (
                    "product" => $loadname,
                    "amount"  => $element["amount"],
                    "price"   => $loadprice,
                    ) );
            }
    
        } else {
    
            echo "<td>{$custom[$key]['name']}</td>";
            echo "<td>{$element['amount']}</td>";
            echo "<td>{$element['dayhours']}</td>";
            echo "<td>{$element['nighthours']}</td>";
            echo "<td>{$custom[$key]['power']}</td>";

            if (key_exists("sell",$element)) {
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



?>
