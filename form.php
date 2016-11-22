<?php
/**
 * @version     1.0.0
 * @package     mobileid-helper
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     Licensed under the Apache License, Version 2.0 or later; see LICENSE.md
 * @author      Swisscom (Schweiz) AG
 */

define('__ROOT__', dirname(__FILE__));
require_once(__ROOT__.'/helpers/app.php');
require_once(__ROOT__.'/helpers/mobileid-helper.php');

/* Get the Ajax request, Json encoded */
$form_request = get_magic_quotes_gpc() ? stripslashes($_GET['request']) : $_GET['request'];

/* No request */
if (!$form_request) {
	return false;
}

/* Change default message request (AJAX) */
if ($form_request == 'default_msg') {
	$lang = $_GET["lang"];

	if (!strlen($lang)) {
		return false;
	}

	echo mobileid_helper::getDefaultMsg($lang);

	return false;
}

/* Json decoding of the request */
$request = json_decode($form_request);

if (!isset($request->mid_msg)) {
	$request->mid_msg = '';
}

/* New instance of the app class */
$app = new mobileid_app();

/* New instance of the mobileID class */
$mobileIdRequest = new mobileid_helper($request->mid_phone, $request->mid_lang, $request->mid_msg);

if (!$mobileIdRequest->profileQuery()) {
	$mobileIdRequest->setResponseError();
	setMobileIdError($mobileIdRequest, $app, $request->mid_lang);
	return false;
}

/* Calculate the request duration */
$time_start = microtime(true);

if (!$mobileIdRequest->signature()) {
	$mobileIdRequest->setResponseError();
	setMobileIdError($mobileIdRequest, $app, $request->mid_lang);
	return false;
}

echo $app->getText('APP_SUBMIT_SUCCESS');

/* Calculate the request duration */
$time_end = microtime(true);

if (strlen($mobileIdRequest->mid_serialnumber)) {
	echo ' '.str_replace('%s', $mobileIdRequest->mid_serialnumber, $app->getText('APP_SUBMIT_SUCCESS_SERIAL'));
}

/* Calculate the request duration */
$time = $time_end - $time_start;

echo '<br />'.str_replace('%s', number_format($time, 3), $app->getText('APP_SUBMIT_SUCCESS_DURATION'));

/**
* Mobileid set the mobileid error
*
* @return 	false
*/

function setMobileIdError($mobileIdRequest, $app, $lang = 'en', $msg_prob = '') {

	if (strlen($mobileIdRequest->response_error_code)) {

		$msg_prob = $app->getText('APP_ERROR_'.$mobileIdRequest->response_error_code);

        if (!strlen($msg_prob)) {
            $msg_prob = $app->getText('APP_ERROR_DEFAULT');
        }

		//$support_txt = utf8_decode($app->getText('APP_ERROR_SOLUTION_'.$mobileIdRequest->response_error_code));
		$support_txt = str_replace('#URL#', $mobileIdRequest->getUserAssistance('Mobile ID', true), $app->getText('APP_ERROR_SOLUTION_'.$mobileIdRequest->response_error_code));
	}

	if ($mobileIdRequest->response_error_type == 'warning') {

		$msg  = "<p>".$app->getText('APP_ERROR_WARNING')."</p>";
		$msg .= "<p><strong>".$app->getText('APP_ERROR_PROBLEM')."</strong> ".$msg_prob."</p>";
		$msg .= "<p><strong>".$app->getText('APP_ERROR_SOLUTION')."</strong> ".$support_txt."</p>";


		header('Content-Type: text/html; charset=utf-8');
		header('Status : 401 '.$msg);
		header('HTTP/1.0 401 '.$msg);

		echo $msg;
		return false;
	}

	$msg  = "<p>".$app->getText('APP_ERROR_TITLE')."</p>";
	$msg .= "<p><strong>".$app->getText('APP_ERROR_PROBLEM')."</strong> ".$msg_prob."</p>";
	$msg .= "<p><strong>".$app->getText('APP_ERROR_SOLUTION')."</strong> /etsi:_".$mobileIdRequest->response_error_code." -> ".$mobileIdRequest->statusdetail."</p>";

	header('Content-Type: text/html; charset=utf-8');
	header('Status : 400 '.$msg);
	header('HTTP/1.0 400 '.$msg);

	echo $msg;

	return false;
}
?>
