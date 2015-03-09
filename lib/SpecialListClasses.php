<?php

class listUniqueBattery extends SpecialList {
    public function success($goalValue) {
        if ($this->totalValue() >= $goalValue) {
            return true;
        } else {
            return false;
        }
    }

    public function validElements() {
        if (count($this->elements) > 1) {
            $product = array();
            foreach ($this->elements as $battery) {
                $product[] =  (int)($battery['type']);
            }
            sort($product);
             if ($product[1] != end($product)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
};


class list12VPanel extends SpecialList {
    public function success($goalValue) {
        if ($this->totalValue() >= $goalValue) {
            // check if any subset of this solution is also valid.
            // If yes, take only subset, not $this->elements
            return true;
        } else {
            return false;
        }
    }
    public function validElements() {
        if (end($this->elements) < 11.5 || end($this->elements) > 13) {
            return true;
        }
    }
};

class listPanel extends SpecialList {
    public function success($goalValue) {
        if ($this->totalValue() >= $goalValue) {
            return true;
        } else {
            return false;
        }
    }
    public function validElements() {
       return true; 
    }
};

