<?php
/**
 * Calls through to JSON class while traits are worked out properly in SS (config
 * is applied?)
 */
namespace Modular\Traits;

use Modular\Helpers\JSON as Helper;
use Modular\Interfaces\HasMode;
use Modular\Model;

/**
 * Class json helper traa
 *
 * @package Modular\Traits
 */
trait json {
	/**
	 * @return Model
	 */
	abstract public function model();
	
	/**
	 * Return model fields as a json string.
	 *
	 * @param string $mode
	 * @return string
	 * @internal param array $options
	 */
	public function encode($mode = HasMode::DefaultMode) {
		$data = $this->model()->toMap();
		// TODO data should be trimmed according to mode, e.g. only certain fields encoded
		return Helper::encode($data);
	}
	
	/**
	 * Update model fields from array or json encoded string.
	 *
	 * @param        $data
	 * @param string $mode
	 * @return array the decoded data if it was encoded
	 */
	public function decode($data, $mode = HasMode::DefaultMode) {
		if (!is_array($data)) {
			$data = Helper::decode($data);
		}
		// TODO data should be sanitised before updating the model according to mode
		$this->model()->update($data);
		return $data;
	}

	/**
	 * Encode suitable for a javascript template variable.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function template_encode($data) {
		return Helper::template_encode($data);
	}
}