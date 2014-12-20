<?php

require_once("init.php");

require("templates/tmp.php");

$suntime = $_POST["suntime"];
$load    = $_POST["load"];
$custom  = $_POST["custom"];

t_start();

?>



<table cellspacing=0 cellpadding=0 class="loadtable">
    <tr class="tablehead">
        <td>Product</td>
        <td>Amount</td>
        <td>Day time <div class="unit" title="Hour">[H]</div></td>
        <td>Night time <div class="unit" title="Hour">[H]</div></td>
        <td>Power <div class="unit" title="Watt">[W]</div><td>
    </tr>
<?php
foreach ($load as $element) {
    echo "<tr class='tablerow'>";
    foreach ($element as $key => $entry) {
        if ($key == 0) {
            // make query to database for name
            $db = mysql_connect($DB_HOST, $DB_USER, $DB_PASS) or die(mysql_error());
            mysql_select_db($DB_NAME, $db) or die(mysql_error());
            $query = "SELECT  `name` FROM `load` WHERE `id` = '$entry'";
            $result = mysql_query($query, $db) or die(mysql_error());
            $name = mysql_fetch_assoc($result);
            $entry = $name["name"];
        }
        echo "<td>$entry</td>";
    }
    echo "</tr>";
}
foreach ($custom as $element) {
    echo "<tr class='tablerow'>";
    foreach ($element as $key => $entry) {
        if ($key == 0) {
            continue;
        }
        echo "<td>$entry</td>";
    }
    echo "</tr>";
}
?>
</table>
<?php
// compute some stuff



?>

<table cellspacing=0 cellpadding=0 class="configtable">
    <tr class="confighead">
        <td>Panel</td>
        <td>Battery</td>
        <td>Controller</td>
        <td>Inverter</td>
    </tr>
<?php
foreach ($solution as $currentsol) {

    // CONFIGROW: describes number and type of possible panel/battery/controller/inverter configurations
    echo "<tr class='configrow'>";
    echo "  <td>";
    $currentpanel = $currentsol["panel"];
    $panelOverview = array();
    foreach ($currentpanel as $key => $value) {
        $query = "SELECT  `name` FROM `panel` WHERE `id` = '$key'";
        $result = mysql_query($query, $db) or die(mysql_error());
        $name = mysql_fetch_assoc($result);
        $entry = $name["name"];
        array_push($panelOverview, "<div class='amount'> $value x</div> $entry");
    };
    echo join('<br>', $panelOverview);
    echo "  </td>";

    echo "  <td>";
    $currentbattery = $currentsol["battery"];
    $batteryOverview = array();
    foreach ($currentbattery as $key => $value) {
        $query = "SELECT  `name` FROM `battery` WHERE `id` = '$key'";
        $result = mysql_query($query, $db) or die(mysql_error());
        $name = mysql_fetch_assoc($result);
        $entry = $name["name"];
        array_push($batteryOverview, "<div class='amount'> $value x</div> $entry");
    };
    echo join('<br>', $batteryOverview);
    echo "  </td>";

    echo "  <td>";
    $currentcontroller = $currentsol["controller"];
    $controllerOverview = array();
    foreach ($currentcontroller as $key => $value) {
        $query = "SELECT  `name` FROM `controller` WHERE `id` = '$key'";
        $result = mysql_query($query, $db) or die(mysql_error());
        $name = mysql_fetch_assoc($result);
        $entry = $name["name"];
        array_push($controllerOverview, "<div class='amount'> $value x</div> $entry");
    };
    echo join('<br>', $controllerOverview);
    echo "  </td>";

    echo "  <td>";
    $currentinverter = $currentsol["inverter"];
    $inverterOverview = array();
    foreach ($currentinverter as $key => $value) {
        $query = "SELECT  `name` FROM `inverter` WHERE `id` = '$key'";
        $result = mysql_query($query, $db) or die(mysql_error());
        $name = mysql_fetch_assoc($result);
        $entry = $name["name"];
        array_push($inverterOverview, "<div class='amount'> $value x</div> $entry");
    };
    echo join('<br>', $inverterOverview);
     // add all inverters of this solution

    echo "  </td>";
    echo "</tr>";

    echo "<tr class='statsrow'> 
            <td colspan=4> 
                <table cellpadding=0 cellspacing=0 class='tbl_detail'>
                  <tr>";
    echo "          <td class='tbl_key'>Input power <div class='unit' title='Volt'>[V]</div></td>";
    echo "          <td class='tbl_value'>$value1</td>";
    echo "          <td class='tbl_key'>Total price <div class='unit' title='Central African Franc'>[CFA]</div></td>";
    echo "          <td class='tbl_value'>$value2</td>
                  </tr>
                  <tr>";
    echo "          <td class='tbl_key'>Battery capacity <div class='unit' title='Ampere hours'>[Ah]</div></td>";
    echo "          <td class='tbl_value'>$value3</td>"; 
    echo "          <td class='tbl_key'>Price per kwh <div class='unit' title='Dollar'>[$]</div></td>";
    echo "          <td class='tbl_value'>$value4</td>
                  </tr>
                  <tr>";
    echo "          <td class='tbl_key'>Expected lifetime <div class='unit' title='Year'>[Y]</div></td> ";
    echo "          <td class='tbl_value'>$value5</td> ";
    echo "          <td class='tbl_key'>In stock</td>";
    echo "          <td class='tbl_value'>$value6</td>
                  </tr>";
    echo"       </table>
            </td>
          </tr>";

    echo "<tr> <td colspan=4> </td> </tr> ";
}
echo "</table>";
?>








<?php
t_end();
?>
