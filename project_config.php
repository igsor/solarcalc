<?php

require_once("init.php");

// Argument checking
if (!isset($_POST['load']) or !isset($_POST['sunhours'])) {
    t_argumentError();
}

// POST cleanup
if (!isset($_POST['custom'])) {
    $_POST['custom'] = array();
}

$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or die(mysqli_connect_error());

t_start();
t_loadTable($_POST['load'], $_POST['custom'], $db);

// $commonList = mergeLoads($_POST['load'], $_POST['custom']);
// compute some stuff

$solution = solarcalc($_POST['sunhours'], $_POST['load'], $_POST['custom']);
?>

<table cellspacing=0 cellpadding=0 class="configtable">
    <tr class="confighead">
        <td>Panel</td>
        <td>Battery</td>
        <td>Controller</td>
        <td>Inverter</td>
    </tr>
<?php
foreach ($solution as $idx => $currentsol) {

    // CONFIGROW: describes number and type of possible panel/battery/controller/inverter configurations
    echo "<tr class='configrow' onclick='tableToggler(this, \"shortTable_$idx\", \"longTable_$idx\");'>";
        
    echo "  <td>";
    t_createOverview($currentsol, 'panel', $db);
    echo "  </td>";
    
    echo "  <td>";
    t_createOverview($currentsol, 'battery', $db);
    echo "  </td>";
    
    echo "  <td>";
    t_createOverview($currentsol, 'controller', $db);
    echo "  </td>";
    
    echo "  <td>";
    t_createOverview($currentsol, 'inverter', $db);
    echo "  </td>";

    echo "  </td>";
    echo "</tr>";

?>
    <!-- Add data to short overview table -->

    <tr class='statsrow'> 
        <td colspan=4> 
            <table cellpadding=0 cellspacing=0 class='tbl_detail' style='display:table-row' id='shortTable_<?php echo $idx; ?>'>
                <tr>
                    <td class='tbl_key'>Input power<? echo T_Units::V; ?></td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['inputPower']; ?></td>
                    <td class='tbl_key'>Total price<? echo T_Units::CFA; ?></td>
                    <td class="tbl_value"><?php echo number_format($currentsol['numbers']['totalPrice'], "0", ".", "'"); ?></td>
                  </tr>
                  <tr>
                    <td class='tbl_key'>Battery capacity<? echo T_Units::Ah; ?></td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['batteryCapacity']; ?></td> 
                    <td class='tbl_key'>Price per kwh<? echo T_Units::DOL; ?> </td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['pricekWh']; ?></td>
                  </tr>
                  <tr>
                    <td class='tbl_key'>Expected lifetime<? echo T_Units::Y; ?></td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['lifetime']; ?></td> 
                    <td class='tbl_key'>In stock</td>
                    <td class='tbl_value'><?php echo $currentsol['numbers']['inStock']; ?></td>
                  </tr>
            </table>

            <!--  Add data to long overview table -->
            <table cellpadding=0 cellspacing=0 class='tbl_detail' style='display:none' id='longTable_<?php echo $idx; ?>'>
                <tr>
                    <td class="tbl_key">Input power<? echo T_Units::V; ?></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['inputPower']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Total price<? echo T_Units::CFA; ?></td>
                    <td class="tbl_value"><?php echo number_format($currentsol['numbers']['totalPrice'], "0", ".", "'"); ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Battery capacity<? echo T_Units::Ah; ?></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['batteryCapacity']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Price per kwh<? echo T_Units::DOL; ?></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['pricekWh']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Expected lifetime<? echo T_Units::Y; ?></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['lifetime']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">In stock</td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['inStock']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Battery reserve<? echo T_Units::Ah; ?></td>
                    <td class="tbl_value"><?php echo $currentsol['numbers']['batteryReserve']; ?></td>
                </tr>
                <tr>
                    <td class="tbl_key">Price detail<? echo T_Units::CFA; ?></td>
                    <td class="tbl_value">
                        <table class="tbl_detail">

                            <?php
                            t_priceDetail($currentsol, 'panel', $db);
                            t_priceDetail($currentsol, 'battery', $db);
                            t_priceDetail($currentsol, 'controller', $db);
                            t_priceDetail($currentsol, 'inverter', $db);
                            ?>

                        </table>
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

<?php
$db->close();
t_end();
?>
