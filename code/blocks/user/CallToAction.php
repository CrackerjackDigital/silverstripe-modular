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
			return $this->{ExternalLink::SingleFieldName};
		}
	}

}