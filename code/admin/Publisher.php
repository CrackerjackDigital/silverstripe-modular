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
				return $options[$name];
			}
		}
		return null;
	}

	protected function confirmationToken() {
		return $this->config()->get('confirmation_token');
	}

	/**
	 * Checks permissions to see if we can run a Publisher 'task'. This should only be called first time task is entered (e.g. not on entry do to a redirect).
	 */
	protected function canDoItOrFail() {
		// no Session.PublishAllLogFileName set up so check permissions, if we're in cli and that correct confirmation token is given
		if (!\Director::is_cli() && !\Permission::check('ADMIN')) {
			$this->debug_fail(new Exception("Not an admin and not cli"));
		}
		if (!$this->confirmationToken()) {
			$this->debug_fail(new Exception("No confirmation token configured for Modular\\Admin\\Publisher"));
		}
		if ($this->option('confirm') != $this->confirmationToken()) {
			$this->debug_fail(new Exception("Bad or missing confirmation token"));
		}
	}

	public function publishall($request = null) {
		increase_time_limit_to();
		increase_memory_limit_to();

		if ($this->option('reset')) {
			// force a first-time authentication etc
			\Session::clear('PublishAllLogFileName');
		}

		if ($logFileName = \Session::get('PublishAllLogFileName')) {
			// clear it so if anything goes wrong we're not stuck, will be set again just before redirect later
			\Session::clear('PublishAllLogFileName');

			// write all output to file
			static::debugger()->toFile(Debugger::DebugAll, $logFileName);
			$this->debug_info("Continuing publish all at " . date('Ymdhis'));

		} else {
			$logFileName = 'publisher-' . date('Ymdhis') . '.log';

			// write all output to file
			// first time we want to truncate the log
			static::debugger()->toFile(Debugger::DebugAll | Debugger::DebugTruncate, $logFileName);
			$this->debug_info("Starting publish all");

			// first round permission check, will throws exception if can't do it
			$this->canDoItOrFail();

		}
		// output some progress feedback of log so far
		$this->debug_output_log();

		// first page index in result set of all pages to publish
		$start = $this->option('start');

		// last page index in result set of all pages to publish
		$stop = $this->option('stop');

		// the number of pages to publish per 'chunk'
		$limit = $this->option('limit');

		$this->debug_info("Publishing pages from index '$start' to '" . ($start + $limit) . "' in chunks of '$limit' pages");

		// if set during loop then we will be redirecting back to this page, otherwise we are done
		$response = false;

		\Versioned::set_reading_mode('Stage.Stage');

		/** @var \DataList $pages */
		$pages = \DataObject::get("SiteTree", "", "ID asc", "", "$start,$limit");
		$count = 0;

		/** @var \Page|\Versioned $page */
		foreach ($pages as $page) {
			try {
				if ($page->canPublish()) {

					$page->doPublish();
					$this->debug_info("Published page $page->ID '$page->Title'");

				} else {
					$this->debug_warn("Not allowed to publish page $page->ID '$page->Title'");
				}
			} catch (\Exception $e) {
				$this->debug_error("Failed to publish page $page->ID '$page->Title': " . $e->getMessage());
			}
			$page->destroy();
			unset($page);

			$count++;

			// stop is not always on a start/limit boundary, we can stop partway through a 'chunk'
			if ($stop && ($start + $count) >= $stop) {
				break;
			}
		}
		if ((($start + $count) < $stop) && ($pages->count() > ($limit - 1))) {
			$start += $limit;

			if ($stop == 0 || $start < $stop) {
				$url = $request->getURL(false);

				// set the publishall indicator to be picked up after redirect.
				\Session::set('PublishAllLogFileName', $logFileName);

				$response = $this->redirect("$url?start=$start&limit=$limit&stop=$stop");
			}
		}
		if (!$response) {
			// we're done, make sure this is cleared
			\Session::clear('PublishAllLogFileName');

			$this->debug_info("Ending publish all");

		}
		// at the end there will be no redirect response so return the debug log instead
		return $response;
	}
}