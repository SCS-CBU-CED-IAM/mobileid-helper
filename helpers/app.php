<?php
/**
 * @version     1.0.0
 * @package     mobileid-helper
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */
 
/* Requirements */
/* PHP 5.3.x */
/* php_curl, php_ssl, php_dom */

//require_once 'conf/configuration.php';

class mobileid_app {

	public $defines;
	public $language;
	public $language_code;
	//public $mobileIdConfig;

	public function __construct() {
		
		$this->language = $_GET['lang'];
		
		if (!strlen($this->language)) {
			$this->language      = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
			$this->language_code = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
		}

		switch($this->language) {
		case 'de':
			$this->language_code = 'de-DE';
			break;

		case 'fr':
			$this->language_code = 'fr-FR';
			break;

		case 'it':
			$this->language_code = 'it-IT';
			break;

		case 'en':
		default:
			$this->language_code = 'en-GB';
			break;
		}

		$this->loadLanguage();
	}

	private function loadLanguage() {
		
		if (!file_exists(__ROOT__.'/language/'.$this->language_code.'/'.$this->language_code.'.ini')) {
			$this->language      = 'en';
			$this->language_code = 'en-GB';
		}
		
		$filename = __ROOT__.'/language/'.$this->language_code.'/'.$this->language_code.'.ini';
		
		$this->defines = @parse_ini_file($filename);
	}

	public function getText($define) {
		
		if (!strstr($this->defines[$define], '<a href')) {
			$text = htmlentities($this->defines[$define], null, 'utf-8');
			
			if (!strlen($text)) {
				return htmlentities($this->defines["APP_ERROR_DEFAULT"], null, 'utf-8');
			}

			return $text;
		}
		
		return $this->defines[$define];
	}
}
?>
