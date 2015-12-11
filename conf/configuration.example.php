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
	public $ap_id = "<AP UserID>";             					// AP ID provided by Swisscom
	public $ap_pwd = "disabled";                            	// AP Password provided by Swisscom
	public $ap_cert = "/www/conf/mycertandkey.crt";				// Certificate/key that is allowed to access the service

	/* Client certificate configuration */
	public $ca_ssl = "/www/conf/mobileid-ca-ssl.crt";			// Location of Certificate Authority file which should be used to authenticate the identity of the remote peer
	public $ca_mid = "/www/conf/mobileid-ca-signature.crt";		// Location of CA file which should be used during verifications

	/* Message provider */
	public $mid_msg_service = "serviceprovider.com";        	// Defines the prefix for the request messages

	/* Request messages. Those will be prefixed with the Message provider */
    public $mid_msg_de = "Erlauben Sie das testen Ihrer Mobile ID? (#TRANSID#)";
    public $mid_msg_en = "Allow testing of your Mobile ID? (#TRANSID#)";
    public $mid_msg_fr = "Authoriser le test de la Mobile ID? (#TRANSID#)";
    public $mid_msg_it = "Permetta le prove della vostra Mobile ID? (#TRANSID#)";
	
	/* Allow message edition */	
	public $mid_msg_allowedit = false;
}
?>
