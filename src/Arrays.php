<?php
namespace Francerz\DataModeling;

class Arrays
{
	static public function valuesIndex($arr)
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

	static public function replaceKeys($array, $replace)
	{
		$newArray = array();
		foreach ($array as $key => $value) {
			$newArray[$replace[$key]] = $value;
		}
		return $newArray;
	}
}