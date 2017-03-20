<?php
namespace Modular\Traits;

use Modular\Helpers\Reflection as ArityInfo;

/**
 * Tools for dealing with relationships in SilverStripe
 *
 * @package Modular
 */
trait related {
	/**
	 * @return Model|\DataObject
	 */
	abstract public function __invoke();

	/**
	 * Returns a map of all relationship names this model has, e.g. 'Members' or 'RelatedOrganisations' to their
	 * related model types and their arity. If a single type is given then returns only for that type, otherwise
	 * a merged array of all the types.
	 *
	 * @param array|mixed $arities single or array of numeric arities from Reflection, or 'has_one', 'many_many' etc
	 * @return array e.g. [ 'Members' => [ 'Member' => 2 ], 'Thumbnail' => [ 'Image', 1 ] ]
	 */
	public function definedRelationships($arities = [
		ArityInfo::HasOne,
		ArityInfo::HasMany,
		ArityInfo::ManyMany,
		ArityInfo::BelongsManyMany,
	]) {
		$out = [];
		if (is_array($arities)) {
			// 'multiple' mode build output from 'single' mode calls
			foreach ($arities as $type) {
				// recursively call this method in 'single' mode and merge results into output
				$out = array_merge(
					$out,
					$this->definedRelationships($type)
				);
			}
		} else {
			// 'single' mode get the actual info
			$arityMap = ArityInfo::config()->get('arity_config_map');
			$type            = $arities;

			if (is_int($type)) {
				$arity = $type;
				$type  = $arityMap[ $type ];
			} else {
				// find arity by text e.g. 'has_one'
				$arity = array_flip($arityMap)[ $type ];
			}
			// a map e.g. [ 'Members' => 'Member', 'Noses' => 'Nose' ] (or empty)
			if ($relationships = $this()->config()->get($type)) {
				foreach ($relationships as $relationship => $modelClass) {
					$out[ $relationship ] = [ $modelClass => $arity ];
				}
			}
		}
		return $out;
	}

	/**
	 * Check if a relationship is on the exhibiting class, e.g. 'Members'
	 *
	 * @param string $relationshipName e.g. 'Members'
	 * @return array map of relationship name => model class e.g. [ 'Members' => 'Member' ]
	 *                                 or empty array if not a valid relationship from the model.
	 */
	public function hasDefinedRelationship($relationshipName) {
		$relationships = $this->definedRelationships();

		return isset($relationships[$relationshipName])
			? [ $relationshipName => $relationships[ $relationshipName ] ]
			: [];
	}
}