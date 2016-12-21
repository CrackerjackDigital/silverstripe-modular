<?php
namespace Modular\Relationships;

use DataObject;
use Modular\Fields\Relationship;

class HasMany extends Relationship  {
	const GridFieldConfigName = 'Modular\GridField\HasManyGridFieldConfig';

	public function extraStatics($class = null, $extension = null) {
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			[
				'has_many' => [
					static::RelationshipName => static::RelatedClassName
				]
			]
		);
	}

	/**
	 * Checks if passed model has any links to other models via any has_one relationships. See HasManyMany.hasOtherLinks for more details.
	 *
	 * @param DataObject $model
	 * @return bool
	 */
	protected function hasOtherLinks($model) {
		$ones = $this()->config()->get('has_one') ?: [];

		foreach ($ones as $relationshipName => $className) {
			if ($className == get_class($this())) {
				if ($linked = $className::$relationshipName()) {
					if ($linked()->exists() && $linked->ID !== $this()->ID) {
						// NB EARLY RETURN
						return true;
					}
				}
			}
		}
		return false;
	}
}