<?php
namespace Modular\Traits;

trait environment {
	/**
	 * Return the model or object that config is to be called on (which has the 'environment' config variable)
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
		return $this()->config()->get('environment') ?: SS_ENVIRONMENT_TYPE;
	}
}