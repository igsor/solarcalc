<?php

class ConfigurationData extends MemberCache {

    const dayperyear  = 365;
    const lifetimePanel = 10; 

    // Database variables.
    protected $db;
    private $tblPrefix;

    // Configuration.
    public $battery;
    public $panel;
    public $cload;
    public $controller;
    public $inverter;
    public $custom;
    public $sunhours;

    public function __construct(
        $database,
        $battery    = [],           // [ id => amount ]
        $panel      = [],           // [ id => amount ]
        $controller = [],           // [ id => amount ]
        $inverter   = [],           // [ id => amount ]
        $cload      = [],           // Cannonical load
        $sunhours   = 0,
        $tbl_prefix = ''
    )
    {
        parent::__construct();
        $this->db           = $database;
        $this->tblPrefix    = $tbl_prefix;
        $this->battery      = $battery;
        $this->panel        = $panel;
        $this->cload        = $cload;
        $this->controller   = $controller;
        $this->inverter     = $inverter;
        $this->sunhours     = $sunhours;
    }

    /****************************************************
     *                      HELPERS                     * 
     ****************************************************/

    // Get all columns from a query with a single result.
    protected function dbSingleRow($query) {
        $result = $this->db->query($query) or fatal_error(mysqli_error($this->db));
        $item = $result->fetch_row();
        $result->free();
        return $item;
    }

    // Get a single column from a query with multiple rows.
    protected function dbSingleCol($query, $values=NULL) {
        if ($values != NULL) {
            $query = sprintf($query, implode(',', $values));
        }
        $result = $this->db->query($query) or fatal_error(mysqli_error($this->db));
        $row = [];
        while($item = $result->fetch_row()) {
            $row[] = $item[0];
        }
        $result->free();
        return $row;
    }

    // Get a single column from a query with a single result.
    protected function dbSingleValue($query) {
        $result = $this->db->query($query) or fatal_error(mysqli_error($this->db));
        $item = $result->fetch_row();
        $result->free();
        return $item[0];
    }

    /****************************************************
     *                      GETTERS                     * 
     ****************************************************/

    //////////////////////////////////////////////////////
    //                  BUSINESS VALUES                 //
    //////////////////////////////////////////////////////

    // Expected system lifetime.
    // min_battery( lifespan ) / daysPerYear
    protected function getExpectedLifetime() {
        if (empty($this->battery)) {
            return self::lifetimePanel;
        }

        $query = sprintf("SELECT min(`lifespan`) FROM `{$this->tblPrefix}battery` WHERE `id` IN (%s)", implode(',', array_keys($this->battery)));
        $numCycles = $this->dbSingleValue($query);
        return $numCycles / self::dayperyear;
    }
    
    // Price per Energy (kWh)
    // FIXME: ??????????????
    protected function getPricePerkWh() {
        // Total price per year
        $priceModule = [];
        foreach($this->battery as $id => $amount) {
            $price    = $this->dbSingleValue("SELECT `price` FROM `{$this->tblPrefix}battery` WHERE `id` =  $id");
            $lifespan = $this->dbSingleValue("SELECT `lifespan` FROM `{$this->tblPrefix}battery` WHERE `id` =  $id");
            $pricePerYear = self::dayperyear * $price * $amount / $lifespan;
            $priceModule[] = (float)$pricePerYear;
        }
        foreach($this->panel as $id => $amount) {
            $price = $this->dbSingleValue("SELECT `price` FROM `{$this->tblPrefix}panel` WHERE `id` =  $id");
            $pricePerYear = $price * $amount / self::lifetimePanel;
            $priceModule[] = (float)$pricePerYear;
        }
        $totalPricePerYear = array_sum($priceModule);

        // Price per energy
        $wattHoursPerYear = $this->panelPower * $this->sunhours * self::dayperyear;
        if ($wattHoursPerYear == 0) { // Guard against zero div.
            return 0;
        }

        return (float)(1000 * $totalPricePerYear / $wattHoursPerYear);
    }

    // Number of modules.
    // sum_modules ( sum_module( amount ) )
    protected function getNumModules() {
        $numModules = 0;
        // Modules
        foreach($this->panel as $id => $amount) {
            $numModules += $amount;
        }
        foreach($this->battery as $id => $amount) {
            $numModules += $amount;
        }
        foreach($this->inverter as $id => $amount) {
            $numModules += $amount;
        }
        foreach($this->controller as $id => $amount) {
            $numModules += $amount;
        }
        return $numModules;
    }

    // Number of items.
    // numLoads + numModules
    protected function getNumItems() {
        return $this->numLoads + $this->numModules;
    }

    // Number of items sold.
    // numModules + sum_sold_load( amount )
    protected function getNumItemsSold() {
        $loadSold = 0;
        foreach($this->cload as $device) {
            if ($device['sold']) {
                $loadSold += $device['amount'];
            }
        }
        return $this->numModules + $loadSold;
    }

    // Total price.
    // sum_sold( amount * price )
    protected function getTotalPrice() {
        $totalPrice = 0;
        foreach ($this->panel as $id => $amount) {
            $totalPrice += $amount * $this->dbSingleValue("SELECT `price` FROM `{$this->tblPrefix}panel` WHERE `id` =  $id");
        }
        foreach ($this->battery as $id => $amount) {
            $totalPrice += $amount * $this->dbSingleValue("SELECT `price` FROM `{$this->tblPrefix}battery` WHERE `id` =  $id");
        }
        // FIXME: Use $id instead of $amount['product']; Check data structures first!
        foreach($this->controller as $id => $amount) {
            $totalPrice += $this->dbSingleValue("SELECT `price` FROM `{$this->tblPrefix}controller` WHERE `id` = $id");
        } 
        foreach($this->inverter as $id => $amount) {
            $totalPrice += $this->dbSingleValue("SELECT `price` FROM `{$this->tblPrefix}inverter` WHERE `id` = $id");
        }

        return $totalPrice;
    }

    // Indicator if all items are on stock.
    // all_module( amount <= stock ) && all_load( amount <= stock )
    protected function getInStock() {
        $inStock = true;
        foreach ($this->panel as $id => $amount) {
            $numStock = $this->dbSingleValue("SELECT `stock` FROM `{$this->tblPrefix}panel` WHERE `id` =  $id");
            $inStock  = $inStock && ($amount <= $numStock);
        }
        foreach ($this->battery as $id => $amount) {
            $numStock = $this->dbSingleValue("SELECT `stock` FROM `{$this->tblPrefix}battery` WHERE `id` =  $id");
            $inStock  = $inStock && ($amount <= $numStock);
        }

        // FIXME: Consider controller, inverter and loads

        return ($inStock ? "Yes" : "No");
    }

    // Total number of load devices.
    // sum_load( amount )
    protected function getNumLoads() {
        $numLoads = 0;
        foreach($this->cload as $device) {
            $numLoads += $device['amount'];
        }
        return $numLoads;
    }


    //////////////////////////////////////////////////////
    //                   NETWORK DATA                   //
    //////////////////////////////////////////////////////

    // Determine if load voltage is different from 12
    // num_load( 11.5 <= voltage <= 12.5 ) >= 0.75 * numLoads
    protected function getChangeBaseVoltage() {
        $totalOtherVoltage = 0;
        foreach ($this->cload as $device) {
            $voltage = $device['voltage'];
            if ($voltage < 11.5 || $voltage > 12.5) {
                $totalOtherVoltage += 1;
            }   
        }

        if ($totalOtherVoltage > 0.75 * count($this->cload)) {
            return true;
        }

        return false;
    }

    // Determine if boostbuck is to be used.
    // ! any_load( 11.5 <= voltage <= 12.5 )
    protected function getBoostbuck() {
        $excess = any($this->cload, function($device) {
            $voltage = $device['voltage'];
            return $voltage < 11.5 || $voltage > 12.5;
        });

        return ($excess ? "Yes" : "No");
    }

    // Load input voltage level.
    // 12.5 || Non standard value
    protected function getInputVoltage() {
        if ($this->changeBaseVoltage) {
            return 'Non standard value';
        } else {
            return 12.5;
        }
    }

    // Load input voltage level.
    // inputVoltage
    protected function getLoadVoltage() {
        return $this->inputVoltage;
    }

    // Panel output voltage level.
    // max_panel( voltage )
    protected function getPanelVoltage() {
        // FIXME: How to do it really? E.g. current will be larger for panels with lower voltage...
        $query = "SELECT max(`voltage`) FROM `{$this->tblPrefix}panel` WHERE `id` IN (%s)";
        $query = sprintf($query, array_keys($this->panel));
        $voltage = $this->dbSingleValue($query);
        return $voltage;
    }
   
    // Battery input/output voltage level.
    // battery( voltage )
    protected function getBatteryVoltage() {
        $id = array_keys($this->battery)[0];
        $voltage = $this->dbSingleValue("SELECT `voltage` FROM `{$this->tblPrefix}battery` WHERE `id` = '{$id}'");
        return $voltage;
    }

    // Number of panels in serie.
    // 1
    protected function getPanelsSerie() {
        // FIXME: Implement real function
        return 1;
    }

    // Number of panels in parallel.
    // sum_panels( amount )
    protected function getPanelsParallel() {
        // FIXME: Implement real function
        $num_panels = 0;
        foreach($this->panel as $id => $amount) {
            $num_panels += $amount;
        }
        return $num_panels;
    }

    // Number of batteries in serie.
    // 1
    protected function getBatteriesSerie() {
        // FIXME: Implement real function
        return 1;
    }

    // Number of batteries in parallel.
    // sum_battery( amount )
    protected function getBatteriesParallel() {
        // FIXME: Implement real function
        $num_batteries = 0;
        foreach($this->battery as $id => $amount) {
            $num_batteries += $amount;
        }
        return $num_batteries;
    }

    // "Yes" if the network as AC parts.
    // numAC > 0
    protected function getHasAC() {
        $hasAC = any($this->cload, function($item) {
            return $item['type'] == 'AC';
        });
        
        return ($hasAC ? "Yes" : "No");
    }

    // "Yes" if the network as DC parts.
    // numDC > 0
    protected function getHasDC() {
        // FIXME: Use numDC instead
        $hasDC = any($this->cload, function($item) {
            return $item['type'] == 'DC';
        });

        return ($hasDC ? "Yes" : "No");
    }

    // Number of AC devices.
    // sum_ac_load( amount )
    protected function getNumAC() {
        $count = 0;
        foreach($this->cload as $device) {
            if ($device['type'] == 'AC') {
                $count += $device['amount'];
            }
        }

        return $count;
    }

    // Number of DC devices.
    // sum_dc_load( amount )
    protected function getNumDC() {
        $count = 0;
        foreach($this->cload as $device) {
            if ($device['type'] == 'DC') {
                $count += $device['amount'];
            }
        }

        return $count;
    }

    // Total AC power.
    // sum_ac_load( amount * power )
    protected function getLoadAC() {
        $power = 0;
        foreach($this->cload as $device) {
            if ($device['type'] == 'AC'){
                $power += $device['amount'] * $device['power'];
            }
        }
        return $power;
    }

    // Total DC power.
    // sum_dc_load( amount * power )
    protected function getLoadDC() {
        $power = 0;
        foreach($this->cload as $device) {
            if ($device['type'] == 'DC'){
                $power += $device['amount'] * $device['power'];
            }
        }
        return $power;
    }

    //////////////////////////////////////////////////////
    //                   ENERGY BALANCE                 //
    //////////////////////////////////////////////////////

    /**********************************************************************************************

    Load Voltage                    V   12.5 load voltage level                                             loadVoltage
    Panel Voltage                   V   12.5 panel output voltage level                                     panelVoltage
    Battery Voltage                 V   12.5 battery input/output voltage level                             batteryVoltage

    Peak consumed power (day)       W   sum_load( day_active * power * amount )                             consumedDayPower
    Consumed energy (day)           Wh  sum_load( dayhours * power * amount )                               consumedDayEnergy
    Consumed energy (night)         Wh  sum_load( nighthours * power * amount )                             consumedNightEnergy
    Peak consumed power (night)     W   sum_load( night_active * power * amount )                           consumedNightPower
    Peak consumed power             W   max( Peak consumed power (day), Peak consumed power (night) )       consumedPower
    Max. current (day)              A   Peak consumed power (day) / Load voltage                            currentDay
    Max. current (night)            A   Peak consumed power (night) / Load voltage                          currentNight
    Max. current                    A   max( Max. current (day), Max. current (night) )                     currentMax

    Total panel power               W   sum_panels( amount * power )                                        panelPower
    Total produced energy           J   Total panel power * sunhours                                        panelEnergy
    Max. panel current output       A   Total panel power / panel voltage                                   panelCurrent
    Panel power excess              W   Total panel power - Peak consumed power (night)                     panelReserve
                                                          - Battery consumed energy / sunhours
    Total battery capacity          Ah  sum_battery( dod * capacity * amount_parallel )                     batteryCapacity
    Consumed battery capacity       Ah  sum_load( nighthours * power * amount / load voltage )              batteryCapacityConsumed
    Unused battery capacity         Ah  Total battery capacity - Consumed battery capacity                  batteryReserve
    Battery discharge time          H   Total battery capacity * battery voltage / Peak cons. power(night)  batteryDischargeTime
    Battery input energy            Wh  Total produced energy - Consumed energy (day)                       batteryInputEnergy
    Battery usable energy           Wh  Battery input energy * (1 - loss)                                   batteryUsableInputEnergy
    Battery charge energy excess    Wh  Battery usable energy - Consumed energy (night)                     batteryEnergyReserve
    Battery consumed energy         Wh  (1 + loss) * Consumed energy (night)                                batteryConsumedEnergy
    Max. Charge current             A   (Total panel power - Peak consumed power) / Battery voltage         batteryChargeCurrent

    Totally stored energy, output   Wh  Total battery capacity * battery voltage                            batteryTotalEnergyOut
    Totally stored energy, input    Wh  (1 + loss) * Totally stored energy, output                          batteryTotalEnergyIn

    Min. time until charged         H   Totally stored energy (in) / Total panel power                      batteryChargeTimeMin
    Max. time until charged         H   Totally stored energy (in) / (Total panel power - Peak consumed power (day))    batteryChargeTimeMax
    Avg. time until charged         H   Totally stored energy (in) / (Battery input energy / sunhours)      batteryChargeTimeAvg

    **********************************************************************************************/

    // Totally generated power.
    // sum_panel( power * amount )
    protected function getPanelPower() {
        $panelPower = 0;
        foreach($this->panel as $id => $amount) {
            $power = $this->dbSingleValue("SELECT `power` FROM `{$this->tblPrefix}panel` WHERE `id` = $id");
            $panelPower += $amount * $power;
        }
        return $panelPower;
    }

    // Battery capacity used by night load.
    // sum_load( amount * power * nighthours / voltage )
    protected function getBatteryCapacityConsumed() {
        $deviceEnergy = [];
        foreach($this->cload as $device) {
            $deviceEnergy[] =  $device['amount'] * $device['power'] * $device['nighthours'] / $device['voltage'];
        }

        return array_sum($deviceEnergy);
    }

    // Battery consumed energy         
    //  (1 + loss) * Consumed energy (night)
    protected function getBatteryConsumedEnergy() {
        return (1.0 + $this->batteryLoss) * $this->consumedNightEnergy;
    }

    // Unused battery capacity.
    // batteryCapacity - batteryCapacityConsumed
    protected function getBatteryReserve() {
        return $this->batteryCapacity - $this->batteryCapacityConsumed;
    }

    // Total battery capacity.
    // sum_battery( dod * capacity * amount_parallel )
    protected function getBatteryCapacity() {
        $batteryCapacity = 0;
        foreach($this->battery as $id => $amount) {
            $capacity = $this->dbSingleValue("SELECT `capacity` FROM `{$this->tblPrefix}battery` WHERE `id` =  $id");
            $dod = $this->dbSingleValue("SELECT `dod` / 100 FROM `{$this->tblPrefix}battery` WHERE `id` =  $id");
            $batteryCapacity += $capacity * $dod * $amount; // FIXME: Only parallel batteries!
        }

        return $batteryCapacity;
    }

    // Total produced energy.
    // Total panel power * sunhours
    protected function getPanelEnergy() {
        return $this->panelPower * $this->sunhours;
    }

    // Max. panel current output.
    // Total panel power / panel voltage
    protected function getPanelCurrent() {
        return $this->panelPower / $this->panelVoltage;
    }

    // Peak consumed power (day).
    // sum_load( day_active * power * amount )
    protected function getConsumedDayPower() {
        $power = 0;
        foreach($this->cload as $device) {
            if ($device['dayhours'] > 0) {
                $power += $device['power'] * $device['amount'];
            }
        }
        return $power;
    }

    // Consumed energy (day).
    // sum_load( dayhours * power * amount )
    protected function getConsumedDayEnergy() {
        $energy = 0;
        foreach($this->cload as $device) {
            $energy += $device['dayhours'] * $device['power'] * $device['amount'];
        }
        return $energy;
    }

    // Consumed energy (night).
    // sum_load( nighthours * power * amount )
    protected function getConsumedNightEnergy() {
        $sum_load = 0;
        foreach($this->cload as $device) {
            $sum_load += $device['nighthours'] * $device['power'] * $device['amount'];
        }
        return $sum_load;
    }

    // Peak consumed power (night).
    // sum_load( night_active * power * amount )
    protected function getConsumedNightPower() {
        $sum_load = 0;
        foreach($this->cload as $device) {
            if($device['nighthours'] > 0) {
                $sum_load += $device['power'] * $device['amount'];
            }
        }
        return $sum_load;
    }
    
    // Max. current (day)
    // Peak consumed power (day) / Load voltage
    protected function getCurrentDay() {
        return $this->consumedDayPower / $this->loadVoltage;
    }
    
    // Max. current (night)
    // Peak consumed power (night) / Load voltage
    protected function getCurrentNight() {
        return $this->consumedNightPower / $this->loadVoltage;
    }

    // Max. current    
    // max( Max. current (day), Max. current (night) )                 
    protected function getCurrentMax() {
        return max($this->currentDay, $this->currentNight);
    }

    // Battery discharge time.
    // Total battery capacity * battery voltage / Peak cons. power (night)
    protected function getBatteryDischargeTime() {
        return $this->batteryCapacity * $this->batteryVoltage / $this->consumedNightPower;
    }

    // Battery input energy.
    // Total produced energy - Consumed energy (day)
    protected function getBatteryInputEnergy() {
        return $this->panelEnergy - $this->consumedDayEnergy;
    }

    // Battery usable energy.
    // Battery input energy * (1 - loss)
    protected function getBatteryUsableInputEnergy() {
        return $this->batteryInputEnergy * (1.0 - $this->batteryLoss);
    }

    // Peak consumed power.
    // max( Peak consumed power (day), Peak consumed power (night) )
    protected function getConsumedPower() {
        return max($this->consumedDayPower, $this->consumedNightPower);
    }

    // Battery charge energy excess.
    // Battery usable energy - Consumed energy (night)
    protected function getBatteryEnergyReserve() {
        return $this->batteryUsableInputEnergy - $this->consumedNightEnergy;
    }

    // Max. Charge current.
    // (Total panel power - Peak consumed power) / Battery voltage
    protected function getBatteryChargeCurrent() {
        return ($this->panelPower - $this->consumedDayPower) / $this->batteryVoltage;
    }
    
    // Totally stored energy, output   
    // Total battery capacity * battery voltage                           
    protected function getBatteryTotalEnergyOut() {
        return $this->batteryCapacity + $this->batteryVoltage;
    }

    // Totally stored energy, input    
    // (1 + loss) * Totally stored energy, output
    protected function getBatteryTotalEnergyIn() {
        return (1.0 + $this->batteryLoss) * $this->batteryTotalEnergyOut;
    }

    // Min. time until charged.
    // (1 + loss) * Total battery capacity * battery voltage / Total panel power
    protected function getBatteryChargeTimeMin() {
        return $this->batteryTotalEnergyIn / $this->panelPower;
    }

    // Max. time until charged.
    // (1 + loss) * Total battery capacity * battery voltage / (Total panel power - Peak consumed power (day))
    protected function getBatteryChargeTimeMax() {
        $genPower = $this->panelPower - $this->consumedDayPower;
        if ($genPower == 0) {
            return 'inf';
        }
        return $this->batteryTotalEnergyIn / $genPower;
    }

    // Avg. time until charged.
    // Total battery capacity * battery voltage / (Battery input energy / sunhours)
    protected function getBatteryChargeTimeAvg() {
        return $this->batteryTotalEnergyIn * $this->sunhours / $this->batteryInputEnergy;
    }

    // Panel power excess
    // Total panel power - Peak consumed power (night) - Battery consumed energy / sunhours
    protected function getPanelReserve() {
        return $this->panelPower - ($this->consumedDayPower + $this->batteryConsumedEnergy / $this->sunhours);
    }

    //////////////////////////////////////////////////////
    //                      HELPERS                     //
    //////////////////////////////////////////////////////

    // Battery loss.
    protected function getBatteryLoss() {
        $id = array_keys($this->battery)[0];
        return $this->dbSingleValue("SELECT `loss` / 100 FROM `{$this->tblPrefix}battery` WHERE `id` = '$id'");
    }

};

// EOF //
