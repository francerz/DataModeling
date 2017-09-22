<?php
namespace Francerz\PhpModel;

use \Countable;
use \Iterator;

/**
 * INHERITED METHODS
 *  ArrayAccess:
 *      offsetSet($offset, $value)
 *      offsetExists($offset)
 *      offsetGet($offset)
 *      offsetUnset($offset)
 *  Serializable:
 *      serialize()
 *      unserialize()
 *  JsonSerializable:
 *      jsonSerialize()
 *  ItemInterface:
 *      __set($name)
 *      __isset($name)
 *      __get($name)
 *      __unset($name)
 *  Countable:
 *      count()
 *  Iterator:
 *      rewind()
 *      current()
 *      key()
 *      next()
 *      valid()
 */
interface CollectionInterface extends ItemInterface, Countable, Iterator
{
    /**
     * Appends a new item at the end of the collection.
     *
     * @param ItemInterface $item
     */
    public function push(ItemInterface $item);

    /**
     * Removes the last item on the collection, and returns it's value.
     * 
     * @return ItemInterface
     */
    public function pop();

    /**
     * Retrieves the column contents into a array, paired by the current
     * collection keys.
     *
     * @param string $column
     * @return array
     */
    public function getColumnValues($column);

    /**
     * Undocumented function
     *
     * @param string[] $properties
     * @return array
     */
    public function itemsToArray($properties = null);
}