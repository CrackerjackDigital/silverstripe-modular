<?php
namespace Modular\Workflows;

use Modular\Controller;
use Modular\Exceptions\Exception;
use Modular\ModelExtension;

class WorkflowExtension extends ModelExtension {
	const AuthorGroup = 'content-authors';

	public function canDoIt($member = null) {
		$member = $member ?
			(is_numeric($member) ? \Member::get()->byID($member) : $member)
			: \Member::currentUser();

		if ($member) {
			return $member->inGroup(self::AuthorGroup) || \Permission::check('ADMIN', 'any', $member);
		}
	}

	public function canCreate($member = null) {
		return $this->canDoIt($member);
	}

	public function canView($member = null) {
		return $this->canDoIt($member);
	}

	public function canEdit($member) {
		return $this->canDoIt($member);
	}

	public function canDelete($member) {
		return $this->canDoIt($member);
	}

}