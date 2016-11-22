<?php
/**
 * @version     1.0.0
 * @package     mobileid-helper
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     Licensed under the Apache License, Version 2.0 or later; see LICENSE.md
 * @author      Swisscom (Schweiz) AG
 */

/* Requirements */
/* PHP 5.3.x */
/* php_libcurl, php_libxml, OpenSSL */

require_once 'conf/configuration.php';
require_once 'helpers/mobileid.php';

class mobileid_helper extends mobileid {

	/* Configuration */
	protected $mobileIdConfig;

	/* AP configuration */
	protected $ap_cert;				// Certificate/key that is allowed to access the service
	protected $ap_cert_pwd;			// Optional Password if $ap_cert uses an encrypted private key

	/* Client certificate configuration */
	public $ca_ssl;   				// Location of Certificate Authority file which should be used to authenticate the identity of the remote peer
	public $ca_mid;					// Location of CA file which should be used during verifications

	public $UserLang;				// Language
	public $MobileUser;				// Phone number
	public $DataToBeSigned;			// Messsage

	/* Request messages  */
	public $mid_msg_de;				// German
	public $mid_msg_en;				// English
	public $mid_msg_fr;				// French
	public $mid_msg_it;				// Italian

	/* Allow message edition */
	protected $mid_msg_allowedit = false;

	/* Message provider */
	protected $mid_msg_service;

	/* Response error logs */
	public $response_error = false;	// Error, true or false
	public $response_error_type;	// Type of error, warning or error
	public $response_error_code;	// Error status code
	public $response_error_message;	// Error message

	/* proxy settings */
	protected $proxy_host;                 // proxy host as string without protocol prefix (http://)
	protected $proxy_port;                 // proxy port as number

	/**
	* mobileid_helper class
	*
	*/

	public function __construct($MobileUser, $UserLang = 'en', $DataToBeSigned = '') {

		/* Check the server requirements */
		if (!$this->checkRequirements()) {
			return false;
		}

		/* Set the configuration */
		if (!$this->setConfiguration()) {
			return false;
		}

		/* Set the application parameters */
		if (!$this->setParameters($MobileUser, $UserLang, $DataToBeSigned)) {
			return false;
		}
		$options = null;
		if (isSet($this->proxy_host)) {
			$options = array(
				'proxy_host' => $this->proxy_host,
				'proxy_port' => $this->proxy_port
			);
		};
		if (isSet($this->ap_cert_pwd)) $options['passphrase'] = $this->ap_cert_pwd;

		parent::__construct($this->ap_id, $this->ap_pwd, $this->ap_cert, $this->ca_ssl,$options);
	}

	/**
	* Mobileid check the requirements of the web server
	*
	* @return 	boolean	true on success, false on failure
	*/

	private function checkRequirements() {

		if (!class_exists('SOAPClient')) {
			$this->setError('PHP <soap> library is not installed!');
			return false;
		}

		return true;
	}

	/**
	* Mobileid set the default configuration
	*
	* @return 	boolean	true on success, false on failure
	*/

	private function setConfiguration() {

		/* New instance of the mobileID configuration class */
		$this->mobileIdConfig = new mobileIdConfig();

		/* Check if the configuraiton is correct */
		if (!$this->checkConfiguration()) {
			return false;
		}

		/* Set the default values */

		/* AP configuration */
		$this->ap_id   = $this->mobileIdConfig->ap_id;
		$this->ap_pwd  = $this->mobileIdConfig->ap_pwd;
		$this->ap_cert = $this->mobileIdConfig->ap_cert;
		$this->ap_cert_pwd = $this->mobileIdConfig->ap_cert_pwd;

		/* Client certificate configuration */
		$this->ca_ssl  = $this->mobileIdConfig->ca_ssl;
		$this->ca_mid  = $this->mobileIdConfig->ca_mid;

		/* Request messages */
		if (strlen($this->mobileIdConfig->mid_msg_de)) {
			$this->mid_msg_de = $this->mobileIdConfig->mid_msg_de;
		}

		if (strlen($this->mobileIdConfig->mid_msg_en)) {
			$this->mid_msg_en = $this->mobileIdConfig->mid_msg_en;
		}

		if (strlen($this->mobileIdConfig->mid_msg_fr)) {
			$this->mid_msg_fr = $this->mobileIdConfig->mid_msg_fr;
		}

		if (strlen($this->mobileIdConfig->mid_msg_it)) {
			$this->mid_msg_it = $this->mobileIdConfig->mid_msg_it;
		}

		if ($this->mobileIdConfig->mid_msg_allowedit) {
			$this->mid_msg_allowedit = $this->mobileIdConfig->mid_msg_allowedit;
		}

		if (strlen($this->mobileIdConfig->mid_msg_service)) {
			$this->mid_msg_service = $this->mobileIdConfig->mid_msg_service;
		}

		/* get proxy settings */
		$this->proxy_host   = $this->mobileIdConfig->proxy_host;
		$this->proxy_port  = $this->mobileIdConfig->proxy_port;
		if ($this->proxy_host<>'') {
		  /* verify the proxy settings */
  		try {
        $waitTimeoutInSeconds = 1;
        if($fp = fsockopen($this->proxy_host,$this->proxy_port,$errCode,$errStr,$waitTimeoutInSeconds)){
           // It worked
        } else {
           // It didn't work
          $this->setError('Proxy (' . $this->proxy_host . ') not reachable:' . $errStr);
          return false;
        }
        fclose($fp);
      }
      catch (Exception $e) {
          $this->setError('Proxy (' . $this->proxy_host . ') not reachable!');
          return false;
      }
    }
		return true;
	}

	/**
	* Mobileid check the configuration
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkConfiguration() {

		if (!strlen($this->mobileIdConfig->ap_id)) {
			$this->setError('No AP ID configured!');
			return false;
		}

		if (!strlen($this->mobileIdConfig->ap_pwd)) {
			$this->setError('No AP password configured!');
			return false;
		}

		if (!strlen($this->mobileIdConfig->ap_cert)) {
			$this->setError('No AP certificate/key configured!');
			return false;
		}

		if (!strlen($this->mobileIdConfig->ca_ssl)) {
			$this->setError('No CA SSL file configured!');
			return false;
		}

		if (!strlen($this->mobileIdConfig->ca_mid)) {
			$this->setError('No CA file configured!');
			return false;
		}

		if (!strlen($this->mobileIdConfig->mid_msg_service)) {
			$this->setError('No Service Provider configured!');
			return false;
		}

		return true;
	}

	/**
	* Mobileid check if the client could edit his message or not
	*
	* @return 	boolean	true on success, false on failure
	*/

	public static function getMsgAllowEdit() {

		/* New instance of the mobileID configuration class */
		$mobileIdConfig = new mobileIdConfig();

		return $mobileIdConfig->mid_msg_allowedit;
	}

	/**
	* Mobileid set the parameters
	*
	* #params	string phone_number
	* #params	string language_code
	* #params	string message
	* @return 	boolean	true on success, false on failure
	*/
	public function setParameters($MobileUser, $UserLang = 'en', $DataToBeSigned = '') {

		if (!strlen($MobileUser)) {
			$this->setError('No mobile user defined!');
			return false;
		}

		/* Set the parameters */
		$this->UserLang   = $UserLang;
		$this->MobileUser = $MobileUser;

		/* Force the default message when edition is not allowed */
		if (!strlen($DataToBeSigned) || !$this->mid_msg_allowedit) {
			if (!$this->setDataToBeSigned()) {
				return false;
			}
		} else {
			$this->DataToBeSigned = $DataToBeSigned;
		}
		$this->DataToBeSigned = $this->mid_msg_service . ': ' . $this->DataToBeSigned;

		if (!$this->checkMobileUser()) {
			return false;
		}

		return true;
	}

	/**
	* Mobileid set the parameters
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function setDataToBeSigned() {

		if (!$this->checkDataToBeSigned()) {
			return false;
		}

		switch($this->UserLang) {
		case 'de':
			$this->DataToBeSigned = $this->mid_msg_de;
			break;

		case 'en':
			$this->DataToBeSigned = $this->mid_msg_en;
			break;

		case 'fr':
			$this->DataToBeSigned = $this->mid_msg_fr;
			break;

		case 'it':
			$this->DataToBeSigned = $this->mid_msg_it;
			break;
		}

		$this->DataToBeSigned = str_replace('#TRANSID#', $this->generateTransactionID(), $this->DataToBeSigned);

		return true;
	}

	/**
	* Mobileid set the parameters
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkDataToBeSigned() {

		if (!strlen($this->UserLang)) {
			$this->setError('No user language defined!');
			return false;
		}

		if (!strlen($this->mid_msg_de)) {
			$this->setError('No german data to be signed defined!');
			return false;
		}

		if (!strlen($this->mid_msg_en)) {
			$this->setError('No english data to be signed defined!');
			return false;
		}

		if (!strlen($this->mid_msg_fr)) {
			$this->setError('No french data to be signed defined!');
			return false;
		}

		if (!strlen($this->mid_msg_it)) {
			$this->setError('No italian data to be signed defined!');
			return false;
		}

		return true;
	}

	/**
	* Mobileid Get the value of the default message
	*
	* @params	string lang
	* @return 	string message on success, false on failure
	*/

	public static function getDefaultMsg($lang = 'en') {

		if (strlen($lang) != 2) {
			return false;
		}

		/* set the language variable */
		$lang_var = 'mid_msg_'.$lang;

		/* New instance of the mobileID configuration class */
		$mobileIdConfig = new mobileIdConfig();

		return $mobileIdConfig->$lang_var;
	}

	/**
	* Mobileid Get the service provider
	*
	* @return 	string message on success, false on failure
	*/

	public static function getServiceProvider() {

		/* New instance of the mobileID configuration class */
		$mobileIdConfig = new mobileIdConfig();

		if (!strlen($mobileIdConfig->mid_msg_service)) {
			$mobileIdConfig->mid_msg_service = 'No service provider defined!';
		}

		return $mobileIdConfig->mid_msg_service;
	}

	/**
	* Mobileid check the mobile phone according to Swisscom rules
	*
	* @return 	boolean	true
	*/
	private function checkMobileUser() {

		/* format the mobile user to ensure international format with specified prefix (+ or 00) and no spaces */
		$this->MobileUser = $this->getMSISDNfrom($this->MobileUser);

		return true;
	}

    /**
     * profileQuery request
     * #params     string    phone number
     * @return     boolean   true on success, false on failure
     */
    public function profileQuery($phoneNumber = '') {

		if (strlen($phoneNumber)) {
			$this->MobileUser = $phoneNumber;
		}

		return parent::profileQuery($this->MobileUser);
	}

    /**
     * signature request
     * #params     string    phone number
     * #params     string    message
     * #params     string    user language
     * #params     string    location of CA file which should be used during verifications
     * @return     boolean   true on success, false on failure
     */
    public function signature($phoneNumber = '', $message = '', $userlang = '', $cafile = '') {

		if (strlen($phoneNumber)) {
			$this->MobileUser = $phoneNumber;
		}

		if (strlen($message)) {
			$this->DataToBeSigned = $message;
		}

		if (strlen($userlang)) {
			$this->userlang = $userlang;
		}

		if (strlen($cafile)) {
			$this->ca_mid = $cafile;
		}

		return parent::signature($this->MobileUser, $this->DataToBeSigned, $this->UserLang, $this->ca_mid);
	}

    /**
     * receipt request
     * #params     string    phone number
     * #params     string    MSSP TransID
     * #params     string    message
     * #params     string    user language
     * #params     string    optional public certificate of the mobile user to encrypt the message
     * @return     boolean   true on success, false on failure
     */
    public function receipt($phoneNumber = '', $transID = '', $message = '', $userlang = '', $publicKey = null) {

		if (!strlen($this->getLastMSSPtransID())) {
			return false;
		}

		if (!strlen($this->mid_certificate)) {
			return false;
		}

		if (strlen($phoneNumber)) {
			$this->MobileUser = $phoneNumber;
		}

		if (strlen($message)) {
			$this->DataToBeSigned = $message;
		}

		if (strlen($userlang)) {
			$this->userlang = $userlang;
		}

		return parent::receipt($this->MobileUser, $this->getLastMSSPtransID(), $this->DataToBeSigned, $this->UserLang, $this->mid_certificate);
	}

    /* A helper function for generating a unique Transaction ID string.
     *
     * @return string  Transaction ID with a length of 6
     */
    private function generateTransactionID() {

        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxlen = strlen($pattern) - 1;

        $id = '';

        for ($i = 1; $i <= 6; $i++) {
            $id .= $pattern{mt_rand(0, $maxlen)};
        }

        return $id;
    }

	/**
	* Mobileid set the error of the soap request
	*
	* @return     boolean   true on success, false on failure
	*/
	public function setResponseError() {

		if (!$this->setResponseErrorCode()) {
			return false;
		}

		return $this->setError($this->statusdetail);
	}

	/**
	* Mobileid set the status code
	*
	* @return     boolean   true on success, false on failure
	*/
	private function setResponseErrorCode() {

		if (!strlen($this->statuscode)) {
			$this->setError('No status code found!');
			return false;
		}

		if (!strstr($this->statuscode, '_')) {
			$this->setError('Status code is invalid!');
			return false;
		}

		$array_tmp = explode('_', $this->statuscode);

		$this->response_error_code = $array_tmp[1];

		if (!strlen($this->response_error_code)) {
			$this->setError('Can not get the response error code!');
			return false;
		}

		return true;
	}

	/**
	* Mobileid set the errors
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function setError($msg, $error_type = 'error') {

		if (!strlen($msg)) {
			return false;
		}

		$this->response_error          = true;
		$this->response_error_message  = $msg;
		$this->response_error_type     = $error_type;

		if ($this->response_error_code == '501' || $this->response_error_code == '503' ) {
			$this->response_error_type = 'warning';
		}

		$warning_code = array("105", "208", "209", "401", "402", "403", "404", "406", "422");

		if (in_array($this->response_error_code, $warning_code)) {
			$this->response_error_type = 'warning';
		}

		return true;
	}

	/**
	* Mobileid clean up the temporaries files
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function setRequestSuccess() {

		$this->response_error = false;
		$this->response_error_type = false;

		return true;
	}

	/**
    * Ensures international format with specified prefix (+ or 00) and no spaces
	*
	* @return	string	uid
    */
    private function getMSISDNfrom($uid, $prefix = '+') {

        $uid = preg_replace('/\s+/', '', $uid);     	// Remove all whitespaces
        $uid = str_replace('+', '00', $uid);            // Replace all + with 00
        $uid = preg_replace('/\D/', '', $uid);          // Remove all non-digits

        if (strlen($uid) > 5) {                         // Still something here

			if ($uid[0] == '0' && $uid[1] != '0') {     // Add implicit 41 if starting with one 0
                $uid = '41' . substr($uid, 1);
            }

            $uid = ltrim($uid, '0');                    // Remove all leading 0
        }

        $uid = $prefix . $uid;                          // Add the defined prefix

        return $uid;
    }
}
?>
