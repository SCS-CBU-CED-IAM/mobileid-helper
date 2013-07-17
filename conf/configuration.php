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
	public $cert_ca   = "/Users/HP/scs/mid/mobileid-cmd/bash/swisscom-ca.crt";
	public $cert_file = "/Users/HP/scs/mid/mobileid-cmd/bash/mycert.crt";             // The certificate that is allowed to access the service
	public $cert_key  = "/Users/HP/scs/mid/mobileid-cmd/bash/mycert.key";             // The related key of the certificate
	
	/* AP configuration */
	public $ap_id  = "http://iam.swisscom.ch";
	public $ap_pwd = "disabled";                            // AP Password must be present but is not validated
	
	/* OCSP configuration */
	public $ocsp_cert = "/Users/HP/scs/mid/mobileid-cmd/swisscom-ocsp.crt";
	
	/* Soap configuration */
	public $ws_url    = "https://soap.mobileid.swisscom.com/soap/services/MSS_SignaturePort";
	public $ws_action = "#MSS_Signature";

	/* Set the timeout for the request */
	//public $TimeOutWSRequest  = 90;                       // Optional, to set the timeout of the web service call
	//public $TimeOutMIDRequest = 80;                       // Optional, to set the timeout of the mobile id call

	/* Message provider */
	public $mid_msg_service = "PostFinance";        // Defines the prefix for the request messages

	/* Request messages. Those will be prefixed with the Message provider */
	public $mid_msg_de = "Sie erhalten eine Testmeldung von PostFinance. Bitte bestätigen Sie den Erhalt der Meldung.";
	public $mid_msg_en = "Allow testing of your Mobile ID?";
	public $mid_msg_fr = "Authoriser le test de la Mobile ID?";
	public $mid_msg_it = "Permetta le prove della vostra Mobile ID?";
	
	/* Allow message edition */	
	public $mid_msg_allowedit = false;

}
?>
