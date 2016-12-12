<?php
namespace Modular\Workflows;
/**
 * Adds permissions checks and a mechanism to allow all related models of the extended models type to be
 * published when the 'owner' is published.
 *
 * @package Modular\Workflows
 */
class ModelExtension extends \Modular\ModelExtension {
	const AuthorGroup    = 'content-authors';
	const PublisherGroup = 'content-publishers';

	const ActionEdit = 'edit';
	const ActionView = 'view';

	private static $view_groups = [
		self::AuthorGroup    => true,
		self::PublisherGroup => true,
	];

	private static $edit_groups = [
		self::AuthorGroup    => true,
		self::PublisherGroup => false,
	];

	public function canDoIt($what, $member = null) {
		$member = $member
			? (is_numeric($member) ? \Member::get()->byID($member) : $member)
			: \Member::currentUser();

		if ($member) {
			$groups = array_keys(array_filter($this->config()->get("{$what}_groups")));

			return $member->inGroups($groups) || \Permission::check('ADMIN', 'any', $member);
		}
	}

	public function canCreate($member = null) {
		return $this->canDoIt(self::ActionEdit, $member);
	}

	public function canView($member = null) {
		return $this->canDoIt(self::ActionView, $member);
	}

	public function canViewVersioned($member = null) {
		return $this->canDoIt(self::ActionView, $member);
	}

	public function canEdit($member) {
		return $this->canDoIt(self::ActionEdit, $member);
	}

	public function canDelete($member) {
		return $this->canDoIt(self::ActionEdit, $member);
	}

	/*
	 * TODO SLW 2016-12-02 This could be a better way to do it, needs to be tested though, then can remove
	 * onAfterPublish handlers from extensions like 'HasBlocks' and 'HasTags'
	 *
	 * Deep publish owned blocks when the owner is published.
	 *
	public function onAfterPublish() {
		if ($relateds = $this->providePublishableRelatedModels()) {
			/** @var \DataObject|\Versioned $related *//*
			foreach ($relateds as $related) {
				if ($related->hasExtension('Versioned')) {
					$related->publish('Stage', 'Live');
					// now ask the block to publish it's own blocks.
					$related->extend('onAfterPublish');
				}
			}
		}
	}

	/**
	 * Call extensions on the model to ask them to return a list of publishable related models,
	 * e.g. on HasBlocks extension should return a list of blocks related to the model, HasTags a list of tags etc
	 *
	 * @return \ArrayList
	 *
	protected function providePublishableRelatedModels() {
		$lists = $this()->extend('related');
		$related = new \ArrayList();
		foreach ($lists as $list) {
			$related->merge($list);
		}
		return $related;
	}
	*/
}