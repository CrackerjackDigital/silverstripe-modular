<?php
namespace Modular\GridField;

/**
 * Alters the config to be suitable for adding/removing many_many related models to the extended model, configurably using 'Add New MultClass' support.
 *
 * Adds an 'AddNewMultiClass' selector
 */
class HasManyManyGridFieldConfig extends GridFieldConfig {
	const ComponentAddNewMultiClass = 'Modular\GridField\GridFieldAddNewMultiClassSorted';

	private static $allowed_related_classes = [];

	private static $exclude_related_classes = [];

	private static $add_new_multi_class = false;

	public function __construct($itemsPerPage = null) {
		parent::__construct($itemsPerPage);

		if ($this->config()->get('allow_add_new') && $this->config()->get('add_new_multi_class')) {
			// remove the stock 'Add New' button if present
			$this->removeComponentsByType(
				static::ComponentAddNewButton
			);
			// now replace the AddNewMultiClass if present with the custom one
			$this->removeComponentsByType(
				'GridFieldAddNewMultiClass'
			);
			$this->addComponent(
				\Injector::inst()->create(static::ComponentAddNewMultiClass)
			);
			// try default before we have a grid field model, this could be overwritten later
			// by explicit call to setAddNewClasses if other more specific classes needed
			if ($classes = static::limited_related_classes()) {
				$this->setAddNewClasses($classes);
			}
		}
	}

	public static function allowed_related_classes() {
		return static::config()->get('allowed_related_classes');
	}

	/**
	 * Explicitly set the classes which are allowed on the add new multiclass button if in config, ignoring
	 * exclude_related_classes and allow_related_classes configuration.
	 * @param array $classes expected to be sorted in the way they will appear
	 */
	public function setAddNewClasses(array $classes) {
		/** @var \GridFieldAddNewMultiClass $component */
		if ($component = $this->getComponentByType(static::ComponentAddNewMultiClass)) {
			if ($component instanceof \GridFieldAddNewMultiClass) {
				$component->setClasses($classes);
			}
		}
	}

	/**
	 * Return list of clases from config.allowed_related_classes merged with parameter classes with config.exclude_related_classes removed.
	 *
	 * @param array $addExtraClasses
	 * @return array
	 */
	public static function limited_related_classes($addExtraClasses = []) {
		return static::exclude_classes(array_merge(
			static::allowed_related_classes(),
			$addExtraClasses
		));
	}

	/**
	 * Remove classes by pattern (fnmatch) defined in config.exclude_related_classes.
	 *
	 * @param array $classList
	 * @return array sorted by value
	 */
	public static function exclude_classes(array $classList) {
		$excluded = static::config()->get('exclude_related_classes');
		$out = [];
		if ($classList && $excluded) {

			foreach ($classList as $className => $title) {
				$exclude = false;
				foreach ($excluded as $pattern) {
					if (fnmatch($pattern, $className)) {
						$exclude = true;
						break;
					}
				}
				if (!$exclude) {
					$out[ $className ] = $title;
				}
			}

		}
		asort($out);
		return $out;
	}

}