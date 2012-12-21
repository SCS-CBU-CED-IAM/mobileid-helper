<?php
/**
 * @version     1.0.0
 * @package     mobileid
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Verbier Technologies - http://www.verbier-technologies.ch
 */

define('__ROOT__', dirname(__FILE__)); 
require_once(__ROOT__.'/helpers/app.php');
require_once(__ROOT__.'/helpers/mobileid.php');

/* Get the Ajax request, Json encoded */
$json_request = $_GET["request"];

/* No request */
if (!$json_request) {
	return;
}

/* Json decoding of the request */
$request = json_decode($json_request);

if ($request->mid_phone[0] == ' ') {
	$request->mid_phone[0] = '+';
}

/* New instance of the app class */
$app = new mobileid_app();

/* New instance of the mobileID class */
$mobileIdRequest = new mobileid($request->mid_phone, $request->mid_msg, $request->mid_lang);

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

	if ($mobileIdRequest->response_error_type == 'warning') {
		$warning_code = array("105", "401", "402", "403", "404", "406", "422");

		if ($mobileIdRequest->response_status_code == '501' || $mobileIdRequest->response_status_code == '503') {
			$msg_prob = $app->getText('APP_ERROR_'.$mobileIdRequest->response_status_code);
		}

		if (in_array($mobileIdRequest->response_status_subcode, $warning_code)) {
			$msg_prob = $app->getText('APP_ERROR_'.$mobileIdRequest->response_status_subcode);			
		}
		
		$support_url = $mobileIdRequest->getSupportUrl().'/'.$lang.'/'.$mobileIdRequest->response_status_code.'-'.$mobileIdRequest->response_status_subcode;
		
		$support_txt = str_replace('%s', $support_url, $app->getText('APP_ERROR_WARNING_SOLUTION'));
		$support_txt = str_replace('%t', $support_url, $support_txt);

		$msg  = "<p>".$app->getText('APP_ERROR_WARNING')."</p>";
		$msg .= "<p><strong>".$app->getText('APP_ERROR_PROBLEM')."</strong> ".$msg_prob."</p>";
		$msg .= "<p><strong>".$app->getText('APP_ERROR_SOLUTION')."</strong> ".$support_txt."</p>";
		
		echo $msg;

		header('Status : 401 '.$msg);
		header('HTTP/1.0 401 '.$msg);

		return;	
	}	
	
	$msg  = "<p>".$app->getText('APP_ERROR_DEFAULT')."</p>";
	
	if ($mobileIdRequest->response_status_subcode) {
		$msg .= "<p><strong>".$app->getText('APP_ERROR_PROBLEM')."</strong> ".$app->getText('APP_ERROR_'.$mobileIdRequest->response_status_subcode)."</p>";
	} else {
		$msg .= "<p><strong>".$app->getText('APP_ERROR_PROBLEM')."</strong> ".$app->getText('APP_ERROR_'.$mobileIdRequest->response_status_code)."</p>";
	}

	$msg .= "<p><strong>".$app->getText('APP_ERROR_SOLUTION')."</strong> ".$mobileIdRequest->response_status_code."/etsi:_".$mobileIdRequest->response_status_subcode." -> ".$mobileIdRequest->response_message."</p>";

	echo $msg;
	
	header('Status : 400 '.$msg);
	header('HTTP/1.0 400 '.$msg);

	return;	
}
?>
