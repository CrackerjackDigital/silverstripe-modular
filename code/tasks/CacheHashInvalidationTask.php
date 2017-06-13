<?php
namespace Modular\Tasks;

use Modular\Task;

/**
 * CacheHashInvalidationTask update all files in the system with a new CacheHash
 *
 * @package Modular\Tasks
 */
class CacheHashInvalidationTask extends Task {

	/**
	 * Update all files in system with new cache hash.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		static::debug_info( "Updating all File cache hashes" );
		\DB::query( "update File set CacheHash = md5(concat(Filename, now()))" );
		static::debug_info( \DB::affected_rows() . " updated" );
	}

}