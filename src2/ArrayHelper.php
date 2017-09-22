<?php
namespace Francerz\PhpModel;

use \ArrayAccess;

class ArrayHelper
{
	static public function valuesIndexer($arr)
	{
		$values = array_unique($arr, SORT_STRING);
		$new = array();
		foreach ($values as $val) {
			$new[$val] = array_keys($arr,$val);
		}
		return $new;
	}
	/**
	 * @param  array
	 * @return array[array]
	 */
	static public function expand($arr)
	{
		
	}

	/**
	 *	Merges two dictionary arrays. Each dictionary array must contain string
	 *	based keys and numerical values.
	 *
	 *	@param $existent:array Contains base dictionary indexes.
	 *	@param $entries:array Contains new key entries and indexes.
	 *	@param &$updated_entries:array Optional parameter. Retrieves new entries
	 *	values obtained by merging.
	 *
	 *	@return array A value sorted array with updated dictionary entries.
	 */
	static public function mergeDictionaries($existent, $entries, &$updated_entries = null)
	{
		$return = $existent;
		$updated_entries = array();

		if (empty($entries)) {
			// asort($return);
			return $return;
		}

		$interkeys = array_intersect_key($return, $entries);
		
		foreach($interkeys as $key => $val) {
			// Sets existent index to selected key.
			$updated_entries[$key] = $existent[$key];

			// Finds all aliases to already existent key.
			$aliases = array_keys($entries, $entries[$key], true);
			foreach ($aliases as $alias) {
				$updated_entries[$alias] = $return[$alias] = $existent[$key];
			}
		}

		// Find all entries that aren't in new array
		$diffkeys = array_diff_key($entries, $return);
		$max_index = empty($return) ? -1 : max($return);

		foreach ($diffkeys as $key => $val) {
			// Checks whether key is already assigned and skip it.
			if (array_key_exists($key, $updated_entries)) {
				continue;
			}

			// Sets new index to selected key.
			$updated_entries[$key] = $return[$key] = ++$max_index;

			// Finds all aliases to selected key and sets index.
			$aliases = array_keys($entries, $val, true);
			foreach ($aliases as $alias) {
				$updated_entries[$alias] = $return[$alias] = $return[$key];
			}
		}

		// asort($return);

		return $return;
	}

	/**
	 * @param array $array
	 * @param array $replace
	 * @return array
	 */
	static public function replaceKeys($array, $replace)
	{
		$newArray = array();
		foreach ($array as $key => $value) {
			$newArray[$replace[$key]] = $value;
		}
		return $newArray;
	}

	/**
	 * Return the values from a single column in the input array.
	 * 
	 * The main difference between this and array_column is that if $index_key
	 * is null, then returned column will preserve $input index association.
	 * 
	 * @param array $input
	 * @param mixed $column_key
	 * @param mixed $index_key
	 * @return array
	 */
	static public function getColumn(array $input, $column_key, $index_key = null)
	{
		// /**
		//  * Performs core library funtion behavior to speed operation, but keeps
		//  * implementation because of compatilibity issues with previous versions.
		//  */
		// if (version_compare(PHP_VERSION,"7.0.0",">")) {
		// 	return array_column($input, $column_key, $index_key);
		// }
		
		// Initializing column variable that will be returned.
		$column = array();

		if (!is_null($column_key) && is_null($index_key)) {
			/**
			 * The most common column retrieve, where the column key is given, but no
			 * the index_key.
			 */
			foreach ($input as $key => $item) {
				if (is_array($item) || $item instanceof ArrayAccess) {
					$column[$key] = isset($item[$column_key]) ? $item[$column_key] : null;
				} elseif (is_object($item)) {
					$column[$key] = isset($item->{$column_key}) ? $item->{$column_key} : null;
				}
			}
		} elseif (!is_null($column_key) && !is_null($index_key)) {
			/**
			 * The second most common column rerieve, where column_key and index_key
			 * is given.
			 */
			foreach ($input as $key => $item) {
				if (is_array($item) || $item instanceof ArrayAccess) {
					$key = $item[$index_key];
					$column[$key] = isset($item[$column_key]) ? $item[$column_key] : null;
				} elseif (is_object($item)) {
					$key = $item->{$index_key};
					$column[$key] = isset($item->{$column_key}) ? $item->{$column_key} : null;
				}
			}
		} elseif (is_null($column_key) && !is_null($index_key)) {
			/**
			 * The third most common column retrieve, where index_key is given,
			 * but column_key is keep as null.
			 */
			foreach ($input as $key => $item) {
				if (is_array($item) || $item instanceof ArrayAccess) {
					$key = $item[$index_key];
					$column[$key] = $item;
				} elseif (is_object($item)) {
					$key = $item->{$index_key};
					$column[$key] = $item;
				}
			}
		} else {
			/**
			 * The fourth and last posible, retrieve, where column_key and index_key
			 * are null, therefore input array will be returned as it is.
			 */
			$column = $input;
		}

		return $column;
	}

	/**
	 * Returns an array that contains the designated keys, if a key isn't set on
	 * the array, then returned array will contain the key but null value.
	 *
	 * @param array $array
	 * @param string[] $keys
	 * @return array
	 */
	static public function filterKeys($array, $keys)
	{
		$new = array();
		foreach ($keys as $key) {
			$new[$key] = isset($array[$key]) ? $array[$key] : null;
		}
		return $new;
	}

	/**
	 * Based upon:
	 * https://stackoverflow.com/a/4254008
	 * 
	 * @return boolean
	 */
	static public function hasNumericKeys($array)
	{
		return count(array_filter(array_keys($array),'is_numeric')) > 0;
	}

	/**
	 * Based upon:
	 * https://stackoverflow.com/a/4254008
	 * 
	 * @return boolean
	 */
	static public function isAssociative($array)
	{
		if (empty($array)) return false;
		return count(array_filter(array_keys($array),'is_string')) === count($array);
	}

	/**
	 * Based upon:
	 * https://stackoverflow.com/a/4254008
	 * 
	 * @return boolean
	 */
	static public function isIndexed($array)
	{
		if (empty($array)) return false;
		return count(array_filter(array_keys($array),'is_int')) === count($array);
	}

	/**
	 * @param array $array
	 * @return array
	 */
	static public function getAssociative($array)
	{
		// Compatibility to PHP versions lower than 5.6.0
		if (version_compare(PHP_VERSION, "5.6.0", "<")) {
			$keys = array_filter(array_keys($array), function($v) {
				return !self::isIndexKey($v);
			});
			return self::filterKeys($array, $keys);
		}

		return array_filter($array, function($k) {
			return !self::isIndexKey($k);
		}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * @param array $array
	 * @return array
	 */
	static public function getIndexed($array)
	{
		// Compatibility to PHP versions lower than 5.6.0
		if (version_compare(PHP_VERSION, "5.6.0", "<")) {
			$keys = array_filter(array_keys($array), function($v) {
				return self::isIndexKey($v);
			});
			return self::filterKeys($array, $keys);
		}

		return array_filter($array, function($k) {
			return self::isIndexKey($k);
		}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Checks if given key is a valid index key.
	 *
	 * @param mixed $k
	 * @return boolean
	 */
	static private function isIndexKey($k)
	{
		return is_int($k);
	}

	/**
	 * @param mixed $key
	 * @return boolean
	 */
	static private function isValidKey($key)
	{
		return is_int($key)
			|| (is_string($key)
			&& (
				function_exists('mb_strlen') ?
				mb_strlen($key) :
				strlen($key)
			) < 256);
	}


	/**
	 * Based upon:
	 * https://stackoverflow.com/a/173479
	 * 
	 * @param array $array
	 * @param integer $offset
	 * @return boolean
	 */
	static public function isSequential($array, $offset = 0)
	{
		if (!self::isIndexed($array)) return false;
		return array_keys($array) === range($offset, $offset+count($array)-1);
	}
}