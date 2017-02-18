<?php
namespace Modular\Traits;

trait generator {
	/**
	 * @param null $className
	 * @return \Config_ForClass
	 */
	abstract public function config($className = null);
	
	abstract public function generator($seed = null);
	
	/**
	 * Return true if config.generate_always is true or the existing value on the model is null.
	 * @return bool
	 * @throws \Modular\Exceptions\Exception
	 */
	public function shouldGenerate() {
		return $this->config()->get('generate_always')
		       || (!$this->singleFieldValue() && $this->config()->get('generate_empty'));
	}
}