<?php
namespace Modular\GridField;

/**
 * Alters the config to be suitable for adding/removing blocks from an article.
 *
 * Adds an 'AddNewMultiClass' selector
 */
class HasBlocksGridFieldConfig extends GridFieldConfig {
	public function __construct($itemsPerPage = null) {
		parent::__construct($itemsPerPage);

		$this->removeComponentsByType(
			'GridFieldAddNewButton'
		);
		$this->addComponents(
			new GridFieldAddNewMultiClassSorted()
		);

		$this->getComponentByType('GridFieldAddExistingAutocompleter')
			->setPlaceholderText('Find Content Block by Title');
	}
}