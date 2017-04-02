<?php
namespace Modular\Extensions\Model;

use Modular\Fields\Title;
use Modular\ModelExtension;

class Unique extends ModelExtension {
	private $fieldName;

	public function __construct($fieldName) {
		parent::__construct();
		$this->fieldName = $fieldName;
	}

	/**
	 * Prefix title so we don't get unique constraint violation errors, we may have to do it a few times to get to where there are no 'Copy of '... at the
	 * start of the field.
	 */
	public function onBeforeDuplicate() {
		if ( isset( $this()->{$this->fieldName} ) ) {
			while (\DataObject::get( $this()->ClassName )->filter( $this->fieldName, $this()->{$this->fieldName})->count() ) {
				$this()->{$this->fieldName} = 'Copy of ' . $this()->{$this->fieldName};
			}
		};
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