<?php
namespace Modular\GridList\Sequencers;

use Modular\GridList\Fields\Mode;
use Modular\GridList\GridList;
use Modular\GridList\Interfaces\GridListTempleDataProvider;
use Modular\GridList\Interfaces\ItemsSequencer;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

class GroupByField extends ModelExtension implements ItemsSequencer, GridListTempleDataProvider {
	const GroupByFieldName  = '';
	const TitleDBFieldClass = 'Text';
	const TemplateDataKey   = 'GroupedBy';

	/**
	 * Return an array of additional data to return to the template and make available via the $GridList template variable.
	 *
	 * @param array $existingData not used
	 * @return array to add to template data
	 */
	public function provideGridListTemplateData($existingData = []) {
		return [
			static::TemplateDataKey => static::GroupByFieldName,
		];
	}

	/**
	 * Sort items by EventDate desc, if we are in list mode then group by EventDate also update ItemCounts by group.
	 *
	 * @param \ArrayList|\DataList $groups
	 * @param                      $filters
	 * @param array                $parameters
	 */
	public function sequenceGridListItems(&$groups, $filters, &$parameters = []) {
		if ($groups->count()) {
			// this was added by Mode field
			$mode = $parameters[Mode::TemplateDataKey];

			if ($mode == GridList::ModeList) {
				$groupByFieldName = static::GroupByFieldName;

				$groups = \GroupedList::create(
					$groups->Sort($groupByFieldName, 'desc')
				)->GroupedBy($groupByFieldName);

				foreach ($groups as $group) {

					if ($children = $group->Children) {
						// de-dup items within the group
						$children->removeDuplicates();
					}
					// this is used for uniqueness testing by front-end when loading via ajax
					$group->Hash = md5($group->$groupByFieldName);

					// add the group title field
					$group->GroupTitle = $this->createGroupTitle($group->$groupByFieldName);


				}
			}
		}
	}

	/**
	 * @param $value
	 * @return \DBField
	 */
	protected function createGroupTitle($value) {
		return \DBField::create_field(static::TitleDBFieldClass, $value);
	}

}