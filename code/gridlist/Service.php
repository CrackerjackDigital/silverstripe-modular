<?php
namespace Modular\GridList;

use Modular\Application;
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

	/**
	 * Returns the currently selected filter (e.g. from request query parameter) or empty if no filter advised.
	 * @return string
	 */
	public function filter() {
		return $this->Filters()->filter();
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
	 * Return current page's default filter from either DefaultFilter method, DefaultFilter field or config.gridlist_default_filter.
	 *
	 * @return string
	 */
	public function defaultFilter() {
		if ($page = Application::get_current_page()) {
			if ($page->hasMethod('DefaultFilter')) {
				return $page->DefaultFilter();
			} elseif ($page->hasField('DefaultFilter')) {
				return $page->DefaultFilter;
			} else {
				return $page->config()->get('gridlist_default_filter');
			}
		}
	}

	/**
	 * Return current page's default filter from either DefaultFilter method, DefaultFilter field or config.gridlist_default_filter.
	 *
	 * @return string
	 */
	public function allFilter() {
		if ($page = Application::get_current_page()) {
			if ($page->hasMethod('AllFilter')) {
				return $page->AllFilter();
			} elseif ($page->hasField('AllFilter')) {
				return $page->AllFilter;
			} else {
				return $page->config()->get('gridlist_all_filter');
			}
		}
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
