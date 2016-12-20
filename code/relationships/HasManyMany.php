<?php
namespace Modular\Relationships;

use Modular\cache;
use Modular\Fields\Relationship;

class HasManyMany extends Relationship {
	use cache;

	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	/**
	 * Customise if shows as a GridField or a TagField depending on config.show_as
	 *
	 * @return array
	 */
	public function cmsFields() {
		if ($this->config()->get('show_as') == self::ShowAsTagsField) {
			$fields = $this->tagFields();
		} else {
			$fields = $this->gridFields();
		}
		return $fields;
	}

	/**
	 * Adds many_many relationships based off relationship_name and related_class_name, and many_many_extraFields such as 'Sort'.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return array
	 */
	public function extraStatics($class = null, $extension = null) {
		$extra = [];

		if (static::sortable()) {
			$extra = [
				'many_many_extraFields' => [
					static::relationship_name() => [
						static::GridFieldOrderableRowsFieldName => 'Int',
					],
				],
			];
		}

		return array_merge_recursive(
			parent::extraStatics($class, $extension),
			$extra,
			[
				'many_many' => [
					static::relationship_name() => static::related_class_name(),
				],
			]
		);
	}

	/**
	 * Return all related items. Optionally (for convenience more than anything) provide a relationship name to dereference otherwise this classes
	 * late static binding relationship_name() will be used.
	 *
	 * @param string $relationshipName if supplied use this relationship instead of static relationship_name
	 * @return \ArrayList|\DataList
	 */
	public function related($relationshipName = '') {
		$relationshipName = $relationshipName ?: static::relationship_name();
		return $this()->$relationshipName();
	}

	/**
	 * Return an array of IDs from the other end of this extendsions Relationship or the supplied relationship name.
	 *
	 * @param string $relationshipName
	 * @return array
	 */
	public function relatedIDs($relationshipName = '') {
		return $this->related($relationshipName)->column('ID');
	}

	/**
	 * Add a csv list of implementors of this class as token 'implementors'
	 * @return mixed
	 */
	public function fieldDecorationTokens() {
		return array_merge(
			parent::fieldDecorationTokens(),
			[
				'implementors' => implode(', ', $this->implementors())
			]
		);
	}

	/**
	 * Return a map of derived implementations and their singular names.
	 *
	 * @param bool $includeCalledClass if true then the class being called will also be in the returned map
	 * @return array [ className => relationshipName ]
	 */
	public static function implementors($includeCalledClass = false) {
		$calledClass = get_called_class();

		if (!$implementors = static::cache("$calledClass-implementors")) {
			$implementors = [];
			// iterate through children of 'HasRelatedPages', eg 'BusinessPages', 'DivisionPages' etc
			foreach (\ClassInfo::subclassesFor($calledClass) as $className) {
				if (($className == $calledClass) && !$includeCalledClass) {
					// skip the related pages class itself if not included
					continue;
				}
				$implementors[ $className ] = $className::relationship_name();
			}
			static::cache("$calledClass-implementors", $implementors);
		}
		return $implementors ?: [];
	}

}