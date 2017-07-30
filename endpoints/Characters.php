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

	private $model;

	/**
	 * Custom constructor for this endpoint. Associates a model to the class that allows persistance of data and can be accessed by all methods of this class.
	 */
	function __construct() {
		$this -> model = new model_Character();
	}

	/**
	 * Characters endpoint GET request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function get( $params ) {


		if ( isset( $params[1] ) ) {
			// Return a single character.
			$character = $this -> model -> model_from_db( intval( $params[1] ) );
			$info = get_object_vars( $character );

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

		if ( empty( self::$data['name'] ) ) {
			parent::error_response( 'Please provide the necessary arguments for this endpoint method.' );
			return false;
		}

		$this -> model -> name = self::$data['name'];
		$persisted = $this -> model -> save();

		if ( $persisted ) {
			parent::response( get_object_vars( $this -> model ) );
		} else {
			parent::error_response( 'The provided name already exists for a living creature in the system!' );
		}

	}

	public function delete( $params ) {
		parent::response( array('message' => 'DELETE METHOD' ) );
	}
}
