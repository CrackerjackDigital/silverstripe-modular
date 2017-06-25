<?php
namespace Modular\Extensions\Model;

use Member;
use Modular\Relationships\HasOne;
use Modular\Traits\enabler;
use SQLQuery;

/**
 * Extension which makes models not really delete when delete is called but instead flag as
 * deleted and when selecting models ignore those flagged as deleted.
 */
class Historical extends HasOne {
	use enabler;

	const Name = 'HistoricalDeletedBy';
	const Schema = 'Member';

    private static $db = [
        'HistoricalDate' => 'SS_DateTime'
    ];

    private static $historical_enabled = true;

    private static $admin_only = true;

    public function onBeforeDelete() {
	    parent::onBeforeDelete();
	    $this()->{self::related_field_name()} = Member::currentUserID();
    }

	public function augmentSQL(SQLQuery &$query) {
        if (static::enabled()) {
            $modelClass = get_class($this());
            $query->addWhere("$modelClass.HistoricalDate is null");
        }
    }
}
