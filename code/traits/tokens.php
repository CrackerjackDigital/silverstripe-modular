<?php
namespace Modular;

trait tokens {
	public static function detokenise($tokenised, array $tokens, $delimeters = [ '{', '}' ]) {
		$start = $delimeters[0];
		$end = $delimeters[1];

		foreach ($tokens as $token => $value) {
			$tokenised = str_replace($start . $token . $end, $value, $tokenised);
		}
		return $tokenised;
	}
}