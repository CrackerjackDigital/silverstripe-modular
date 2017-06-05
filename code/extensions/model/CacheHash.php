<?php
namespace Modular\Extensions\Model;

use Modular\ModelExtension;

class CacheHash extends ModelExtension  {
	const CacheHashFieldName = 'CacheHash';

	private static $db = [
		self::CacheHashFieldName => 'Varchar(255)'
	];

	public function onBeforeWrite() {
		if ($this()->isChanged() || !$this()->{self::CacheHashFieldName}) {
			$this()->{self::CacheHashFieldName} = md5(microtime());
		}
		parent::onBeforeWrite();
	}

	/**
	 * Set CacheHash field to now
	 */
	public function cacheHashInvalidate() {
		$this()->{self::CacheHashFieldName} = md5(microtime());
	}

}