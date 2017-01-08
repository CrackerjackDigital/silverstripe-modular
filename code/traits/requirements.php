<?php
namespace Modular\Traits;

use \Requirements as Requirement;

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
	 * @param string $beforeOrAfterInit wether to include before or after requirements
	 * @param string $modulePath        path to load scripts, css etc from if not the them
	 */
	public function requirements($beforeOrAfterInit = Module::BothInit, $modulePath = '') {
		if ($application = Application::factory()) {
			$config = $application->config();

			if (is_a(get_parent_class(), 'Modular\Module', true)) {
				// if parent is also a Module then do it's requirements first
				$parent = parent::requirements($beforeOrAfterInit);
			}

			if ($requirements = array_filter($config->get('requirements', \Config::UNINHERITED))) {
				$modulePath = $modulePath
					?: $config->get('module_path', \Config::UNINHERITED)
					?: \SSViewer::get_theme_folder();

				if (isset($requirements[ $beforeOrAfterInit ])) {
					// exclude any requirements which have been set to 'false' in config
					$requirements = array_filter($requirements[ $beforeOrAfterInit ]);
				}

				foreach ($requirements as $requirement => $info) {
					if (substr($requirement, 0, 1) != '/') {
						$requirement = \Controller::join_links($modulePath, $requirement);
					}
					if (substr($requirement, -3) == '.js') {
						Requirement::javascript(substr($requirement, 1));
					} else {
						Requirement::css(substr($requirement, 1));
					}
				}
			}
		}

	}
}