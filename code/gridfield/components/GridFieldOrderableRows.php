<?php
namespace Modular\GridField;
/**
 * Override to fix bug to do with LastEdited field being on different model in inheritance heirarchy to the Sort field.
 */
class GridFieldOrderableRows extends \GridFieldOrderableRows {

	const SortFieldName = 'Sort';

	/**
	 * Remove update of LastEdited as may be on different model to the one where the Sort field is.
	 *
	 * TODO fix so LastEdited is updated on correct model
	 *
	 * @param       $list
	 * @param array $values
	 * @param array $sortedIDs
	 * @throws \Exception
	 */
	protected function reorderItems($list, array $values, array $sortedIDs) {
		$sortField = $this->getSortField();
		/** @var \SS_List $map */
		$map = $list->map('ID', $sortField);
		//fix for versions of SS that return inconsistent types for `map` function
		if ($map instanceof \SS_Map) {
			$map = $map->toArray();
		}

		// If not a ManyManyList and using versioning, detect it.
		$isVersioned = false;
		$class = $list->dataClass();
		if ($class == $this->getSortTable($list)) {
			$isVersioned = $class::has_extension('Versioned');
		}

		// Loop through each item, and update the sort values which do not
		// match to order the objects.
		if (!$isVersioned) {
			$sortTable = $this->getSortTable($list);
//			$additionalSQL = (!$list instanceof ManyManyList) ? ', "LastEdited" = NOW()' : '';
			foreach($sortedIDs as $sortValue => $id) {
				if($map[$id] != $sortValue) {
					\DB::query(sprintf(
//						'UPDATE "%s" SET "%s" = %d%s WHERE %s',
						'UPDATE "%s" SET "%s" = %d WHERE %s',
						$sortTable,
						$sortField,
						$sortValue,
//						$additionalSQL,
						$this->getSortTableClauseForIds($list, $id)
					));
				}
			}
		} else {
			// For versioned objects, modify them with the ORM so that the
			// *_versions table is updated. This ensures re-ordering works
			// similar to the SiteTree where you change the position, and then
			// you go into the record and publish it.
			foreach($sortedIDs as $sortValue => $id) {
				if($map[$id] != $sortValue) {
					$record = $class::get()->byID($id);
					$record->$sortField = $sortValue;
					$record->write();
				}
			}
		}

		$this->extend('onAfterReorderItems', $list);
	}

	protected function populateSortValues(\DataList $list) {
		$list   = clone $list;
		$field  = $this->getSortField();
		$table  = $this->getSortTable($list);
		$clause = sprintf('"%s"."%s" = 0', $table, $this->getSortField());
//		$additionalSQL = (!$list instanceof \ManyManyList) ? ', "LastEdited" = NOW()' : '';

		foreach($list->where($clause)->column('ID') as $id) {
			$max = \DB::query(sprintf('SELECT MAX("%s") + 1 FROM "%s"', $field, $table));
			$max = $max->value();

			\DB::query(sprintf(
//				'UPDATE "%s" SET "%s" = %d%s WHERE %s',
				'UPDATE "%s" SET "%s" = %d WHERE %s',
				$table,
				$field,
				$max,
//				$additionalSQL,
				$this->getSortTableClauseForIds($list, $id)
			));
		}
	}

}
