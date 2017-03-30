<?php
namespace Modular\Forms;

/**
 * ConfirmedPasswordField add placeholder text to fields created in SS parent class
 *
 * @package Modular\Forms
 */
class ConfirmedPasswordField extends \ConfirmedPasswordField {
	public function __construct( $name, $title = null, $value = "", $form = null, $showOnClick = false, $titleConfirmField = null ) {
		parent::__construct( $name, $title, $value, $form, $showOnClick, $titleConfirmField );
		foreach ($this->getChildren() as $childField) {
			$childField->setAttribute( 'placeholder', $childField->attrTitle() );
			$childField->setAttribute( 'data-placeholder', $childField->attrTitle() );
		}
	}

}