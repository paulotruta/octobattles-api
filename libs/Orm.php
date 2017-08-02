<?php

/**
 * A class that implements the base ORM used by the model classes.
 * Every database connection made throught the API project is done using either this class or a child model class.
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 * @abstract
 */
abstract class lib_Orm {

	/**
	 * The PHP Data Objects instance used to connect and access the MySQL Database
	 *
	 * @var \PDO
	 */
	protected $pdo;

	/**
	 * The table name where operations will be performed
	 *
	 * @var string|null
	 */
	protected $table_name;

	/**
	 * The record primary key
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Constructs this class, making any necessary assignments and adjustments before being available as an instance.
	 *
	 * @throws \Exception When a database connection cannot be established.
	 */
	public function __construct() {

		if ( empty( $id ) ) {
			$this -> id = 0;
		}

		$reflection = new \ReflectionClass( $this );
		$this -> table_name = str_replace( array( 'model_', 'lib_' ), '', strtolower( $reflection -> getShortName() ) . 's' ); // The corresponding table name equals the plural of the child class name in lower characters, without the "model_" class name reference.

		try {
			$connection_details = 'mysql:host=' . API_DBHOST . ';dbname=' . API_DBNAME;

			$this -> pdo = new \PDO(
				$connection_details,
				API_DBUSER,
				API_DBPASS
			);
		} catch ( \Exception $e ) {
			throw new \Exception( 'A database connection could not be established.' );
		}

		// TODO: Implement instantiation with persistant data when a valid id is passed. (model_from_db method).
	}

	/**
	 * Saves the current instance state as persistent data.
	 * If a primary key is defined, the record is updated, else a new record is created and the current instance primary key updated.
	 *
	 * @return int|boolean The number of rows that were modified by the generated SQL statement, or false if an error ocurred.
	 * @throws \Exception In the case an SQL error occurs while executing the resulting generated query.
	 */
	public function save() {

		$result = 0;
		$reflection = new \ReflectionClass( $this ); // See http://php.net/manual/en/class.reflectionclass.php.
		$set_statement = '';
		$sql_query = '';

		foreach ( $reflection -> getProperties( \ReflectionProperty::IS_PUBLIC ) as $property ) {
			if ( null != $this->{ $property -> getName() } ) {

				$property_to_set = $this->{ $property -> getName() };

				if ( ! is_numeric( $this->{ $property -> getName() } ) ) {
					$property_to_set = '"' . $this->{ $property -> getName() } . '"';
				}

				$set_statement .= '' . $property -> getName() . '=' . $property_to_set . ', '; // "Key"="Value" string pair for this property
			}
		}
		$set_statement = substr( $set_statement, 0, -2 );


		if ( $this -> id > 0 ) {
			// Generate an update statement for a valid primary key value.
			$sql_query = 'UPDATE ' . $this -> table_name . ' SET ' . $set_statement . ' WHERE id = ' . $this -> id;
		} else {
			// Generate an insert statement for the new record to save.
			$sql_query = 'INSERT INTO ' . $this -> table_name . ' SET ' . $set_statement;
		}

		// Execute the generated query, throwing the respective error if it occurs.
		$result = $this -> pdo -> exec( $sql_query );

		if ( $result ) { // Redo model properties with possible auto generated data in MySQL insert / update query.
			$record_id = $this -> pdo -> lastInsertId();
			$this -> id = $record_id;
			$new_data = $this -> raw_from_db( $record_id );

			foreach ( $reflection -> getProperties( \ReflectionProperty::IS_PUBLIC ) as $property ) {
				$this->{ $property -> getName() } = $new_data[ $property -> getName() ];
			}
		}

		return $result;
	}

	/**
	 * Deletes a record from persistant database.
	 *
	 * @return bool True if success, False if not.
	 */
	public function delete() {

		if ( is_numeric( $this -> id ) && $this -> id > 0 ) {

			$reflection = new \ReflectionClass( $this );

			$sql_query = 'DELETE FROM ' . $this -> table_name . ' WHERE id = ' . $this -> id;
			$result = $this -> pdo -> exec( $sql_query );
			if ( $result ) {
				foreach ( $reflection -> getProperties( \ReflectionProperty::IS_PUBLIC ) as $property ) {
					$this->{ $property -> getName() } = null;
				}
				return $result;
			}
		}
		return false;
	}

	/**
	 * Returns the table name associated with this model class.
	 * This is a lower character version of the class name, in plural form.
	 *
	 * @return string The table name used to persist data for the class instances.
	 */
	public static function get_table_name() {

		$reflection = new \ReflectionClass( get_called_class() );
		return strtolower( $reflection -> getShortName() ) . 's';

	}

	/**
	 * Converts a raw data array into a child class instance.
	 *
	 * @param  array $data The "model_property_name => value" pairs array for the new model instance to feed from.
	 * @return Class Child class instance of Orm with the given data filled and ready to save.
	 */
	public static function model_from_raw( array $data ) {

		$model_class = new \ReflectionClass( get_called_class() );
		$model_instance = $model_class -> newInstance();

		foreach ( $model_class -> getProperties( \ReflectionProperty::IS_PUBLIC ) as $model_property ) {
			$model_instance -> { $model_property -> getName() } = ( ! empty( $data[ $model_property -> getName() ] ) ) ? $data[ $model_property -> getName() ] : null;
		}

		return $model_instance;
	}

	/**
	 * Loads a database record into a child class instance, given the record's primary key.
	 *
	 * @param  int $id    The database record primary key to load the information from.
	 * @return Class|bool Child class instance of Orm with the record data filled and ready to use. False if not able to load a record successfully.
	 * @throws \Exception In the case an SQL error occurs while executing the resulting generated query.
	 */
	public function model_from_db( $id = null ) {

		$model_instance = false;

		$data = $this -> raw_from_db( $id );

		if ( $data ) {
			$model_instance = $this -> model_from_raw( $data );
		}

		return $model_instance;

	}

	/**
	 * Returns raw data from database, given a valid id.
	 *
	 * @param  int $id The primary key for this record.
	 * @return array|bool Information array or false if not found.
	 */
	private function raw_from_db( $id = null ) {

		if ( is_numeric( $id ) && $id > 0 ) {
			$sql_query = 'SELECT * FROM ' . $this -> table_name . ' WHERE id = ' . $id;
			$data = $this -> pdo -> query( $sql_query ) -> fetch();
			return $data;
		}

		return false;
	}

	/**
	 * Finds records in the database and returns them. Allows options array of value equality in rows, or a custom WHERE statement.
	 *
	 * @param array|string $where Array of value equality in rows, or custom string with where statement.
	 * @return array An array of model class instances.
	 * @throws \Exception Invalid parameters passed, or error executing MySQL query.
	 */
	public function find( $where = null, $raw = false ) {

		// TODO: Rather than inserting the value directly into the query, use prepared statements and parameters, which aren't vulnerable to SQL injection.
		//
		// http://www.php.net/manual/en/pdo.prepared-statements.php
		// https://stackoverflow.com/questions/2304317/surround-string-with-quotes .
		$result = array();
		$where_statement = '';
		$sql_query = 'SELECT * FROM ' . $this -> table_name . ' ';

		if ( is_array( $where ) ) {
			// TODO: IMPLEMENT FIND BY OPTIONS.
			$sql_query .= 'WHERE ';
			foreach ( $where as $row_key => $value ) {
				if ( ! is_numeric( $value ) ) {
					$value = '"' . $value . '"';
				}
				$sql_query .= '' . $row_key . '=' . $value . ' AND ';
			}
			$sql_query = substr( $sql_query, 0, -4 );

		} elseif ( is_string( $where ) ) {
			$sql_query .= 'WHERE ' . $where;
		} /* else {
			throw new \Exception( 'Invalid parameters passed to ' . $this -> table_name . ' model find method.' );
			return false;
		} */

		new lib_LogDebug( 'SQL Query to be executed in find method', $sql_query );

		$query_result = $this -> pdo -> query( $sql_query ) -> fetchAll();

		new lib_LogDebug( 'SQL Query result set', $query_result );

		if( ! $raw ){
			foreach ( $query_result as $model_row ) {
				$result[] = $this -> model_from_raw( $model_row );
			}
		} else {
			$result = $query_result;
		}
		

		return $result;
	}

	/**
	 * Escapes input data.
	 *
	 * @param  string $input data to escape.
	 * @return string Data escaped with quotes.
	 */
	public function escape_data( $input ) {
		return $this -> pdo -> quote( $input );
	}
}
