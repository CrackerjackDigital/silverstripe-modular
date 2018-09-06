<?php
namespace Modular\Helpers;

class Config extends \Config {
	public function getMany($class, ...$names) {
		$values = [];
		foreach ($names as $name) {
			$values[$name] = $this->get($class, $name);
		}
		return $values;
	}
}