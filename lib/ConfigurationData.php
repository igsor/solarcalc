<?php

class ConfigurationData extends MemberCache {

    const dayperyear  = 365;
    const lifetimePan = 10; 

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

    // FIXME: Could be combined with getChangeBaseVoltage.
    protected function getBoostbuck() {
        foreach ($this->cload as $device) {
            $voltage = $device['voltage'];
            if ($voltage < 11.5 || $voltage > 12.5) {
                return 1;
            }   
        }

        return 0;
    }

    // FIXME: Could be combined with getBoostbuck.
    protected function getChangeBaseVoltage() {
        $totalOtherVoltage = 0;
        foreach ($this->cload as $device) {
            $voltage = $device['voltage'];
            if ($voltage < 11.5 || $voltage > 12.5) {
                $totalOtherVoltage += 1;
            }   
        }

        if ($totalOtherVoltage > 0.75 * count($this->cload)) {
            return 1;
        }

        return 0;
    }

    protected function getInputVoltage() {
        if ($this->changeBaseVoltage) {
            return 'Non standard value';
        } else {
            return 12.5;
        }
    }

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

    protected function getBatteryCapacity() {
        $batteryCapacity = 0;
        foreach($this->battery as $id => $amount) {
            $capacity = $this->dbSingleValue("SELECT `capacity` FROM `{$this->tblPrefix}battery` WHERE `id` =  $id");
            $dod = $this->dbSingleValue("SELECT `dod` / 100 FROM `{$this->tblPrefix}battery` WHERE `id` =  $id");
            $batteryCapacity += $capacity * $dod * $amount;
        }

        return $batteryCapacity;
    }


    protected function getExpectedLifetime() {
        if (empty($this->battery)) {
            return self::lifetimePan;
        }

        $query = sprintf("SELECT min(`lifespan`) FROM `{$this->tblPrefix}battery` WHERE `id` IN (%s)", implode(',', array_keys($this->battery)));
        $numCycles = $this->dbSingleValue($query);
        return $numCycles / self::dayperyear;
    }
    
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
            $pricePerYear = $price * $amount / self::lifetimePan;
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

    protected function getTotalDeviceEnergy() {
        $deviceEnergy = [];
        foreach($this->cload as $device) {
            $deviceEnergy[] =  $device['amount'] * $device['power'] * $device['nighthours'] / $device['voltage'];
        }

        return array_sum($deviceEnergy);
    }

    protected function getBatteryReserve() {
        // $this->batteryCapacity = get total Battery capacity
        // $deviceEnergy = $devicePower * $deviceNighttime
        // $totalDeviceEnergy = sum($deviceEnergy)
        // $batteryReserve = $this->batteryCapacity - $totalDeviceEnergy
        return $this->batteryCapacity - $this->totalDeviceEnergy;
    }

    protected function getPanelPower() {
        $panelPower = 0;
        foreach($this->panel as $id => $amount) {
            $power = $this->dbSingleValue("SELECT `power` FROM `{$this->tblPrefix}panel` WHERE `id` = $id");
            $panelPower += $amount * $power;
        }
        return $panelPower;
    }

    protected function getPanelReserve() {
        // X panelPower = sum over all panel Watt
        // needPanel = sum over all daytime Watt plus nightime Ah*Wp/Ts
        // X all daytime Watt
        // X all nightime Watt
        
        $daytimeWatt = 0;
        foreach($this->cload as $device) {
            if ($device['dayhours'] > 0) {
                $daytimeWatt += $device['amount'] * $device['power'];
            }
        }

        $nighttimeWatt = $this->totalDeviceEnergy * 12.5  / $this->sunhours;
        $needPanel = $daytimeWatt + $nighttimeWatt;
        return $this->panelPower - $needPanel;

        //echo "<br/>The battery capacity is: $this->batteryCapacity";
        //echo "<br/>The panel power is: $panelPower";
        //echo "<br/>The needed panel power is: $needPanel";
        //echo "<br/>The panel reserve therefore is $this->panelReserve<br/>";
    }

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

        return ($inStock ? "Yes" : "No");
    }
};


