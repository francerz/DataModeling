<?php

namespace Francerz\DataModeling;

use \ArrayAccess;
use \Serializable;
use \JsonSerializable;
use \Iterator;
use \Countable;
use \InvalidArgumentException;

class ItemsList extends Item implements
	ArrayAccess,
	Serializable,
	JsonSerializable,
	Iterator,
	Countable
{
	private $items;
	private $parsed;

	public function __construct($type)
	{
		parent::__construct($type);
		$this->items = array();
		$this->parsed = array();
	}

	/**
	 *
	 */
	public function offsetExists($offset)
	{
		if (is_numeric($offset)) {
			return isset($this->items[$offset]);
		}
		return parent::offsetExists($offset);
	}

	/**
	 *
	 */
	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset)) {
			return null;
		}
		if (is_int($offset)) {

			// Checks if item is already parsed and parses it if not.
			if (!isset($this->parsed[$offset])) {

				// Parsing the row to object and save it to quick access
				// and processing time saving :)
				$this->parseItemAt($offset);
			}
			return $this->parsed[$offset];
		}
		return parent::offsetGet($offset);
	}

	private function parseItemAt($offset)
	{
		$this->parsed[$offset] = Item::fromRawData(
				$this->getType(),
				$this->items[$offset]
			);
	}

	/**
	 *
	 */
	public function offsetSet($offset, $value)
	{
		if ($offset === null) {
			$this->push($value);
		} elseif (is_numeric($offset) && !is_integer($offset)) {
			// Triggers error reporting that given index is not integer value
			trigger_error(
				"Numeric list index must be integer value.",
				E_USER_ERROR
			);
		} elseif(!$value instanceof Item) {
			// Triggers error reporting that given value is not Item
			trigger_error(
				"Value must be type ".__NAMESPACE__."\\Item.",
				E_USER_ERROR
			);
		} elseif($value->getType() !== $this->getType()) {
			// Triggers error reporting that given Item::type must be same as list
			trigger_error(
				"Item type must match with list type. Required {$this->getType()}, given {$value->getType()}",
				E_USER_ERROR
			);
		} elseif (is_integer($offset)) {
			$this->items[$offset] = &$value->getRawData();
			$this->parsed[$offset] = $value;
			return;
		} else {
			parent::offsetSet($offset, $value);
		}
	}

	/**
	 *
	 */
	public function offsetUnset($offset)
	{
		if (is_numeric($offset)) {
			unset($this->items[$offset]);
		}
		parent::offsetUnset($offset);
	}

	/**
	 *	
	 */
	public function rewind()
	{
		return reset($this->items);
	}

	/**
	 *
	 */
	public function current()
	{
		$key = $this->key();
		if (!isset($this->parsed[$key])) {
			$this->parseItemAt($key);
		}
		return $this->parsed[$key];
	}

	/**
	 *	
	 */
	public function key()
	{
		return key($this->items);
	}

	/**
	 *	
	 */
	public function next()
	{
		return next($this->items);
	}

	/**
	 *	
	 */
	public function valid()
	{
		return key($this->items) !== null;
	}

	/**
	 *	
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * Pushes an element to the end at the list
	 * 
	 * @param  Item $item Item to be pushed
	 */
	public function push($item)
	{
		if (!$item instanceof Item) {
			trigger_error("Must be ".__NAMESPACE__."\\Item type", E_USER_ERROR);
		} elseif ($item->getType() !== $this->getType()) {
			trigger_error("Item must be same type as list", E_USER_ERROR);
		} else {
			array_push($this->items, $item);
		}
	}

	/**
	 * Pop the element off the end of the list
	 * 
	 * @return Item Last element of the list
	 */
	public function pop()
	{
		if (empty($this->items)) {
			return null;
		}
		return array_pop($this->items);
	}

	/**
	 * Returns an string representation of an object
	 * 
	 * @return string Representation of object
	 */
	public function serialize()
	{
		return serialize([
			'items' => $this->items,
			'@parent' => parent::serialize()
		]);
	}
	/**
	 * Parses a serialized string to an object
	 * 
	 * @param  string $serialized string representation of the object
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);

		$this->items = $data['items'];
		parent::unserialize($data['@parent']);
	}

	/**
	 * Retrieves an array with the values for an especified column name.
	 *
	 * @param string $columnName Name of the column
	 *
	 * @return  array An array that contents the values for the given column
	 * associated to current item key. If an items doesn't contains value for
	 * given column, null will be put instead.
	 */
	public function getColumnValues($columnName)
	{
		$index = $this->getAttributeIndex($columnName);
		
		$column = array();
		foreach ($this->items as $key => $item) {
			$column[$key] = isset($item[$index]) ? $item[$index] : null;
		}
		return $column;
	}

	/**
	 * [setDefaultJsonAttributes description]
	 * 
	 * @param string[] $attributes List of attributes names to be included on
	 * resulting JSON.
	 */
	public function setDefaultJsonAttributes($attributes)
	{
		$this->defaultJsonAttributes = $attributes;
	}

	/**
	 * [itemsToArray description]
	 * 
	 * @param  string[]  $attributesList List of attributes that MAY be included
	 * for each item on the list.
	 * 
	 * @return array Items list on array data structure.
	 */
	public function itemsToArray($attributesList = array())
	{
		if (empty($attributesList)) {
			$items = array();
			foreach ($this as $key => $item) {
				$items[$key] = $item->dataToArray();
			}
			return $items;
		} else {
			$items = array();
			foreach ($this as $key => $item) {
				$items[$key] = $item->dataToArray($attributesList);
			}
			return $items;
		}
	}

	/**
	 * Especifies the data that should serialize for JSON formatting.
	 * 
	 * @return array Contains every attribute and items.
	 */
	public function jsonSerialize()
	{
		$json = parent::jsonSerialize();

		if (empty($this->defaultJsonAttributes)) {
			$json['items'] = $this->itemsToArray();
		} else {
			$json['items'] = $this->itemsToArray($this->defaultJsonAttributes);
		}

		return $json;
	}

	/**
	 * Creates an ItemsList from a given type and data matrix
	 * 
	 * @param  string $type Type of the ItemsList
	 * @param  matrix $data Matrix that contains rows of data
	 * 
	 * @return ItemsList Created list
	 */
	static public function fromData($type, $data)
	{
		$keys = array_keys(call_user_func_array('array_merge',$data));

		$updatedKeys = static::addFieldsToTypeIndex($type, array_flip($keys));

		$list = new ItemsList($type);

		foreach ($data as $key => $item) {
			foreach ($item as $field => $value) {
				$list->items[$key][$updatedKeys[$field]] = $value;
			}
		}

		return $list;
	}
}