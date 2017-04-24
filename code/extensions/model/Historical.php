<?php
namespace Modular\Extensions\Model;

use Modular\Traits\enabler;
use SQLQuery;

/**
 * Extension which makes models not really delete when delete is called but instead flag as
 * deleted and when selecting models ignore those flagged as deleted.
 */
class Historical extends \Modular\ModelExtension {
	use enabler;

    private static $db = [
        'HistoricalDate' => 'SS_DateTime',
    ];
    private static $has_one = [
        'HistoricalDeletedBy' => 'Member'
    ];

    private static $historical_enabled = true;

    public function augmentSQL(SQLQuery &$query) {
        if (static::enabled()) {
            $modelClass = get_class($this());
            $query->addWhere("$modelClass.HistoricalDate is null");
        }
    }
}
