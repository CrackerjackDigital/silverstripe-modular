<?php
namespace Modular\Tasks;

use Modular\Fields\CacheHash;

class InvalidateCacheHashes extends \BuildTask {
	public function run( $request ) {
		\DB::query( 'update "Modular\Model" set CacheHash = UUID()' );
		\DB::query( 'update "Modular\VersionedModel" set CacheHash = UUID()' );
		\DB::query( 'update "Modular\VersionedModel_Live" set CacheHash = UUID()' );
		\DB::query( 'update "Page" set CacheHash = UUID()' );
		\DB::query( 'update "Page_Live" set CacheHash = UUID()' );
	}
}