<?php
/**
 * @version     1.0.0
 * @package     mobileid-helper
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */

define('__ROOT__', dirname(__FILE__)); 
require_once(__ROOT__.'/helpers/app.php');
require_once(__ROOT__.'/helpers/mobileid.php');

/* Get the Ajax request, Json encoded */
$form_request = $_GET["request"];

/* No request */
if (!$form_request) {
	return;
}

/* Change default message request (AJAX) */
if ($form_request == 'default_msg') {
	$lang = $_GET["lang"];
	
	if (!strlen($lang)) {
		return;
	}

	echo mobileid::getDefaultMsg($lang);
	
	return;
}

/* Json decoding of the request */
$request = json_decode($form_request);

if ($request->mid_phone[0] == ' ') {
	$request->mid_phone[0] = '+';
}

/* New instance of the app class */
$app = new mobileid_app();

/* New instance of the mobileID class */
$mobileIdRequest = new mobileid($request->mid_phone, $request->mid_lang, $request->mid_msg);

/* Send the request */
$mobileIdRequest->sendRequest();

if ($mobileIdRequest->response_error) {
	setMobileIdError($mobileIdRequest, $app, $request->mid_lang);
	return;
}

echo $app->getText('APP_SUBMIT_SUCCESS');

/**
* Mobileid set the mobileid error
*
* @return 	false
*/

function setMobileIdError($mobileIdRequest, $app, $lang = 'en') {

	if (strlen($mobileIdRequest->response_mss_status_code)) {
		$msg_prob    = $app->getText('APP_ERROR_'.$mobileIdRequest->response_mss_status_code);
		$support_txt = $app->getText('APP_ERROR_SOLUTION_'.$mobileIdRequest->response_mss_status_code);
	}

	if (strlen($mobileIdRequest->response_soap_fault_subcode)) {
		$msg_prob    = $app->getText('APP_ERROR_'.$mobileIdRequest->response_soap_fault_subcode);			
		$support_txt = $app->getText('APP_ERROR_SOLUTION_'.$mobileIdRequest->response_soap_fault_subcode);
	}

	if ($mobileIdRequest->response_error_type == 'warning') {

		$msg  = "<p>".$app->getText('APP_ERROR_WARNING')."</p>";
		$msg .= "<p><strong>".$app->getText('APP_ERROR_PROBLEM')."</strong> ".$msg_prob."</p>";
		$msg .= "<p><strong>".$app->getText('APP_ERROR_SOLUTION')."</strong> ".$support_txt."</p>";
		
		echo $msg;

		header('Status : 401 '.$msg);
		header('HTTP/1.0 401 '.$msg);

		return;	
	}	
	
	$msg  = "<p>".$app->getText('APP_ERROR_TITLE')."</p>";	
	$msg .= "<p><strong>".$app->getText('APP_ERROR_PROBLEM')."</strong> ".$msg_prob."</p>";
	$msg .= "<p><strong>".$app->getText('APP_ERROR_SOLUTION')."</strong> ".$mobileIdRequest->response_mss_status_code."/etsi:_".$mobileIdRequest->response_soap_fault_subcode." -> ".$mobileIdRequest->response_status_message."</p>";

	echo $msg;
	
	header('Status : 400 '.$msg);
	header('HTTP/1.0 400 '.$msg);

	return;	
}
?>
