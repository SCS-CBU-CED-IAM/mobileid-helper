<?php
class mobileIdConfig {

	/* Client certificate configuration */
	public $cert_ca   = "/data/webdev/www/dev.swisscom.ch/conf/swisscom-ca.crt";		// Bag file with the server/client issuing and root certifiates
	public $cert_file = "/data/webdev/www/dev.swisscom.ch/conf/mycert.crt";				// The certificate that is allowed to access the service
	public $cert_key  = "/data/webdev/www/dev.swisscom.ch/conf/mycert.key";				// The related key of the certificate
	
	/* AP configuration */
	public $ap_id  = "http://iam.swisscom.ch";											// AP UserID provided by Swisscom
	public $ap_pwd = "disabled";														// AP Password must be present but is not validated
	
	/* OCSP configuration */
	public $ocsp_cert = "/data/webdev/www/dev.swisscom.ch/conf/swisscom-ocsp.crt";		// OCSP information of the signers certificate
	//public $ocsp_url  = "http://ocsp.swissdigicert.ch/sdcs-rubin2";					// Not mandatory
	
	/* Soap configuration */
	public $ws_url    = "https://soap.mobileid.swisscom.com/soap/services/MSS_SignaturePort";
	public $ws_action = "#MSS_Signature";

	/* Set the timeout for the request */
	//public $TimeOutWSRequest  = 90;													// Not mandatory
	//public $TimeOutMIDRequest = 80;													// Not mandatory

	/* Request messages  */
	public $mid_msg_de = "Erlauben Sie das testen Ihrer Mobile ID ?";
	public $mid_msg_en = "Allow testing of your Mobile ID?";
	public $mid_msg_fr = "Authoriser le test de la Mobile ID ?";
	public $mid_msg_it = "Allow testing of your Mobile ID?";
}
?>
