<?php
namespace Modular\GridList;

use Modular\GridList\Interfaces\Service\Service as ServiceInterface;
use Modular\Object;
use Modular\owned;

class Service extends Object implements ServiceInterface {
	use owned;

	const FiltersClassName = 'Modular\GridList\Constraints';

	const ServiceName = '';

	/**
	 * Factory method return from Injector either self.ServiceName or called class.
	 * @return mixed
	 */
	public static function factory() {
		return \Injector::inst()->get(static::ServiceName ?: get_called_class(), true, func_get_args());
	}

	public function constraint($name, $persistance = null) {
		return $this->Filters()->constraint($name, $persistance);
	}

	public function mode() {
		return $this->Filters()->mode();
	}

	public function sort() {
		return $this->Filters()->sort();
	}

	public function start() {
		return $this->Filters()->start();
	}

	public function limit() {
		return $this->Filters()->limit();
	}

	/**
	 * @param $params
	 * @return string
	 */
	public function filterLink($params) {
		$params = is_array($params) ? $params : [$params];
		return \Director::get_current_page()->Link() . '?filter=' . implode(',', $params);
	}

	/**
	 * Allow calls statically through to Filters as it's easier then
	 *
	 * @return Constraints
	 */
	public function Filters() {
		return \Injector::inst()->get(static::FiltersClassName);
	}
}
