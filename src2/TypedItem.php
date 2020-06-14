<?php

namespace Francerz\PhpModel;

class TypedItem implements ItemInterface
{
    static private $types = array();
    private $type;

    /**
     * Undocumented function
     *
     * @param string $type
     * @param array $data
     */
    public function __construct($type, $data = null)
    {
        if (!isset(static::$types[$type])) {
            static::$types[$type] = array(
                'properties' => new PropertiesList()
            );
        }
        $this->type = $type;
        $this->data = array();
        if ($data !== null && is_array($data)) {
            $this->setProperties($data);
        }
    }

    protected function setProperties($properties)
    {
        foreach ($properties as $name => $value) {
            $this->__set($name, $value);
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

        $index = $this->getPropertiesList()->addOrGet($name);
        $this->data[$index] = $value;
    }

    /**
     * 
     */
    public function __isset($name)
    {
        $index = $this->getPropertiesList()->get($name);
        if (is_null($index)) {
            return false;
        }
        return isset($this->data[$index]);
    }

    /**
     * 
     */
    public function __get($name)
    {
        $index = $this->getPropertiesList()->get($name);
        if (is_null($index) || !$this->__isset($name)) {
            trigger_error("Undefined attribute ($name) in TypedItem object.", E_USER_NOTICE);
            return null;
        }
        return $this->data[$index];

    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     */
    public function __unset($name)
    {
        $index = $this->getPropertiesList()->get($name);
        if (is_null($index) || !array_key_exists($index, $this->data)) {
            return;
        }
        unset($this->data[$index]);
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
        $this->__set($offset, $value);
    }
    
    /**
     * Undocumented function
     *
     * @param [type] $offset
     * @return void
     */
    public function offsetExists($offset)
    {
        $this->__isset($offset);
    }

    /**
     * Undocumented function
     *
     * @param [type] $offset
     * @return void
     */
    public function offsetGet($offset)
    {
        $this->__get($offset);
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
    protected function toArray($properties = null)
    {
        $data = array();

        $props = $this->getPropertiesList();
        if (empty($properties)) {
            foreach ($properties as $property) {
                $index = $props->get($property);
                $data[$property] = is_null($index) ? null : $this->data[$index];
            }
        } else {
            foreach ($this->data as $key => $value) {
                $props = $props->find($key);
                foreach ($props as $prop) {
                    $data[$prop] = $value;
                }
            }
        }
        return $data;
    }

    /**
     *
     */
    public function serialize()
    {
        return array(
            'type' => $this->type,
            'data' => $this->toArray()
        );
    }

    /**
     * Undocumented function
     *
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->type = $data['type'];
        foreach ($data['data'] as $name => $value) {
            $this->__set($name, $value);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getPropertiesList()
    {
        return static::$types[$this->type]['properties'];
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function jsonSerialize()
    {
        return array(
            'type'  => $this->getType(),
            'data'  => $this->toArray()
        );
    }
}