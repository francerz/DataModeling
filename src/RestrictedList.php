<?php

namespace Francerz\DataModeling;

use \InvalidArgumentException;

class RestrictedList extends List
{
	private $type;

	public function __construct($type)
	{
		parent::__construct();
		$this->type = $type;
	}

	public function offsetSet($offset, $value)
	{
		if (is_numeric($offset)
			&& $value instanceof Item 
			&& $value->getType() != $this->type
		) {
			throw new InvalidArgumentException("Value type must be the same as list");
		}
		parent::offsetSet($offset,$value);
	}

	public function serialize()
	{
		return serialize([
			'type'		=> $this->type,
			'@parent'	=> parent::serialize()
		]);
	}

	public function unserialize($serialized)
	{
		$data = unserialize($serialized);

		$this->type = $data['type'];
		parent::unserialize($data['@parent']);
	}

	public function itemsToArray($attributesList = array())
	{
		if (empty($attributesList)) {
			$attributesList = static::getAttributesOfType($this->type);
		}

		$items = array();
		foreach ($this->items as $key => $item) {
			$items[$key] = $item->toArray($attributesList);
		}
		
		return $items;
	}
}