<?php
namespace Modular\Fields;

use Config;
use HiddenField;
use Modular\Model;
use Permission;
use ReadonlyField;
use SQLQuery;
use TextField;
use ValidationException;
use ValidationResult;

/**
 * Adds a 5-letter 'Code' field to the extended model and makes it readonly in CMS,
 * and adds ability for a code of SYSTM to be filtered out via augmentSQL extension call.
 */
class Code extends Field {
	const SystemCode = '_SYS_';
	const CodeFieldName = 'Code';

	private static $db = [
		self::CodeFieldName => 'Varchar(5)'
	];
	/**
	 * Set to false if you really want to see TaxonomyTerms with a code of 'SYSTM' everywhere
	 * ( see build.yml for example ). Enabled by default.
	 * @var bool true
	 */
	private static $augment_enabled = true;

	public function cmsFields() {
		if ($this->owner()->isInDB()) {
			return [
				new ReadonlyField(self::CodeFieldName . 'RO', 'Unique Code', $this()->{self::CodeFieldName}),
				new HiddenField(self::CodeFieldName)
			];
		} else {
			return [
				new TextField(self::CodeFieldName)
			];
		}
	}

	/**
	 * Prevent duplicate code being entered.
	 *
	 * @param \ValidationResult $result
	 * @return array|void
	 * @throws \ValidationException
	 */
	public function validate(ValidationResult $result) {
		// this could throw an exception, let it
		parent::validate($result);

		$code = $this->owner->{self::CodeFieldName};

		if ($this->owner->isInDB()) {
			// code should be read-only in CMS but check anyway that doesn't exist on another ID
			$existing = Model::get($this->owner->class)
				->exclude('ID', $this->owner->ID)
				->filter(self::CodeFieldName, $code)
				->first();
		} else {
			// check code doesn't exist already
			$existing = Model::get($this->owner->class)
				->filter(self::CodeFieldName, $code)
				->first();
		}
		if ($existing) {
			$message = $this->fieldDecoration(
				self::CodeFieldName,
				'Duplicate',
				"Code must be unique, the {singular} '{title}' already uses '{code}'", [
					'code'  => $code,
					'title' => $existing->Title ?: $existing->Name
				]
			);

			$result->error($message);
			throw new ValidationException($result);
		}
	}

	/**
	 * Don't show TaxonomyTerms with code of SYSTM unless you're an Admin or config.augment_enabled = false
	 * @param \SQLQuery $query
	 */
	public function augmentSQL(SQLQuery &$query) {
		parent::augmentSQL($query);

		if (static::augment_enabled() && !Permission::check('ADMIN')) {
			$query->addWhere(self::CodeFieldName . " != '" . self::SystemCode . "'");
		}
	}
	public static function augment_enabled() {
		return Config::inst()->get(static::class, 'augment_enabled');
	}

	public static function disable_augment() {
		return static::augment_enable(false);
	}

	public static function enable_augment() {
		return static::augment_enable(true);
	}

	private static function augment_enable($enabled) {
		$wasEnabled = static::augment_enabled();
		Config::inst()->update(static::class, 'augment_enabled', $enabled);
		return $wasEnabled;
	}

}