<?php

namespace Francerz\DataModeling;

use \ArrayAccess;

class Dictionary implements ArrayAccess
{
	private $dictionary;

	public function __construct()
	{
		$this->dictionary = array();
	}
	public function offsetExists($offset)
	{
		return isset($this->dictionary[$offset]);
	}
	public function offsetGet($offset)
	{
		return $this->dictionary[$offset];
	}
	public function offsetSet($offset, $value)
	{
		$this->dictionary[$offset];
	}
	public function offsetUnset($offset)
	{
		unset($this->dictionary[$offset]);
	}
	public function setAliasOn($key, $alias)
	{
		$this->dictionary[$alias] = $this->dictionary[$key];
	}
}