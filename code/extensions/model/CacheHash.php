<?php
namespace Modular\Extensions\Model;

use Modular\ModelExtension;

class CacheHash extends ModelExtension  {
	const CacheHashFieldName = 'CacheHash';

	private static $db = [
		self::CacheHashFieldName => 'Varchar(255)'
	];

	private static $cache_hash_invalidate_parents = true;

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ($this()->isChanged()) {
			$this->cacheHashInvalidate();
		}
	}

	/**
	 * Set CacheHash field to now, checks config.cache_hash_invalidate_parents or first of any parameters passed.
	 *
	 * @throws \ValidationException
	 */
	public function cacheHashInvalidate() {
		$invalidateParents = func_num_args()
			? func_get_args()[0]
			: $this->config()->get('cache_hash_invalidate_parents');

		$hash = $this->cacheHashGenerate();
		$this()->{self::CacheHashFieldName} = $hash;

		/** @var \DataObject $parent */
		if ($invalidateParents && $this->owner->ParentID) {
			$parent = $this->owner->Parent();
			if ($parent->exists() && $parent->hasExtension(self::class)) {
				$parent->{self::CacheHashFieldName} = $hash;
				$parent->write();
			}
		}
	}

	public function cacheHashGenerate() {
		return md5( uniqid(microtime()));
	}

}