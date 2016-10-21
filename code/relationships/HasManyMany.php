<?php
namespace Modular\Relationships;

class HasManyMany extends RelatedModels {
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	/**
	 * Return all related items. Optionally (for convenience more than anything) provide a relationship name to dereference otherwise this classes
	 * late static binding relationship_name() will be used.
	 *
	 * @param string $relationshipName if supplied use this relationship instead of static relationship_name
	 * @return \SS_List
	 */
	public function related($relationshipName = '') {
		$relationshipName = $relationshipName ?: static::relationship_name();
		return $this()->$relationshipName();
	}

	/**
	 * Return an array of IDs from the other end of this extendsions Relationship or the supplied relationship name.
	 * @param string $relationshipName
	 * @return array
	 */
	public function relatedIDs($relationshipName = '') {
		return $this->related($relationshipName)->column('ID');
	}

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


}