<?php
namespace Modular\Controllers;

use Modular\Controller;
use Modular\Exceptions\Exception;
use Modular\Interfaces\ModelController;
use Modular\Interfaces\RouteProvider;
use Modular\Traits\reflection;
use Modular\Traits\routing;

/**
 * A modular Model always controls one primary model type. It expects at least $ModelClass URL param
 * and optionally $ModelID if a single model is being addressed. If no ID then a list of the models can be provided.
 *
 * @package Modular
 */
class Model extends Controller implements ModelController, RouteProvider {
	use reflection;
	use routing;
	
	// should be set in concrete class to model class being managed by this controller
	const ModelClassName = '';
	
	// if there are likely to be collissions between page structure and model controllers set this to something
	// unlikely to end up as a root page on the site tree as a url-segment.
	private static $route_prefix = '';
	
	// if set this will be used instead of the manufactured route to build the Director rule to this controller
	private static $alternate_route = '';
	
	public function __construct() {
		/** @var string|Model $class */
		$class = get_class($this);
		if (!$class::model_class_name()) {
			throw new Exception("No ModelClassName defined on '$class'");
		}
		parent::__construct();
	}
	
	/**
	 * Return the class name of the model this controller manages, e.g  'Modular\Models\Social\Organisation'
	 * @return string
	 */
	public static function model_class_name() {
		return static::ModelClassName;
	}
	
	/**
	 * Return the primary route to this controller,
	 * e.g. [
	 *  'modular/social/controllers/post' => 'Modular\Social\Controllers\Post',
	 *  'topics' => 'Modular\Social\Controllers\ForumTopic'                      // with config.alternate_route set
	 * ]
	 */
	public function routes() {
		$route = static::config()->get('alternate_route') ?: static::class_name_to_route(static::model_class_name());
		return [
			 $route => get_class($this)
		];
	}
	
	/**
	 * If ModelID is passed as param then use it along with model_class to try and fetch the model. If doesn't exist
	 * then returns null.
	 *
	 * @return \DataObject|\Modular\Model
	 */
	public function model() {
		$modelClass = static::model_class_name();
		if ($id = $this->modelID()) {
			return \DataObject::get($modelClass)->byID($id);
		} else {
			static::debug_warn("No model for '$modelClass' with id '$id'");
		}
	}
	
	/**
	 * Return the ModelID part of the url or null if not set.
	 * @return string
	 */
	public function modelID() {
		return $this->getRequest()->param(static::ModelIDParam) ?: null;
	}
	
	/**
	 * returns the config.model_class not the actual model class from the url.
	 * @return string
	 */
	public function modelClassName() {
		return static::model_class_name();
	}
	
}