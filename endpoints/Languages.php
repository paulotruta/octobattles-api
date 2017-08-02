<?php

/**
 * The Api endpoint class for the Languages feature of the octobattles project.
 * This class handles GET and POST requests for the /languages/ endpoint.
 *
 * Each endpoint method receives a $params array, being index 0 the requested path, and subsequential indexes the request parameters
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 */
class endpoint_Languages extends Api {

	use trait_OctobattlesType;
	use trait_OctobattlesLanguage;

	/**
	 * Languages endpoint GET request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function get( $params ) {

		if ( empty( $params[1] ) ) {
			parent::response( self::$languages );
		} else {
			$languages_list = $this -> model -> find(
				array(
					'character_id' => $params[1],
				),
				true
			);

			if ( empty( $languages_list ) ) {
				return parent::error_response( 'No languages found for the provided character id.' );
			}

			return parent::response(
				$languages_list
			);
		}
	}

	/**
	 * Languages endpoint POST request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function post( $params ) {

		if ( empty( $params[1] ) ) {
			parent::error_response( 'A new language can only be added to a character. Please provide a character id as url parameter.' );
		} elseif ( empty( self::$data['name'] ) ) {
			parent::error_response( 'You must provide a valid language name in the request body.' );
		} else {

			if ( ! $this -> get_language( self::$data['name'] ) ) {
				parent::error_response( 'An invalid language name was provided.' );
				return false;
			}

			$this -> model -> character_id = $params[1];
			$this -> model -> name = self::$data['name'];
			$this -> model -> power_level = $this -> get_language( self::$data['name'] )['base_power_level'];
			$this -> model -> type = $this -> get_language( self::$data['name'] )['type'];

			if ( $this -> model -> save() ) {
				parent::response(
					array(
						'message' => 'New language created correctly for character!',
						'info' => get_object_vars( $this -> model ),
					)
				);
			} else {
				parent::error_response( 'Language insertion failed. Please make sure the provided character id is valid and it does not alreay possess this power!' );
			}
		}
	}

	public function delete( $params ) {

		if ( empty( $params[1] ) ) {
			self::error_response( 'Deleting all languages of all characters is not allowed.' );
			return false;
		}

		if ( empty( self::$data['name'] ) ) {
			$character_languages = $this -> model -> find( array(
				'character_id' => $params[1],
			) );

			foreach ( $character_languages as $language ) {
				$language -> delete();
			}

			parent::response( 'Languages were reset for the character.' );

		} else {
			$languages = $this -> model -> find( array(
				'character_id' => $params[1],
				'name' => self::$data['name'],
			) );
			if ( empty( $language[0] ) ) {
				parent::error_response( 'The given language was not found for this character.' );
				return false;
			}

			$language_info = get_object_vars( $languages[0] );
			$languages[0] -> delete();
			parent::response( array(
				'message' => 'Languages were reset for the character.',
				'info' => $language_info,
			) );
		}
	}
}
