<?php
namespace Modular\Traits;

use Director;

trait environment {
	/**
	 * Return the model or object that config is to be called on (which has the 'environment' config variable)
	 *
	 * @return mixed
	 */
	abstract public function __invoke();

	/**
	 * @param $forClass
	 *
	 * @return mixed
	 */
	abstract public function config($forClass = null);

	public function environment() {
		return $this()->config()->get( 'environment' )
			?: Director::get_environment_type();
	}

	/**
	 * Lookup a map by key using fnmatch to compare key to target value so simple wildcards can be used
	 *
	 * TODO allow config to be e.g. 'service.endpoint.data' (dot-encoded)
	 *
	 * @param string|array $configOrName either name of configuration variable, or data to search,
	 * @param string       $key          to match, e.g. 'service', if not supplied defaults to env()
	 * @param mixed        $default      returned if no match found
	 *
	 * @return mixed
	 */
	public function configForEnvironment( $configOrName, $key = '', $default = null ) {
		$data = ( is_array( $configOrName )
			? $configOrName
			: $this->config()->get( $configOrName )
		) ?: [];

		$key = $key ?: $this->environment();

		foreach ( $data as $match => $value ) {
			if ( fnmatch( $match, $key ) ) {
				return $value;
			}
		}

		return $default;
	}
}
