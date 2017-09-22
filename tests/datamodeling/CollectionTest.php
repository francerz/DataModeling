<?php

use Francerz\DataModeling\Item;
use Francerz\DataModeling\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
	/**
	 *	@test
	 */
	public function testCollectionInstantiation()
	{
		$list = new Collection('tipoA');

		$this->assertInstanceOf(Collection::class, $list);

		return $list;
	}

	/**
	 *	@test
	 *	@expectedException ArgumentCountError
	 *	@expectedException PHPUnit_Framework_Error
	 */
	public function testBadCollectionInstantiation()
	{
		$list = new Collection();
	}

	public function itemsProvider()
	{
		return array(
				[0, new Item('tipoA')],
				[1, new Item('tipoA')]
			);
	}
	public function badItemsProvider()
	{
		return array(
				"stringValue"		=> [1, "cadena"],
				"numberValue"		=> [2, 123],
				"arrayValue"		=> [3, array(1,2,3)],
				"assocArrayValue"	=> [4, array('a'=>1,'b'=>2)],
				"floatIndex"		=> [4.2, new Item('tipoA')],
				"numericStringIndex"=> ['6', new Item('tipoA')],
				"unmatchItemType"	=> [5, new Item('tipoB')],
				"itemsNamedField"=>['items', new Item('tipoB')],
			);
	}
	public function fieldsProvider()
	{
		return array(
				['field', new Item("tipoC")],
				['_nombre'	, "con cadena"],
				['s24'		, "segundo 24"]
			);
	}

	/**
	 *	@test
	 *	@depends testCollectionInstantiation
	 *	@dataProvider itemsProvider
	 */
	public function testSettingItems($index, $valor, $list)
	{
		$list = clone $list;

		$list[$index] = $valor;

		$this->assertEquals($valor, $list[$index]);
	}

	/**
	 *	@test
	 *	@depends testCollectionInstantiation
	 *	@dataProvider itemsProvider
	 *	@expectedException PHPUnit_Framework_Error
	 */
	public function testSettingItemsAsFields($index, $valor, $list)
	{
		$list = clone $list;

		$list->$index = $valor;

		$this->assertEquals($valor, $list->$index);
	}

	/**
	 *	@test
	 *	@depends testCollectionInstantiation
	 *	@dataProvider badItemsProvider
	 *	@expectedException PHPUnit_Framework_Error
	 */
	public function testSettingBadItems($index, $valor, $list)
	{
		$list = clone $list;

		$list[$index] = $valor;

		$this->assertNotEquals($valor, $list[$index]);
	}

	/**
	 *	@test
	 *	@depends testCollectionInstantiation
	 *	@dataProvider fieldsProvider
	 */
	public function testSettingFields($name, $value, $list)
	{
		$list = clone $list;

		$list->$name = $value;

		// retrieving through Item magic __get
		$this->assertEquals($value, $list->$name);

		// retrieving through Item implemented ArrayAccess::offsetGet
		$this->assertEquals($value, $list[$name]);
	}

	/**
	 *	@test
	 *	@depends testCollectionInstantiation
	 */
	public function testPushingContent($list)
	{
		$list = clone $list;

		// Checks that list is empty
		$this->assertCount(0, $list);

		// Creates a new item and appends to list
		$item = $newItem = new Item('tipoA');
		$item->primer = 1;
		$list[] = $item;

		// Checks that list counting increments to 1
		$this->assertCount(1, $list);

		// Creates another item and appends to list
		$item = $newItem = new Item ('tipoA');
		$item->segundo = 2;
		$list->push($item);

		// Checks that list counting increments to 2
		$this->assertCount(2, $list);

		// Unsets current item variable and checksit to following tests
		$item = null;
		$this->assertNull($item);

		// Pops the last element and checks if corresponds to itself
		$item = $list->pop();
		$this->assertEquals($newItem, $item);

		// Checks that list couting decrements to 1
		$this->assertCount(1, $list);
	}

	/**
	 *	@test
	 */
	public function testJsonSerializing()
	{
		$list = new Collection('tipoB');

		$list->field1 = 'First Field';
		$list->field2 = 'Second Field';

		$list[0] = new Item('tipoB');
		$list[0]->alfa = 'a';
		$list[0]->bravo = 'b';

		$list[1] = new Item('tipoB');
		$list[1]->charlie = 'c';
		$list[1]->delta = 'd';

		$jsonString = json_encode($list->jsonSerialize());

		$expectedJsonString = '{'.
			'"field1":"First Field",'.
			'"field2":"Second Field",'.
			'"items":['.
				'{"alfa":"a","bravo":"b"},'.
				'{"charlie":"c","delta":"d"}'.
			']'.
		'}';

		$this->assertEquals($expectedJsonString, $jsonString);
	}

	/**
	 * @test
	 */
	public function testMatrixParsing()
	{
		$matrix = array(
				["alfa"=> 1, "bravo"=> 2, "charlie" => 3],
				["alfa"=> 4, "bravo"=> 5, "delta" =>6],
				["bravo"=>7, "charlie"=>8]
			);

		$list = Collection::fromData("matrixed", $matrix);

		$this->assertEquals(1,$list[0]->alfa);
		$this->assertEquals(2,$list[0]['bravo']);
		$this->assertEquals(3,$list[0]->charlie);

		$this->assertEquals(4,$list[1]->alfa);
		$this->assertEquals(5,$list[1]->bravo);
		$this->assertNull($list[1]->charlie);
		$this->assertEquals(6,$list[1]->delta);

		$item2 = $list[2];
		$this->assertNull($item2->alfa);
		$this->assertEquals(7, $item2->bravo);
		$this->assertEquals(8, $item2->charlie);

		$this->assertCount(3, $list);

		return $list;
	}

	/**
	 * @test
	 * @depends  testMatrixParsing
	 */
	public function testRetrieveColumn($list)
	{
		$column = $list->getColumnValues('bravo');

		$expected = array(0=>2, 1=>5, 2=>7);
		$this->assertEquals($expected, $column);

		$column = $list->getColumnValues('charlie');

		$expected = array(0=>3, 1=>null, 2=>8);
		$this->assertEquals($expected, $column);

		return $list;
	}

	/**
	 * @test
	 * @depends testRetrieveColumn
	 */
	public function testUpdatingItemAndArray($list)
	{
		$list = clone $list;

		$item = $list[1];

		$column = $list->getColumnValues('charlie');
		$this->assertNull($column[1]);

		$value = 'testingUpdate';
		$item->charlie = $value;
		$column = $list->getColumnValues('charlie');

		$this->assertEquals($value, $item->charlie);
		$this->assertEquals($value, $item['charlie']);
		$this->assertEquals($value, $column[1]);

		$newItem = new Item('matrixed');
		$newItem->newField = '1234';
		$list[3] = $newItem;
		$this->assertEquals('1234',$list[3]['newField']);

		$list[3]['newField'] = '4321';
		$this->assertEquals('4321',$list[3]->newField);

		$column = $list->getColumnValues('newField');
		$this->assertEquals('4321',$column[3]);
	}

	/**
	 * @test
	 * @depends  testMatrixParsing
	 */
	public function testSerializing($list)
	{
		$serialized = serialize($list);

		$this->assertNotNull($serialized);

		return $serialized;
	}

	/**
	 * @test
	 * @depends testSerializing
	 */
	public function testUnserializing($serializedList)
	{
		$list = unserialize($serializedList);

		$this->assertEquals(1,$list[0]->alfa);
		$this->assertEquals(2,$list[0]['bravo']);
		$this->assertEquals(3,$list[0]->charlie);


		$this->assertEquals(4,$list[1]->alfa);
		$this->assertEquals(5,$list[1]->bravo);
		$this->assertEquals(6,$list[1]->delta);

		$item2 = $list[2];
		$this->assertNull($item2->alfa);
		$this->assertEquals(7, $item2->bravo);
		$this->assertEquals(8, $item2->charlie);

		$this->assertCount(3, $list);
	}

	/**
	 * @test
	 * @depends  testMatrixParsing
	 */
	public function testCloning($list)
	{
		$copy = clone $list;

		$this->assertEquals($copy, $list);

		$copy[] = new Item('matrixed');

		$this->assertNotEquals($copy, $list);

		$item0 = $list[0];
		$copy0 = $copy[0];

		$this->assertEquals($copy0, $item0);

		$copy0 = clone $item0;
		$copy0->alfa = "New Value";
		$item0->alfa = "Another Thing";

		$this->assertNotEquals($copy0, $item0);
	}
}