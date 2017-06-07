<?php

namespace Modular\Fields;

class CacheHash extends Field {
	const SingleFieldName   = 'CacheHash';
	const SingleFieldSchema = 'Varchar(128)';

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ( ! $this->owner()->{self::SingleFieldName} || $this->owner()->isChanged() ) {
			$this->invalidateCacheHash( false );
		}
	}

	public function invalidateCacheHash( $write = false ) {
		$this->owner()->{self::SingleFieldName} = $this->generateCacheHash();
		if ( $write ) {
			$this->owner()->write();
		}
	}

	public function generateCacheHash() {
		return uniqid( get_class( $this->owner() ) . '-' . $this->owner()->ID . '-');
	}
}