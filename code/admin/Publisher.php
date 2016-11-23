<?php
namespace Modular\Admin;

use Modular\Debugger;
use Modular\debugging;
use Modular\Exceptions\Exception as Exception;

class Publisher extends \Modular\Controller {
	use debugging;

	private static $allowed_actions = [
		'publishall',
	];

	private static $defaults = [
		'start' => 0,
	    'limit' => 30,
	    'stop' => 0
	];

	private static $confirmation_token = '';

	protected function options() {
		// cache it
		static $options;

		if (is_null($options)) {
			$options = array_merge(
				$this->config()->get('defaults') ?: [],
				$this->getRequest()->getVars(),
				$this->getRequest()->postVars()
			) ?: [];
		}
		return $options;
	}

	protected function option($name) {
		if ($options = $this->options()) {
			if (array_key_exists($name, $options)) {
				return $this->options[$name];
			}
		}
		return null;
	}

	protected function confirmationToken() {
		return $this->config()->get('confirmation_token');
	}

	public function publishall($request = null) {
		$this->debugger()->toFile(Debugger::DebugAll, "publisher.log");

		$request = $request ?: $this->getRequest();

		$response = false;

		$this->debug_info("Starting publish all");

		increase_time_limit_to();
		increase_memory_limit_to();

		if (!$confirmationToken = $this->confirmationToken()) {
			$this->debug_fail(new Exception("No confirmation token configured for Modular\\Admin\\Publisher"));
		}

		if ($this->option('confirm') != $confirmationToken) {
			$this->debug_fail(new Exception("Bad or missing confirmation token"));
		}
		$start = $this->option('start');
		$limit = $this->option('limit');
		$stop = $this->option('stop');

		$this->debug_info("Publishing page from '$start' to '" . ($start + $limit) . "'");

		/** @var \DataList $pages */
		$pages = \DataObject::get("SiteTree", "", "", "", "$start,$limit");
		$count = 0;
		/** @var \Page|\Versioned $page */
		foreach ($pages as $page) {

			if ($page && !$page->canPublish()) {
				$this->debug_warn("Not allowed to publish page $page->ID '$page->Title'");
				continue;
			}

			try {
				$page->doPublish();
				$this->debug_info("Published page $page->ID '$page->Title'");

			} catch (\Exception $e) {
				$this->debug_error("Failed to publish page $page->ID '$page->Title': " . $e->getMessage());
			}
			$page->destroy();
			unset($page);

			$count++;
		}
		if ($pages->count() > 29) {
			$start += 30;
			if ($stop == 0 || $start < $stop) {
				$url = $request->getURL(false);

				$response = $this->redirect("$url?confirm=1&start=$start&limit=$limit&stop=$stop");
			}
		} else {
			$response = $this->debug_read_log();
		}

		return $response;
	}
}