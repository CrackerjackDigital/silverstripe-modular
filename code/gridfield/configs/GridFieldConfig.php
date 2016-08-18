<?php
namespace Modular\GridField;

use \GridFieldConfig_RelationEditor;
use Modular\lang;

class GridFieldConfig extends GridFieldConfig_RelationEditor {
	use lang;

	const ComponentAddNewButton = 'GridFieldAddNewButton';
	const ComponentAutoCompleter = 'GridFieldAddExistingAutocompleter';

	private static $items_per_page = 20;

	public function __construct($itemsPerPage = null) {
		parent::__construct($itemsPerPage ?: static::config()->get('items_per_page'));
	}

	public function setSearchPlaceholder($placeholderText) {
		/** @var \GridFieldAddExistingAutocompleter $component */
		if ($component = $this->getComponentByType(static::ComponentAutoCompleter)) {
			$component->setPlaceholderText($placeholderText);
		}
	}

	/**
	 * Invoking GridFieldConfig returns itself
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

	public static function base() {
		return parent::class;
	}
}