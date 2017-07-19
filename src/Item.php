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

	// INSTANTIATION STATIC METHODS
	/**
	 *	Returns a new instance that contains data as fields.
	 *
	 *	@param $type:string Type of item.
	 *	@param $data:array Contains a pair of $name => $value for fields.
	 *	
	 *	@return Item Instance that contains data.
	 */
	static public function fromData($type, $data)
	{
		$item = new Item($type);

		if (!isset(self::$types[$type]['fields'])) {
			self::$types[$type]['fields'] = array();
		}

		self::$types[$type]['fields'] =
			Arrays::mergeDictionaries(
				self::$types[$type]['fields'],
				array_flip(array_keys($data)),
				$updatedKeys
			);

		foreach ($updatedKeys as $key => $i) {
			$item->data[$i] = $data[$key];
		}

		return $item;
	}

	/**
	 *	Returns a new Instance that contains data fields, but
	 *	fields are indexed on custom way.
	 *
	 *	@param $type:string Type of item.
	 *	@param $data:array Contains indexed fields values.
	 *	@param $index:array Contains a pair of $name => $index for fields.
	 *
	 *	@return Item Instance that contains indexed data.
	 */
	static public function fromIndexedData($type, $data, $index)
	{
		$item = new Item($type);

		if (!isset(self::$types[$type]['fields'])) {
			self::$types[$type]['fields'] = array();
		}

		self::$types[$type]['fields'] =
			Arrays::mergeDictionaries(
				self::$types[$type]['fields'],
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

	// FIELD/ATTRIBUTES INDEX STATIC METHODS

	/**
	 *	Retrieves a list of fields names for a given type name
	 *
	 *	@param $type:string Name of type
	 *
	 *	@return string[] Returns a string array with the fields name. If
	 *	type doesn't exists an empty array will be returned.
	 */
	static public function getFieldsListOfType($type)
	{
		if (isset(self::$types[$type]['fields'])) {
			return array_keys(self::$types[$type]['fields']);
		}
		return array();
	}

	/**
	 * Returns the fields position index for a given type.
	 * 
	 * @param  string $type Desired type index to be returned.
	 * 
	 * @return array Returns the fields position index for the given type.
	 * If there's no index for the type, an empty array will be returned.
	 */
	static public function getFieldsIndexOfType($type)
	{
		if (isset(self::$types[$type]['fields'])) {
			return self::$types[$type]['fields'];
		}
		return array();
	}

	/**
	 *	
	 */
	static public function getFieldIndexOfType($type, $name, $autocreate = false)
	{
		if (!isset(self::$types[$type]['fields'])) {
			if (!$autocreate) {
				return null;
			}
			self::$types[$type]['fields'] = array();
		}
		$attrIndex = &self::$types[$type]['fields'];

		if (!isset($attrIndex[$name])) {
			if (!$autocreate) {
				return null;
			}
			$attrIndex[$name] = empty($attrIndex) ? 0 : max($attrIndex) + 1;
		}
		return self::$types[$type]['fields'][$name];
	}

	static public function addFieldsToIndexOfType($type, $new_fields)
	{
		if (!isset(self::$types[$type]['fields'])) {
			self::$types[$type]['fields'] = array();
		}
		self::$types[$type]['fields'] = Arrays::mergeDictionaries(
				self::$types[$type]['fields'],
				$new_fields,
				$updatedKeys
			);
		return $updatedKeys;
	}
	
	static public function addAliasToFieldOfType($type, $field, $alias)
	{
		if (!self::isValidFieldName($alias)) {
			trigger_error("Invalid field name {$alias}.");
		}
		$index = self::getFieldIndexOfType($type, $name);
		if (is_null($index)) {
			trigger_error("Given name '{$field}' doesn't exists on type '{$type}'.");
			return false;
		}
		if (isset(self::$types[$type]['fields'][$alias])) {
			trigger_error("Given alias '{$alias}' name already exists on type '{$type}'");
			return false;
		}
		self::$types[$type]['fields'][$alias] = $index;
		return $index;
	}
	/**
	 *	
	 */
	protected function getFieldIndex($name, $autocreate = false)
	{
		return static::getFieldIndexOfType($this->type, $name, $autocreate);
	}

	/**
	 *	
	 */
	public function setAlias($alias, $field)
	{
		return static::setFieldAlias($this->type, $field, $alias, false);
	}

	/**
	 *	Checks if given field name is a valid one.
	 *
	 *	@param $name:string Name to be checked.
	 *	@return boolean Indicate TRUE when is a valid field name, FALSE
	 *	otherwise.
	 */
	static private function isValidFieldName($name)
	{
		if (is_string($name) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name)) {
			return true;
		}
		return false;
	}
	/**
	 *	Sets an field value.
	 *
	 *	@param $name:string Name of the field.
	 *	@param $value:mixed Value of the field.
	 */
	public function __set($name, $value)
	{
		// Checks whether field name is numeric only and reports error.
		if (!self::isValidFieldName($name)) {
			$name =
				is_array($name) ? 'array' :
				is_object($name) ? 'object' :
				$name;
			trigger_error(
				"Invalid field name, given '{$name}'",
				E_USER_ERROR
			);
			return;
		}
		// Retrieves the field column index if exists, registers new if
		// the specified name is not registered.
		$index = $this->getFieldIndex($name, true);

		// Sets field value.
		$this->data[$index] = $value;
	}
	/**
	 *	Retrieves field value. If field is unset or doesn't exists
	 *	function returns NULL.
	 *
	 *	@param $name:string Name of the field.
	 *	
	 *	@return mixed Field value or null if field is not set.
	 */
	public function __get($name)
	{
		// Retrieves the field column index, if field name does not
		// exists returns null.
		$index = $this->getFieldIndex($name);

		// Returns null if field does not exists or data isn't set.
		if (null === $index || !isset($this->data[$index])) {
			return null;
		}

		return $this->data[$index];
	}
	/**
	 *	Checks wheter the field contains any value set.
	 *	
	 *	@param $name:string Name of the field.
	 *
	 *	@return boolean TRUE if attibute isset, even if it is null, FALSE if 
	 *	isn't set.
	 */
	public function __isset($name)
	{
		$index = $this->getFieldIndex($name);

		if (null === $index || !isset($this->data[$index])) {
			return false;
		}

		return true;
	}

	/**
	 *	Unsets value field on object
	 *
	 *	@param $name:string Name of field to be unseted
	 */
	public function __unset($name)
	{
		$index = $this->getFieldIndex($name);

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
	 *	Retrieves a list of fields names that exists on current Item object.
	 */
	public function getFieldsList()
	{
		return array_keys(
				array_intersect(
					self::$types[$this->type]['fields'],
					array_keys($this->data)
				)
			);
	}

	/**
	 *	Converts all data fields in the object to an associative array.
	 *	Field alias will be included as independent values.
	 */
	public function dataToArray($fieldsList = array())
	{
		if (empty($fieldsList)) {
			$fieldsList = $this->getFieldsList();
		}

		$content = array();
		foreach ($fieldsList as $field) {
			$content[$field] = $this->__get($field);
		}

		return $content;
	}

	/**
	 *	Specifies default fields that should be included when the json
	 *	format is requested.
	 *
	 *	@param $fields:array A list of fields.
	 *
	 */
	public function setDefaultJsonFields($fields)
	{
		$this->defaultJsonFields = $fields;
	}
	
	/**
	 *	Retrieves an array with fields and values.
	 */
	public function jsonSerialize()
	{
		if (empty($this->defaultJsonFields)) {
			return $this->dataToArray();
		} else {
			return $this->dataToArray($this->defaultJsonFields);
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