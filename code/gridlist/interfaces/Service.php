<?php
namespace Modular\GridList\Interfaces\Service;

use Modular\Constraints;

interface Service {
	/**
	 * Return a particular constraint, such as a query or url parameter, generally calls
	 * through to filter
	 *
	 * @param $name
	 * @return mixed
	 */
	public function constraint($name);

	/**
	 * Returns a Constraints object, which we call Filters here as we like to be confusing.
	 *
	 * @return Constraints
	 */
	public function Filters();

	/**
	 * Return a link to current page (or a dedicated filters page) with filters applied
	 *
	 * @param $params
	 * @return mixed
	 */
	public function filterLink($params);

}
