<?php

namespace Modular\Extensions\Model;

use DataObject;
use Modular\ModelExtension;

class CacheHash extends ModelExtension {
	const CacheHashFieldName = 'CacheHash';
	const CacheHashChangedFieldName = '_PreviousCacheHash';

	private static $db = [
		self::CacheHashFieldName => 'Varchar(255)',
	];

	private static $cache_hash_invalidate_parents = true;


	public function onBeforeWrite() {
		$changed = $this->model()->getChangedFields( true, DataObject::CHANGE_VALUE );
		if ( $changed ) {
			$this->cacheHashRegenerate();
		}
		parent::onBeforeWrite();
	}

	public function onAfterWrite() {
		parent::onAfterWrite();
		if ($this->cacheHashChanged() && $this->config()->get( 'cache_hash_invalidate_parents' )) {
			/** @var \DataObject|\Modular\Extensions\Model\CacheHash $parent */
			if ( $this->owner->ParentID ) {
				$parent = $this->owner->Parent();
				if ( $parent->exists() && $parent->hasExtension( self::class ) ) {
					$parent->cacheHashRegenerate(true);
				}
			}
		}
	}

	public function cacheHashRegenerate($write = false) {
		$newHash = md5( uniqid( microtime() ) );
		$this()->{self::CacheHashChangedFieldName} = $this()->{self::CacheHashFieldName};
		$this()->{self::CacheHashFieldName} = $newHash;
		if ($write) {
			$this()->write();
		}
		return $newHash;
	}

	protected function cacheHashChanged() {
		return $this()->{self::CacheHashFieldName} != $this()->{self::CacheHashChangedFieldName};
	}
}