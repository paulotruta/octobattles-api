<?php

/**
 * The Api endpoint class for the Characters feature of the octobattles project.
 * This class handles GET, POST, PUT and DELETE requests for the /characters/ endpoint.
 *
 * Each endpoint method receives a $params array, being index 0 the requested path, and subsequential indexes the request parameters
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 */
class endpoint_Characters extends Api {

	use trait_OctobattlesType;

	/**
	 * Characters endpoint GET request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function get( $params ) {


		if ( isset( $params[1] ) ) {
			// Return a single character.
			$character = $this -> model -> model_from_db( intval( $params[1] ) );
			if ( $character ) {
				$info = get_object_vars( $character );
			} else {
				self::error_response( 'Character not found. Please provide a valid character id.' );
				return false;
			}
		} else {
			// Return the list of characters.
			$characters_list = $this -> model -> find();
			new lib_LogDebug( 'Model find result', $characters_list, false );
			$info = array_map( 'get_object_vars', $characters_list );
		}

		// Example response using the API class.
		parent::response(
			$info
		);
	}

	/**
	 * Characters endpoint POST request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function post( $params ) {

		if ( ! empty( $params[1] ) ) {
			parent::error_response( 'Editing a character after its creation is not allowed.' );
			return false;
		}

		if ( empty( self::$data['name'] ) || empty( self::$data['type'] ) ) {
			parent::error_response( 'Please provide the necessary arguments for this endpoint method.' );
			return false;
		}

		if ( ! $this -> get_type( self::$data['type'] ) ) {
			parent::error_response( 'Please provide a valid character type!' );
			return false;
		}

		$this -> model -> name = self::$data['name'];
		$this -> model -> type = self::$data['type'];
		$persisted = $this -> model -> save();

		if ( $persisted ) {
			parent::response( get_object_vars( $this -> model ) );
		} else {
			parent::error_response( 'The provided name already exists for a living creature in the system!' );
		}

	}

	/**
	 * Characters endpoint DELETE request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function delete( $params ) {

		if ( empty( $params[1] ) ) {
			parent::error_response( 'It is not possible to delete all characters at once.' );
			return false;
		}

		$character = $this -> model -> model_from_db( $params[1] );
		if ( ! $character ) {
			parent::error_response( 'Character not found. Please provide a valid character id.' );
			return false;
		}

		$character_last_info = get_object_vars( $character );

		if ( $character -> delete() ) {
			parent::response( array(
				'message' => 'Character deleted successfully.',
				'info' => $character_last_info,
			) );
		} else {
			parent::error_response( 'It was not possible to delete the character.' );
		}

	}
}
