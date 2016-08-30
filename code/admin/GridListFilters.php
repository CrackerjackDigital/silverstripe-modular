<?php

namespace Modular\Admin;

class GridListFilters extends ModelAdmin {
	private static $menu_title = 'GridListFilters';

	private static $url_segment = 'gridlistfilters';

	private static $managed_models = [
		'Modular\Models\GridListFilter'
	];

	public function EditForm($request = null) {
		$form = parent::EditForm($request);
		$fields = $form->Fields();

		/** @var \GridField $gridField */
		if ($gridField = $fields->dataFieldByName('Modular-Models-GridListFilter')) {
			if ($config = $gridField->getConfig()) {
				$config->addComponent(new \GridFieldOrderableRows('Sort'));
			}
		}
		return $form;
	}

}