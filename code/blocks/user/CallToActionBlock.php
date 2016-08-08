<?php
namespace Modular\Blocks;

use ArrayList;
use Modular\InternalLinkField;
use Modular\ExternalLinkField;


class CallToAction extends Block {
	public function ResolvedLink() {
		if ($this->{InternalOrExternalLinkBehaviour::LinkTypeFieldName} == InternalLinkField::InternalLinkOption) {

			if ($target = $this->{InternalLinkField::RelationshipName}()) {
				return $target->Link();
            }
		} elseif ($this->{InternalOrExternalLinkBehaviour::LinkTypeFieldName} == ExternalLinkField::ExternalLinkOption) {
			return $this->{ExternalLinkField::ExternalLinkFieldName};
		}
	}

	/**
	 * Checks HasDisplayLocationField value to see if it is Both or InSidebar
	 *
	 * @return bool
	 */
	public function DisplayInSidebar() {
		return in_array(
			$this->{HasDisplayLocationField::DisplayLocationFieldName}, [
			HasDisplayLocationField::DisplayInBoth,
			HasDisplayLocationField::DisplayInSidebar
		]);
	}

	/**
	 * Checks HasDisplayLocationField value to see if it is Both or InContent
	 *
	 * @return bool
	 */
	public function DisplayInContent() {
		return in_array(
			$this->{HasDisplayLocationField::DisplayLocationFieldName}, [
			HasDisplayLocationField::DisplayInBoth,
			HasDisplayLocationField::DisplayInContent
		]);
	}
}