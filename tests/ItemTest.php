<?php

use Francerz\DataModeling\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase 
{
	/**
	 *	@test
	 */
	public function testItemInstantiation()
	{
		$item = new Item("tipoA");
		return $item;
	}

	/**
	 *	@test
	 *	@expectedException PHPUnit_Framework_Error
	 */
	public function testItemInstantiationWithoutType()
	{
		$item = new Item();
	}

	/**
	 *	List of valid attribute test cases
	 */
	public function attributesProvider()
	{
		return array(
				["campoA", "valorA"],
				["camp_B", "valorB"],
				["campo3", "valorC"]
			);
	}

	/**
	 *	List of invalid attribute test cases
	 */
	public function badAttributesProvider()
	{
		return array(
				'arrayName'		=> [ array(), "valorArreglo"],
				'numericName'	=> ['123', "valorNumero"],
				'objectName'	=> [ new stdClass(), "valorObjeto"],
				'nullName'		=> [null, "valorNull"],
				'trueName'		=> [true, "valorTrue"],
				'falseName'		=> [false, "valorFalse"],
				'dashedName'	=> ['dashed-name', "valorDashed"],
				'numFirstChar'	=> ['3d', "valorNumFirstChar"],
				'prntsName'		=> ['the(name)', 'valorParentheses'],
				'sqbrktsName'	=> ['the[name]', 'valorSquareBrakets'],
				'plusName'		=> ['a+bName', 'valorPlus'],
				'asteriskName'	=> ['a*bName', 'valorAsterisk'],
				'slashName'		=> ['a/bName', 'valorSlash'],
				'moduleName'	=> ['a%bName', 'valorModule']
			);
	}

	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider attributesProvider
	 */
	public function testItemAttributesMagicSetGet($attribute, $value, $item)
	{
		$item = clone $item;

		// testing magic method __set
		$item->$attribute = $value;

		// testing magic method __get
		$this->assertEquals($value, $item->$attribute);
		// testing ArrayAccess::offsetGet
		$this->assertEquals($value, $item[$attribute]);

		return $item;
	}
	
	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider attributesProvider
	 */
	public function testItemAttributesArraySetGet($attribute, $value, $item)
	{
		$item = clone $item;

		// testing ArrayAccess::offsetSet
		$item[$attribute] = $value;

		// testing magic method __get
		$this->assertEquals($value, $item->$attribute);
		// testing ArrayAccess::offsetGet
		$this->assertEquals($value, $item[$attribute]);

		return $item;
	}

	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider badAttributesProvider
	 *	@expectedException PHPUnit_Framework_Error
	 */
	public function testItemSetBadWithMagic($attribute, $value, $item)
	{
		$item = clone $item;

		$item->$attribute = $value;

		return $item;
	}

	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider badAttributesProvider
	 *	@expectedException PHPUnit_Framework_Error
	 *	@expectedExceptionMessage Invalid attribute name
	 */
	public function testItemSetBadWithArray($attribute, $value, $item)
	{
		$item = clone $item;

		$item[$attribute] = $value;
	}

	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider attributesProvider
	 */
	public function testItemGetUnsetAttributes($attribute, $value, $item)
	{
		$item = clone $item;

		// testing with magic method __get
		$this->assertNull($item->$attribute);

		// testing with ArrayAccess:offsetGet
		$this->assertNull($item[$attribute]);
	}

	/**
	 *	@test
	 *	@depends testItemInstantiation
	 */
	public function testItemJsonSerialization($item)
	{
		$item = clone $item;

		$item->first = 1;
		$item->second = '2';
		$item->third = array(1,2,3);
		$item->fourth = new stdClass();
		$item->fifth = null;
		$item->sixth = true;

		$jsonContent = $item->jsonSerialize();
		$jsonString = '{"first":1,"second":"2","third":[1,2,3],"fourth":{},"fifth":null,"sixth":true}';

		$this->assertEquals($jsonString,json_encode($jsonContent));
	}


	/**
	 *	@depends testItemInstantiation
	 */
	public function testItemSerialization($item)
	{
		$item = clone $item;

		$attributes = $this->attributesProvider();

		foreach($attributes as $value) {
			$item->$value[0] = $value[1];
		}

		$serialized = serialize($item);

		return $serialized;
	}

	/**
	 *	@depends testItemSerialization
	 *	@dataProvider attributesProvider
	 */
	public function testItemUnserialization($attribute, $value, $serializedItem)
	{
		$unserialized = unserialize($serializedItem);

		$this->assertEquals($value, $unserialized->$attribute);

		return $unserialized;
	}

	/**
	 *	@test
	 */
	public function testCreateFromData()
	{
		$attributes = $this->attributesProvider();

		$type = 'alpha';
		$data = array();
		foreach ($attributes as $value) {
			$data[$value[0]] = $value[1];
		}

		$item = Item::fromData($type, $data);

		return $item;
	}
	/**
	 *	@test
	 *	@depends testCreateFromData
	 *	@dataProvider attributesProvider
	 */
	public function testGettingCreatedFromData($name, $value, $item)
	{
		$item = clone $item;

		// Testing magic __get
		$this->assertEquals($value, $item->$name);
		// Testing ArrayAccess::offsetGet
		$this->assertEquals($value, $item[$name]);
	}

	/**
	 * @test
	 * @depends testCreateFromData
	 */
	public function testCloning($item)
	{
		$copy = clone $item;

		$this->assertEquals($copy, $item);

		$copy->newField = 'new Field';

		$this->assertNotEquals($copy, $item);
	}
}