<?php

namespace Modular\Traits;

trait tokens {
	/**
	 * Replace tokens in text wrapped by delimeters with values from tokens array.
	 *
	 * @param string $text to replace tokens in
	 * @param array  $tokens map of [ token-name => value ]
	 * @param array  $wrappers must have two values
	 *
	 * @return mixed
	 */
	public static function detokenise( $text, array $tokens, $wrappers = [ '{', '}' ] ) {
		$start = $wrappers[0];
		$end   = $wrappers[1];

		foreach ( $tokens as $token => $value ) {
			$text = str_replace( $start . $token . $end, $value, $text );
		}

		return $text;
	}
}