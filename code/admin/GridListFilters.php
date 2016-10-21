<?php

namespace Modular\Admin;

use Modular\GridField\GridFieldOrderableRows;

class GridListFilters extends ModelAdmin {
	private static $allowed_actions = [
		'EditForm'
	];
	private static $menu_title = 'GridListFilters';

	private static $url_segment = 'gridlistfilters';

	private static $managed_models = [
		'Modular\Models\GridListFilter'
	];

	private static $default_sort = 'Sort';

	/**
	 * Add GridFieldOrderableRows to the RelatedModels
	 * @param null $request
	 * @return \Form
	 */
	public function EditForm($request = null) {
		$form = parent::EditForm($request);
		$fields = $form->Fields();

		/** @var \GridField $gridField */
		if ($gridField = $fields->dataFieldByName('Modular-Models-GridListFilters')) {
			if ($config = $gridField->getConfig()) {
				$config->addComponent(new GridFieldOrderableRows('Sort'));
			}
		}
		return $form;
	}

}