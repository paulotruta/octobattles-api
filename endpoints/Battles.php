<?php

/**
 * The Api endpoint class for the Battles feature of the octobattles project.
 * This class handles GET and POST requests for the /battles/ endpoint.
 *
 * Each endpoint method receives a $params array, being index 0 the requested path, and subsequential indexes the request parameters
 *
 * @package    octobattles-api
 * @author     Paulo Truta
 * @version    0.1
 */
class endpoint_Battles extends Api {

	use trait_OctobattlesType;
	use trait_OctobattlesLanguage;

	/**
	 * Battles endpoint GET request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function get( $params ) {

		if ( ! empty( $params[1] ) && empty( $params[2] ) ) {
			// Get list of battles for this character id.
			$battles_initiated = $this -> model -> find( array(
				'character1_id' => $params[1],
			) );

			$battles_challenged = $this -> model -> find( array(
				'character2_id' => $params[1],
			) );

			$response = array(
				'message' => 'Last battles for character ' . $params[1],
				'total' => count( $battles_initiated ) + count( $battles_challenged ),
				'last_created_battles' => array_map( 'get_object_vars', $battles_initiated ),
				'last_received_battles' => array_map( 'get_object_vars', $battles_challenged ),
			);

			parent::response( $response );
			return true;

		}

		if ( ! empty( $params[2] ) ) {

			// Get specific battles between two oponents.
			$character1_initiated_battles = $this -> model -> find( array(
				'character1_id' => $params[1],
				'character2_id' => $params[2],
			) );

			$character2_initiated_battles = $this -> model -> find( array(
				'character1_id' => $params[2],
				'character2_id' => $params[1],
			) );

			$response = array(
				'message' => 'Battles between character ' . $params[1] . ' and character ' . $params[2],
				'total' => count( $character1_initiated_battles ) + count( $character2_initiated_battles ),
				'character1_initiated_battles' => array_map( 'get_object_vars', $character1_initiated_battles ),
				'character2_initiated_battles' => array_map( 'get_object_vars', $character2_initiated_battles ),
			);

			parent::response( $response );
			return true;

		}

		// Get list of last battles made.
		parent::response( array(
			'message' => 'Last battles made between all octopus',
			'result' => array_map( 'get_object_vars', $this -> model -> find() ),
		) );
	}

	/**
	 * Battles endpoint POST request handler.
	 *
	 * @param array $params The request parameters for this endpoint method.
	 */
	public function post( $params ) {

		if ( empty( $params[1] )  || empty( $params[2] ) ) {
			parent::error_response( 'Please provide the two fighting character ids via endpoint url. Example: "/battles/22/23.json"' );
			return false;
		}

		if ( $params[1] == $params[2] ) {
			parent::error_response( 'An octopus cannot battle itself!' );
			return false;
		}

		$character1 = ( new model_Character() ) -> model_from_db( $params[1] );
		if ( ! $character1 ) {
			parent::error_response( 'An invalid id was provided for the first battle contestant.' );
			return false;
		} elseif ( $character1 -> life_gauge <= 0 ) {
			parent::error_response( 'You cannot fight with a dead character, and character 1 clearly is.' );
			return false;
		}

		$character2 = ( new model_Character() ) -> model_from_db( $params[2] );
		if ( ! $character2 ) {
			parent::error_response( 'An invalid id was provided for the second contestant.' );
			return false;
		} elseif ( $character2 -> life_gauge <= 0 ) {
			parent::error_response( 'You cannot fight with a dead character, and character 2 clearly is.' );
			return false;
		}

		$character1_languages = ( new model_Language() ) -> find( array(
			'character_id' => $params[1],
		) );

		$character2_languages = ( new model_Language() ) -> find( array(
			'character_id' => $params[2],
		) );

		if ( empty( $character1_languages ) ) {
			parent::error_response( 'The first contestant does not have any languages learned. Start coding please?!' );
			return false;
		} elseif ( empty( $character2_languages ) ) {
			parent::error_response( 'The second contestant does not have any languages learned. Start coding please?!' );
			return false;
		}

		$turn = 0;
		$end = false;
		$battle_log = array();
		$winner_id = 0;

		while ( ! empty( $character1_languages[ $turn ] ) && ! empty( $character2_languages[ $turn ] ) && ! $end ) {

			if ( $this -> get_language( $character1_languages[ $turn ] -> name )['speed'] > $this -> get_language( $character2_languages[ $turn ] -> name )['speed'] ) {

				$battle_log[] = $this -> attack( $character1, $character2, $character1_languages[ $turn ], $character2_languages[ $turn ] );

			} elseif ( $this -> get_language( $character1_languages[ $turn ] -> name )['speed'] < $this -> get_language( $character2_languages[ $turn ] -> name )['speed'] ) {

				$battle_log[] = $this -> attack( $character2, $character1, $character2_languages[ $turn ], $character1_languages[ $turn ] );

			} else {

				if ( $character1_languages[ $turn ] -> power_level >= $character2_languages[ $turn ] -> power_level ) {
					$battle_log[] = $this -> attack( $character1, $character2, $character1_languages[ $turn ], $character2_languages[ $turn ] );
				} else {
					$battle_log[] = $this -> attack( $character2, $character1, $character2_languages[ $turn ], $character1_languages[ $turn ] );
				}
			}

			$turn++;

			if ( $character1 -> life_gauge <= 0 || $character2 -> life_gauge <= 0 ) {
				
			}
		}

		$battle = new model_Battle();
		$battle -> character1_id = $character1 -> id;
		$battle -> character2_id = $character2 -> id;

		if ( $character1 -> life_gauge < $character2 -> life_gauge ) {
			$winner = &$character2;
		} elseif ( $character2 -> life_gauge < $character1 -> life_gauge ) {
			$winner = &$character1;
		} else {
			$winner = null;
		}

		$battle -> battle_log = $battle -> escape_data( json_encode( $battle_log ) );

		if ( $winner ) {
			$battle -> victorious_character_id = $winner -> id;
			$battle_log[] = $winner -> name . ' won the battle!';
			$winner -> experience_points++;
			$winner_id = ( $winner ) ? $winner -> id : null;
		} else {
			$battle_log[] = 'This match was a tie!';
		}

		if ( $battle -> save() ) {

			$character1 -> save();
			$character2 -> save();

			parent::response( array(
				'character1' => get_object_vars( $character1 ),
				'character2' => get_object_vars( $character2 ),
				'battle' => get_object_vars( $battle ),
			) );
		} else {
			parent::error_response( 'An error ocurred while simulating the battle.' );
		}

	}

	/**
	 * Performs a turn attack between attacker and defender. Attacker attacks first.
	 * If one of the characters die, experience points is incremented by to the current attacking character.
	 *
	 * @param  Object $attacker        The first character to attack.
	 * @param  Object $defender        The first character to defend, second to attack.
	 * @param  Object $attacker_attack The attack to be used by the first attacker.
	 * @param  Object $defender_attack The attack to be used by the second attacker.
	 * @return array An array of strings representing the attack log.
	 */
	protected function attack( &$attacker, &$defender, $attacker_attack, $defender_attack ) {

		if ( $attacker -> life_gauge <= 0 || $attacker -> life_gauge <= 0 ) {
			return false;
		}

		// Formula of attack.
		$attack_damage = $this -> attack_formula( $attacker, $attacker_attack, $defender );
		if ( $attacker -> type != $attacker_attack -> type ) {
			$attack_damage = $attack_damage / 2;
		}

		$attack_log[] = $attacker -> name . ' (hp: ' . $attacker -> life_gauge . ') attacks ' . $defender -> name . ' (hp: ' . $defender -> life_gauge . ') with the ' . $attacker_attack -> name . ' language hability. Power level: ' . $attacker_attack -> power_level . '; Wheight: ' . $attacker_attack -> wheight . '. Took ' . $attack_damage . ' damage';

		$defender -> life_gauge -= $attack_damage;

		if ( $defender -> life_gauge <= 0 ) {
			$defender -> life_gauge = -1;
			$attack_log[] = $defender -> name . ' died.';
			$attacker -> experience_points += 2;
			return $attack_log;
		}

		// Formula of attack.
		$attack_damage = $this -> attack_formula( $defender, $defender_attack, $defender );
		if ( $defender -> type != $defender_attack -> type ) {
			$attack_damage = $attack_damage % 2;
		}

		$attack_log[] = $defender -> name . ' (hp: ' . $defender -> life_gauge . ') attacks ' . $attacker -> name . ' (hp: ' . $attacker -> life_gauge . ') with the ' . $defender_attack -> name . ' language hability. Power level: ' . $defender_attack -> power_level . '; Wheight: ' . $defender_attack -> wheight . '. Took ' . $attack_damage . ' damage';

		$attacker -> life_gauge -= $attack_damage;

		if ( $attacker -> life_gauge <= 0 ) {
			$attacker -> life_gauge = -1;
			$attack_log[] = $attacker -> name . ' died.';
			$defender -> experience_points += 2;
			return $attack_log;
		}

		return $attack_log;

	}

	/**
	 * The attack damage for one attack formula.
	 *
	 * @param  Object $attacker        The attacker object.
	 * @param  Object $attacker_attack The attacker language hability.
	 * @param  Object $defender        The defender object.
	 * @return Object
	 */
	protected function attack_formula( $attacker, $attacker_attack, $defender ) {
		return ( ( $attacker -> experience_points * $attacker_attack -> power_level ) + $attacker_attack -> wheight ) / $defender -> experience_points;
	}
}
