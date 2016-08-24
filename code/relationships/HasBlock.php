<?php
namespace Modular\Relationships;

use Modular\Blocks\Block;
use ValidationException;
use Modular\Model;
use DropdownField;
use ValidationResult;
use Modular\Fields\Field;
use Controller;

/**
 * Class which adds a single block to a model
 */

class HasBlock extends Field {
	const RelationshipName = 'Block';
	const BlockClassName = 'Modular\Blocks\Block';
	const RelationshipFieldName = 'BlockID';

	// not in database, just UI selector for the class of the related block which could be
	// set from CMS form post to something different to current associated block type, and governs
	// creation of the associated block
	const BlockTypeFieldName = 'BlockType';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];
		return array_merge_recursive(
			$parent,
			[
				'has_one' => [
					static::RelationshipName => static::BlockClassName
				]
			]
		);
	}

	/**
	 * Return the value from passed BlockType field or the class name of the current associated block
	 */
	public function getBlockType() {
		return $this()->{self::BlockTypeFieldName} ?: ($this()->Block() ? $this()->Block()->ClassName : '');
	}

	/**
	 * Add the BlockType dropdown selector for the extended model.
	 * @return array
	 */
	public function cmsFields() {
		$blockType = $this->getBlockType();

		return [
			new DropdownField(
				self::BlockTypeFieldName,
				'Event type',
				$this()->allowed_related_classes(),
				$blockType
			)
		];
	}

	/**
	 * Check we either have an associated block already or if BlockType has been passed in as value on extended model
	 * e.g. from CMS form submission.
	 *
	 * @param \ValidationResult $validationResult
	 * @return array|void
	 * @throws \ValidationException
	 */
	public function validate(ValidationResult $validationResult) {
		parent::validate($validationResult);
		if (!$blockType = $this->getBlockType()) {
			$validationResult->error(
				$this->fieldDecoration(
					self::BlockTypeFieldName,
					'missing',
					'{model} requires a block type',
					[
						'model' => $this()->i18n_singular_name()
					]
				)
			);
			throw new ValidationException($validationResult);
		}
	}

	/**
	 * If we don't already have an associated block then create and attach one (BlockType is the class selector). If we do
	 * have an associated block then check it is of correct class, if not re-create and relate it as correct Class. It will leave dangling
	 * blocks.
	 *
	 * We do this because initially only a single Block of constrained Class (type) can be created, however doing it this way enables us
	 * to keep templates clean via Blocks interface and possibly easier change later to more than one block or new block types.
	 */
	public function onBeforeWrite() {
		// just get the data for this extension when creating blocks.
		$blockData = $this()->extendedFieldData();

		// block type is either the class of the current block or the BlockTypeField name which is not in the DB but submitted by CMS form.
		$blockType = $this->getBlockType();

		/** @var Model $block */
		if (!$block = $this()->Block()) {
			// create a new related block
			$block = Model::create(
				$blockType,
				$blockData
			);
			$this()->BlockID = $block->write();
		} else {

			if ($block->ClassName != $blockType) {
				/** @var Block $newBlock */
				$block = $blockType::create($blockData);
				// force insert
				$this()->BlockID = $block->write(false, true);
				// this will leave dangling blocks
			} else {
				$block->update($blockData);
				$block->write();
			}
		}
	}

	/**
	 * Handles attaching images to the saved block after it has been written if images are allowed.
	 */
	public function onAfterWrite() {
		parent::onAfterWrite();

		/** @var Block $block */
		if ($block = $this()->Block()) {

			$request = Controller::curr()->getRequest();

			if ($imageIDs = ($request->postVar('Images')
				? $request->postVar('Images')['Files']
				: [])
			) {

				// TODO may need to fix so handles more than one image (later)
				if ($block->hasMethod('Image')) {

					$block->ImageID = reset($imageIDs);

					$block->write();
				}
			}
		}
	}
}