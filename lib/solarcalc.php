<?php

function solarCalc($element, $list, $bin, $data)
{
    $list->appendElements($element);
    
    if (($list->elementinList($data->solutions)) || ($list->elementinList($data->failed))) {
        return;
    } elseif (!$list->validElements()) {
    	array_push($data->failed, $list->elements);
        return;
    } elseif ($list->success($data->goalValue)) {
        array_push($data->solutions, $list->elements);
        return;
    } else {
        // add $bin update if needed
        foreach ($bin as $element) {
      	    solarCalc($element, clone $list, $bin, $data);
       	}
    }
}

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
};  

