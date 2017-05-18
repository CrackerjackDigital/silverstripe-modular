<?php

namespace Modular\Admin;

use GridFieldSortableRows;
use Modular\Models\GridListFilter;

class GridListFilters extends ModelAdmin {
	private static $allowed_actions = [
		'EditForm'
	];
	private static $menu_title = 'GridListFilters';

	private static $url_segment = 'gridlistfilters';

	private static $managed_models = [
		'Modular\Models\GridListFilter'
	];

	/**
	 * Add GridFieldOrderableRows to the GridField
	 * @param null $request
	 * @return \Form
	 */
	public function EditForm($request = null) {
		$form = parent::EditForm($request);
		$fields = $form->Fields();

		/** @var \GridField $gridField */
		if ($gridField = $fields->dataFieldByName('Modular-Models-GridListFilter')) {
			/** @var \GridFieldConfig $config */
			if ($config = $gridField->getConfig()) {
				$config->addComponent(new GridFieldSortableRows(GridListFilter::SortFieldName));
				/** @var \GridFieldPaginator $paginator */
				if ($paginator = $config->getComponentByType( \GridFieldPaginator::class)) {
					$paginator->setItemsPerPage( $gridField->getList()->count());
				}
			}
		}
		return $form;
	}

}