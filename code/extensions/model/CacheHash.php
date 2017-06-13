<?php

namespace Modular\Extensions\Model;

use DataObject;
use Modular\ModelExtension;

class CacheHash extends ModelExtension {
	const CacheHashFieldName = 'CacheHash';

	private static $db = [
		self::CacheHashFieldName => 'Varchar(255)',
	];

	private static $cache_hash_invalidate_parents = true;


	public function onBeforeWrite() {
		$changed = $this->model()->getChangedFields( true, DataObject::CHANGE_VALUE );
		if ( $changed ) {
			$this()->{self::CacheHashFieldName} = $this->cacheHashGenerate();
			$invalidateParents = $this->config()->get( 'cache_hash_invalidate_parents' );

			/** @var \DataObject|\Modular\Extensions\Model\CacheHash $parent */
			if ( $invalidateParents && $this->owner->ParentID ) {
				$parent = $this->owner->Parent();
				if ( $parent->exists() && $parent->hasExtension( self::class ) ) {
					// invalidate and write the parent also
					$parent->{self::CacheHashFieldName} = $parent->cacheHashGenerate();
					$parent->write();
				}
			}
		}
		parent::onBeforeWrite();
	}

	public function onAfterWrite() {
		parent::onAfterWrite();


	}

	public function cacheHashGenerate() {
		return md5( uniqid( microtime() ) );
	}

}