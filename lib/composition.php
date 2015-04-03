<?php

/**
 * Creates a unified array from *load* and *custom*.
 * All load data are read from the database and combined
 * with what's in *custom*.
 *
 * The resulting array has the following keys:
 *  - amount
 *  - dayhours
 *  - nighthours
 *  - sold
 *  - product (id or 'custom')

 *  - name
 *  - power
 *  - type
 *  - voltage
 *  - price
 *  - stock
 * 
 */
function cannonical_load($load, $custom, $db) {
    $cannonical = [];
    foreach($load as $key => $device) {
        if ($device['product'] == 'custom') {
            $data = $custom[$key];
        } else {
            $id = $device['product'] = $db->escape_string($device['product']);
            $result = $db->query("SELECT `name`, `power`, `type`, `voltage`, `price`, `stock` FROM `load` WHERE `id` = {$id}") or fatal_error(mysqli_error($db));
            $data = $result->fetch_assoc();
        }

        // FIXME: Input checking?
        $cannonical[] = array_merge($data, $device);
    }

    return $cannonical;
}

/**
 * Search for valid configurations satisfying the *load*, assuming
 * *sunhours* hours of light per day.
 */
function solaradapter($cload, $sunhours, $database) {

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //               CALCULATE VALUEGOAL FOR BATTERIES
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    // sum over [device power] X [nighttime usage] / [device voltage] X [amount]
    $totalAh = 0;
    foreach ($cload as $device) {
         $totalAh += $device['amount'] * $device['nighthours'] * $device['power'] / $device['voltage'];
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //                  POSSIBLE BATTERY CONFIGURATIONS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $outputLists = new SearchConfig();
    $outputLists->goalValue =  $totalAh;
    $list        = new listUniqueBattery();
    $bin         = [];
    $query       = "SELECT  `id` AS `type`, `dod` / 100 as 'dod', `loss` / 100 as 'loss', `capacity` FROM `battery`";
    $result      = $database->query($query) or die(mysqli_error($database));
    
    while ($data = $result->fetch_assoc()) {
        $value = round($data["dod"] * $data["capacity"] / (1 + $data["loss"]),1);
        $bin[] = [ 'type' => $data['type'], 'value' => $value ];
    }
    $result->free();

    // Compute battery configurations.
    $outputLists->calculation($list, $bin);

    // Extract solutions.
    $battery = [];
    foreach ($outputLists->solutions as $solution) {
        $keys = [];
        foreach ($solution as $key => $value) {
            $keys[] = $value['type'];
        }

        $proposedSolution = array_count_values($keys);
        $battery[] = $proposedSolution;
    }
    
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //               CALCULATE VALUEGOAL FOR PANELS 
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    // sum over all devices power with dayhours > 0 and $totalAh x [voltage]
    $totalW = 0;
    foreach ($cload as $device) {
        if ($device["dayhours"] > 0) {
            $totalW  += $device["power"] * $device["amount"]; 
        }    
    }

    $totalW += $totalAh * 12.5 * 1.2 / $sunhours; // ampere hours to watt if voltage = 12.5volt // FIXME: Hardcoded voltage

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //                  POSSIBLE PANEL CONFIGURATIONS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $outputLists  = new SearchConfig();
    $outputLists->goalValue = $totalW;
    $list         = new list12VPanel();
    $bin          = [];
    $query        = "SELECT  `id` AS `type`, `power` AS `value` FROM `panel`";
    $result       = $database->query($query) or die(mysqli_error($database));
    
    while ($data = $result->fetch_assoc()) {
        $bin[] = array_map('intval', $data);
    }
    $result->free();

    // Search valid panel configuration.
    $outputLists->calculation($list, $bin);

    // Extract solutions.
    $panel = [];
    foreach ($outputLists->solutions as $solution) {
        $keys = [];
        foreach ($solution as $key => $value) {
            $keys[] = $value['type'];
        }
        $proposedSolution = array_count_values($keys);

        $panel[] = $proposedSolution;
    }
       
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //                  CHECK IF INVERTER IS NEEDED
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $refInverter = [];
    $optInverter = [];
    if (any($cload, function($device) { return $device['type'] == 'AC'; })) {
        $refInverter = [ ["product" => 1, "amount" => 1] ]; // FIXME: Hardcoded ID
        $optInverter = [ 1 => 1 ];
    }
    // FIXME: Inverter parameters not considered (max. current)

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //                  ADD CONTROLLER
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $refController = [["product" => 1, "amount" => 1]]; // FIXME: Hardcoded ID / Amount
    $optController = [ 1 => 1 ];
    // FIXME: Controller parameters not considered (max. current).
    // FIXME: Add controller dep. on battery/panel configuration

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //      GET ALL POSSIBLE COMBINATIONS OF PANEL AND BATTERY
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $solution = [];
    foreach ($panel as $optPanel) {
        foreach ($battery as $optBattery) {
            $refPanel = [];
            foreach ($optPanel as $keyP => $valueP) {
                $refPanel[] = array ('product' => $keyP, 'amount' => $valueP);
            }

            $refBat = [];
            foreach ($optBattery as $keyP => $valueP) {
                $refBat[] = array ('product' => $keyP, 'amount' => $valueP);
            }

            $optNumbers    = new ConfigurationData(
                                $database,              // Database object
                                $optBattery,            // [ id => amount ]
                                $optPanel,              // [ id => amount ]
                                $optController,         // [ id => amount ]
                                $optInverter,           // [ id => amount ]
                                $cload,                 // See *cannonical_load*
                                (float)($sunhours)      // Scalar
                            );
            $allDevices    = [
                "panel"     => $refPanel,
                "battery"   => $refBat,
                "controller"=> $refController,
                "inverter"  => $refInverter,
                "numbers"   => $optNumbers,
            ];

            $solution[] = $allDevices;
        }
    }
  
    return $solution;
}

?>
