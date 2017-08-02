<?php

trait trait_OctobattlesType {

	/**
	 * The definition of an octobattles type. This type may be assigned to characters and languages.
	 *
	 * @var array
	 */
	protected static $types = array(
		'geek' => array(
			'id' => 1,
			'description' => 'The geek type uses every necessary tool to accomplish the job. It is this way a neutral player that knows a little of everything.',
		),
		'assassin' => array(
			'id' => 2,
			'description' => 'An assassin provides deep knowlege of the internals, beating to death every other type in terms of speed.',
		),
		'functional' => array(
			'id' => 3,
			'description' => 'The functional knows calculus, and uses its power to overwhelm enemies with awesome single liners!',
		),
		'cleaner' => array(
			'id' => 4,
			'description' => 'Cleaners do the minimal exequible job, without traces. It just works!',
		),
		'charmer' => array(
			'id' => 5,
			'description' => 'Charmers always use the newest tools to Wow its adversaries.',
		),
	);

	/**
	 * Provides a method to get a type based on the name. If a given type name does not exists, false is returned.
	 *
	 * @param  string $name The type name to get information from.
	 * @return array\bool   Type information or false if not existant.
	 */
	public function get_type( $name ) {

		if ( ! empty( self::$types[ $name ] ) ) {
			return self::$types[ $name ];
		}

		return false;
	}

}