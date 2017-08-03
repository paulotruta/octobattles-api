<?php
/**
 * Autoload classes from the appropriate locations, only when needed.
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 */

spl_autoload_register( function( $classname ) {

	if ( ! strstr( $classname, 'Test' ) ) {
		$classmodule = strtok( $classname, '_' );

		$classname = str_replace( $classmodule . '_', $classmodule . 's/', $classname );

		if ( API_DEBUG_LEVEL >= 2 ) {
			echo 'Loading new class via autoloader: <br>';
			var_dump( __DIR__ . '/' . $classname . '.php' );
		}

		if ( file_exists( __DIR__ . '/' . $classname . '.php' ) ) {
			require __DIR__ . '/' . $classname . '.php';
		}
	}

});
