<?php
namespace Modular;

use Extension;

/**
 * Since PHP 5.4 strip_tags doesn't strip XML style non-closing tags so need to handle
 * it explicitly in content, especially from PDF to text which has loads of <br>'s in.
 *
 * CERAStringFieldExtension
 */
class StringFieldExtension extends Extension {
	/**
	 * Returns extened fields value stripped of '<br>' and multiple newlines as well as via standard 'NoHTML' output filter.
	 * @return string
	 */
	public function Clean() {
		$content = str_replace(['<br>', "\n\n"], '', $this->owner->NoHTML());
		//remove characters outside ASCII
		// return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content);
		$find = array('â€œ', 'â€™', 'â€¦', 'â€”', 'â€“', 'â€˜', 'Ã©', 'Â', 'â€¢', 'Ëœ', 'â€', '<br />'); // en dash
		$replace = array('“', '’', '…', '—', '–', '‘', 'é', '', '•', '˜', '”', '&nbsp;');
		$content = str_replace($find, $replace, $content);
		return $content;
	}
}