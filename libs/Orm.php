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
abstract class Orm {

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
	 * @param int $id The primary key for the record to be imediately loaded.
	 * @throws \Exception When a database connection cannot be established.
	 */
	function __construct( $id ) {

		if ( empty( $id ) ) {
			$this -> id = 0;
		}

		$reflection = new \ReflectionClass( $this );
		$this -> table_name = strtolower( $class -> getShortName() ) . 's'; // The corresponding table name equals the plural of the child class name in lower characters.

		try {
			$connection_details = 'mysql:host=' . API_DBHOST . ';dbname=' . API_DBNAME;

			$this -> pdo = new \PDO(
				$connection_details,
				API_DBUSER,
				''
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
		$set_properties = array();
		$sql_query = '';

		foreach ( $reflection -> getProperties( \ReflectionProperty::IS_PUBLIC ) as $property ) {
			$set_statement .= '"' . $property -> getName() . '"="' . $this->{ $property -> getName() } . '"'; // "Key"="Value" string pair for this property.
		}

		if ( $this -> id > 0 ) {
			// Generate an update statement for a valid primary key value.
			$sql_query = 'UPDATE "' . $this -> table_name . '" SET ' . $set_statement . ' WHERE id = ' . $this -> id;
		} else {
			// Generate an insert statement for the new record to save.
			$sql_query = 'INSERT INTO "' . $this -> table_name . '" SET id=' . $this -> id . ',' . $set_statement;
		}

		// Execute the generated query, throwing the respective error if it occurs.
		$result = self::$pdo -> exec( $sql_query );
		if ( self::$pdo -> errorCode() ) {
			throw new \Exception( self::$pdo -> errorInfo()[2] ); // Directly route the pdo error message as the exception message.
		}

		return $result;
	}

	/**
	 * Converts a raw data array into a child class instance.
	 *
	 * @param  array $data The "model_property_name => value" pairs array for the new model instance to feed from.
	 * @return Class Child class instance of Orm with the given data filled and ready to save.
	 */
	public static function model_from_raw( array $data ) {

		$model_class = new \ReflectionClass( get_called_class() );
		$model_instance = $class -> newInstance();

		foreach ( $model_class -> getProperties( \ReflectionProperty::PUBLIC ) as $model_property ) {

			$model_instance -> { $model_property -> getName() } = ( ! empty( $data[ $model_property -> getName() ] ) ) ? $data[ $model_property -> getName() ] : null;
		}

		return $model;
	}

}
