<?php
namespace Modular\Collections;

use DataObject;

class VersionedManyManyList extends \ManyManyList  {

	/**
	 * Add an item to this many_many relationship
	 * Does so by adding an entry to the joinTable.
	 *
	 * @param mixed $item
	 * @param array $extraFields A map of additional columns to insert into the joinTable.
	 *                           Column names should be ANSI quoted.
	 */
	public function add($item, $extraFields = array()) {
		parent::add($item, $extraFields);
	}

	/**
	 * Remove the given item from this list.
	 *
	 * Note that for a ManyManyList, the item is never actually deleted, only
	 * the join table is affected.
	 *
	 * @param DataObject $item
	 */
	public function remove($item) {
		return parent::remove($item);
	}

	/**
	 * Remove the given item from this list.
	 *
	 * Note that for a ManyManyList, the item is never actually deleted, only
	 * the join table is affected
	 *
	 * @param int $itemID The item ID
	 */
	public function removeByID($itemID) {
		parent::removeByID($itemID);
	}

	/**
	 * Remove all items from this many-many join.  To remove a subset of items,
	 * filter it first.
	 *
	 * @return void
	 */
	public function removeAll() {
		parent::removeAll();
	}

	public function getForeignID() {
		return parent::getForeignID();
	}

	/**
	 * Returns a copy of this list with the ManyMany relationship linked to
	 * the given foreign ID.
	 *
	 * @param int|array $id An ID or an array of IDs.
	 */
	public function forForeignID($id) {
		return parent::forForeignID($id);
	}
}