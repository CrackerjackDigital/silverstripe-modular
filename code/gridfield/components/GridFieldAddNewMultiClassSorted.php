<?php
namespace Modular\GridField;

use GridFieldAddNewMultiClass;
use GridField;
use Modular\config;

/**
 * A component which lets the user select from a list of classes to create a new record form.
 *
 * By default the list of classes that are createable is the grid field's model class, and any
 * subclasses. This can be customised using {@link setClasses()}.
 */
class GridFieldAddNewMultiClassSorted extends GridFieldAddNewMultiClass {
	use config;

	const DescendantsOnly = true;


	private static $exclude_classes = [
		'Modular*'          // only include classes which have been 'brought through' to the application via inheritance
	];

	/**
	 * Overrides the parent method so that we can sort the classes by their value
	 *
	 * @param GridField $grid
	 * @return array a map of class name to title
	 */
	public function getClasses(GridField $grid) {
		$classes = parent::getClasses($grid);

		$excludeClasses = $this->config()->get('exclude_classes');

		if (static::DescendantsOnly) {
			$excludeClasses[] = $grid->getModelClass();
		}

		if ($excludeClasses) {
			// remove classes from dropdown
			if ($form = $grid->getForm()) {
				if ($record = $form->getRecord()) {
					$classNameKeys = array_keys($classes);

					foreach ($classNameKeys as $className) {
						foreach ($excludeClasses as $pattern) {
							if (fnmatch($pattern, $className)) {
								unset($classes[ $className ]);
							}
						}
					}
				}
			}
		}
		asort($classes);
		return $classes;
	}
}