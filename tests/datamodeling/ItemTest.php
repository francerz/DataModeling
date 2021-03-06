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
		$this->assertInstanceOf(Item::class, $item);

		return $item;
	}

	/**
	 *	@test
	 *	@expectedException ArgumentCountError
	 *	@expectedException PHPUnit_Framework_Error
	 */
	public function testItemInstantiationWithoutType()
	{
		$item = new Item();
	}

	/**
	 *	List of valid field test cases
	 */
	public function fieldsProvider()
	{
		return array(
				["campoA", "valorA"],
				["camp_B", "valorB"],
				["campo3", "valorC"]
			);
	}

	/**
	 *	List of invalid field test cases
	 */
	public function badFieldsProvider()
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
	 *	@dataProvider fieldsProvider
	 */
	public function testItemFieldsMagicSetGet($field, $value, $item)
	{
		$item = clone $item;

		// testing magic method __set
		$item->$field = $value;

		// testing magic method __get
		$this->assertEquals($value, $item->$field);
		// testing ArrayAccess::offsetGet
		$this->assertEquals($value, $item[$field]);

		return $item;
	}
	
	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider fieldsProvider
	 */
	public function testItemFieldsArraySetGet($field, $value, $item)
	{
		$item = clone $item;

		// testing ArrayAccess::offsetSet
		$item[$field] = $value;

		// testing magic method __get
		$this->assertEquals($value, $item->$field);
		// testing ArrayAccess::offsetGet
		$this->assertEquals($value, $item[$field]);

		return $item;
	}

	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider badFieldsProvider
	 *	@expectedException PHPUnit_Framework_Error
	 */
	public function testItemSetBadWithMagic($field, $value, $item)
	{
		$item = clone $item;

		$item->$field = $value;

		return $item;
	}

	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider badFieldsProvider
	 *	@expectedException PHPUnit_Framework_Error
	 *	@expectedExceptionMessage Invalid field name
	 */
	public function testItemSetBadWithArray($field, $value, $item)
	{
		$item = clone $item;

		$item[$field] = $value;
	}

	/**
	 *	@test
	 *	@depends testItemInstantiation
	 *	@dataProvider fieldsProvider
	 */
	public function testItemGetUnsetFields($field, $value, $item)
	{
		$item = clone $item;

		// testing with magic method __get
		$this->assertNull($item->$field);

		// testing with ArrayAccess:offsetGet
		$this->assertNull($item[$field]);
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

		$fields = $this->fieldsProvider();

		foreach($fields as $value) {
			$attribute = $value[0];
			$item->$attribute = $value[1];
		}

		$serialized = serialize($item);

		$this->assertNotNull($serialized);

		return $serialized;
	}

	/**
	 *	@depends testItemSerialization
	 *	@dataProvider fieldsProvider
	 */
	public function testItemUnserialization($field, $value, $serializedItem)
	{
		$unserialized = unserialize($serializedItem);

		$this->assertEquals($value, $unserialized->$field);

		return $unserialized;
	}

	/**
	 *	@test
	 */
	public function testCreateFromData()
	{
		$fields = $this->fieldsProvider();

		$type = 'alpha';
		$data = array();
		foreach ($fields as $value) {
			$data[$value[0]] = $value[1];
		}

		$item = Item::fromData($type, $data);

		$this->assertInstanceOf(Item::class, $item);

		return $item;
	}
	/**
	 *	@test
	 *	@depends testCreateFromData
	 *	@dataProvider fieldsProvider
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