<?php

function fatal_error($msg) {
    // FIXME: DEBUG ONLY!
    // FIXME: Production: die silently
    echo '<pre>';
    echo "\n\n--------- FATAL ERROR ----------\n";
    debug_print_backtrace(); // FIXME: Debug
    echo '</pre>';
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

/**
 * Class for caching member variables.
 * 
 * The member's getter is executed once, after that the cached value is returned instead.
 * The class is expected to have a method *getVar* for every cached member *var*.
 * 
 */
class MemberCache {

    private $cache; // Associative array [member -> cached value]

    public function __construct() {
        $this->cache = [];
    }

    /**
     * Caches members. Any member *name* can be retrieved.
     * If *name* is not cached, its getter is executed.
     * The getter for member *foo* must be called *getFoo*.
     */
    public function __get($name) {
        if (!key_exists($name, $this->cache)) {
            $this->cache[$name] = call_user_func([$this, "get" . ucfirst($name)]);
        }
        return $this->cache[$name];
    }

    /**
     * Simulates setters. Any member *name* can be defined. It will be stored, together
     * with its *value* in the *cache*.
     */
    public function __set($name, $value) {
        $this->cache[$name] = $value;
    }

    /**
     * Remove a member from the cache.
     */
    public function __unset($name) {
        unset($this->cache[$name]);
    }
};

