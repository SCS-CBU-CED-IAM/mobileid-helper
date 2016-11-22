<?php
/**
 * @version     1.0.0
 * @package     mobileid-helper
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */

class mobileIdConfig {

	/* AP configuration */
	// AP ID provided by Swisscom
	public $ap_id = "<AP UserID>";
	// AP Password provided by Swisscom
 	public $ap_pwd = "disabled";
	// Certificate/key that is allowed to access the service
	public $ap_cert = "conf/mycertandkey.crt";
  /* Optional Password when using an encrpyted key with $ap_cert */
  //public $ap_cert_pwd = "password";

	/* Client certificate configuration */
	// Location of Certificate Authority file which should be used to authenticate the identity of the remote peer
	public $ca_ssl = "conf/mobileid-ca-ssl.crt";
	// Location of CA file which should be used during verifications
	public $ca_mid = "conf/mobileid-ca-signature.crt";

	/* Message provider */
	// Defines the prefix for the request messages
	public $mid_msg_service = "serviceprovider.com";

	/* Request messages. Those will be prefixed with the Message provider */
	public $mid_msg_de = "Erlauben Sie das testen Ihrer Mobile ID? (#TRANSID#)";
	public $mid_msg_en = "Allow testing of your Mobile ID? (#TRANSID#)";
	public $mid_msg_fr = "Autoriser le test de la Mobile ID? (#TRANSID#)";
	public $mid_msg_it = "Permetta le prove della vostra Mobile ID? (#TRANSID#)";

	/* Allow message edition */
	public $mid_msg_allowedit = false;

	/* Uncomment proxy settings if needed */
	//public $proxy_host = "138.190.132.11";
	//public $proxy_port = "8079";
}
?>
