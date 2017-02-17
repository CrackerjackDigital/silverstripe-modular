<?php
namespace Modular\Behaviours;

use Modular\ModelExtension;
use SilverStripe\Admin\CMSPreviewable;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\LiteralField;
use SilverStripe\CMS\Controllers\SilverStripeNavigator;

/**
 * Adds preview pane to CMS for extended Models.
 *
 * Standing on the shoulders of Jotham Reid here https://github.com/jotham/silverstripe-dataobject-preview just tweaked to fit
 * the Modular code and data model.
 */

class Previewable extends ModelExtension {

	/**
	 * @param Form $form
	 */
	public function updateItemEditForm(&$form) {
		$fields = $form->Fields();
		if ($this->owner->record instanceof CMSPreviewable && !$fields->fieldByName('SilverStripeNavigator')) {
			$this->injectNavigatorAndPreview($form, $fields);
		}
	}

	/**
	 * @param \Form $form
	 * @param \FieldList $fields
	 */
	private function injectNavigatorAndPreview(&$form, &$fields) {
		$template = Controller::curr()->getTemplatesWithSuffix('_SilverStripeNavigator');
		$navigator = new SilverStripeNavigator($this->owner->record);
		$field = new LiteralField('SilverStripeNavigator', $navigator->renderWith($template));
		$field->setAllowHTML(true);
		$fields->push($field);
		$form->addExtraClass('cms-previewable');
		$form->addExtraClass(' cms-previewabledataobject');
		$form->removeExtraClass('cms-panel-padded center');
	}

}