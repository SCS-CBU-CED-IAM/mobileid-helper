<?php
/**
 * @version     1.0.0
 * @package     mobileid-helper
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */
    
class mobileIdConfig {

	/* Client certificate configuration */
	public $cert_ca   = "/www/conf/swisscom-ca.crt";        // Bag file with the server/client issuing and root certifiates
	public $cert_file = "/www/conf/mycert.crt";             // The certificate that is allowed to access the service
	public $cert_key  = "/www/conf/mycert.key";             // The related key of the certificate
	
	//public $cert_key_pw  = "";                            // Optional, password to access the private key
	
	/* AP configuration */
	public $ap_id  = "<AP UserID>";                         // AP UserID provided by Swisscom
	public $ap_pwd = "disabled";                            // AP Password must be present but is not validated
	
	/* OCSP configuration */
	public $ocsp_cert = "/www/conf/swisscom-ocsp.crt";      // OCSP information of the signers certificate
	
	/* Proxy configuration */
	public $curl_proxy = "";                                // e.g. "my-proxy.com:8080", leave empty for no proxy
	
	/* Soap configuration */
	public $ws_url    = "https://mobileid.swisscom.com/soap/services/MSS_SignaturePort";
	public $ws_action = "#MSS_Signature";

	/* Set the timeout for the request */
	//public $TimeOutWSRequest  = 90;                       // Optional, to set the timeout of the web service call
	//public $TimeOutMIDRequest = 80;                       // Optional, to set the timeout of the mobile id call

	/* Message provider */
	public $mid_msg_service = "serviceprovider.com";        // Defines the prefix for the request messages

	/* Request messages. Those will be prefixed with the Message provider */
	public $mid_msg_de = "Erlauben Sie das testen Ihrer Mobile ID?";
	public $mid_msg_en = "Allow testing of your Mobile ID?";
	public $mid_msg_fr = "Autoriser le test de la Mobile ID?";
	public $mid_msg_it = "Permetta le prove della vostra Mobile ID?";
	
	/* Allow message edition */	
	public $mid_msg_allowedit = false;

}
?>
