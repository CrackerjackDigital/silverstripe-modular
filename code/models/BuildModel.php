<?php
namespace Modular\Models;

use Modular\Model;

class Build extends Model {
    const BuildAllFlag = 'all';

	private static $enabled = true;

	// disable all BuildModel requireDefaultRecords processing (providePermissions should still work).
	private static $disable_all = false;

    public function requireTable() {
        \DB::dontRequireTable(get_class($this));
	    \DB::dontRequireTable('Modular\Models\Build');
    }

    /**
     * Check if we should run the build using config and request url
     * @return bool
     */
    public function shouldRun() {
        return (!self::config()->get('disable_all'))
            && (static::config()->get('enabled')
	        || $this->forceBuild());
    }

    /**
     * Check if we are being forced to build class or classes irrespective of 'run_on_build' setting.
     * @return bool - true yes build this class, false no leave out of build
     */
    public function forceBuild() {
        // classes to build are passed as build=BuildClass1,BuildClass2,BuildClassN or 'build=all' for all classes
	    // where BuildClass is e.g. RelationshipTypeBuild
        $buildClasses = array_map(
	        'strtolower',
	        explode(',', \Controller::curr()->getRequest()->getVar('build'))
        );

	    $className = strtolower(get_class($this));

	    return in_array(static::BuildAllFlag, $buildClasses)
		    || in_array($className, $buildClasses);
    }
}