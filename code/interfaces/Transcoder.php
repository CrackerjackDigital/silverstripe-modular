<?php
namespace Modular\Interfaces;

use Modular\Model;

interface Transcoder {
	/**
	 * @return Model
	 */
	public function model();
	
	/**
	 * Encode the model returned by model into the target data type/schema/string (e.g. a json string).
	 * @param string $mode
	 * @param null   $options
	 * @return mixed
	 */
	public function encode($mode = HasMode::DefaultMode, $options = null);
	
	/**
	 * Decode the passed data and update the model returned by model method.
	 * @param        $data
	 * @param string $mode
	 * @param null   $options
	 * @return mixed
	 */
	public function decode($data, $mode = HasMode::DefaultMode, $options = null);
		
		
}

