<?php
namespace Modular\GridList\Interfaces;
/**
 * Interface TempleDataProvider for extensions which provide additional data to be returned to the template by the 'GridList' method.
 *
 * @package Modular\GridList\Interfaces
 */
interface TempleDataProvider {
	/**
	 * Return an array of additional data to return to the template and make available via the $GridList template variable.
	 * @param array $existingData already being provided, is not altered but is for reference, calculations etc
	 * @return array
	 */
	public function provideGridListTemplateData($existingData = []);
}