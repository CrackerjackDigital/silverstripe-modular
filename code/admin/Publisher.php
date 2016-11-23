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

	public function __construct() {
		$this->debugger()->toFile(Debugger::DebugAll, "publisher.log");
		parent::__construct();
	}

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
				return $options[$name];
			}
		}
		return null;
	}

	protected function confirmationToken() {
		return $this->config()->get('confirmation_token');
	}
	/**
	 * Checks permissions and confirmation token, throws exception if not OK, otherwise returns true.
	 * @return boolean
	 * @throws Exception
	 */
	protected function canDoItOrFail() {
		if (!\Permission::check('ADMIN') && !\Director::is_cli()) {
			$this->debug_fail(new Exception("Not and admin and not cli"));
		}
		if (!$confirmationToken = $this->confirmationToken()) {
			$this->debug_fail(new Exception("No confirmation token configured for Modular\\Admin\\Publisher"));
		}
		if ($this->option('confirm') != $confirmationToken) {
			$this->debug_fail(new Exception("Bad or missing confirmation token"));
		}
		return true;
	}

	public function publishall($request = null) {
		$this->canDoItOrFail();

		$request = $request ?: $this->getRequest();

		$response = false;

		$this->debug_info("Starting publish all");

		increase_time_limit_to();
		increase_memory_limit_to();

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
				$confirmationToken = $this->confirmationToken();
				$response = $this->redirect("$url?confirm=$confirmationToken&start=$start&limit=$limit&stop=$stop");
			}
		}
		// at the end there will be no redirect response so return the debug log instead
		return $response ?: $this->debug_read_log(!\Director::is_cli());
	}
}