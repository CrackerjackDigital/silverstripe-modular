<?php

namespace Modular\Forms;

use \DataObjectInterface;

class TagField extends \TagField {
	private $constraints;

	public function setConstraints(array $constraints) {
		$this->constraints = $constraints;
	}
	public function saveInto(DataObjectInterface $record) {
		if (is_array($this->constraints) && $this->constraints[0] ) {
			if (!$this->Value()) {
				return false;
			}
		}
		return parent::saveInto($record);
	}
}