<?php
namespace Modular\Forms;

class TabField extends \CompositeField {
	private $tabID;
	
	public function __construct($children = null) {
		parent::__construct($children);
	}
	public function setTabID($id) {
		$this->tabID = $id;
	}
	public function getTabID() {
		return $this->tabID;
	}
}