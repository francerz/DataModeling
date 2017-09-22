<?php

namespace Francerz\PhpModel;

class Item implements ItemInterface
{
    protected $data;

    /**
     * Undocumented function
     */
    public function __construct($data = null)
    {
        if (empty($data)) {
            $this->data = array();
        } elseif (ArrayHelper::isAssociative($data)) {
            $this->data = $data;
        } else {
            trigger_error("Invalid data content", E_USER_ERROR);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $value
     */
    public function __set($name, $value)
    {
        if (!ModelDirectives::isValidPropertyName($name)) {
            \trigger_error("Invalid given property name ($name) on Item", E_USER_ERROR);
            return;
        }
        $this->data[$name] = $value;
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @return void
     */
    public function __get($name)
    {
        if (!$this->__isset($name)) {
            trigger_error("Undefined attribute ($name) in Item object.", E_USER_NOTICE);
            return null;
        }
        return $this->data[$name];
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Undocumented function
     *
     * @param [type] $offset
     * @param [type] $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset,$value);
    }

    /**
     * Undocumented function
     *
     * @param [type] $offset
     * @return void
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Undocumented function
     *
     * @param [type] $offset
     * @return void
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Undocumented function
     *
     * @param [type] $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function serialize()
    {
        return array(
            'data'  => $this->data
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $content = unserialize($serialized);

        foreach ($content['data'] as $name => $value) {
            $this->__set($name, $value);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function jsonSerialize()
    {
        return array(
            'data'=>$this->toArray()
        );
    }

    /**
     * Retrieves the item with given properties
     *
     * @param string[] $properties
     * @return array
     */
    public function toArray($properties = null)
    {
        if (empty($properties)) {
            return $this->data;
        }
        return ArrayHelper::filterKeys($this->data, $properties);
    }
}
