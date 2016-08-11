<?php

/**
 * Page_Controller extension for pages which have a GridList component.
 */
class HasGridListControllerExtension extends \Modular\ContentControllerExtension {

	/**
	 * Returns information for use by the GridList template, excluding the GridList items as they are retrieved by HasGridListExtension.GridList.
	 *
	 * @return \ArrayData
	 */
	public function GridList() {
		static $data;

		if (!$data) {
			/** @var GridListService $service */
			$service = Injector::inst()->get('GridListService');

			$mode = $this->config()->get('gridlist_force_mode') ?: $service->currentMode();

			$results = $service->find();

			list($start, $limit) = array_values($service->pagination());

			if ($start > $results->count()) {
				$start = $results->count() - $service->default_page_length();
			}
			if ($start < 0) {
				$start = 0;
			}
			$pagination = [
				'start' => $start,
				'limit' => $limit,
			];

			$pageLength = $service->default_page_length();

			$data = [
				'Items' => PaginatedList::create($results, $pagination)->setPageLength($pageLength),
				'CurrentSort' => $service->currentSort(),
				'CurrentMode' => $mode,
			];
			$data = new ArrayData($data);
		};
		return $data;
	}

}
