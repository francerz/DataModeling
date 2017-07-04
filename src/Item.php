<?php

// File: Item.php
// Author: Francerz

namespace Francerz\DataModeling;

use \ArrayAccess;
use \Serializable;
use \JsonSerializable;

class Item implements ArrayAccess, Serializable, JsonSerializable
{
	static private $types = array();
	static private $nullTypeId = 0;

	private $data;
	private $type;

	/**
	 *	Creates a new instance of object with an specified type identifier.
	 *
	 *	@param $type:string Type Identifier.
	 */
	public function __construct($type)
	{
		// $this->type = ($type !== null ? $type : 'autoType'.static::$nullTypeId);
		$this->type = $type;
		$this->data = array();
	}

	/**
	 *	Returns a new instance that contains data as attributes.
	 *
	 *	@param $type:string Type of item.
	 *	@param $data:array Contains a pair of $name => $value for attributes.
	 *	
	 *	@return Item Instance that contains data.
	 */
	static public function fromData($type, $data)
	{
		$item = new Item($type);

		if (!isset(self::$types[$type]['attributes'])) {
			self::$types[$type]['attributes'] = array();
		}

		self::$types[$type]['attributes'] =
			Arrays::mergeDictionaries(
				self::$types[$type]['attributes'],
				array_flip(array_keys($data)),
				$updatedKeys
			);

		foreach ($updatedKeys as $key => $i) {
			$item->data[$i] = $data[$key];
		}

		return $item;
	}

	/**
	 *	Returns a new Instance that contains data attributes, but
	 *	attributes are indexed on custom way.
	 *
	 *	@param $type:string Type of item.
	 *	@param $data:array Contains indexed attributes values.
	 *	@param $index:array Contains a pair of $name => $index for attributes.
	 *
	 *	@return Item Instance that contains indexed data.
	 */
	static public function fromIndexedData($type, $data, $index)
	{
		$item = new Item($type);

		if (!isset(self::$types[$type]['attributes'])) {
			self::$types[$type]['attributes'] = array();
		}

		self::$types[$type]['attributes'] =
			Arrays::mergeDictionaries(
				self::$types[$type]['attributes'],
				$index,
				$updatedKeys
			);

		foreach ($data as $name => $value) {
			$item->data[$updatedKeys[$name]] = $data[$index[$key]];
		}

		return $item;
	}

	/**
	 * Creates an item object with raw data format
	 * 
	 * This method MUST be only used under controlled circumstances where
	 * item attibutes index and type is properly registered, otherwise,
	 * unexpectd behavior MAY appear.
	 * 
	 * @param  string $type  Item Type.
	 * @param  array &$data Raw data content.
	 * 
	 * @return Item Created item.
	 */
	static public function fromRawData($type, $data)
	{
		$item = new Item($type);
		$item->data = $data;

		return $item; 
	}

	/**
	 *	Retrieves a list of attributes names for a given type name
	 *
	 *	@param $type:string Name of type
	 *
	 *	@return string[] Returns a string array with the attributes name. If
	 *	type doesn't exists an empty array will be returned.
	 */
	static public function getAttributesOfType($type)
	{
		if (isset(self::$types[$type]['attributes'])) {
			return array_keys(self::$types[$type]['attributes']);
		}
		return array();
	}

	/**
	 * Returns the attributes position index for a given type.
	 * 
	 * @param  string $type Desired type index to be returned.
	 * 
	 * @return array Returns the attributes position index for the given type.
	 * If there's no index for the type, an empty array will be returned.
	 */
	static public function getFieldsIndexOfType($type)
	{
		if (isset(self::$types[$type]['attributes'])) {
			return self::$types[$type]['attributes'];
		}
		return array();
	}

	static public function addFieldsToTypeIndex($type, $new_fields)
	{
		if (!isset(self::$types[$type]['attributes'])) {
			self::$types[$type]['attributes'] = array();
		}
		self::$types[$type]['attributes'] = Arrays::mergeDictionaries(
				self::$types[$type]['attributes'],
				$new_fields,
				$updatedKeys
			);
		return $updatedKeys;
	}

	public function getFieldsIndex()
	{
		return self::getFieldsIndexOfType($this->type);
	}

	/**
	 *	
	 */
	static public function getAttributeIndexByType($type, $name, $autocreate = false)
	{
		if (!isset(self::$types[$type]['attributes'])) {
			if (!$autocreate) {
				return null;
			}
			self::$types[$type]['attributes'] = array();
		}
		$attrIndex = &self::$types[$type]['attributes'];

		if (!isset($attrIndex[$name])) {
			if (!$autocreate) {
				return null;
			}
			$attrIndex[$name] = empty($attrIndex) ? 0 : max($attrIndex) + 1;
		}
		return self::$types[$type]['attributes'][$name];
	}

	/**
	 *	Create an alias to an existent attribute.
	 *
	 *	@param $type:string Type Identifier.
	 *	@param $name:string Attribute name.
	 *	@param $alias:string Alias to attribute.
	 *
	 *	@return int|false Returns the index value for created alias, or FALSE if 
	 *	given attribute doesn't exists or alias overwrites existing.
	 */
	static public function setAttributeAlias($type, $name, $alias, $overwrite = false)
	{
		$index = self::getAttributeIndexByType($type, $name);
		if (is_null($index) || (isset(self::$types[$type]['attributes'][$alias]) && !$overwrite)) {
			return false;
		}

		self::$types[$type]['attributes'][$alias] = $index;
		return $index;
	}
	/**
	 *	
	 */
	protected function getAttributeIndex($name, $autocreate = false)
	{
		return static::getAttributeIndexByType($this->type, $name, $autocreate);
	}

	/**
	 *	
	 */
	public function setAlias($alias, $attribute)
	{
		return static::setAttributeAlias($this->type, $attribute, $alias, false);
	}

	/**
	 *	Checks if given attribute name is a valid one.
	 *
	 *	@param $name:string Name to be checked.
	 *	@return boolean Indicate TRUE when is a valid attribute name, FALSE
	 *	otherwise.
	 */
	private function isValidName($name)
	{
		if (is_string($name) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name)) {
			return true;
		}
		return false;
	}
	/**
	 *	Sets an attribute value.
	 *
	 *	@param $name:string Name of the attribute.
	 *	@param $value:mixed Value of the attribute.
	 */
	public function __set($name, $value)
	{
		// Checks whether attribute name is numeric only and reports error.
		if (!$this->isValidName($name)) {
			$name =
				is_array($name) ? 'array' :
				is_object($name) ? 'object' :
				$name;
			trigger_error(
				"Invalid attribute name, given '{$name}'",
				E_USER_ERROR
			);
			return;
		}
		// Retrieves the attribute column index if exists, registers new if
		// the specified name is not registered.
		$index = $this->getAttributeIndex($name, true);

		// Sets attribute value.
		$this->data[$index] = $value;
	}
	/**
	 *	Retrieves attribute value. If attribute is unset or doesn't exists
	 *	function returns NULL.
	 *
	 *	@param $name:string Name of the attribute.
	 *	
	 *	@return mixed Attribute value or null if attribute is not set.
	 */
	public function __get($name)
	{
		// Retrieves the attribute column index, if attribute name does not
		// exists returns null.
		$index = $this->getAttributeIndex($name);

		// Returns null if attribute does not exists or data isn't set.
		if (null === $index || !isset($this->data[$index])) {
			return null;
		}

		return $this->data[$index];
	}
	/**
	 *	Checks wheter the attribute contains any value set.
	 *	
	 *	@param $name:string Name of the attribute.
	 *
	 *	@return boolean TRUE if attibute isset, even if it is null, FALSE if 
	 *	isn't set.
	 */
	public function __isset($name)
	{
		$index = $this->getAttributeIndex($name);

		if (null === $index || !isset($this->data[$index])) {
			return false;
		}

		return true;
	}

	/**
	 *	Unsets value attribute on object
	 *
	 *	@param $name:string Name of attribute to be unseted
	 */
	public function __unset($name)
	{
		$index = $this->getAttributeIndex($name);

		if (null !== $index && isset($this->data[$index])) {
			unset($this->data[$index]);
		}
	}

	/**
	 *	Checks if especified offset exists
	 *
	 *	@param $offset:string Offset value to be checked.
	 *
	 *	@return boolean Returns TRUE when offset exists, FALSE otherwise.
	 */
	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	/**
	 *	Retrieves value for an especified offset.
	 *
	 *	@param $offset:string Offset key to be returned.
	 *
	 *	@return mixed Returns value on given offset or NULL in case that offset
	 *	isn't already set.
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 *	Sets a value on the especified offset.
	 *
	 *	@param $offset:string Offset key
	 *	@param $value:mixed Value to be associated on given offset.
	 */
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}

	/**
	 *	Unsets the value on especified offset.
	 *
	 *	@param $offset:string Offest key to be unseted.
	 */
	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}

	/**
	 *	Serializes object content to be exported or saved.
	 */
	public function serialize()
	{
		return serialize([
			'type' => $this->type,
			'data' => $this->dataToArray()
		]);
	}

	/**
	 *	Unserializes a php serialized string that represents an especific object
	 *	state.
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
	 *	Retrieves the Item object type.
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 *	Retrieves a list of attributes names that exists on current Item object.
	 */
	public function getAttributesList()
	{
		return array_keys(
				array_intersect(
					self::$types[$this->type]['attributes'],
					array_keys($this->data)
				)
			);
	}

	/**
	 *	Converts all data attributes in the object to an associative array.
	 *	Field alias will be included as independent values.
	 */
	public function dataToArray($attributesList = array())
	{
		if (empty($attributesList)) {
			$attributesList = $this->getAttributesList();
		}

		$content = array();
		foreach ($attributesList as $field) {
			$content[$field] = $this->__get($field);
		}

		return $content;
	}

	/**
	 *	Specifies default attributes that should be included when the json
	 *	format is requested.
	 *
	 *	@param $attributes:array A list of attributes.
	 *
	 */
	public function setDefaultJsonAttributes($attributes)
	{
		$this->defaultJsonAttributes = $attributes;
	}
	
	/**
	 *	Retrieves an array with attributes and values.
	 */
	public function jsonSerialize()
	{
		if (empty($this->defaultJsonAttributes)) {
			return $this->dataToArray();
		} else {
			return $this->dataToArray($this->defaultJsonAttributes);
		}
	}

	/**
	 * Retrieves the raw data content of item object.
	 * This means that will be returned a numeric indexed array.
	 * 
	 * @return array numeric indexed array with values.
	 */
	public function &getRawData()
	{
		return $this->data;
	}
}