<?php
namespace Modular\Extensions\Model;

use Modular\ModelExtension;

class Unique extends ModelExtension {
	private $fieldName;

	public function __construct($fieldName) {
		parent::__construct();
		$this->fieldName = $fieldName;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ($this->fieldName) {
			$fieldName = $this->fieldName;
			if ($value = $this()->$fieldName) {
				if (\DataObject::get($this()->ClassName)->filter($fieldName, $value)->count()) {
					throw new \ValidationException("One or more records with $this->fieldName '$value' already exist and it should be unique");
				}
			}
		}
	}
}