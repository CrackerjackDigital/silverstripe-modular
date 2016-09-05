<?php
namespace Modular\Extensions\Views;

use Modular\Fields\Title;
use Modular\ModelExtension;
use Modular\Relationships\HasBlocks;

class DefaultBlocks extends ModelExtension {
	// this should be added to the extended view, e.g in application blocks.yml
	private static $default_blocks = [
		#   'ContentBlock',
		#   'FootnotesBlock'
	];

	public function onAfterWrite() {
		parent::onAfterWrite();
		if ($this()->hasExtension('HasBlocks')) {
			if ($defaultBlockClasses = $this->getDefaultBlockClasses()) {
				// get class names along with count of each expected
				$expected = array_count_values($defaultBlockClasses);

				/** @var \ManyManyList $existing */
				$existing = $this()->{HasBlocks::relationship_name()};

				foreach ($expected as $blockClass => $expectedCount) {

					$existingCount = $existing->filter('ClassName', $blockClass)->count();
					if ($existingCount < $expectedCount) {
						for ($i = $existingCount; $i < $expectedCount; $i++) {

							$title = $this()->{Title::SingleFieldName}
								. ' ' . singleton($blockClass)->i18n_singular_name();

							$block = new $blockClass([
								Title::SingleFieldName => $title
							]);
							$block->write();
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