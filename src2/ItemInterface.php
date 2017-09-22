<?php
namespace Francerz\PhpModel;

use \ArrayAccess;
use \Serializable;
use \JsonSerializable;

/**
 * 
 * INHERITED METHODS:
 *  ArrayAccess:
 *      offsetSet($offset, $value)
 *      offsetExists($offset)
 *      offsetGet($offset)
 *      offsetUnset($offset)
 *  Serializable:
 *      serialize()
 *      unserialize($serialized)
 *  JsonSerializable:
 *      jsonSerialize()
 * 
 */
interface ItemInterface extends ArrayAccess, Serializable, JsonSerializable
{
    /**
     * Sets value to item attribute
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value);

    /**
     * Checks if attribute contains any value.
     * 
     * If attribute's value is set to null, this function will return false.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name);

    /**
     * Retrieves the attribute's value
     * 
     * If attribute is not set, then a notices will be thrown and function will
     * return false.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name);

    /**
     * Unsets the attribute's value.
     *
     * @param string $name
     */
    public function __unset($name);

    /**
     *
     * @param string[] $properties
     * @return array
     */
    public function toArray($properties = null);
}