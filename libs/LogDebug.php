<?php

/**
 * A class that implements the base Debug and Log used by the model classes.
 * It extends the Orm system so it can also log persistently to the database.
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 * @abstract
 */
class lib_LogDebug extends lib_Orm {

	/**
	 * The date this debug log was issued. MySQL timestamp format.
	 *
	 * @var string
	 */
	public $date;

	/**
	 * The log level associated with this debug log. Default is 1. Level 2 prints to scren. Level 3 persists. Level 4 throws exception. Level 0 does not log anything.
	 *
	 * @var int
	 */
	public $level;

	/**
	 * The debug log entry message.
	 *
	 * @var int
	 */
	public $message;

	/**
	 * Serialized context to persist or output.
	 *
	 * @var int
	 */
	public $context;

	/**
	 * Adds a new entry to the log, taking the necessary actions depending on the defined log level.
	 *
	 * @param string $message The info message to log.
	 * @param object $context A context array to be logged, useful for checking out variable content.
	 * @param bool   $persist A boolean indicator that overrides debug setting and allows to persist data to the database table.
	 * @return bool If any log was output, either to console, screen or persistance, returns true.
	 * @throws \Exception When the debug level is higher than 3.
	 */
	public function __construct( $message, $context = 'No context', $persist = false ) {

		parent::__construct();

		if ( API_DEBUG_LEVEL > 0 ) {
			if ( ! is_string( $message ) ) {
				$message = 'An error ocurred but no message was provided.';
			}
			$this -> message = $message;
			$this -> level = API_DEBUG_LEVEL;
			$this -> context = print_r( $context, true );

			error_log( $this -> message . ' (Level ' . API_DEBUG_LEVEL . '): ' . $this -> context );

			if ( API_DEBUG_LEVEL >= 1 && $persist ) {
				$this -> save();
			}

			if ( API_DEBUG_LEVEL >= 2 ) {
				echo( '<p>' . $this -> message . ':</p>' );
				var_dump( $context );
			}

			if ( API_DEBUG_LEVEL >= 3 ) {
				throw new \Exception( $message );
			}

			return true;

		}

		return false;

	}

}
