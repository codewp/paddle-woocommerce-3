<?php
defined( 'ABSPATH' ) || exit;

/**
 * Inserts a new key/value after the key in the array.
 *
 * @param $needle The array key to insert the element after
 * @param $haystack An array to insert the element into
 * @param $new_key The key to insert
 * @param $new_value An value to insert
 * @return The new array if the $needle key exists, otherwise an unmodified $haystack
 */
function paddle_array_insert_after( $needle, $haystack, $new_key, $new_value ) {

	if ( array_key_exists( $needle, $haystack ) ) {

		$new_array = array();

		foreach ( $haystack as $key => $value ) {

			$new_array[ $key ] = $value;

			if ( $key === $needle ) {
				$new_array[ $new_key ] = $new_value;
			}
		}

		return $new_array;
	}

	return $haystack;
}
