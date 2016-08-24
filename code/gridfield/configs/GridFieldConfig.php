<?php
namespace Modular\GridField;

use \GridFieldConfig_RelationEditor;
use Modular\lang;

class GridFieldConfig extends GridFieldConfig_RelationEditor {
	use lang;

	const ComponentAddNewButton = 'GridFieldAddNewButton';
	const ComponentAutoCompleter = 'GridFieldAddExistingAutocompleter';

	private static $items_per_page = 20;

	private static $allow_add_new = true;

	private static $autocomplete = true;

	public function __construct($itemsPerPage = null) {
		parent::__construct($itemsPerPage ?: static::config()->get('items_per_page'));
		if (!$this->config()->get('allow_add_new')) {
			$this->removeComponentsByType(static::ComponentAddNewButton);
		}
		if (!$this->config()->get('autocomplete')) {
			$this->removeComponentsByType(static::ComponentAutoCompleter);
		}
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