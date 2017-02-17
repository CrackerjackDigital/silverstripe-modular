<?php
namespace Modular;

use Modular\Exceptions\Exception;
use SilverStripe\Core\Config\Config_ForClass;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Versioning\Versioned;

/**
 * Add this trait to models or model extensions to notify any 'backward' related models (via has_one back to has_many or via belongs_many_many)
 * when events happen on the model. For example add to a 'Block' model to ensure that the blocks 'owners' (Page or a containing block) get notified
 * when the block itself is changed.
 *
 * @package Modular
 */
trait backprop {

	protected $backprop;

	/**
	 * When exhibited by a model this should return the model, for an extension it should return the extended model
	 *
	 * @return mixed
	 */
	abstract public function __invoke();

	/**
	 * @return Config_ForClass
	 */
	abstract public function config();

	public function initBackprop() {
		$this()->backprop = [];
	}

	/**
	 * Check if the event is configured as a key in the config.backprop_events array and if so returns it, otherwise false. The value can be an array,
	 * object etc which is useful for targets to be advised as part of the backprop notification, and can be managed as per normal config mechanism.
	 *
	 * @param $event
	 * @return mixed
	 * @throws \Modular\Exceptions\Exception
	 */
	public function shouldBackProp($event) {
		if (is_null($this()->backprop)) {
			throw new Exception("initBackprop not called, please do so before advising any events");
		}
		return $this->backpropEventInfo($event);
	}

	/**
	 * Check if an event has already happened.
	 *
	 * @param $event
	 * @return bool
	 */
	public function didBackprop($event) {
		return is_array($this()->backprop) && array_key_exists($event, $this()->backprop);
	}

	/**
	 * Get or set data for an event. Returns the data either way.
	 *
	 * @param string $event
	 * @param mixed  $data
	 * @return mixed
	 */
	public function backpropData($event, $data = null) {
		if (func_num_args() > 1) {
			$this->backprop[ $event ] = $data;
		}
		return array_key_exists($event, $this->backprop) ? $this->backprop[ $event ] : null;
	}

	/**
	 * Return the event info for the event, override to provide results that can't be declared statically in config. What to
	 * store for each event is declared as config.backprop_events on each model, with event as key and a tag as the value. The tag
	 * can be a method name to call on the model, a field to copy the value from, a property (not field) on the model or if none of
	 * those are available a verbatim value.
	 *
	 * @param string $event name of event, e.g. 'changing'
	 * @return mixed results from dereferencing the value from the event
	 */
	public function backpropEventInfo($event) {
		$eventSources = static::config()->get('backprop_events');
		if (array_key_exists($event, $eventSources)) {
			$source = $eventSources[ $event ];

			if ($this()->hasMethod($source)) {

				$data = $this()->$source($event);               // generally will call 'eventData'

			} elseif (is_string($source) && $this()->hasField($source)) {

				$data = $this()->$source;                       // get value from a field

			} elseif (property_exists($this(), $source)) {

				$data = $this()->$source;                       // property not field

			} else {
				$data = $source;                                // verbatim source
			}
			return $data;
		}
	}

	/**
	 * Notify related models (e.g. owning Page via HasPage or Related pages via RelatedPages ) so it shows as modified in CMS when this model changes.
	 *
	 * Care should be taken that this doesn't happen as part or after a publish, otherwise if the model is published then the owner will be
	 * marked as dirty and so will always appear as being 'modified' to the CMS.
	 *
	 * @param string $event gets passed to related models, could be an 'event name' e.g. 'published' or an originating method name e.g. 'onAfterWrite'.
	 */
	public function backprop($event) {
		if ($data = $this->shouldBackProp($event)) {
			/** @var DataObject $relatedModel */
			/** @var DataObject|Versioned $model */
			$model = $this();

			// in this cycle we only want to record the first event that happens
			if (!isset($this()->backprop[ $event ])) {
				// flag as backpropped (attempted anyway) and add changed fields
				$this()->backpropData($event, $data);
			}

			if ($belongs = $model->config()->get('belongs_many_many')) {
				// e.g for a Block an example relationship would be 'Pages' => 'Page'
				foreach ($belongs as $relationshipName => $className) {
					$relationships = $model->$relationshipName();
					foreach ($relationships as $relatedModel) {
						if ($relatedModel && $relatedModel->exists()) {
							// need to package info up for invokeWithExtensions only taking one param
							$relatedModel->invokeWithExtensions('relatedBackProp', [$event, $data, $this, $model]);
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
						$relatedModel->invokeWithExtensions('relatedBackProp', [$event, $data, $this, $model]);
					}
				}
			}
		} else {
			unset($this()->backprop[ $event ]);
		}
	}

}