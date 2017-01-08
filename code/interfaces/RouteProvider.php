<?php
namespace Modular\Interfaces;
/**
 * Interface RouteProvider implemented by Controller Extensions which provide rules to Director to route to the
 * extended Controller.
 *
 * @package Modular\Interfaces
 */
interface RouteProvider {
	/**
	 * Implemented on a controller and extensions implementing 'Action'.
	 * On a controller returns primary rule for the controller which Director can add to its rules collection.
	 *  - These are gathered and added to Director rules when Application::startup is called
	 *
	 * On Action controller extension returns the url_handlers and allowed_actions which have been added to the
	 * controller by the extension, e.g. '$ID/like', '$ID/unlike'.
	 *  - These are gathered by the ... extension to a Model Controller which provides these in extraStatics
	 *
	 * (generally by Application constructor called in app/_config.php ).
	 *
	 * @return array map of path to controller e.g. [ 'endpoint/path/topic' => 'Modular\Controllers\ForumTopic' ]
	 */
	public function routes();
	
}