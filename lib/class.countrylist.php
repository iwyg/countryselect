<?php
/*
vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4
*/

/**
 * @package Lang
 * @author thomas appel <mail@thomas-appel.com>

 * Displays <a href="http://opensource.org/licenses/gpl-3.0.html">GNU Public License</a>
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 */

class Countrylist
{

	//$gateway->setopt('URL', 'http://spreadsheets.google.com/feeds/list/p9pdwsai2hDMsLkXsoM05KQ/default/public/values');
	//$gateway->setopt('URL', 'https://spreadsheets.google.com/feeds/list/p9pdwsai2hDMsLkXsoM05KQ/default/public/basic?alt=json-in-script&callback=myFunc');
	//$response = preg_replace('/(^\/+\s+?\w+(\s+?|\w+|\(+)+|(\);$))/m', '', $response); // google geolocation spreadsheed
	public static function get()
	{
		$gateway = new Gateway;
		$feed = $gateway->init();
		$gateway->setopt('URL', 'http://opendata.socrata.com/api/views/mnkm-8ram/rows.xml');

		$response = $gateway->exec();

		if ($response === false) {
			$parsed = simplexml_load_file(EXTENSIONS . '/countryselect/assets/countries.xml');
		} else {
			$parsed = new SimpleXMLElement($response);
		}
		
		$ccodes = array();
		$row = $parsed->row->row;

		foreach($row as $tablerow) {
			$ccodes[(string)$tablerow->alpha_2_code] = (string)$tablerow->country;
		}
		return $ccodes;
	}
}
