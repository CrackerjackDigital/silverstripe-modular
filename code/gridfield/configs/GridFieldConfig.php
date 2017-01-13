<?php
namespace Modular\GridField\Configs;

use \GridFieldConfig_RelationEditor;
use Modular\Traits\lang;

class GridFieldConfig extends GridFieldConfig_RelationEditor {
	use lang;

	const ComponentAddNewButton = 'GridFieldAddNewButton';
	const ComponentAutoCompleter = 'GridFieldAddExistingAutocompleter';

	private static $items_per_page = 20;

	private static $allow_create = true;

	private static $autocomplete = true;

	public function __construct($itemsPerPage = null) {
		parent::__construct($itemsPerPage ?: static::config()->get('items_per_page'));
		if (!$this->config()->get('allow_create')) {
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

	public static function class_name() {
		return get_called_class();
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