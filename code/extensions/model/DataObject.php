<?php
namespace Modular\Extensions;

use Modular\Traits\owned;

class DataObjectExtension extends \DataExtension {
	use owned;

	public function NiceName() {
		return $this()->i18n_singular_name() ?: $this()->class;
	}
}