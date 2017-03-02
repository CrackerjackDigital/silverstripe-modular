<?php
namespace Modular\Traits;

use \Requirements as Requirement;
use \Modular\Application;
use \Modular\Module;

trait requirements {

	/**
	 * Includes requirements from Injector configured Application. If a requirement starts with '/' then
	 * it is included relative to site root, otherwise it is included relative to module root. Requirements
	 * can be defined as 'before' and 'after' in which case they will be included as per $beforeOrAfterInit
	 * parameter otherwise all requirements will be included when called.
	 *
	 * Will also walk to parent class first to get it's requirements so they are included in the correct order, if it
	 * is a
	 *
	 * e.g.
	 *
	 * private static $requirements = array(
	 *  '/framework/thirdpary/jquery/jquery.min.js': true,  // will come relative to site root
	 *  'js/modulescript.js': true                          // will load from module_path/js/
	 *  'css/modulecss.js': false                           // will not load as value is set to 'false' (but would load from module_path/css/)
	 * )
	 *
	 * or
	 *
	 * private static $requirements = array(
	 *  'block' => array( ... ),                //  requirements in here will be blocked
	 *  'before' => array( ... )',              //  these will be loaded onBeforeInit of extended controller
	 *  'after' => array( ... )'                //  these will be loaded onAfterInit of extended controller
	 * )
	 *
	 * @param string $beforeOrAfterInit whether to include before or after requirements, or both
	 * @param string $modulePath        path to load scripts, css etc from if not the theme
	 * @return $this
	 */
	public function requirements($beforeOrAfterInit = Module::BothInit, $modulePath = '') {
		$ancestry = array_slice(\ClassInfo::ancestry(get_class()), 0, -1);
		foreach ($ancestry as $className) {
			if (is_a($className, 'Modular\Module', true)) {
				$this->addRequirements($beforeOrAfterInit, $className);
			}
		}
		// finish off ourselves with possibly custom module path
		$this->addRequirements($beforeOrAfterInit, get_class($this), $modulePath);
	}

	/**
	 * @param string $beforeOrAfterInit
	 * @param string $className
	 * @param string $modulePath
	 * @return $this
	 */
	protected function addRequirements($beforeOrAfterInit, $className, $modulePath = '') {
		// we want the config for the actual class instance we are in as we will be doing Config::UNINHERITED
		// to read from that level first.

		if ($requirements = array_filter(\Config::inst()->get($className, 'requirements', \Config::UNINHERITED) ?: [])) {
			$config = \Config::inst()->forClass($className);

			$modulePath = $modulePath
				?: $config->get('module_path', \Config::UNINHERITED)
					?: \SSViewer::get_theme_folder();

			if (isset($requirements[ $beforeOrAfterInit ])) {
				// exclude any requirements which have been set to 'false' in config
				$requirements = array_filter($requirements[ $beforeOrAfterInit ]);
			}

			foreach ($requirements as $requirement => $info) {
				// atm info is just a boolean so parent requirements can be turned off in config
				if ($info) {
					if (substr($requirement, 0, 1) != '/') {
						// prepend module path as not 'absolute' path (relative to web root)
						$requirement = \Controller::join_links($modulePath, $requirement);
					}
					$this->requireFile($requirement);
				}
			}
		}
		return $this;
	}

	/**
	 * Add file to SS requirements depending on extension (.js or other atm).
	 *
	 * @param $requirement
	 * @return string
	 */
	private function requireFile($requirement) {
		if ($requirement = trim($requirement, DIRECTORY_SEPARATOR)) {
			if (substr($requirement, -3) == '.js') {
				Requirement::javascript($requirement);
			} else {
				Requirement::css($requirement);
			}
		}
		return $requirement;

	}
}
