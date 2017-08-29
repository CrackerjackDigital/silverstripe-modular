<?php
namespace Modular\Traits;

use Controller;

trait routing {
	public static function class_name_to_route($className) {
		return strtolower(str_replace('\\', '/', $className));
	}

	/**
	 * Return the endpoints that point to this controller class by looking up in Director rules.
	 *
	 * @param string $appendPath optionally append this to found path with '/' separator
	 *
	 * @return array e.g. [ 'documents/$ID/view', 'documents'  ]
	 */
	public static function director_rule( $appendPath = '' ) {
		$routes = [];

		foreach ( \Config::inst()->get( 'Director', 'rules' ) as $rule => $controller ) {
			// looking for this class
			if ( strtolower($controller) == strtolower(static::class )) {
				$routes[] = Controller::join_links( $rule, $appendPath );
			}
		}

		return $routes;
	}
}