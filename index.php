<?php
/**
 * Octobattles-api.
 *
 * @package  octobattles-api
 * @author  Paulo Truta <pinheirotruta5@ŋmail.com>
 * @version  0.1 Initial version with API and ORM implementations.
 */

// PHP errors and warnings displaying.
error_reporting( E_ALL );
ini_set( 'display_errors', 'on' );

// Define various configuration constants.
define( 'API_DEBUG_LEVEL', 1 );
define( 'API_DBHOST', 'localhost' );
define( 'API_DBNAME', 'octobattles' );
define( 'API_DBUSER', 'root' );
define( 'API_DBPASS', 'jpteurotux' );

// Autoload classes from the appropriate locations, only when needed.
// spl_autoload_register( function( $classname ) {

// 	$classmodule = strtok( $classname, '_' );

// 	$classname = str_replace( $classmodule . '_', $classmodule . 's/', $classname );

// 	if ( API_DEBUG_LEVEL >= 2 ) {
// 		echo 'Loading new class via autoloader: <br>';
// 		var_dump( __DIR__ . '/' . $classname . '.php' );
// 	}

// 	if ( file_exists( __DIR__ . '/' . $classname . '.php' ) ) {
// 		require __DIR__ . '/' . $classname . '.php';
// 	}

// });

include( 'autoloader.php' );

// Boot the api.
Api::start();

