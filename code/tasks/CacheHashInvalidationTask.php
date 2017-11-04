<?php
namespace Modular\Tasks;

use Modular\Extensions\Model\CacheHash;
use Modular\Task;
use Modular\Traits\md5;

/**
 * CacheHashInvalidationTask update all files in the system with a new CacheHash
 *
 * @package Modular\Tasks
 */
class CacheHashInvalidationTask extends Task {
	use md5;

	/**
	 * Update all files in system with new cache hash.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function execute( $params = [], &$resultMessage = '' ) {

		$tableNames = [];
		foreach (\ClassInfo::subclassesFor( 'Object') as $className) {
			if (\Object::has_extension( $className, CacheHash::class)) {
				if ($tableName = \ClassInfo::table_for_object_field( $className, CacheHash::CacheHashFieldName)) {
					if (!in_array($tableName, $tableNames)) {
						\DB::prepared_query( 'update "' . $tableName . '" set CacheHash = ?', [ $this->hash( '', $className ) ] );
						static::debug_info( "Updated " . \DB::affected_rows() . " rows in '$tableName'" );
						$tableNames[] = $tableName;
					}
				}

			}
		}
	}

}