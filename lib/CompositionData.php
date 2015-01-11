<?php
class CompositionData {
    public $battery;//
    public $panel;//
    public $load;//
    public $controller;
    public $inverter;
    public $custom;
    public $boostbuck;//
    public $changeBaseVoltage;//
    public $totalPrice;//
    public $totalCapacity;//
    public $expectedLifetime;//
    public $pricekWh;//
    public $batteryReserve;
    public $panelReserve;
    public $inStock;//
    public $sunhours;
    public $panelPower;
    public $database;

    private $totalDeviceEnergy;

    const dayperyear  = 365;
    const lifetimePan = 10; 

    public function __construct(
        $newDatabase,
        $newBattery = array(), 
        $newPanel = array(),
        $newLoad = array(),
        $newController = array(),
        $newInverter = array(),
        $newCustom = array(),
        $newSunhours  = 0
    )
    {
        $this->database             = $newDatabase;
        $this->battery              = $newBattery;
        $this->panel                = $newPanel;
        $this->load                 = $newLoad;
        $this->controller           = $newController;
        $this->inverter             = $newInverter;
        $this->custom               = $newCustom;
        $this->sunhours             = $newSunhours;
        $this->boostbuck            = 0;
        $this->changeBaseVoltage    = 0;
        $this->totalPrice           = 0;
        $this->totalCapacity        = 0;
        $this->expectedLifetime     = 0;
        $this->pricekWh             = 0;
        $this->batteryReserve       = 0;
        $this->panelReserve         = 0;
        $this->inStock              = "Yes";
        $this->totalDeviceEnergy    = 0;
        $this->panelPower            = 0;
    }

    public function computation() {
        $this->setInputVoltage();
        $this->setTotalPrice();
        $this->setBatteryCapacity();
        $this->setExpectedLifetime();
        $this->setInStock();
        $this->setPriceperkWh();
        $this->setBatteryReserve();
        $this->setPanelReserve();
    }

    private function setInputVoltage() {
        $totalDevices      = count($this->load); 
        $totalotherVoltage = 0;
         foreach ($this->load as $key => $device) {
            if ($device['product'] != 'custom') {
                $query = "SELECT `voltage` FROM `load` WHERE `id` =  {$device['product']}";
                $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
                $name = $result->fetch_assoc();
                $result->free();
                if ($name["voltage"] < 11.5 || $name["voltage"] > 12.5) {
                    $this->boostbuck    = 1;
                    $totalotherVoltage += 1;
               }   
            } else {
                if ($this->custom[$key]['voltage'] < 11.5 || $this->custom[$key]['voltage'] > 12.5) {
                    $this->boostbuck    = 1;
                    $totalotherVoltage += 1;
                }
            }
        }
        if ($totalotherVoltage > 0.75* $totalDevices) {
            $this->changeBaseVoltage = 1;    
        }
    }

    private function setTotalPrice() {
        $this->totalPrice = 0;
        foreach ($this->panel as $key => $device) {
            $query = "SELECT `price` FROM `panel` WHERE `id` =  $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            $this->totalPrice += $device * $name["price"];        
        }
        foreach ($this->battery as $key => $device) {
            $query = "SELECT `price` FROM `battery` WHERE `id` =  $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            $this->totalPrice += $device * $name["price"];
        }
        foreach($this->controller as $key => $device) {
            $query = "SELECT `price` FROM `controller` WHERE `id` =  {$device['product']}";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            $this->totalPrice += $name["price"];
        }
        foreach($this->inverter as $key => $device) {
            $query = "SELECT `price` FROM `inverter` WHERE `id` =  {$device['product']}";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            $this->totalPrice += $name["price"];
        }
     }

    private function setBatteryCapacity() {
        $this->totalCapacity = 0;
        foreach($this->battery as $key => $device) {
            $query = "SELECT `capacity`, `dod` FROM `battery` WHERE `id` =  $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            $this->totalCapacity += $name["capacity"] * $name["dod"];
        }
    }

    private function setExpectedLifetime() {
        $this->expectedLifetime = 0;
        $allCycles = array();
        foreach($this->battery as $key => $device) {
            $query = "SELECT `lifespan` FROM `battery` WHERE `id` =  $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            $allCycles[] = $name['lifespan'];
        }
        sort($allCycles);
        if (empty($allCycles)) {
            $this->expectedLifetime = self::lifetimePan;
        } else {
            $this->expectedLifetime = $allCycles[0] / self::dayperyear;
        }
    }
    
    
    private function setPriceperkWh() {
        $priceBat = array();
        foreach($this->battery as $key => $device) {
            $query = "SELECT `lifespan`, `price` FROM `battery` WHERE `id` =  $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            array_push($priceBat, (float)($name['price'] / $name['lifespan'] * self::dayperyear ));
        }
        $pricePan = array();
        foreach($this->panel as $key => $device) {
            $query = "SELECT `price` FROM `panel` WHERE `id` =  $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            array_push($pricePan, (float)($name['price'] / self::lifetimePan ));
        }
        $totalPricepYear = array_sum($pricePan) + array_sum($priceBat);
        $panelWatt = array();
        foreach($this->panel as $key => $device) {
            $query = "SELECT `power` FROM `panel` WHERE `id` = $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            array_push($panelWatt, $name['power']);
        }
        $totalWatt = array_sum($panelWatt);
        $wattHourspYear = $totalWatt * $this->sunhours * self::dayperyear;
        if ($wattHourspYear != 0) {
            $this->pricekWh = (float)($totalPricepYear / $wattHourspYear * 1000);
        } else {
            $this->priceKwh = 0;
        }

    }

    private function setBatteryReserve() {
        // $this->totalCapacity = get total Battery capacity
        // $deviceEnergy = $devicePower * $deviceNighttime
        // $totalDeviceEnergy = sum($deviceEnergy)
        // $batteryCapacity = $this->totalCapacity - $totalDeviceEnergy
        $deviceEnergy = array();
        foreach($this->load as $key => $device) {
            if ($device['product'] != 'custom') {
                $query = "SELECT `power`, `voltage` FROM `load` WHERE `id` =  {$device['product']}";
                $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
                $name = $result->fetch_assoc();
                $result->free();
                array_push($deviceEnergy, $name['power'] * $device['nighthours'] / $name['voltage']);
            } else {
                array_push($deviceEnergy, $this->custom[$key]['power'] * $device['nighthours'] / $this->custom[$key]['voltage']);
            }
        }
        $this->totalDeviceEnergy = array_sum($deviceEnergy);
        $this->batteryReserve = $this->totalCapacity - $this->totalDeviceEnergy;
    }

    private function setPanelReserve() {
        // X panelPower = sum over all panel Watt
        // needPanel = sum over all daytime Watt plus nightime Ah*Wp/Ts
        // X all daytime Watt
        // X all nightime Watt
        $this->panelPower = 0;
        foreach($this->panel as $key => $device) {
            $query = "SELECT `power` FROM `panel` WHERE `id` = $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $data = $result->fetch_assoc();
            $result->free();
            $this->panelPower += $device * $data['power'];
        }
        
        $daytimeWatt = 0;
        foreach($this->load as $key => $device) {
            if ($device['dayhours'] > 0 && $device['product'] != 'custom') { 
                $query = "SELECT `power` FROM `load` WHERE `id` =  {$device['product']}";
                $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
                $data = $result->fetch_assoc();
                $result->free();
                $daytimeWatt += $device['amount'] * $data['power'];
            } elseif ($device['dayhours'] > 0) {
                $daytimeWatt += $device['amount'] * $this->custom[$key]['power'];
            }
        
        $nighttimeWatt = $this->totalDeviceEnergy * 12.5  / $this->sunhours;
        $needPanel = $daytimeWatt + $nighttimeWatt;
        $this->panelReserve = $this->panelPower - $needPanel;
       }
    }

    private function setInStock() {
        $this->inStock = "Yes";
        foreach ($this->panel as $key => $device) {
            $query = "SELECT `stock` FROM `panel` WHERE `id` =  $key";
            $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
            $name = $result->fetch_assoc();
            $result->free();
            if ($device['amount'] > $name['stock']) {
                $this->inStock = "No";
            }
        }
        if ($this->inStock == "Yes") {
            foreach ($this->battery as $key => $device) {
                $query = "SELECT `stock` FROM `battery` WHERE `id` =  $key";
                $result = $this->database->query($query) or fatal_error(mysqli_error($this->database));
                $name = $result->fetch_assoc();
                $result->free();
                if ($device['amount'] > $name['stock']) {
                    $this->inStock = "No";
                }
            }
        }
    }

}
