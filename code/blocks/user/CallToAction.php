<?php
namespace Modular\Blocks;

use Modular\Behaviours\InternalOrExternalLink;
use Modular\Fields\InternalLink;
use Modular\Fields\ExternalLink;
use Modular\Fields\DisplayLocation;

class CallToAction extends Block {
	public function ResolvedLink() {
		if ($this->{InternalOrExternalLink::LinkTypeFieldName} == InternalLink::InternalLinkOption) {

			if ($target = $this->{InternalLink::RelationshipName}()) {
				return $target->Link();
            }
		} elseif ($this->{InternalOrExternalLink::LinkTypeFieldName} == ExternalLink::ExternalLinkOption) {
			return $this->{ExternalLink::ExternalLinkFieldName};
		}
	}

	/**
	 * Checks HasDisplayLocationField value to see if it is Both or InSidebar
	 *
	 * @return bool
	 */
	public function DisplayInSidebar() {
		return in_array(
			$this->{DisplayLocation::DisplayLocationFieldName}, [
			DisplayLocation::DisplayInBoth,
			DisplayLocation::DisplayInSidebar
		]);
	}

	/**
	 * Checks HasDisplayLocationField value to see if it is Both or InContent
	 *
	 * @return bool
	 */
	public function DisplayInContent() {
		return in_array(
			$this->{DisplayLocation::DisplayLocationFieldName}, [
			DisplayLocation::DisplayInBoth,
			DisplayLocation::DisplayInContent
		]);
	}
}