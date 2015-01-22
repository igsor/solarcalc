<?php

function detailNumbers($panel, $battery, $controller, $inverter, $load, $custom, $database, $sunhours) {
    $newComposition = new ConfigurationData($database, $battery, $panel, $load, $controller, $inverter, $custom, (float)($sunhours));
    $newComposition->computation();
    $numbers= array (
         "inStock"         => $newComposition->inStock,
         "lifetime"        => $newComposition->expectedLifetime,
         "pricekWh"        => $newComposition->pricekWh,
         "batteryCapacity" => $newComposition->totalCapacity,
         "totalPrice"      => $newComposition->totalPrice,
         "inputVoltage"    => $newComposition->changeBaseVoltage,
         "batteryReserve"  => $newComposition->batteryReserve,
         "panelReserve"    => $newComposition->panelReserve,
         "panelPower"      => $newComposition->panelPower,
    );
    if ($numbers['inputVoltage'] == 0) {
        $numbers['inputVoltage'] = 12.5;
    } else {
        $numbers['inputVoltage'] = 'Not standard Value';
    }
    return $numbers;
};


function makeDevice($numInd) {
    $device = array ();
    for ($i = 1; $i <= $numInd; $i++) {
        array_push($device, array("product" => $i, "amount" => 1));
    };
    return $device;
};

function solaradapter($sunhours, $load, $custom, $database) {
    // clean up data> if dayhours > sunhours -> nighthours += dayhours - sunhours, dayhours = sunhours
    foreach ($load as $device) {
        if ($device["dayhours"] > $sunhours) {
            $device["nighthours"] += $device["dayhours"] - $sunhours;
            $device["dayhours"] = $sunhours;
        };
    };

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //               CALCULATE VALUEGOAL FOR BATTERIES
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // sum over [device power] X [nighttime usage] / [device voltage] X [amount]
    $totalAh = 0;
    foreach ($load as $key => $device) {
        if ($device["product"] != "custom") {
            $query     = "SELECT  `power`, `voltage` FROM `load` WHERE `id` = " . $device["product"]; 
            $result    = $database->query($query) or die(mysqli_error($database));
            $data = $result->fetch_assoc();
            $result->free();
            $totalAh += $device["amount"] * $device["nighthours"] * $data["power"] / $data["voltage"];
         } else {
            $totalAh += $device["amount"] * $device["nighthours"] * $custom[$key]["power"] / $custom[$key]["voltage"];
         };
    };

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //                  POSSIBLE BATTERY CONFIGURATIONS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $outputLists = new SearchConfig();
    $outputLists->goalValue =  $totalAh;
    $list        = new listUniqueBattery();

    $bin         = array ();
    $query       = "SELECT  `id` AS `type`, `dod`, `loss`, `capacity` FROM `battery`";
    $result      = $database->query($query) or die(mysqli_error($database));
    
    while ($data = $result->fetch_assoc()) {
        $value = round($data["dod"] * $data["capacity"] / (1 + $data["loss"]),1);
        array_push($bin, array ('type' => $data['type'], 'value' => $value));
    };
    $result->free();

    $outputLists->calculation($list, $bin);


    $battery = array();
    foreach ($outputLists->solutions as $solution) {
        $keys = array ();
        foreach ($solution as $key => $value) {
            array_push($keys, $value['type']);
        }
        $proposedSolution = array_count_values($keys);
        array_push($battery, $proposedSolution);
    };
    
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //               CALCULATE VALUEGOAL FOR PANELS 
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    // sum over all devices power with dayhours > 0 and $totalAh x [voltage]
    $totalW = 0;
    foreach ($load as $key => $device) {
        if ($device["product"] != "custom" && $device["dayhours"] > 0) {
            $query     = "SELECT  `power`, `voltage` FROM `load` WHERE `id` = " . $device["product"]; 
            $result    = $database->query($query) or die(mysqli_error($database));
            $data = $result->fetch_assoc();
            $result->free();
            $totalW  += $data["power"] * $device["amount"];
        } elseif ($device["product"] == "custom" && $device["dayhours"] > 0 ) {
            $totalW  += $custom[$key]["power"] * $device["amount"]; 
        }    
    };
    $totalW += $totalAh * 12.5 * 1.2 / $sunhours; // ampere hours to watt if voltage = 12.5volt

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //                  CHECK IF INVERTER IS NEEDED
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $optInverter = array ();
    $setInv = 0;
    foreach ($load as $key => $element) {
        if ($element["product"] != "custom") {
            $query     = "SELECT  `type` FROM `load` WHERE `id` = " . $element['product']; 
            $result    = $database->query($query) or die(mysqli_error($database));
            $data      = $result->fetch_assoc();
            $result->free();
            if ($data["type"] == "AC" && $setInv == 0) {
                $optInverter = array( array("product" => 1, "amount" => 1));
                $setInv = 1;
            };
        } elseif ($custom[$key]["type"] == "AC" && $setInv == 0) {
             $optInverter = array( array("product" => 1, "amount" => 1));
             $setInv = 1;
        };
    };

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //                  POSSIBLE PANEL CONFIGURATIONS
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $outputLists  = new SearchConfig();
    $outputLists->goalValue = $totalW;
    $list         = new list12VPanel();

    $bin          = array ();
    $query     = "SELECT  `id` AS `type`, `power` AS `value` FROM `panel`";
    $result    = $database->query($query) or die(mysqli_error($database));
    
    while ($data = $result->fetch_assoc()) {
        array_push($bin, array_map('intval', $data));
    };
    $result->free();

    $outputLists->calculation($list, $bin);


    $panel = array();
    foreach ($outputLists->solutions as $solution) {
        $keys = array ();
        foreach ($solution as $key => $value) {
            array_push($keys, $value['type']);
        }
        $proposedSolution = array_count_values($keys);
        array_push($panel, $proposedSolution);
    };
       
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //      GET ALL POSSIBLE COMBINATIONS OF PANEL AND BATTERY
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    $solution = array ();
    foreach ($panel as $optPanel) {
        foreach ($battery as $optBattery) {
            $refPanel = array();
            foreach ($optPanel as $keyP => $valueP) {
                array_push($refPanel, array ('product' => $keyP, 'amount' => $valueP));
            };
            $refBat = array();
            foreach ($optBattery as $keyP => $valueP) {
                array_push($refBat, array ('product' => $keyP, 'amount' => $valueP));
            };
            $optController = makeDevice(1);
            $optNumbers    = detailNumbers($optPanel, $optBattery, $optController, $optInverter, $load, $custom, $database, $sunhours);
            $allDevices    = array (
                "panel"     => $refPanel,
                "battery"   => $refBat,
                "controller"=> $optController,
                "inverter"  => $optInverter,
                "numbers"   => $optNumbers,
            );
            array_push($solution, $allDevices);
        };
    };

  
    return $solution;
}

?>
