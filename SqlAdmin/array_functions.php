<?

function array_unset_keys($original, $keys_to_remove)
{
	$cleaned = $original;
	foreach ($keys_to_remove as $key)
	{
		unset($cleaned[$key]);
	}
	return $cleaned;
}
function array_extract($original, $keys_to_extract)
{
	$subset = array();
	if (!is_array($keys_to_extract))
	{
		$keys_to_extract = explode('|', $keys_to_extract);
	}

	foreach ($keys_to_extract as $index => $key)
	{
		$subset[$key] = $original[$key];
	}
	return $subset;
}

function array_update_existing($original, $overwriting)
{
	if (!is_array($overwriting) || count($overwriting) == 0)
		return $original;

	foreach ($original as $key => $value)
		if (array_key_exists($key, $overwriting))
			$original[$key] = $overwriting[$key];
	return $original;
}

function array_from_object($fields)
{
	if (is_object($fields))
	{
		$tmp = array();
		foreach ($fields as $attribute => $value)
		{
			$tmp[$attribute] = $value;
		}
		$fields = $tmp;
	}

	if (!is_array($fields))
		return null;

	return $fields;
}

/**
 * In order to overwrite the default options (which are embedded in a multidimensional array of multidimensional arrays of... etc), we need
 * something a little different from both array_merge() and array_merge_recursive(). This function behaves similarly, to each, but works for
 * the specific purposes of overriding values for saving to a JSON blob.
 * 
 * This function does the following with two arrays:
 * - If a new key exists in the overriding array, add the new key/value to the original array.
 * - If a key in the original array doesn't exist in the overriding array, leave the existing key/value as is.
 * - If a key exists in BOTH arrays and...
 *   -> ... that key's value is a not an array, replace the value in the original array with that in the overriding array.
 *   -> ... that key's value is an array, recurse through that array and munge as above, then replace the value at this key with the munged result.
 * 
 * The result is a multidimensional array with at least all the original keys still included, and additional key/value pairs as needed from the
 * overriding array and any values in the original array with the same keys in the overriding array being replaced. It is essentially a middle
 * ground between array_merge() and array_merge_recursive().
 *
 * @author Damien Wilmann
 * @param array $original_array
 * @param array $overriding_array
 * @return array
 */	
function array_munge($original_array, $overriding_array) {
	// Go through all the key/value pairs that we need to add or replace.
	foreach($overriding_array as $key => $val) {
		// Since PHP arrays are mutable, just pretend the value already existed and we're replacing it.
		if (@is_array($original_array[$key]) && @is_array($val)) {
			// We're replacing an array with an array here, so we need to munge those, too.
			$original_array[$key] = array_munge($original_array[$key], $val);
		}
		else {
			// It's a standard (non-array) value, so go nuts and just replace it.
			$original_array[$key] = $val;
		}
	}
	
	return $original_array;
}

