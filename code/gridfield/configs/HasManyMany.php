<?php
namespace Modular\GridField;

/**
 * Alters the config to be suitable for adding/removing blocks from an article.
 *
 * Adds an 'AddNewMultiClass' selector
 */
class HasManyManyGridFieldConfig extends GridFieldConfig {
	private static $allowed_related_classes = [];

	private static $add_new_multi_class = true;

	public function __construct($itemsPerPage = null) {
		parent::__construct($itemsPerPage);

		if ($this->config()->get('add_new_multi_class')) {
			$this->removeComponentsByType(
				static::ComponentAddNewButton
			);
			$this->addComponents(
				$addNewButton = new GridFieldAddNewMultiClassSorted()
			);
			if ($classes = $this->config()->get('allowed_related_classes')) {
				$addNewButton->setClasses($classes);
			}
		}
	}

	/**
	 * Set the classes which are allowed on the add new multiclass button if in config.
	 * @param array $classes
	 */
	public function setAddNewClasses(array $classes) {
		/** @var \GridFieldAddNewMultiClass $component */
		if ($component = $this->getComponentByType(static::ComponentAddNewButton)) {
			if ($component instanceof \GridFieldAddNewMultiClass) {
				$component->setClasses($classes);
			}
		}
	}
}