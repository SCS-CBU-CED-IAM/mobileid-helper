<?php
/**
 * @version     1.0.0
 * @package     mobileid
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Swisscom
 */
 
/* Requirements */
/* PHP 5.3.x */
/* php_libcurl, php_libxml, OpenSSL */

require_once 'conf/configuration.php';

class mobileid {
	
	/* Configuration */
	protected $mobileIdConfig;

	/* Client certificate configuration */
	protected $cert_ca;					// Bag file with the server/client issuing and root certifiates
	protected $cert_key;				// The related key of the certificate
	protected $cert_file;				// The certificate that is allowed to access the service
	
	/* AP configuration */
	protected $ap_id;					// AP UserID provided by Swisscom
	protected $ap_pwd;					// AP Password must be present but is not validated
	protected $ap_instant;				// AP instant
	protected $ap_trans_id;				// AP transaction ID
	
	/* OCSP configuration */
	protected $ocsp_cert;				// OCSP information of the signers certificate
	protected $ocsp_url;				// OCSP Url
	
	/* Soap configuration */
	protected $ws_url;					// WS Url
	protected $ws_action;				// WS action
	
	/* parameters */
	protected $TimeOutWSRequest  = 90;	// Timeout WS request
	protected $TimeOutMIDRequest = 80;	// Timeout MobileID request
	
	protected $UserLang;				// Language
	protected $MobileUser;				// Phone number
	protected $DataToBeSigned;			// Messsage
	
	/* Request messages  */
	public $mid_msg_de;					// German
	public $mid_msg_en;					// English
	public $mid_msg_fr;					// French
	public $mid_msg_it;					// Italian

	/* Soap request */
	protected $soap_request;			// Soap request
	
	/* Response */
	protected $soap_response_xml;			// XML response buffer
	protected $soap_response_status;		// Response Soap request status
	protected $soap_response_simple_xml;	// Response in SimpleXML Object
	protected $soap_response_pkcs7;			// Signed signature, PKCS7 format
	
	/* Curl response */
	protected $curl_errno;				// Curl error code
	protected $curl_error;				// Curl error message
	
	/* Files manipulations */
	protected $tmp_dir;
	protected $file_sig;
	protected $file_sig_msg;
	protected $file_sig_cert;
	protected $file_sig_cert_check;

	/* Response datas */
	public $data_response_message;
	public $data_response_trans_id;
	public $data_response_mobile_user;
	public $data_response_certificate;
	public $data_response_certificate_status;

	/* Response logs */
	public $response_error = false;		 	// Error
	public $response_error_type = false;	// Type of error, warning or error
	public $response_mss_status_code;		// MSS Status code
	public $response_soap_fault_subcode;	// Soap fault subcode
	public $response_status_message;		// Status Message

	/**
	* Mobileid class
	*
	*/

	public function __construct($MobileUser, $UserLang = 'en', $DataToBeSigned = '') {

		/* Check the server requirements */
		if (!$this->checkRequirements()) {
			return;
		}

		/* Set the configuration */
		if (!$this->setConfiguration()) {
			return;
		}
		
		/* Set the application parameters */
		if (!$this->setParameters($MobileUser, $UserLang, $DataToBeSigned)) {
			return;
		}
		
		/* Set the AP transaction ID and instant */
		$this->setApTransaction();
		
		/* Set the temporary directory for files manipulations */
		$this->setTempDir();
	}

	/**
	* Mobileid check the requirements of the web server
	*
	* @return 	boolean	true on success, false on failure
	*/
	
	private function checkRequirements() {
		
		if (!function_exists('curl_init')) {
			$this->setError('PHP <libcurl> library is not installed!');
			return;			
		}
		
		if (!function_exists('xml_parse')) {
			$this->setError('PHP <libxml> library is not installed!');
			return;			
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
			return;
		}
		
		/* Set the default values */
		$this->cert_ca     = $this->mobileIdConfig->cert_ca;
		$this->cert_file   = $this->mobileIdConfig->cert_file;
		$this->cert_key    = $this->mobileIdConfig->cert_key;
		$this->ap_id       = $this->mobileIdConfig->ap_id;
		$this->ap_pwd      = $this->mobileIdConfig->ap_pwd;
		$this->ocsp_cert   = $this->mobileIdConfig->ocsp_cert;
		$this->ws_url      = $this->mobileIdConfig->ws_url;
		$this->ws_action   = $this->mobileIdConfig->ws_action;
		$this->support_url = $this->mobileIdConfig->support_url;

		if ($this->mobileIdConfig->TimeOutWSRequest) {
			$this->TimeOutWSRequest = (int)$this->mobileIdConfig->TimeOutWSRequest;
		}

		if ($this->mobileIdConfig->TimeOutMIDRequest) {
			$this->TimeOutMIDRequest = (int)$this->mobileIdConfig->TimeOutMIDRequest;
		}

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
		
		return true;
	}

	/**
	* Mobileid check the configuration
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkConfiguration() {
		
		if (!strlen($this->mobileIdConfig->cert_ca)) {
			$this->setError('No CA Certificate configured!');
			return;
		}

		if (!strlen($this->mobileIdConfig->cert_file)) {
			$this->setError('No Client Certificate configured!');
			return;
		}

		if (!strlen($this->mobileIdConfig->cert_key)) {
			$this->setError('No Client Certificate key configured!');
			return;
		}

		if (!strlen($this->mobileIdConfig->ap_id)) {
			$this->setError('No AP ID configured!');
			return;
		}

		if (!strlen($this->mobileIdConfig->ap_pwd)) {
			$this->setError('No AP Password configured!');
			return;
		}

		if (!strlen($this->mobileIdConfig->ocsp_cert)) {
			$this->setError('No OCSP Certificate configured!');
			return;
		}

		if (!strlen($this->mobileIdConfig->ws_url)) {
			$this->setError('No WS Url configured!');
			return;
		}

		if (!strlen($this->mobileIdConfig->ws_action)) {
			$this->setError('No WS Action configured!');
			return;
		}
		
		return true;
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
			return;
		}

		/* Set the parameters */
		$this->UserLang   = $UserLang;
		$this->MobileUser = $MobileUser;

		if (!strlen($DataToBeSigned)) {
			if (!$this->setDataToBeSigned()) {
				return;				
			}
		} else {
			$this->DataToBeSigned = $DataToBeSigned;
		}

		if (!$this->checkMobileUser()) {
			return;
		}

		if (!$this->setMobileUser()) {
			return;
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
			return;				
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
			return;
		}
		
		if (!strlen($this->mid_msg_de)) {
			$this->setError('No german data to be signed defined!');
			return;
		}

		if (!strlen($this->mid_msg_en)) {
			$this->setError('No english data to be signed defined!');
			return;
		}

		if (!strlen($this->mid_msg_fr)) {
			$this->setError('No french data to be signed defined!');
			return;
		}

		if (!strlen($this->mid_msg_it)) {
			$this->setError('No italian data to be signed defined!');
			return;
		}
		
		return true;
	}

	/**
	* Mobileid check the mobile phone according to Swisscom rules
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkMobileUser() {
		
		if (strlen($this->MobileUser) < 10) {
			$this->setError('Not a valid mobile user!');
			return;
		}

		if ($this->MobileUser[0] != '0' && $this->MobileUser[0] != '+') {
			$this->setError('Not a valid mobile user!');
			return;
		}

		if ($this->MobileUser[0].$this->MobileUser[1] == '00' && strlen($this->MobileUser) < 12) {
			$this->setError('Not a valid mobile user!');
			return;
		}

		if ($this->MobileUser[0] == '+' && strlen($this->MobileUser) < 12) {
			$this->setError('Not a valid mobile user!');
			return;
		}

		if ($this->MobileUser[0] == '0' && $this->MobileUser[1] != '0' && strlen($this->MobileUser) < 10) {
			$this->setError('Not a valid mobile user!');
			return;
		}

		return true;
	}

	/**
	* Mobileid check the mobile phone according to Swisscom rules
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function setMobileUser() {
		
		if ($this->MobileUser[0].$this->MobileUser[1] == '00') {
			$this->MobileUser = str_replace('00', '+', $this->MobileUser);
		}
		
		if ($this->MobileUser[0] == '0' && $this->MobileUser[1] != '0') {
			$this->MobileUser[0] = '';
			$this->MobileUser = '+41'.trim($this->MobileUser);
		}
		
		return true;
	}

	/**
	* Mobileid set the AP transaction ID and instant
	*
	* @return 	boolean	true on success, false on failure
	*/
	
	private function setApTransaction() {

		/* Set the AP transaction ID */
		$this->ap_trans_id = 'AP.CHECK.'.rand(89999, 10000).'.'.rand(8999, 1000);
		
		/* Set the AP instant */
		$timestamp = time();
		$this->ap_instant = date('Y-m-d', $timestamp).'T'.date('H:i:s', $timestamp);		
	}

	/**
	* Mobileid set the temporary directory for files manipulations
	*
	* @return 	boolean	true on success, false on failure
	*/
	
	private function setTempDir() {
		$this->tmp_dir = sys_get_temp_dir();		
	}
	
	/**
	* Mobileid send the request
	*
	* @return 	boolean	true on success, false on failure
	*/
	public function sendRequest() {

		if ($this->response_error) {
			return;
		}

		/* Set the soap XML request */
		$this->setSoapRequest();

		/* Initialize curl session */
		/* NOTE: no error handling done at the moment */

		$ch = curl_init();

		/* Set all session options */

		/* URI */
		curl_setopt($ch, CURLOPT_URL, $this->ws_url);
		
		/* Avoid cache */
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);						// No cache
		
		/* SSL Verification options */
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);					// SSL certificate verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);					// SSL certificate host name verification

		/* SSL Certificate and keyfile */
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);						// Use version 3 of SSL
		curl_setopt($ch, CURLOPT_CAINFO, $this->cert_ca);				// Set the issued CA root certificates
		curl_setopt($ch, CURLOPT_SSLCERT, $this->cert_file);			// Set the client certificate file
		curl_setopt($ch, CURLOPT_SSLKEY, $this->cert_key);				// Set the private key file for client authentication
		//curl_setopt($ch, CURLOPT_SSLKEYPASSWD, '');					// No password yet

		/* HTTP protocol and stream options */
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);					// Allow redirects
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->TimeOutMIDRequest);	// Times out
		curl_setopt($ch, CURLOPT_POST, 1); 								// Set POST method
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 					// Return into a variable. This is IMPORTANT!

		/* add POST body */
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->soap_request); 		// Add POST fields (Soap envelop)

		/* Set custom headers */
		$headers = array('Content-Type: text/xml', 'SOAPAction: "'.$this->ws_action.'"', 'Content-Length: '.strlen($this->soap_request) );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		/* run the whole process. returns the requested XML structure on success, FALSE on failure */
		$this->soap_response_xml = curl_exec($ch);

		/* Get the status of the response request */
		$this->soap_response_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($this->soap_response_xml === false) {
			$this->curl_errno = curl_errno($ch);
			$this->curl_error = curl_error($ch);

			$this->checkCurlError();

			return;
		}

		/* Close curl session */
		curl_close($ch);

		/* Test the response */
		if (!$this->checkResponseRequest()) {
			return;
		}

		return true;
	}

	/**
	* Mobileid set the soap request
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkCurlError() {
		
		// Test if we had a timeout
		if ($this->curl_errno == '28') {
			$this->response_soap_fault_subcode = '208';
		}
		
		$this->setError($this->curl_error);
		
		return;
	}

	/**
	* Mobileid set the soap request
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function setSoapRequest() {

		$this->soap_request = '<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
			xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
			soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" 
			xmlns:soapenv="http://www.w3.org/2003/05/soap-envelope" 
			xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
		  <soapenv:Body>
			<MSS_Signature xmlns="">
			  <mss:MSS_SignatureReq MinorVersion="1" MajorVersion="1" xmlns:mss="http://uri.etsi.org/TS102204/v1.1.2#" MessagingMode="synch" xmlns:fi="http://mss.ficom.fi/TS102204/v1.0.0#">
				<mss:AP_Info AP_PWD="'.$this->ap_pwd.'" AP_TransID="'.$this->ap_trans_id.'" Instant="'.$this->ap_instant.'" AP_ID="'.$this->ap_id.'" />
				<mss:MSSP_Info>
				  <mss:MSSP_ID/>
				</mss:MSSP_Info>
				<mss:MobileUser>
				  <mss:MSISDN>'.$this->MobileUser.'</mss:MSISDN>
				</mss:MobileUser>
				<mss:DataToBeSigned MimeType="text/plain" Encoding="UTF-8">'.$this->DataToBeSigned.'</mss:DataToBeSigned>
				<mss:SignatureProfile>
				  <mss:mssURI>http://mid.swisscom.ch/MID/v1/AuthProfile1</mss:mssURI>
				</mss:SignatureProfile>
				<mss:AdditionalServices>
				  <mss:Service>
					<mss:Description>
					  <mss:mssURI>http://uri.etsi.org/TS102204/v1.1.2#validate</mss:mssURI>
					</mss:Description>
				  </mss:Service>
				  <mss:Service>
					<mss:Description>
					  <mss:mssURI>http://mss.ficom.fi/TS102204/v1.0.0#userLang</mss:mssURI>
					</mss:Description>
					<fi:UserLang>'.$this->UserLang.'</fi:UserLang>
				  </mss:Service>
				</mss:AdditionalServices>
				<mss:MSS_Format>
				  <mss:mssURI>http://uri.etsi.org/TS102204/v1.1.2#PKCS7</mss:mssURI>
				</mss:MSS_Format>
				<mss:TimeOut>'.$this->TimeOutWSRequest.'</mss:TimeOut>
			  </mss:MSS_SignatureReq>
			</MSS_Signature>
		  </soapenv:Body>
		</soapenv:Envelope>';
		
		return true;
	}

	/**
	* Mobileid check the response request
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkResponseRequest() {
		
		/* Check the response request, does we have a valid XML buffer */
		if (!$this->checkResponseRequestXmlBuffer()) {
			return;
		}

		/* Clean up the XML soap response to parse it using SimpleXML */
		if (!$this->setXmlResponseObject()) {
			return;
		}

		/* Soap request response is an error */
		if (!$this->isResponseRequestSuccess()) {
			$this->setResponseError();
			return;			
		}
		
		/* Set the response Datas */
		if (!$this->setResponseData()) {
			return;
		}

		/* Get the encoded signature */
		if (!$this->getEncodedSignature()) {
			return;
		}

		/* Extract the signers certificate */
		if (!$this->extractSignersCertificate()) {
			return;
		}

		/* Verify the revocation status over ocsp */
		if (!$this->revocationStatusVerify()) {
			return;
		}
		
		$this->cleanUpTempFiles();
		$this->setRequestSuccess();
		
		return true;
	}

	/**
	* Mobileid check the response request, does we have a valid XML buffer
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkResponseRequestXmlBuffer() {

		$xml = xml_parser_create('UTF-8');
		
		if (!xml_parse($xml, $this->soap_response_xml)) {
			$this->setError('MobileID XML Response error: '.xml_error_string(xml_get_error_code($xml)));
			return;			
		}
		
		return true;
	}
	
	/**
	* Mobileid get the request response as SimpleXML object
	*
	* @return 	simpleXML objet
	*/
	private function setXmlResponseObject() {
		
		$this->soap_response_simple_xml = simplexml_load_string($this->cleanUpResponse());
		
		if (!$this->soap_response_simple_xml) {
			$this->setError('Error parsing the XML response.');
			return;			
		}
		
		return true;
	}

	/**
	* Mobileid check the soap request response, to test if the request is valid or not
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function isResponseRequestSuccess() {
		
		$fault = (string)$this->soap_response_simple_xml->soapenvBody->soapenvFault->soapenvCode->soapenvSubcode->soapenvValue;
		
		if (strlen($fault)) {
			return;
		}
		
		return true;
	}	

	/**
	* Mobileid set the error of the soap request
	*
	* @return 	true
	*/
	private function setResponseError() {
		
		if (!$this->setResponseErrorSubCode()) {
			return;
		}
		
		if (!$this->setResponseErrorMessage()) {
			return;
		}

		return $this->setError($this->response_status_message);
	}

	/**
	* Mobileid set the status code
	*
	* @return 	simpleXML objet
	*/
	private function setResponseErrorSubCode() {
		
		$subcode = (string)$this->soap_response_simple_xml->soapenvBody->soapenvFault->soapenvCode->soapenvSubcode->soapenvValue;
		
		if (!strlen($subcode)) {
			$this->setError('No subcode error found!');
			return;			
		}
		
		if (!strstr($subcode, '_')) {
			$this->setError('Subcode is invalid!');
			return;			
		}
		
		$array_tmp = explode('_', $subcode);
		
		$this->response_soap_fault_subcode = $array_tmp[1];
		
		if (!strlen($this->response_soap_fault_subcode)) {
			$this->setError('Can not get the subcode!');
			return;
		}

		return true;
	}

	/**
	* Mobileid set the error response message
	*
	* @return 	simpleXML objet
	*/
	private function setResponseErrorMessage() {
		
		$this->response_status_message = (string)$this->soap_response_simple_xml->soapenvBody->soapenvFault->soapenvReason->soapenvText;
		
		if (!strlen($this->response_status_message)) {
			$this->setError('No response error message found!.');
			return;			
		}

		return true;
	}

	/**
	* Mobileid set the transaction ID
	*
	* @return 	simpleXML objet
	*/
	private function setResponseData() {
		
		if (!$this->setResponseTransId()) {
			return;
		}

		if (!$this->setResponseMobileUser()) {
			return;
		}

		if (!$this->setResponseMessage()) {
			return;
		}

		if (!$this->setResponseMssStatusCode()) {
			return;
		}
		
		return true;
	}

	/**
	* Mobileid set the transaction ID
	*
	* @return 	simpleXML objet
	*/
	private function setResponseTransId() {
		
		$this->data_response_trans_id = (string)$this->soap_response_simple_xml->soapenvBody->MSS_SignatureResponse->mssMSS_SignatureResp["MSSP_TransID"];

		if (!strlen($this->data_response_trans_id)) {
			$this->setError('No response transaction ID found!.');
			return;			
		}

		return true;
	}

	/**
	* Mobileid set the mobile user
	*
	* @return 	simpleXML objet
	*/
	private function setResponseMobileUser() {
		
		$this->data_response_mobile_user = (string)$this->soap_response_simple_xml->soapenvBody->MSS_SignatureResponse->mssMSS_SignatureResp->mssMobileUser->mssMSISDN;
		
		if (!strlen($this->data_response_mobile_user)) {
			$this->setError('No response mobile user found!.');
			return;			
		}
		
		return true;
	}

	/**
	* Mobileid set the response message
	*
	* @return 	simpleXML objet
	*/
	private function setResponseMessage() {
		
		$this->data_response_message = (string)$this->soap_response_simple_xml->soapenvBody->MSS_SignatureResponse->mssMSS_SignatureResp->mssStatus->mssStatusMessage;
		
		if (!strlen($this->data_response_message)) {
			$this->setError('No response message found!.');
			return;			
		}
		
		return true;
	}

	/**
	* Mobileid set the status code
	*
	* @return 	simpleXML objet
	*/
	private function setResponseMssStatusCode() {
		
		$this->response_mss_status_code = (string)$this->soap_response_simple_xml->soapenvBody->MSS_SignatureResponse->mssMSS_SignatureResp->mssStatus->mssStatusCode["Value"];
		
		if (!strlen($this->response_mss_status_code)) {
			$this->setError('No response MSS status code found!.');
			return;			
		}
		
		if (!$this->checkResponseMssStatusCode()) {
			$this->setError($this->data_response_message);
			return;
		}
		
		return true;
	}

	/**
	* Mobileid check the mss status code
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkResponseMssStatusCode() {
		
		if ($this->response_mss_status_code == '501' || $this->response_mss_status_code == '503' ) {
			return;
		}
		
		return true;
	}

	/**
	* Mobileid get then encoded signature
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function getEncodedSignature() {

		$this->soap_response_pkcs7 = (string)$this->soap_response_simple_xml->soapenvBody->MSS_SignatureResponse->mssMSS_SignatureResp->mssMSS_Signature->mssBase64Signature;

		if (!strlen($this->soap_response_pkcs7)) {
			return;
		}

		// split, to ensure that the input is formatted correctly
		$this->soap_response_pkcs7 = chunk_split($this->soap_response_pkcs7, 64);
		
		return true;
	}

	/**
	* Mobileid extract the signers certificate
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function extractSignersCertificate() {

		/* 
		 * This because the openssl_pkcs7_verify() function needs some mime headers to make it work 
		 */
		$signature = "MIME-Version: 1.0\nContent-Disposition: attachment;
		filename=\"smime.p7m\"\nContent-Type: application/x-pkcs7-mime;
		name=\"smime.p7m\"\nContent-Transfer-Encoding: base64\n\n".$this->soap_response_pkcs7;

		$this->file_sig      = $this->tmp_dir.'/signature_'.$this->data_response_trans_id.'.txt';
		$this->file_sig_msg  = $this->tmp_dir.'/signature_'.$this->data_response_trans_id.'.msg';
		$this->file_sig_cert = $this->tmp_dir.'/signature_'.$this->data_response_trans_id.'.crt';
		
		$fp = fopen($this->file_sig, 'w');
		
		if (!$fp) {
			$this->setError('Error when opening the signature file!');
			return;			
		}

		if (!fwrite($fp, $signature)) {
			$this->setError('Error when writing the signature file!');
			return;
		}

		fclose($fp);
		
		//$status = openssl_pkcs7_verify($this->file_sig, PKCS7_NOVERIFY, $this->file_sig_cert, array($this->cert_ca), array(), $this->file_sig_msg);
		$status = openssl_pkcs7_verify($this->file_sig, PKCS7_NOVERIFY, $this->file_sig_cert, array($this->cert_ca));
		
		if (!$status) {
			$this->setError('Error when verifing the signature : '.openssl_error_string());
			return;
		}

		if (!$this->getCertificateData()) {
			return;			
		}		

		return true;
	}

	/**
	* Mobileid extract the certificate datas
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function getCertificateData() {

		$certificate_data = openssl_x509_parse(file_get_contents($this->file_sig_cert));
		
		if (!$certificate_data) {
			$this->setError('No certificate data found!');
			return;			
		}
		
		$this->data_response_certificate = $certificate_data;
		
		return true;
	}

	/**
	* Mobileid verify the revocation status over ocsp
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function revocationStatusVerify() {
		
		if (!$this->getOcspUrl()) {
			return;
		}

		$this->file_sig_cert_check = $this->tmp_dir.'/signature_'.$this->data_response_trans_id.'.crt.check';
		
		exec("openssl ocsp -CAfile ".$this->cert_ca." -issuer ".$this->ocsp_cert." -nonce -out ".$this->file_sig_cert_check." -url ".$this->ocsp_url." -cert ".$this->file_sig_cert);

		if (!$this->checkRevocationStatus()) {
			return;			
		}
		
		return true;
	}

	/**
	* Mobileid get the OCSP URL for checking the validity of the certificate
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function getOcspUrl() {

		// The OCSP Url is manually defined on the configuration file
		if (strlen($this->ocsp_url)) {
			return true;
		}

		$matches = array();
		preg_match_all('/OCSP.+((https?|ftp):\/\/.+)/', $this->data_response_certificate['extensions']['authorityInfoAccess'], $matches);

		if (!strlen($matches[1][0])) {
			return;
		}
		
		$this->ocsp_url = $matches[1][0];

		return true;
	}

	/**
	* Mobileid check the revocation status
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function checkRevocationStatus() {
		
		$result = file($this->file_sig_cert_check);

		$status = explode(':', $result[0]);
		
		switch(trim($status[1])) {
		case 'revoked':
			$this->setError('The signers certificate is revoked!', '501');
			break;

		case 'failed':
			$this->setError('The signers certificate is unknown!', '501');
			break;

		case 'good':
		default:
			$this->data_response_certificate_status = trim($status[1]);
			break;
		}

		if ($this->response_error) {
			return;			
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
			return;
		}

		$this->response_error          = true;
		$this->response_status_message = $msg;
		$this->response_error_type     = $error_type;

		if ($this->response_mss_status_code == '501' || $this->response_mss_status_code == '503' ) {
			$this->response_error_type = 'warning';
		}

		$warning_code = array("105", "208", "401", "402", "403", "404", "406", "422");

		if (in_array($this->response_soap_fault_subcode, $warning_code)) {
			$this->response_error_type = 'warning';
		}
		
		$this->cleanUpTempFiles();

		return true;
	}

	/**
	* Mobileid clean up the temporaries files
	*
	* @return 	boolean	true on success, false on failure
	*/
	private function cleanUpTempFiles() {

		if (file_exists($this->file_sig_cert)) {
			if (!unlink($this->file_sig_cert)) {
				$this->setError('Error when removing the temporary file: '.$this->file_sig_cert, false, 'warning');
			}		
		}

		if (file_exists($this->file_sig_cert_check)) {
			if (!unlink($this->file_sig_cert_check)) {
				$this->setError('Error when removing the temporary file: '.$this->file_sig_cert_check, false, 'warning');
			}		
		}

		if (file_exists($this->file_sig)) {
			if (!unlink($this->file_sig)) {
				$this->setError('Error when removing the temporary file: '.$this->file_sig, false, 'warning');
			}		
		}

		/*
		if (file_exists($this->file_sig_msg)) {
			if (!unlink($this->file_sig_msg)) {
				$this->setError('Error when removing the temporary file: '.$this->file_sig_msg, false, 'warning');
			}		
		}
		*/

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
		$this->response_status_message = 'Signed data verified!';

		return true;		
	}

	/**
	* Mobileid clean up the XML response
	*
	* @return 	string XML response
	*/
	private function cleanUpResponse() {

		/* SimpleXML does not correctly parse SOAP XML results if the result comes back with colons ‘:’ in a tag, like <soap:Envelope>.
		 * Why? Because SimpleXML treats the colon character ‘:’ as an XML namespace, and places the entire contents of the SOAP XML result
		 * inside a namespace within the SimpleXML object. There is no real way to correct this using SimpleXML, but we can alter the raw XML result
		 * a little before we send it to SimpleXML to parse.		
		 */

		return preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $this->soap_response_xml);
	}
}
?>
