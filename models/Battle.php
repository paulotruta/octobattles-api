<?php

/**
 * This class represents a battle model in the game and holds information about a past battle.
 * Each character has habilities he uses to battle other characters in turn based attacks. Those habilities are called Languages.
 * A battle can either result in a shamefull loss (with the special case of a kill), or a victory.
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 * @abstract
 */
class model_Battle extends lib_Orm {

	// //////////////////////////////.
	// INSTANCE VARIABLES.
	// //////////////////////////////.
	/**
	 * The battle chalenger character.
	 *
	 * @var int
	 */
	public $character1_id;

	/**
	 * The changled character.
	 *
	 * @var int
	 */
	public $character2_id;

	/**
	 * Victorious character.
	 *
	 * @var int
	 */
	public $victorious_character_id;

	/**
	 * The print_r log of the turn based battle attacks used and damage taken and received.
	 *
	 * @var int
	 */
	public $battle_log;

	/**
	 * The date this battle ocurred.
	 *
	 * @var int
	 */
	public $battle_timestamp;

}
