<?php

namespace Francerz\PhpModel;

use \ArrayAccess;
use \Iterator;

class Multimap implements ArrayAccess, Iterator
{
    private $multimap;

    /**
     * @param array[] $multimap
     */
    public function __construct($multimap = null)
    {
        $this->multimap = is_null($multimap) ? array() : $multimap;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    private function coerceType($offset)
    {
        $content = $this->multimap[$offset];
        if (is_array($content)) {
            $this->multimap[$offset] = new Collection($content);
        }
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            trigger_error("Undefined offset $offset in Multimap structure.");
            return null;
        }
        return $this->multimap[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        trigger_error("Unavailable function", E_USER_NOTICE);
    }

    /**
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->multimap[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->multimap[$offset]);
    }

    /**
     * @return void
     */
    public function reset()
    {
        return reset($this->multimap);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->multimap);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->multimap);
    }

    /**
     * @return void
     */
    public function next()
    {
        return next($this->multimap);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return $this->key() !== false;
    }
}