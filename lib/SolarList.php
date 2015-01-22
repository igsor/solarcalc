<?php

abstract class SolarList {
    public $elements;

    public function __construct()
    {
        $this->elements = array();
    }    

    private function sortElements($sortKey = "type") {
        // Sort all arrays in $elements according to the key $sortKey
        $sortOrder =  function ($a, $b) use ($sortKey) {
            return strnatcmp($a[$sortKey], $b[$sortKey]);
        };        
        usort($this->elements, $sortOrder);
      }

    public function appendElements($newElement) {
        // Adds new element to $elements
        $this->elements[] = $newElement;       
    }

    public function displayElements() {
        // Displays all entries in $elements with key and value
        foreach ($this->elements as $key => $elem) {
            echo "{$elem["type"]}: {$elem["value"]} <br/>";
        }
    }

    abstract public function validElements();

    abstract public function success( $goalValue);
        
    public function totalValue() {
        // Returns to total sum of all values of $elements
        $totSum = 0;
        foreach($this->elements as $elem) {
            $totSum += $elem["value"];
        } 
        return $totSum;
    }

    private function equalKeys($otherList, $sortOrder) {
        // Compares $elements to an array of arrays $otherList
        // Returns TRUE if all keys in $elements are identical to the keys in $otherList
        // $elements and $otherList have the same structure
        
        // sort $otherList according to keys
        $this->sortElements($sortOrder);

        // sort $elements according to keys
        $sortStuff =  function ($a, $b) use ($sortOrder) {
            return strnatcmp($a[$sortOrder], $b[$sortOrder]);
        };        
        usort($otherList, $sortStuff);

        // write both array of arrays into 1D arrays
        $myKey = array ();
        foreach ($this->elements as $elem) {
            $myKey[] = $elem["type"];
        }

        $otherKey = array ();
        foreach ($otherList as $elem) {
            $otherKey[] = $elem["type"];
        }
        
        // sort the two arrays
        sort($myKey);
        sort($otherKey);
        
        // compare keys
        
        return ($myKey == $otherKey);
    }

    public function elementinList($otherLists) {
        // $otherLists is list of array of arrays
        // Checks whether any of the array of arrays in $otherLists is equal to $elements
        
        foreach ($otherLists as $otherElements) {
            if ($this->equalKeys($otherElements, "type")) {
                return true;
            }
        }
        return false;
    }
};


