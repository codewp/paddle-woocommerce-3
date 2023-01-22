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

function paddle_register_polyfills() {
	global $wp_version;

	$handles = array(
		'react'        => array( '17.0.2', array() ),
		'react-dom'    => array( '17.0.2', array( 'react' ) ),
		'wp-i18n'      => array( '6.0', array() ),
		'wp-hooks'     => array( '6.0', array() ),
		'wp-api-fetch' => array( '6.0', array() ),
	);
	foreach ( $handles as $handle => $value ) {
		if ( ! version_compare( $wp_version, '5.9', '>=' ) && in_array( $handle, array( 'react', 'react-dom' ) ) ) {
			wp_deregister_script( $handle );
		}

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			wp_register_script(
				$handle,
				plugins_url( 'assets/js/vendor/' . $handle . '.js', ASNP_PADDLE_WC_PLUGIN_FILE ),
				$value[1],
				$value[0],
				true
			);
		}
	}
}

function paddle_get_template( $template_name, $args ) {
	return wc_get_template(
		$template_name,
		$args,
		'',
		plugin_dir_path( ASNP_PADDLE_WC_PLUGIN_FILE ) . 'templates/'
	);
}
