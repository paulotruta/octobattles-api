<?php

class Api {

	private static $versions = array(
		'v1.0',
	);

	private static $formats = array(
		'json',
	);

	private static $endpoints = array(
		'characters' => 'characters(?:/?([0-9]+)?)',
	);

	private static $request = array(
		'version' => null,
		'format' => null,
	);

	public static $input = null;

	public static $data = array();

	const RESPONSE = array(
		'ok' => 'OK',
		'err' => 'ERROR',
	);

	/**
	 * Each endpoint should define a valid regex containing the arguments and valid formats for them.
	 *
	 * @return string The regex of allowed parameters and types for a api endpoint extending this class.
	 */
	abstract protected function get_route_regex();

	/**
	 * Returns the main version being used in the API.
	 *
	 * @return string The version descriptor, normally "v1.0" where 1 is major version and 0 is minor version.
	 */
	public static function get_main_version() {
		return self::$versions[0];
	}

	/**
	 * Returns the main format used in the API responses.
	 *
	 * @return string The format name.
	 */
	public static function get_main_format() {
		return self::$formats[0];
	}

	/**
	 * Checks if the request format is one of the allowed formats.
	 *
	 * @return bool Self-explanatory
	 */
	public static function check_format() {
		if ( in_array( self::$request['format'], self::$formats ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the requested api version is supported.
	 *
	 * @return bool Self-explanatory
	 */
	public static function check_version() {
		if ( in_array( self::$request['version'], self::$versions ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Parses a request to the API using server input variable information.
	 *
	 * @return array|bool An array with information on endpoint name, method used and parameters passed in the request.
	 */
	public function parse_request() {

		$handle_name = null;
		$request_parameters = null;
		$method = null;

		if ( ! empty( $_SERVER['PATH_INFO'] ) ) {
			$path = $_SERVER['PATH_INFO'];
		} elseif ( ! empty( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '?' ) > 0 ) {
			$path = strstr( $_SERVER['REQUEST_URI'], '?', true );
		} else {
			$path = $_SERVER['REQUEST_URI'];
		}

		$request = null;
		preg_match( '#^/?([^/]+?)/.+?\.(.+?)$#', $path, $request );

		if ( ! $request || ! isset( $request_info[2] ) ) {
			return false;
		}

		self::$request['version'] = $request[1];
		self::$request['format'] = $request[2];

		// Support for PUT / DELETE requests comes below.
		self::$input = file_get_contents( 'php://input' );
		if ( ! empty( self::$input ) ) {
			$matches = null;
			preg_match( '/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches );
			if ( isset( $matches[1] ) && strpos( self::$input, $matches[1] ) !== false ) {
				// TODO: self::parse_raw_request(self::input, self::data).
			} else {
				parse_str( self::$input, self::$data );
			}
		}

		$method = strtolower( $_SERVER['REQUEST_METHOD'] );
		if ( 'options' == $method ) {
			self::outputHeaders();
		} else {

			$url_version_part = '/(?:' . implode( '|', self::$versions ) . ')/';
			$url_format_part = '\.(?:' . implode( '|', self::$formats ) . ')';

			foreach ( self::$endpoints as $endpoint_name => $endpoint_regex ) {
				$current_regex = $url_version_part . $endpoint_regex . $url_format_part;
				$request_parameters = null;
				if ( preg_match( '#^' . $regex . '$#', $path, $request_parameters ) ) {
					$handle_name = $endpoint_name;
					break; // WUT?
				}
			}

			if ( ! $handle_name || ! $method || ! $request_parameters ) {
				return false;
			}
		}

		return array(
			'endpoint' => $handle_name,
			'method' => $method,
			'params' => $request_parameters,
		);

	}

	public static function handler() {

		$path = '/';

		$request = self::parse_request();

		if( ! $request ){
			// TODO: Send 404 error on invalid request.
		}

		$endpoint_classname = 'endpoints_' . ucfirst( $request['endpoint'] );
		if ( class_exists( $endpoint_classname ) ) {
			$endpoint_class = new $endpoint_classname();
			if ( ! method_exists( $endpoint_class, $request['method'] ) ) {
				// TODO: Send 404 error on invalid endpoint method request.
				throw new \Exception( 'Request method not implemented for the ' . $endpoint_classname . ' endpoint.' );
			}

			call_user_func_array(
				array(
					$endpoint_class,
					$request['method'],
				),
				$request['parameters']
			);

		} else {
			throw new \Exception( 'Unable to load endpoint class ' . $endpoint_class . ' to build a response.' );
		}
	}

}
