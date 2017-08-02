<?php

trait trait_OctobattlesLanguage {

	/**
	 * The definition of an octobattles base language. To be used across the languages endpoint on aiding insertion and listing of languages..
	 *
	 * @var array
	 */
	protected static $languages = array(
		'javascript' => array(
			'base_power_level' => 20,
			'speed' => 15,
			'type' => 'charmer',
			'description' => 'Javascript is executed on the client side, Javascript is a relatively easy language, Extended functionality to web pages',
		),
		'java' => array(
			'base_power_level' => 19,
			'speed' => 19,
			'type' => 'cleaner',
			'description' => 'Java (the platform) has a very large and standard class library, some parts of which are very well written, Good portability (certainly better than that of nearly any compiled alternative), Lots of available code and third-party libraries',
		),
		'php' => array(
			'base_power_level' => 18,
			'speed' => 18,
			'type' => 'geek',
			'description' => 'It\'s a quick and easy server side scripting language for web development and general use. Large community, widely used. Most problems faced by a web developer have pre existing solutions. It works well with databases, file systems, images, et cetera.',
		),
		'python' => array(
			'base_power_level' => 17,
			'speed' => 16,
			'type' => 'functional',
			'description' => 'The main characteristics of a Python program is that it is easy to read,  It helps you think more clearly when writing programs, it requires less effort to write a Python program than to write one in another language like C++ or Java.',
		),
		'ruby' => array(
			'base_power_level' => 16,
			'speed' => 17,
			'type' => 'charmer',
			'description' => 'Solid. Reliable. Middle of the road.',
		),
		'c#' => array(
			'base_power_level' => 15,
			'speed' => 20,
			'type' => 'assassin',
			'description' => 'Learning C# will help you later on if you decide to learn harder programming languages (e.g. C or C++).  The programming style of C# is very similar to other C languages.',
		),

		// ... TODO: Fill the rest of the languages.
	);

	/**
	 * Provides a method to get a type based on the name. If a given type name does not exists, false is returned.
	 *
	 * @param  string $name The type name to get information from.
	 * @return array\bool   Type information or false if not existant.
	 */
	public function get_language( $name ) {

		if ( ! empty( self::$languages[ $name ] ) ) {
			return self::$languages[ $name ];
		}

		return false;

	}

}