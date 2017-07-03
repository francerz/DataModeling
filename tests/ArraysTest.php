<?php

use Francerz\DataModeling\Arrays;
use PHPUnit\Framework\TestCase;

class ArraysTest extends TestCase
{
	public function testMergingDictionaries()
	{
		$existent = array('a'=>0, 'b'=>1, 'c'=>2);
		$entries = array('c'=>0, 'd'=>0, 'e'=>1);

		$new_dict = Arrays::mergeDictionaries($existent, $entries, $updated);

		$expectedDict = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>2, 'e'=>3);
		$this->assertEquals($expectedDict, $new_dict);

		$expectedUpdated = array('c'=>2, 'd'=>2, 'e'=>3);
		$this->assertEquals($expectedUpdated, $updated);
	}
	public function testMergingDictionariesWithEmptyExistent()
	{
		$existent = array();
		$entries = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>1);

		$new_dict = Arrays::mergeDictionaries($existent, $entries, $updated);

		$expectedDict = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>1);
		$this->assertEquals($expectedDict, $new_dict);

		$expectedUpdated = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>1);
		$this->assertEquals($expectedUpdated, $updated);
	}
	public function testMergingDictionariesWithEmptyEntries()
	{
		$existent = array('a'=>0, 'b'=>1, 'c'=>'2', 'd'=>1);
		$entries = array();

		$new_dict = Arrays::mergeDictionaries($existent, $entries, $updated);

		$expectedDict = array('a'=>0, 'b'=>1, 'c'=>'2', 'd'=>1);
		$this->assertEquals($expectedDict, $new_dict);

		$expectedUpdated = array();
		$this->assertEquals($expectedUpdated, $updated);
	}
}