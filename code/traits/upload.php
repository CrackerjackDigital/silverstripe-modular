<?php

namespace Modular\Traits;

use Modular\Exceptions\Exception;
use UploadField;

/**
 * Trait adds functionality for dealing with upload fields
 *
 * @package Modular
 */
trait upload {
	abstract public function __invoke();

	/**
	 * Check if allowing existing files to be attached is enabled in config via config.allow_attach_existing.
	 *
	 * @return bool true if can attach an existing file from storage, false otherwise.
	 */
	public function allowAttachExisting() {
		return $this->config()->get('allow_attach_existing');
	}

	/**
	 * Return an upload field wrapped in a DisplayLogicWrapper as they all should be when using displaylogic.
	 *
	 * @param string $relationshipName optional override the static field_name
	 * @return \DisplayLogicWrapper
	 * @throws \Exception
	 */
	public function makeUploadField($relationshipName) {
		$wrapper = (new \DisplayLogicWrapper(
			$field = new \SortableUploadField(
				static::field_name()
			)
		))->setID($relationshipName)->setName($relationshipName);
		return $wrapper;
	}

	/**
	 * @param UploadField|\DisplayLogicWrapper $field
	 * @param string                           $allowedFilesConfigVar - allow you to switch config to check e.g. 'allowed_video_files'
	 * @throws Exception
	 */
	protected function configureUploadField($field, $allowedFilesConfigVar = 'allowed_files') {
		$fieldName = $field->getName();
		if ($field instanceof \DisplayLogicWrapper) {
			// drill down into wrapper to get actual UploadField
			$field = $field->fieldByName($fieldName);
		}

		list($minlength, $maxlength, $pattern) = $this->fieldConstraints($fieldName, [0, 0, '']);

		$field->setAllowedMaxFileNumber($maxlength ?: null);

		// don't allow existing media to be re-attached it's a has_one so would be messy
		$field->setCanAttachExisting($this->allowAttachExisting());
		$field->setFolderName($this->uploadFolderName());

		// try extension first, then model for config.allowed_files (or whatever configVarName is passed in).

		$extensions = $allowedFiles = $this->config()->get($allowedFilesConfigVar)
			?: $this()->config()->get($allowedFilesConfigVar);

		$categories = [];
		if (!is_array($allowedFiles)) {
			// could be comma separated list of categories
			$categories = explode(',', $allowedFiles);
			// get extensions from category so we always get a list of extensions for the CMS right title
			$allCategoryExtensions = \File::config()->get('app_categories') ?: [];

			foreach ($categories as $category) {

				if (isset($allCategoryExtensions[ $category ])) {
					$extensions = $allCategoryExtensions[ $category ];
				} else {
					$extensions = [$category];
				}

			}
		}
		if (is_array($allowedFiles)) {
			// was an array originally, so pass merged extensions
			$field->setAllowedExtensions($extensions);
		} elseif ($categories) {
			// array of categories to apply to setAllowedFileCategories as parameters
			call_user_func_array([$field, 'setAllowedFileCategories'], $categories);
		} elseif ($allowedFiles) {
			// not an array so a category e.g. 'video'
			$field->setAllowedFileCategories($allowedFiles);
		} else {
			throw new Exception("No $allowedFilesConfigVar configuration set");
		}

		$field->setRightTitle($this->fieldDecoration(
			$fieldName, 'Label', $field->Title(), [
				'extensions' => implode(', ', $extensions),
			]
		));

	}

	/**
	 * Returns the path relative to assets/ for the particular media type, e.g 'videos', 'audio' etc.
	 *
	 * Uses config.allowed_files if not overriden by UploadFolderName const.
	 */
	public function uploadFolderName() {
		// try extension first, then model
		return \Controller::join_links(
			$this->config()->get('base_upload_folder')
				?: $this()->config()->get('base_upload_folder'),
			$this->config()->get('upload_folder')
				?: ($this()->config()->get('upload_folder')
				?: static::DefaultUploadFolderName)
		);
	}

}