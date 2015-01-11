<?php

function fatal_error($msg) {
    echo "\n\n--------- FATAL ERROR ----------\n";
    debug_print_backtrace(); // FIXME: Debug
    die($msg);
}

class EmptyArray implements ArrayAccess {
    public function __construct($data=null) {
        if ($data == null || !is_array($data)) {
            $data = array();
        }
        $this->data = $data;
    }
    public function offsetExists($offset) {
        return true;
    }

    public function offsetGet($offset) {
        if (key_exists($offset, $this->data)) {
            return $this->data[$offset];
        }
        return '';
    }

    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
};

function array_with_defaults($keys, $default='')
{
    $data = [];
    foreach($keys as $key) {
        $data[$key] = $default;
    }
    return $data;
}

function all($list, $clbk) {
    foreach($list as $item) {
        if (!$clbk($item)) {
            return false;
        }
    }
    return true;
}

function any($list, $clbk) {
    foreach($list as $item) {
        if ($clbk($item)) {
            return true;
        }
    }
    return false;
}

function fix_checkbox_post($names) {
    if (!is_array($names)) {
        $names = [$names];
    }

    foreach($names as $name) {
        if (isset($_POST[$name])) {
            $_POST[$name] = 1;
        } else {
            $_POST[$name] = 0;
        }
    }
}
