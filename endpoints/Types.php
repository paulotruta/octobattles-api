<?php

/**
 * The Api endpoint class for the Types feature of the octobattles project.
 * This class handles GET request for the /characters/ endpoint.
 *
 * Each endpoint method receives a $params array, being index 0 the requested path, and subsequential indexes the request parameters
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 */
class endpoint_Types extends Api {

	use trait_OctobattlesType;

	/**
	 * Types endpoint GET request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function get( $params ) {

		if ( ! empty( $params[1] ) ) {
			$type = self::$types[ $params[1] ];
			if ( ! empty( $type ) ) {
				parent::response( $type );

			} else {
				parent::error_response( 'Type not found. Please provide a valid type name.' );
				return false;
			}
		} else {
			return parent::response(
				self::$types
			);
		}
	}
}
