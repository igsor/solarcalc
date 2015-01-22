<?php
class GlobLists
{
    public $solutions;
    public $failed;
    public $goalValue;
    
    public function __construct()
    {
        $this->solutions    = array();
        $this->failed       = array();
        $this->goalValue    = 0;
    }
    
    public function cleanUp()
    {
        foreach ($this->solutions as $key => $elem) {
            if (array_key_exists(0, $elem)) {
                $lostElement = array_shift($this->solutions[$key]);
                }
        }
        foreach ($this->failed as $key => $elem) {
            if (array_key_exists(0, $elem)) {
                $lostElement = array_shift($this->failed[$key]);
                }
        }
    }

    protected function solarCalc($element, $list, $bin) 
    {
        $list->appendElements($element);
    
        if (($list->elementinList($this->solutions)) || ($list->elementinList($this->failed))) {
            return;
        } elseif (!$list->validElements()) {
    	    array_push($this->failed, $list->elements);
            return;
        } elseif ($list->success($this->goalValue)) {
            array_push($this->solutions, $list->elements);
            return;
        } else {
            // add $bin update if needed
            foreach ($bin as $element) {
      	        $this->solarCalc($element, clone $list, $bin);
       	    }
        }   

    }
    
    public function calculation($list, $bin) {
        $elem0        = array ("type" => 0, "value" => 0);
        $this->solarCalc($elem0, $list, $bin);
        $this->cleanUp();
    }

};  

