<?php
namespace Modular\Extensions\Views;

use Modular\Blocks\Block;
use Modular\Fields\Field;
use Modular\Fields\Title;
use Modular\Relationships\HasBlocks;

class AddDefaultBlocks extends Field {
	const SingleFieldName = 'AddDefaultBlocks';
	const SingleFieldSchema = 'Boolean';

	// this should be added to the extended view, e.g in application blocks.yml
	// blocks added here will be appended to existing blocks in the order defined
	private static $default_blocks = [
		#   'ContentBlock',
		#   'FootnotesBlock'
	];

	/**
	 * Add controls to the same tab as the blocks.
	 * @return string
	 */
	public function cmsTab() {
		return HasBlocks::config()->get('cms_tab_name');
	}

	/**
	 * If the 'add default blocks' checkbox was not set (ie not checked in UI) then set it to 0 on the model
	 */
	public function onBeforeWrite() {
		if (!\Controller::curr()->getRequest()->postVar(self::SingleFieldName)) {
			$this()->{self::SingleFieldName} = 0;
		}
		// pick this up in onAfterWrite
		$this()->WasNew = !$this()->isInDB();
		parent::onBeforeWrite();
	}

	public function onAfterWrite() {
		parent::onAfterWrite();
		if ($this()->hasExtension(HasBlocks::class_name())) {
			/** @var \ManyManyList $existing */
			$existing = $this()->{HasBlocks::relationship_name()}();

			if ($this()->{self::SingleFieldName} || $this()->WasNew) {

				if ($defaultBlockClasses = $this->getDefaultBlockClasses()) {
					// get class names along with count of each expected
					$expected = array_count_values($defaultBlockClasses);
					$sort = $existing->count() + 1;

					foreach ($expected as $blockClass => $expectedCount) {
						if (!\ClassInfo::exists($blockClass)) {
							continue;
						}

						$existingCount = $existing->filter('ClassName', $blockClass)->count();
						if ($existingCount < $expectedCount) {
							for ($i = $existingCount; $i < $expectedCount; $i++) {

								// generate a default title for the block from lang
								// e.g. ContentBlock.DefaultTitle

								$templateVars = [
									'pagetitle' => $this()->{Title::SingleFieldName},
									'singular'  => singleton($blockClass)->i18n_singular_name(),
									'index'     => $i + 1,
								];
								// try the block class.DefaultTitle and then Block.DefaultTitle
								$title = _t(
									"$blockClass.DefaultTitle",
									_t(
										'Block.DefaultTitle',
										'{pagetitle} {singular} - {index}',
										$templateVars
									),
									$templateVars
								);
								/** @var Block $block */
								$block = new $blockClass();
								$block->update([
									'Title' => $title
								]);
								$block->write();

								$existing->add($block, [
									'Sort' => $sort++,
								]);
							}
						}
					}
				}
			}
		}
	}

	protected function getDefaultBlockClasses() {
		return $this()->config()->get('default_blocks');
	}
}