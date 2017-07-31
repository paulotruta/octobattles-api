<?php

/**
 * This class represents an Ability / Language in the game.
 * Each character has habilities he uses to battle other characters in turn based attacks. Those habilities are called Languages.
 * Each language has to be one of the defined in this class constant, and contains a base power level (that can be incremented / decremented) per character.
 * Also, one of the fields represents the wheight of it among the other user available languages, and is measured ideally by the number of bytes written by that user in that language, across all its public github repositories.
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 * @abstract
 */
class model_Language extends lib_Orm {

	// //////////////////////////////.
	// INSTANCE VARIABLES.
	// //////////////////////////////.
	/**
	 * The language name. Should be a valid programming language and available in the AVAILABLE_LANGUAGES constant.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The type id associated with this language. Should be a valid Type record id.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Current power level of this language.
	 *
	 * @var int
	 */
	public $power_level;

	/**
	 * The wheight of this language.
	 *
	 * @var int
	 */
	public $wheight;

	/**
	 * The character associated with this language.
	 * @var int
	 */
	public $character_id;

}
