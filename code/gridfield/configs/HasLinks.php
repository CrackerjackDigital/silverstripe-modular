<?php
namespace Modular\GridField;

/**
 * Alters the config to be suitable for adding/removing links from a block
 */
class HasLinksGridFieldConfig extends GridFieldConfig {
    public function __construct($itemsPerPage = null) {
        parent::__construct($itemsPerPage);

        $this->getComponentByType('GridFieldAddExistingAutocompleter')->setPlaceholderText('Find Links by Title');
    }
}