<?php

namespace Modular\Extensions\Model;

use DataObject;
use Modular\ModelExtension;

class CacheHash extends ModelExtension {
	const CacheHashFieldName        = 'CacheHash';
	const CacheHashChangedFieldName = '_PreviousCacheHash';

	private static $db = [
		self::CacheHashFieldName => 'Varchar(255)',
	];

	private static $cache_hash_invalidate_parents = true;

	// don't update the cache hash if only the cache hash has changed
	private static $cache_hash_ignore_fields = [
		'CacheHash',
	];

	public function onBeforeWrite() {
		$changed = $this->model()->getChangedFields( true, DataObject::CHANGE_VALUE );
		$ignore  = $this->model()->config()->get( 'cache_hash_ignore_fields' ) ?: [];

		if ( array_diff( array_keys( $changed ), $ignore ) ) {
			$this->cacheHashRegenerate();
		}
		parent::onBeforeWrite();
	}

	public function onAfterWrite() {
		parent::onAfterWrite();
		if ( $this->cacheHashChanged() && $this->config()->get( 'cache_hash_invalidate_parents' ) ) {
			$this()->{self::CacheHashChangedFieldName} = '';

			// update parents without triggering regeneration etc
			$parent = $this->owner->Parent();
			while ( $parent && $parent->exists() && $parent->hasExtension( self::class ) ) {
				$hash = static::generate_hash();

				\DB::query( "update File set CacheHash = '$hash' where ID = $parent->ID" );
				$parent = $parent->Parent();
			}
		}
	}

	public static function generate_hash() {
		return md5( uniqid( microtime() ) );
	}

	public function cacheHashRegenerate( $write = false ) {
		$this()->{self::CacheHashChangedFieldName} = $this()->{self::CacheHashFieldName};
		$this()->{self::CacheHashFieldName}        = $newHash = static::generate_hash();

		if ( $write ) {
			$this()->write();
		}

		return $newHash;
	}

	protected function cacheHashChanged() {
		return $this()->{self::CacheHashChangedFieldName} && ( $this()->{self::CacheHashFieldName} != $this()->{self::CacheHashChangedFieldName} );
	}
}