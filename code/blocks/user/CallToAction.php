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
				$link = $target->Link();
            } else {
				$link = '';
			}
		} elseif ($this->{InternalOrExternalLink::LinkTypeFieldName} == ExternalLink::ExternalLinkOption) {
			$link = $this->{ExternalLink::SingleFieldName};
		}
		return new \ArrayData([
			'Link' => $link
		]);
	}

}