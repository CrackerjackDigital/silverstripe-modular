<?php

/**
 * Dummy DataModel included to force trait_loader to regenrate trait cache on build. This may be a really dumb way
 * to do it but it is what it is.
 */
class ModularBuildModel extends DataObject {
	public function requireTable() {
		DB::dont_require_table(__CLASS__);
	}

	public function requireDefaultRecords() {
		new \sgn\TraitManifest(BASE_PATH, true);
	}
}