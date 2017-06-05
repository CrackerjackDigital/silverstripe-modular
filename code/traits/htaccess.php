<?php

namespace Modular\Traits;

use Director;

trait htaccess {
	/**
	 * Return a chunk of .htaccess rules which rewrites attempts to access a path to a controller route.
	 *
	 * @param string $path         to rewrite if writeToPath is true, otherwise path to specify in rewrite rule returned.
	 * @param string $toRoute      route to rewrite accesses to
	 * @param string $pathVariable name of variable to pass on query string of original path requested, empty to not pass anything.
	 * @param bool   $writeToPath  will write the .htaccess to the provided path if true, otherwise just returns the rule. If write to path is true then
	 *                             the rule will be relative to the written directory (e.g. applying to that directory). If not written then the path will
	 *                             be relative to site root, e.g. '/path/' for inclusion at the root.
	 * @param bool   $overwrite    if true any existing .htaccess file will be overwritten, otherwise existing file will silently be left alone
	 *
	 * @return string the generated rule
	 */
	public static function rewrite_to_controller( $path, $toRoute, $pathVariable = 'path', $writeToPath = true, $overwrite = false ) {
		$rule = "RewriteCond %{REQUEST_URI} ^(.*)$" . PHP_EOL;
		$htAccessFile = Director::getAbsFile( $path . DIRECTORY_SEPARATOR . '.htaccess' );

		if ( $writeToPath && ($overwrite || !file_exists( $htAccessFile))) {
			$rule         .= "RewriteRule .* $toRoute" . ( $pathVariable ? "?$pathVariable=%1" : '' ) . PHP_EOL;

			file_put_contents( $htAccessFile, $rule );
		} else {
			// return suitable for adding to 'root' .htaccess
			$rule .= "RewriteRule $path/.* $toRoute" . ( $pathVariable ? "?$pathVariable=%1" : '' ) . PHP_EOL;
		}

		return $rule;

	}

}