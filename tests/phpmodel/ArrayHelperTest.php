<?php

use Francerz\PhpModel\ArrayHelper;
use Francerz\PhpModel\Item;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
    public function testValuesIndexer()
    {
        $array = array(
            1 => "alpha",
            2 => "bravo",
            3 => "charlie",
            4 => "bravo",
            5 => "Charlie",
            6 => "",
            "a" => "bravo"
        );
        
        $expected = array(
            "alpha" => array(1),
            "bravo" => array(2,4,"a"),
            "charlie" => array(3),
            "Charlie" => array(5),
            "" => array(6)
        );
        
        $multimap = ArrayHelper::valuesIndexer($array);

        $this->assertEquals($expected, $multimap);
    }

    public function testMergeDictionaries()
    {
        $existent = array('a'=>0, 'b'=>1, 'c'=>2);
		$entries = array('c'=>0, 'd'=>0, 'e'=>1);

		$new_dict = ArrayHelper::mergeDictionaries($existent, $entries, $updated);

		$expectedDict = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>2, 'e'=>3);
		$this->assertEquals($expectedDict, $new_dict);

		$expectedUpdated = array('c'=>2, 'd'=>2, 'e'=>3);
		$this->assertEquals($expectedUpdated, $updated);
    }
	public function testMergingDictionariesWithEmptyExistent()
	{
		$existent = array();
		$entries = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>1);

		$new_dict = ArrayHelper::mergeDictionaries($existent, $entries, $updated);

		$expectedDict = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>1);
		$this->assertEquals($expectedDict, $new_dict);

		$expectedUpdated = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>1);
		$this->assertEquals($expectedUpdated, $updated);
	}
	public function testMergingDictionariesWithEmptyEntries()
	{
		$existent = array('a'=>0, 'b'=>1, 'c'=>'2', 'd'=>1);
		$entries = array();

		$new_dict = ArrayHelper::mergeDictionaries($existent, $entries, $updated);

		$expectedDict = array('a'=>0, 'b'=>1, 'c'=>'2', 'd'=>1);
		$this->assertEquals($expectedDict, $new_dict);

		$expectedUpdated = array();
		$this->assertEquals($expectedUpdated, $updated);
    }
    
    public function testReplaceKeys()
    {
        $array = array(
            1 => "alpha",
            2 => "bravo",
            3 => "charlie"
        );

        $replace = array(
            1 => "one",
            2 => "two",
            3 => "three"
        );

        $expected = array(
            "one" => "alpha",
            "two" => "bravo",
            "three" => "charlie"
        );

        $result = ArrayHelper::replaceKeys($array, $replace);

        $this->assertEquals($expected, $result);
    }

    public function matrixTestData()
    {
        return array(
            1 => ["alpha" => 1, "bravo" => 2],
            2 => ["bravo" => 3, "charlie" => 4],
            4 => ["bravo"=>5],
            8 => new Item(["alpha"=>"one", "bravo"=>"two", "charlie"=>"three"])
        );
    }

    public function testGetColumnNullIndexKey()
    {
        $matrix = $this->matrixTestData();
        $expected = array(
            1 => 1,
            2 => null,
            4 => null,
            8 => "one"
        );

        $result = ArrayHelper::getColumn($matrix, 'alpha', null);

        $this->assertEquals($expected, $result);
    }

    public function testGetColumnWithColumnAndIndexKey()
    {
        $matrix = $this->matrixTestData();
        $expected = array(
            2 => 1,
            3 => null,
            5 => null,
            "two" => "one"
        );

        $result = ArrayHelper::getColumn($matrix, 'alpha', 'bravo');

        $this->assertEquals($expected, $result);
    }

    public function testGetColumnNullColumnKey()
    {
        $matrix = $this->matrixTestData();

        $expected = ArrayHelper::replaceKeys($matrix, array(
            1 => 2,
            2 => 3,
            4 => 5,
            8 => "two"
        ));

        $result = ArrayHelper::getColumn($matrix, null, 'bravo');

        $this->assertEquals($expected, $result);
    }

    public function testGetColumnNullColumnAndIndexKeys()
    {
        $expected = $matrix = $this->matrixTestData();

        $result = ArrayHelper::getColumn($matrix, null, null);

        $this->assertEquals($expected, $result);
    }

    public function filterKeysDataProvider()
    {
        return array(
           "regular" => array(
               ["first","second","third"],
               [1,3],
               [1=>"second",3=>null]
           ),
           "numIndexed" => array(
                [0 => "first", 2 => "second", 4 => "third"],
                [0,4],
                [0=>"first",4=>"third"]
           ),
           "associative" => array(
               ["a" => "first", "b" => "second", "c" => "third"],
               ["b","c"],
               ["b"=>"second","c"=>"third"]
           )
        );
    }

    /**
     * @dataProvider filterKeysDataProvider
     */
    public function testFilterKeys($array, $keys, $expected)
    {
        $result = ArrayHelper::filterKeys($array, $keys);

        $this->assertEquals($expected, $result);
    }

    public function testHasNumericKeys()
    {
        $array = array();
        $result = ArrayHelper::hasNumericKeys($array);
        $this->assertFalse($result);
        
        $array = ["first","second","third"];
        $result = ArrayHelper::hasNumericKeys($array);
        $this->assertTrue($result);

        $array = ["a"=>"first","second","c"=>"third"];
        $result = ArrayHelper::hasNumericKeys($array);
        $this->assertTrue($result);

        $array = ["a"=>"first","b"=>"second","c"=>"third"];
        $result = ArrayHelper::hasNumericKeys($array);
        $this->assertFalse($result);
    }

    public function testIsAssociative()
    {
        $array = array();
        $result = ArrayHelper::isAssociative($array);
        $this->assertFalse($result);

        $array = ["first","second","third"];
        $result = ArrayHelper::isAssociative($array);
        $this->assertFalse($result);

        $array = ["a"=>"first","second","c"=>"third"];
        $result = ArrayHelper::isAssociative($array);
        $this->assertFalse($result);

        $array = ["a"=>"first","b"=>"second","c"=>"third"];
        $result = ArrayHelper::isAssociative($array);
        $this->assertTrue($result);
    }

    public function testIsIndexed()
    {
        $array = array();
        $result = ArrayHelper::isIndexed($array);
        $this->assertFalse($result);

        $array = ["first","second","third"];
        $result = ArrayHelper::isIndexed($array);
        $this->assertTrue($result);

        $array = ["a"=>"first","second","c"=>"third"];
        $result = ArrayHelper::isIndexed($array);
        $this->assertFalse($result);

        $array = ["a"=>"first","b"=>"second","c"=>"third"];
        $result = ArrayHelper::isIndexed($array);
        $this->assertFalse($result);
    }
    
    public function testGetAssociative()
    {
        $array = array();
        $result = ArrayHelper::getAssociative($array);
        $this->assertEmpty($result);

        $array = ["first","second","third"];
        $result = ArrayHelper::getAssociative($array);
        $this->assertEmpty($result);
        
        $array = ["a"=>"first","second","c"=>"third"];
        $expected  = ["a"=>"first","c"=>"third"];
        $result = ArrayHelper::getAssociative($array);
        $this->assertEquals($expected, $result);

        $array = ["a"=>"first","b"=>"second","c"=>"third"];
        $expected = ["a"=>"first","b"=>"second","c"=>"third"];
        $result = ArrayHelper::getAssociative($array);
        $this->assertEquals($expected, $result);
    }
        
    public function testGetIndexed()
    {
        $array = array();
        $result = ArrayHelper::getIndexed($array);
        $this->assertEmpty($result);

        $array = ["first","second","third"];
        $expected = $array;
        $result = ArrayHelper::getIndexed($array);
        $this->assertEquals($expected, $result);
        
        $array = ["a"=>"first","second","c"=>"third",2=>"fourth"];
        $expected  = [0=>"second",2=>"fourth"];
        $result = ArrayHelper::getIndexed($array);
        $this->assertEquals($expected, $result);
        
        $array = ["a"=>"first","b"=>"second","c"=>"third"];
        $result = ArrayHelper::getIndexed($array);
        $this->assertEmpty($result);
    }

    public function testIsSequential()
    {
        $array = array();
        $result = ArrayHelper::isSequential($array);
        $this->assertFalse($result);

        $array = ["first","second","third"];
        $result = ArrayHelper::isSequential($array);
        $this->assertTrue($result);

        $array = [0=>"first",1=>"second","third"];
        $result = ArrayHelper::isSequential($array);
        $this->assertTrue($result);

        $array = [0=>"first",1=>"second",4=>"third"];
        $result = ArrayHelper::isSequential($array);
        $this->assertFalse($result);

        $array = ["first","second","third","foo"=>"bar"];
        $result = ArrayHelper::isSequential($array);
        $this->assertFalse($result);

        $array = [0=>"first",2=>"third",1=>"second"];
        $result = ArrayHelper::isSequential($array);
        $this->assertFalse($result);
    }
}