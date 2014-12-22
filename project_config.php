<?php

require_once("init.php");

//require("test/template_tmp.php");

// POST cleanup
if (isset($_POST['load']['%i'])) {
    unset($_POST['load']['%i']);
}

if (isset($_POST['custom']['%i'])) {
    unset($_POST['custom']['%i']);
}

if (isset($_POST['custom']) and isset($_POST['load'])) {
    foreach($_POST['load'] as $key => $values) {
        if ($values['product'] != 'custom') {
            unset($_POST['custom'][$key]);
        }
    }
}

$sunhours = $_POST["sunhours"];
$load     = $_POST["load"];
$custom   = $_POST["custom"];
$db = mysql_connect($DB_HOST, $DB_USER, $DB_PASS) or die(mysql_error());
mysql_select_db($DB_NAME, $db) or die(mysql_error());

t_start();
t_loadTable($load, $custom, $db);

// compute some stuff

$solution = solarcalc($sunhours, $load, $custom);
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
    $panelOverview = array();
    foreach ($currentsol["panel"] as $value) {
        $query = "SELECT  `name` FROM `panel` WHERE `id` = '{$value['product']}'";
        $result = mysql_query($query, $db) or die(mysql_error());
        $name = mysql_fetch_assoc($result);
        $panelName = $name["name"];
        array_push($panelOverview, "<div class='amount'> {$value['amount']} x</div> $panelName");
    };
    echo join('<br>', $panelOverview);
    echo "  </td>";

    
    echo "  <td>";
    $batteryOverview = array();
    foreach ($currentsol["battery"] as $value) {
        $query = "SELECT  `name` FROM `battery` WHERE `id` = '{$value['product']}'";
        $result = mysql_query($query, $db) or die(mysql_error());
        $name = mysql_fetch_assoc($result);
        $batteryName = $name["name"];
        array_push($batteryOverview, "<div class='amount'> {$value['amount']} x</div> $batteryName");
    };
    echo join('<br>', $batteryOverview);
    echo "  </td>";


    echo "  <td>";
    $controllerOverview = array();
    foreach ($currentsol["controller"] as $value) {
        $query = "SELECT  `name` FROM `controller` WHERE `id` = '{$value['product']}'";
        $result = mysql_query($query, $db) or die(mysql_error());
        $name = mysql_fetch_assoc($result);
        $controllerName = $name["name"];
        array_push($controllerOverview, "<div class='amount'> {$value['amount']} x</div> $controllerName");
    };
    echo join('<br>', $controllerOverview);
    echo "  </td>";


    echo "  <td>";
    $inverterOverview = array();
    foreach ($currentsol["inverter"] as $value) {
        $query = "SELECT  `name` FROM `inverter` WHERE `id` = '{$value['product']}'";
        $result = mysql_query($query, $db) or die(mysql_error());
        $name = mysql_fetch_assoc($result);
        $inverterName = $name["name"];
        array_push($inverterOverview, "<div class='amount'> {$value['amount']} x</div> $inverterName");
    };
    echo join('<br>', $inverterOverview);
     // add all inverters of this solution

    echo "  </td>";
    echo "</tr>";

?>
    <!-- Add data to short overview table -->

    <tr class='statsrow'> 
        <td colspan=4> 
            <table cellpadding=0 cellspacing=0 class='tbl_detail'>
                <tr>
                    <td class='tbl_key'>Input power <div class='unit' title='Volt'>[V]</div></td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['inputPower']; ?></td>
                    <td class='tbl_key'>Total price <div class='unit' title='Central African Franc'>[CFA]</div></td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['totalPrice']; ?></td>
                  </tr>
                  <tr>
                    <td class='tbl_key'>Battery capacity <div class='unit' title='Ampere hours'>[Ah]</div></td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['batteryCapacity']; ?></td> 
                    <td class='tbl_key'>Price per kwh <div class='unit' title='Dollar'>[$]</div></td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['pricekWh']; ?></td>
                  </tr>
                  <tr>
                    <td class='tbl_key'>Expected lifetime <div class='unit' title='Year'>[Y]</div></td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['lifetime']; ?></td> 
                    <td class='tbl_key'>In stock</td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['inStock']; ?></td>
                  </tr>
            </table>

            <!--  Add data to long overview table -->
            <table cellpadding=0 cellspacing=0 class='tbl_detail'>
                <tr>
                    <td class="tbl_key">Input power <div class="unit" title="Volt">[V]</div></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['inputPower']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Total price <div class="unit" title="Central African Franc">[CFA]</div></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['totalPrice']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Battery capacity <div class="unit" title="Ampere hours">[Ah]</div></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['batteryCapacity']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Price per kwh <div class="unit" title="Dollar">[$]</div></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['pricekWh']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Expected lifetime <div class="unit" title="Year">[Y]</div></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['lifetime']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">In stock</td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['inStock']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Battery reserve <div class="unit" title="Ampere hours">[Ah]</div></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['batteryReserve']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Price detail <div class="unit" title="Central African Franc">[CFA]</div></td>
                    <td class="tbl_value">
                        Panel 1:   1000<br>
                        Panel 2:   1000<br>
                        Battery 1: 1000<br>
                        Battery 2: 1000<br>
                        Controller A: 119000 
                    </td>
                </tr>
                <tr class="buttonrow">
                    <td colspan=2>
                        <form action="project_create.php" method="post">
                        <input type="submit" name="chosenSolution" value="To infinity and beyond... >>" />
                        <input type="hidden" name="load" value='<?php echo serialize($_POST['load']); ?>' />
                        <input type="hidden" name="custom" value='<?php echo serialize($_POST['custom']); ?> ' />
                        <input type="hidden" name="sunhours" value='<?php echo $_POST['sunhours']; ?>' />
                        <input type="hidden" name="panel" value='<?php echo serialize($currentsol['panel']); ?>' />
                        <input type="hidden" name="battery" value='<?php echo serialize($currentsol['battery']); ?>' />
                        <input type="hidden" name="controller" value='<?php echo serialize($currentsol['controller']); ?>' />
                        <input type="hidden" name="inverter" value='<?php echo serialize($currentsol['inverter']); ?>' />
                        </form>
                </tr>

            </table>
        </td>
    </tr>

    <tr> <td colspan=4> </td> </tr>


<?php
}

echo "</table>";

?>



<!--
<form action="project_create.php" method="post">
<input type="submit" name="chosenSolution" value="To infinity and beyond... >>" />
<input type="hidden" name="load" value='<?php echo serialize($_POST['load']); ?>' />
<input type="hidden" name="custom" value='<?php echo serialize($_POST['custom']); ?> ' />
<input type="hidden" name="sunhours" value='<?php echo $_POST['sunhours']; ?>' />
<input type="hidden" name="panel" value='<?php echo serialize($chosen['panel']); ?>' />
<input type="hidden" name="battery" value='<?php echo serialize($chosen['battery']); ?>' />
<input type="hidden" name="controller" value='<?php echo serialize($chosen['controller']); ?>' />
<input type="hidden" name="inverter" value='<?php echo serialize($chosen['inverter']); ?>' />
</form>
-->
<?php
mysql_close($db);
t_end();
?>
