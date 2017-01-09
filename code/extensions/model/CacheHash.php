<?php
namespace Modular\Extensions\Model;

use Modular\ModelExtension;

class CacheHash extends ModelExtension  {
	const CacheHashFieldName = 'CacheHash';

	private static $db = [
		self::CacheHashFieldName => 'Varchar(255)'
	];

	public function onBeforeWrite() {
		if ($this()->isChanged() || $this()->{self::CacheHashFieldName}) {
			$this()->{self::CacheHashFieldName} = date('Y-m-d_h:i:s');
		}
		parent::onBeforeWrite();
	}

	/**
	 * Set CacheHash field to null, this will also trigger an 'isChanged' next write.
	 */
	public function cacheHashInvalidate() {
		$this()->{self::CacheHashFieldName} = null;
	}

}