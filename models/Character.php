<?php

/**
 * This class represents a character in the game.
 * Each character has information specific to itself, such as a unique name, a type, experience and life gauge.
 * A character is considered dead when its life level drops below zero. You cal also assassinate your character.
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 * @abstract
 */
class Character extends Orm {

	// //////////////////////////////.
	// INSTANCE VARIABLES.
	// //////////////////////////////.
	/**
	 * The character name. Should be a valid github username.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The type id associated with this character. Should be a valid Type record id.
	 *
	 * @var int
	 */
	public $type;

	/**
	 * Current Experience level of this character.
	 *
	 * @var int
	 */
	public $experience_points;

	/**
	 * The life gauge of this caracter. The max value allowed is equal to the $experience_points value.
	 *
	 * @var int
	 */
	public $life_gauge;

	/**
	 * The last time this character was accessed in the API. It should be a MySQL timestamp representation.
	 *
	 * @var string
	 */
	public $last_checked;

	// //////////////////////////////.
	// PUBLIC METHODS.
	// //////////////////////////////.
	/**
	 * Each time a character is checked out, its last_checked timestamp information must be updated, as well as the information about its life status and experience level.
	 */
	public function __construct() {

		$this -> save(); // Timestamp update is done directly via MySQL update.

	}

	/**
	 * Custom save function, validating conditions for insertion, namely:
	 *  - The character name cannot be equal to any other character that is still alive.
	 *  - Convert the Languages array into valid ids to make a table relation.
	 *
	 * @return int|boolean 1 in case of a successfull save, or false in case of error.
	 */
	public function save() {

		if ( ! is_numeric( $this -> id ) || $this -> id <= 0 ) {
			// Check for characters with the same name that may still be alive.
			$characters_same_name = $this -> find( array(
				'name' => $this -> name,
			) );
			foreach ( $characters_same_name as $character ) {
				if ( $character -> life_gauge > 0 ) {
					return false;
				}
			}
		}
		return parent::save();

	}

	/**
	 * This method returns the 5 most proeminent languages of this character, ordered by percentage of usage across all its github public projects.
	 *
	 * @return array A sequential array containing an array of Language class models.
	 */
	public function get_languages() {
		// TODO: Finish the language model, and put this method to work!
		return array();
	}

	/**
	 * Checks if a character is dead or alive.
	 *
	 * @return boolean True is dead
	 */
	public function is_dead() {

		return ! ( $this -> id > 0 && $this -> life_gauge > 0 );

	}

	/**
	 * Assasinate the character, making it unusable in the system.
	 *
	 * @return bool Only executes the action in the case this is a database record.
	 */
	public function kill() {

		if ( $this -> id > 0 ) {
			$this -> life_gauge = 0;
			$this -> save();
			return true;
		}

		return false;

	}

	/**
	 * Update the experience level of this character based on another character (the defeated one)
	 *
	 * @param  Character $character The character that was defeated by this instance one.
	 * @return int|bool The new updated experience level, or false if the calculation was not possible.
	 */
	public function update_experience( Character $character ) {

		if ( ! is_numeric( $character -> id ) || $character -> id <= 0 ) {
			return false;
		}

		// TODO: Calculate new experience level based on nother Character instance (The defeated one).
		$this -> save();
		return $this -> experience_level;

	}



}
