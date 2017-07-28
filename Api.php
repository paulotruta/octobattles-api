<?php



class Api {

	/**
	 * The versions this api support.
	 *
	 * @var array
	 */
	private static $versions = array(
		'v1.0',
	);

	/**
	 * Allowed response formats.
	 *
	 * @var array
	 */
	private static $formats = array(
		'json'
	);

	/**
	 * References each accepted format serializer method names.
	 * Those methods will be used to decode to PHP array, and encode back to the appropriate response format.
	 *
	 * @var array
	 */
	private static $serializers = array(
		'json' => array(
			'decoder' => 'json_decode',
			'encoder' => 'json_encode',
		),
	);

	/**
	 * Allowed request shcemes.
	 *
	 * @var array
	 */
	private static $schemes = array( 'http', 'https' );

	/**
	 * The available endpoints. Each key represents the endpoint name, being the value thee regex for allowed parameters and respective types.
	 *
	 * @var array
	 */
	private static $endpoints = array(
		'characters' => 'characters(?:/?([0-9]+)?)',
	);

	/**
	 * Allowed Request Methods.
	 *
	 * @var array
	 */
	private static $methods = array(
		'GET',
		'POST',
		'PUT',
		'DELETE',
		'OPTIONS',
	);

	/**
	 * Request variable to hold information while handling a request.
	 *
	 * @var array
	 */
	private static $request = array(
		'version' => null,
		'format' => null,
	);

	/**
	 * Holds the raw input data for a request.
	 *
	 * @var null
	 */
	public static $input = null;

	/**
	 * Holds the parsed input data (parameters) for a request.
	 *
	 * @var array
	 */
	public static $data = array();

	const RESPONSE = array(
		'ok' => 'OK',
		'err' => 'ERROR',
	);

	const TOKEN = array(
		'REQUEST' => 'your-token',
		'COOKIE' => 'token',
	);

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
	 * Checks if the scheme of request is valid.
	 *
	 * @param string $request_scheme The scheme from wich the request was made.
	 * @return bool Self-explanatory
	 */
	public static function check_scheme( $request_scheme ) {
		if ( in_array( $request_scheme, self::$schemes ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Sets necessary response headers depending on request type and origin.
	 *
	 * @param  array $cookies Any cookies to be set on the response. Each cookie must be an iterative array representing the setcookie method arguments.
	 */
	public static function headers( $cookies = array() ) {

		$ref = null;
		if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {
			$ref = $_SERVER['HTTP_ORIGIN'];
		} elseif ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$ref = $_SERVER['HTTP_REFERER'];
		}

		$origin = '*';

		if ( ! empty( $ref ) ) {
			$ref_data = parse_url( $ref );

			if ( ! empty( $ref_data ) && isset( $ref_data['scheme'] ) &&  self::check_scheme( $ref_data['scheme'] ) ) {
				$origin = $ref_data['host'];

				$origin = ( $origin == $_SERVER['HTTP_HOST'] ) ? $info['scheme'] . '://' . $origin : $origin;

			}
		}

		// Time to actually generate the headers for the request.
		header_remove( 'Set-Cookie' );
		$headers = array(
			'Access-Control-Allow-Origin' => null,
			'Access-Control-Expose-Headers' => null,
			'Access-Control-Allow-Header' => null,
			'Access-Control-Allow-Credentials' => null,
			'Access-Control-Allow-Methods' => null,
			'Access-Control-Max-Age' => null,
			'X-Authorization' => null,
		);

		if ( '*' == $origin || ! empty( $_SERVER['HTTP_ACCESS_CONTROL_HEADERS'] ) ) {
			$headers['Access-Control-Allow-Origin'] = '*';
			$headers['Access-Control-Expose_headers'] = 'x-authorization';
			$headers['Access-Control-Allow-Headers'] = 'origin, content-type, accept, x-authorization';
			$headers['X-Authorization'] = self::TOKEN['REQUEST'];
		} else {
			$headers['Access-Control-Allow-Origin'] = $origin;
			$headers['Access-Control-Expose-Headers'] = 'cookie, set-cookie';
			$headers['Access-Control-Allow-Header'] = 'origin, content-type, accept, cookie, set-cookie';
			$headers['Access-Control-Allow-Credentials'] = 'true';

			setcookie(
				self::TOKEN['COOKIE'],
				self::TOKEN['REQUEST'],
				(time() + 86400 * 30),
				'/',
				'.' . $_SERVER['HTTP_HOST']
			);

			foreach ( $cookies as $cookie ) {
				setcookie( ...$cookie );
			}
		}

		$headers['Access-Control-Allow-Methods'] = implode( ', ', self::$methods );
		$headers['Access-Control-Max-Age'] = '86400';

		foreach ( $headers as $header_key => $header_value ) {
			header( $header_key . ': ' . $header_value );
		}

	}

	/**
	 * Handling multipart request data for PUT requests.
	 * See https://stackoverflow.com/questions/5483851/manually-parse-raw-http-data-with-php
	 *
	 * @param  string $input raw request input.
	 * @param  array  $data Associative array of data.
	 * @return array  The multipart parsed data.
	 */
	private function handle_multipart( $input, $data ) {

		$matches = null;
		preg_match( '/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches );
		$request_boundary = $matches[1];

		$request_blocks = preg_split( "/-+$boundary/", $input );
		array_pop( $request_blocks );

		foreach ( $blocks as $block ) {

			if ( ! empty( $block ) ) {
				// Parse the uploaded file.
				if ( strpos( $block, 'application/octet-stream' ) !== false ) {
					preg_match( '/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches );
				} else {
					preg_match( '/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches );
				}

				$data[ $matches[1] ] = $matches[2];
			}
		}

		return $data;

	}

	/**
	 * Parses a request to the API using server input variable information.
	 *
	 * @return array|bool An array with information on endpoint name, method used and parameters passed in the request.
	 */
	private function parse_request() {

		$path = null;
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
				self::handle_multipart( self::$input, self::$data );
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

	/**
	 * Given a request array with keys "endpoint", "method" and "parameters", this method runs the respective request method.
	 *
	 * Example request array:
	 * 	$request = array(
	 * 		'endpoint => 'characters',
	 * 		'method' => 'GET',
	 * 		'arguments' => array(),
	 * 	);
	 *
	 * @param  array|null $request A request array.
	 * @throws \Exception When request invalid or endpoint method not existant.
	 */
	private static function run_endpoint_method( array $request = null ) {
		if ( ! empty( $request ) ) {
			$endpoint_classname = 'endpoints_' . ucfirst( $request['endpoint'] );
			if ( class_exists( $endpoint_classname ) ) {
				$endpoint_class = new $endpoint_classname();
				if ( ! method_exists( $endpoint_class, $request['method'] ) ) {
					// TODO: Send 404 error on invalid endpoint method request.
					self::error_response( 'Request method does not exist.' );
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
				self::error_response( 'An error ocurred while fillind the request.' );
				throw new \Exception( 'Unable to load endpoint class ' . $endpoint_class . ' to build a response.' );
			}
		} else {
			self::error_response( 'Request not valid.' );
			throw new \Exception( 'Request not valid.' );
		}
	}

	/**
	 * Builds and outputs an error response.
	 *
	 * @param  integer     $code    The http error code to produce.
	 * @param  string|null $context Error context to send.
	 */
	private static function error_response( $context = null,  $code = 404 ) {
		http_response_code( $code );

		header( 'Content-type: application/' . self::$request['format'] . '; charset=utf-8' );
		self::headers();

		echo call_user_func_array(
			self::$serializers[ self::$request['format'] ]['encoder'],
			array(
				array(
					'status' => self::RESPONSE['error'],
					'info' => $context,
				),
			)
		);

	}

	/**
	 * The response method, to be used by endpoint to automatically encode the data into the appropriate requested format.
	 *
	 * @param  array $output The array of output to be sent, as generated by the endpoint class.
	 * @param  array $meta Any metadata to output with the response.
	 * @param  array $cookies Any cookies needed to send in the response headers.
	 */
	private static function response( array $output = null, array $meta = null, array $cookies = null ) {

		http_response_code( 200 );
		header( 'Content-type: application/' . self::$request['format'] . '; charset=utf-8' );
		self::headers( $cookies );

		echo call_user_func_array(
			self::$serializers[ self::$request['format'] ]['encoder'],
			array(
				array(
					'metadata' => $meta,
					'status' => self::RESPONSE['ok'],
					'result' => $output,
				),
			)
		);
	}

	/**
	 * Main API Handler.
	 */
	public static function handler() {
		$request = self::parse_request();
		self::run_endpoint_method( $request );
	}

}
