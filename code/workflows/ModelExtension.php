<?php
namespace Modular\Workflows;

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
		$member = $member ?
			(is_numeric($member) ? \Member::get()->byID($member) : $member)
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

	public function canEdit($member) {
		return $this->canDoIt(self::ActionEdit, $member);
	}

	public function canDelete($member) {
		return $this->canDoIt(self::ActionEdit, $member);
	}

	public function canViewVersioned($member = null) {
		return $this->canDoIt(self::ActionEdit, $member);
	}

}