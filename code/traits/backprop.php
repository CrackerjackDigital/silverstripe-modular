<?php
namespace Modular;

use DataObject;
use Versioned;

/**
 * Add this trait to models or model extensions to notify any 'backward' related models (via has_one back to has_many or via belongs_many_many)
 * when events happen on the model. For example add to a 'Block' model to ensure that the blocks 'owners' (Page or a containing block) get notified
 * when the block itself is changed.
 *
 * @package Modular
 */
trait backprop {
	/**
	 * When exhibited by a model this should return the model, for an extension it should return the extended model
	 *
	 * @return mixed
	 */
	abstract public function __invoke();

	/**
	 * @return \Config_ForClass
	 */
	abstract public function config();

	/**
	 * Check if the event is configured as a key in the config.backprop_events array and if so returns it, otherwise false.
	 *
	 * @param $what
	 * @return mixed
	 */
	public function shouldBackProp($what) {
		$events = $events = $this->config()->get('backprop_events');
		if (array_key_exists($what, $events)) {
			return $events[ $what ];
		}
		return false;
	}

	/**
	 * Update the owner (e.g. Page) so it shows as modified in CMS when this model changes.
	 *
	 * Care should be taken that this doesn't happen as part or after a publish, otherwise if the model is published then the owner will be
	 * marked as dirty and so will always appear as being 'modified' to the CMS.
	 *
	 * @param string     $what   gets passed to related models, could be an 'event name' e.g. 'published' or an originating method name e.g. 'onAfterWrite'.
	 * @param mixed      $info   from the sources config.backprop_events for the event
	 * @param DataObject $source object notification coming from, e.g. a VersionedModel being saved
	 */
	public function backprop($what, $info, $source) {
		/** @var DataObject $relatedModel */
		/** @var DataObject|Versioned $model */
		$model = $this();

		$trackingVar = "backprop_$what";

		// flag as in backprop
		$model->{$trackingVar} = $info;

		if ($belongs = $model->config()->get('belongs_many_many')) {
			// e.g for a Block one would be 'Pages' => 'Page'
			foreach ($belongs as $relationshipName => $className) {
				$relatedModels = $model->$relationshipName();
				foreach ($relatedModels as $relatedModel) {
					if ($relatedModel && $relatedModel->exists()) {
						// need to package info up for invokeWithExtensions only taking one param
						$relatedModel->invokeWithExtensions('relatedBackProp', [ $what, $info, $source, $model ]);
					}
				}
			}
		}
		if ($ones = $model->config()->get('has_one')) {
			// e.g. for a Page one would be 'Parent' => 'Page'
			foreach ($ones as $relationshipName => $className) {
				/** @var DataObject $related */
				$relatedModel = $model->$relationshipName();
				if ($relatedModel && $relatedModel->exists()) {
					// need to package info up for invokeWithExtensions only taking one param
					$relatedModel->invokeWithExtensions('relatedBackProp', [ $what, $info, $source, $model ]);
				}
			}
		}
		unset($model->{$trackingVar});

	}

}