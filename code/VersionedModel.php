<?php
namespace Modular;

class VersionedModel extends \DataObject {
	use lang;
	use related;
	use backprop;

	private static $backprop_events = [
		'onBeforeWrite' => true,
		'onAfterWrite'  => true,
	];

	/**
	 * Invoking a model returns itself.
	 *
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

	public static function class_name() {
		return get_called_class();
	}

	/**
	 * Notify related models that this model changed.
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();

		if ($info = $this->shouldBackProp(__FUNCTION__)) {
			if ($this->isChanged()) {
				$this->backprop(__FUNCTION__, $info, $this);
			}

		}
	}

	public function onAfterWrite() {
		parent::onAfterWrite();
		if ($info = $this->shouldBackProp(__FUNCTION__)) {
			$this->backprop(__FUNCTION__, $info, $this);
		}
	}

}