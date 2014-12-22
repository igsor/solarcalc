<?php

function detailNumbers($panel, $battery, $controller, $inverter) {

    return $numbers= array (
         "inStock"         => true,
         "lifetime"        => rand(100, 700) / 100,
         "pricekWh"        => rand(500, 1000) / 1000,
         "batteryCapacity" => rand(100, 250),
         "totalPrice"      => rand(100000, 500000),
         "inputPower"      => 12.5,
         "batteryReserve"  => rand(0, 50),
    );
 
};


function solarcalc($sunhours, $load, $custom) {
    

    function makeDevice($numInd) {
        $device = array ();
        for ($i = 1; $i <= $numInd; $i++) {
            $mnt = rand(0,4);
            if ($mnt != 0) {
                array_push($device, array("product" => $i, "amount" => $mnt));
            };
        };

        return $device;
    };

    
    $solution = array();

    $elements = rand(1,5);
    
    for ($i = 0; $i < $elements; $i++) {
        $panel = makeDevice(2);
        $battery = makeDevice(3);
        $controller = makeDevice(1);
        $inverter = makeDevice(1);
        $numbers = detailNumbers($panel, $battery, $controller, $inverter);
 
        $allDevices = array(
            "panel" => $panel,
            "battery" => $battery,
            "controller" => $controller,
            "inverter" => $inverter,
            "numbers" => $numbers,  
            );
        array_push($solution, $allDevices);
    };

    return $solution;
}

?>
