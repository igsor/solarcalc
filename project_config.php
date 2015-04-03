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

foreach ($_POST['load'] as $key => $device) {

    // Correct dayhours. Account sunhours overflow of day hours to night hours .
    if ($device["dayhours"] > $_POST['sunhours']) {
        $_POST['load'][$key]["nighthours"] += $device["dayhours"] - $_POST['sunhours'];
        $_POST['load'][$key]["dayhours"] = $_POST['sunhours'];
    }

    // Handle autonomy. Add total hours usage to night hours.
    if ($device['autonomy'] > 0) {
        $_POST['load'][$key]['nighthours'] += $device['autonomy'] * ($device['dayhours'] + $device['nighthours']);
    }
}


$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME) or fatal_error(mysqli_connect_error());

// Produce the cannonical load.
$cload = cannonical_load($_POST['load'], $_POST['custom'], $db);

t_start();
?>

<h2>Load summary</h2>
<?php t_project_loadSummary($_POST['load'], $_POST['custom'], $db); ?>


<h2>Search results</h2>
<?php

// Search valid configurations.
$solution = solaradapter($cload, $_POST['sunhours'], $db);

?>

<table cellspacing=0 cellpadding=0 class="project-config">
    <tr class="project-config-head">
        <td>Panel</td>
        <td>Battery</td>
        <td>Controller</td>
        <td>Inverter</td>
    </tr>
    <tr class='project-config-spacer'>
        <td colspan=4> </td>
    </tr>
<?php
foreach ($solution as $idx => $currentsol) {

    // Describe number and type of possible panel/battery/controller/inverter configurations.
    echo "<tr class='project-config-item' onclick='toggleConfigOverview(this, \"shortTable_$idx\", \"longTable_$idx\");'>";
        
    echo "  <td>";
    t_project_moduleSummary($currentsol, 'panel', $db);
    echo "  </td>";
    
    echo "  <td>";
    t_project_moduleSummary($currentsol, 'battery', $db);
    echo "  </td>";
    
    echo "  <td>";
    t_project_moduleSummary($currentsol, 'controller', $db);
    echo "  </td>";
    
    echo "  <td>";
    t_project_moduleSummary($currentsol, 'inverter', $db);
    echo "  </td>";

    echo "  </td>";
    echo "</tr>";

?>
    <!-- Add data to short overview table -->

    <tr> 
        <td colspan=4 class='project-config-values'> 
            <table cellpadding=0 cellspacing=0 style='display:table-row' id='shortTable_<?php echo $idx; ?>'>
                <tr>
                    <td class='table-key'>Total price<?php echo T_Units::DOL; ?></td>
                    <td class="table-value"><?php echo number_format($currentsol['numbers']->totalPrice, "0", ".", "'"); ?></td>
                    <td class='table-key'>Price per kwh<?php echo T_Units::DOL; ?> </td>
                    <td class='table-value'><?php echo number_format($currentsol['numbers']->pricePerkWh,2,'.',"'"); ?></td>
                  </tr>
                  <tr>
                    <td class='table-key'>Battery capacity<?php echo T_Units::Ah; ?></td>
                    <td class='table-value'><?php echo $currentsol['numbers']->batteryCapacity; ?></td> 
                    <td class='table-key'>Panel power<?php echo T_Units::W; ?></td>
                    <td class='table-value'><?php echo $currentsol['numbers']->panelPower; ?></td> 
                  </tr>
                  <tr>
                    <td class='table-key'>Expected lifetime<?php echo T_Units::Y; ?></td>
                    <td class='table-value'><?php echo number_format($currentsol['numbers']->expectedLifetime,1,'.',"'"); ?></td> 
                    <td class='table-key'>In stock</td>
                    <td class='table-value'><?php echo $currentsol['numbers']->inStock; ?></td>
                  </tr>
            </table>

            <!--  Add data to long overview table -->
            <table cellpadding=0 cellspacing=0 style='display:none' id='longTable_<?php echo $idx; ?>'>
                <tr>
                    <td>
                        <table cellpadding=0 cellspacing=0 style='display:table-row' id='longTable_<?php echo $idx; ?>'>
                            <tr>
                                <td class="table-key">In stock</td>
                                <td class="table-value"><?php echo $currentsol['numbers']->inStock; ?></td>
                            </tr>
                            <tr>
                                <td class="table-key">Total price<?php echo T_Units::DOL; ?></td>
                                <td class="table-value"><?php echo number_format($currentsol['numbers']->totalPrice, "0", ".", "'"); ?></td>
                            </tr>
                            <tr>
                                <td class="table-key">Price per kwh<?php echo T_Units::DOL; ?></td>
                                <td class="table-value"><?php echo number_format($currentsol['numbers']->pricePerkWh,2,'.',"'"); ?></td>
                            </tr>
                            <tr>
                                <td class="table-key">Price detail<?php echo T_Units::DOL; ?></td>
                                <td class="table-value">
                                    <table cellspacing=0 cellpadding=0 class='project-budget-module'>
            
                                        <?php
                                        t_project_modulePrice($currentsol, 'panel', $db);
                                        t_project_modulePrice($currentsol, 'battery', $db);
                                        t_project_modulePrice($currentsol, 'controller', $db);
                                        t_project_modulePrice($currentsol, 'inverter', $db);
                                        ?>
            
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>     
                        <table cellpadding=0 cellspacing=0 style='display:table-row' id='longTable_<?php echo $idx; ?>'>
                            <tr>
                                <td class="table-key">Input voltage<?php echo T_Units::V; ?></td>
                                <td class="table-value"><?php echo $currentsol['numbers']->inputVoltage; ?></td>
                            </tr>
                            <tr>
                                <td class="table-key">Expected lifetime<?php echo T_Units::Y; ?></td>
                                <td class="table-value"><?php echo number_format($currentsol['numbers']->expectedLifetime,1,'.',"'"); ?></td>
                            </tr>
                            <tr>
                                <td class="table-key">Total battery capacity<?php echo T_Units::Ah; ?></td>
                                <td class="table-value"><?php echo $currentsol['numbers']->batteryCapacity; ?></td>
                            </tr>
                            <tr>
                                <td class="table-key">Unused battery capacity<?php echo T_Units::Ah; ?></td>
                                <td class="table-value"><?php echo number_format($currentsol['numbers']->batteryReserve,1,'.',"'"); ?></td>
                            </tr>
                            <tr>
                                <td class="table-key">Total panel power<?php echo T_Units::W; ?></td>
                                <td class="table-value"><?php echo $currentsol['numbers']->panelPower; ?></td>
                            </tr>
                            <tr>
                                <td class="table-key">Unused panel power<?php echo T_Units::W; ?></td>
                                <td class="table-value"><?php echo number_format($currentsol['numbers']->panelReserve,1,'.',"'"); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="form-table-action">
                    <td colspan=2>
                        <form action="project_create.php" method="post">
                        <button type="submit">To infinity and beyond... >></button>
                        <input type="hidden" name="load" value='<?php echo serialize($_POST['load']); ?>' />
                        <input type="hidden" name="custom" value='<?php echo serialize($_POST['custom']); ?> ' />
                        <input type="hidden" name="sunhours" value='<?php echo $_POST['sunhours']; ?>' />
                        <input type="hidden" name="panel" value='<?php echo serialize($currentsol['panel']); ?>' />
                        <input type="hidden" name="battery" value='<?php echo serialize($currentsol['battery']); ?>' />
                        <input type="hidden" name="controller" value='<?php echo serialize($currentsol['controller']); ?>' />
                        <input type="hidden" name="inverter" value='<?php echo serialize($currentsol['inverter']); ?>' />
                        </form>

                        <!--
                        <form action="project_explanation.php" method="post"i target="_blank">
                        <input type="submit" name="explanationDemand" value="Are you talking to me? >>" />
                        <input type="hidden" name="load" value='<?php echo serialize($_POST['load']); ?>' />
                        <input type="hidden" name="custom" value='<?php echo serialize($_POST['custom']); ?> ' />
                        <input type="hidden" name="sunhours" value='<?php echo $_POST['sunhours']; ?>' />
                        <input type="hidden" name="panel" value='<?php echo serialize($currentsol['panel']); ?>' />
                        <input type="hidden" name="battery" value='<?php echo serialize($currentsol['battery']); ?>' />
                        <input type="hidden" name="controller" value='<?php echo serialize($currentsol['controller']); ?>' />
                        <input type="hidden" name="inverter" value='<?php echo serialize($currentsol['inverter']); ?>' />
                        </form>
                        -->
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr class='project-config-spacer'>
        <td colspan=4> </td>
    </tr>

    <?php
}
?>

</table>

<?php
$db->close();
t_end();
?>
