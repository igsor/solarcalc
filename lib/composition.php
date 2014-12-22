<?php

function solarcalc($sunhours, $load, $custom) {
    
    
    $panel= array (
        0=> array (
            "product"=>  2,
            "amount"=>  4,
        ),
        1=> array (
            "product"=> 1,
            "amount"=>  3,
        ),
    );

    $battery= array (
        0=> array (
            "product"=>  2,
            "amount"=>  1,
        ),
        1=> array (
            "product"=> 1,
            "amount"=> 2,
        ),
    );

    $controller= array (
        0=> array (
            "product"=>  1,
            "amount"=>  1,
        ),
    );

    $inverter= array (
        0=> array (
            "product"=>  1,
            "amount"=>  1,
        ),
    );

    $numbers= array (
        "inStock"         => true,
        "lifetime"        => 5.43,
        "pricekWh"        => 0.684,
        "batteryCapacity" => 125,
        "totalPrice"      => 486000,
        "inputPower"      => 12.5,
        "batteryReserve"  => 25.5,
    );


    return array(
        array(
            "panel"      => $panel, 
            "battery"    => $battery, 
            "controller" => $controller, 
            "inverter"   => $inverter,
            "numbers"    => $numbers,
        ),
    );
}

?>
