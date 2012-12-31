<?php
define('__ROOT__', dirname(__FILE__)); 
require_once(__ROOT__.'/helpers/app.php'); 
$app = new mobileid_app();
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $app->getText('TITLE'); ?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="assets/css/bootstrap-responsive.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="assets/css/custom.css" type="text/css" media="screen" />
</head>
<body>
	<div id="container">
		<h1><?php echo $app->getText('APP_TITLE'); ?></h1>
		<form id="mid_form" class="form-horizontal">
			<fieldset>
			<div class="control-group">
				<label class="control-label" for="mid_phone"><strong><?php echo $app->getText('APP_PHONE'); ?></strong></label>
				<div class="controls">
					<input type="tel" id="mid_phone" value="" required />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="mid_lang"><strong><?php echo $app->getText('APP_LANG'); ?></strong></label>
				<div class="controls">
					<label class="radio">
						<input type="radio" name="mid_lang" id="mid_lang_de" value="de" <?php if ($app->language == 'de') { ?>checked<?php } ?> />
						<?php echo $app->getText('APP_LANG_1'); ?>
					</label>
					<label class="radio">
						<input type="radio" name="mid_lang" id="mid_lang_fr" value="fr" <?php if ($app->language == 'fr') { ?>checked<?php } ?> />
						<?php echo $app->getText('APP_LANG_2'); ?>
					</label>
					<label class="radio">
						<input type="radio" name="mid_lang" id="mid_lang_it" value="it" <?php if ($app->language == 'if') { ?>checked<?php } ?> />
						<?php echo $app->getText('APP_LANG_3'); ?>
					</label>
					<label class="radio">
						<input type="radio" name="mid_lang" id="mid_lang_en" value="en" <?php if ($app->language == 'en') { ?>checked<?php } ?> />
						<?php echo $app->getText('APP_LANG_4'); ?>
					</label>
				</div>
			</div>
			<div class="form-actions">
				<input type="button" value="<?php echo $app->getText('APP_SUBMIT_BTN_REMOVE'); ?>" class="btn" id="submit_btn_remove" />
				<input type="button" value="<?php echo $app->getText('APP_SUBMIT_BTN_SEND'); ?>" class="btn" id="submit_btn_send" />
			</div>
			<input type="hidden" value="<?php echo $app->language; ?>" id="mid_lang_default" />
			</fieldset>
		</form>
		<div id="msg_wait" class="alert alert-block"><img src="assets/img/ajax-loader.gif" alt="<?php echo $app->getText('APP_SUBMIT_WAIT_ALT'); ?>" title="<?php echo $app->getText('APP_SUBMIT_WAIT_ALT'); ?>" /> <?php echo $app->getText('APP_SUBMIT_WAIT_MSG'); ?></div>
		<div id="msg_error" class="error"><?php echo $app->getText('APP_ERROR_1'); ?></div>
		<div id="msg_result" class=""><?php echo $app->getText('APP_SUBMIT_SUCCESS'); ?></div>
	</div>
	<script type="text/javascript" src="assets/js/jquery/jquery-1.8.3.min.js"></script>
	<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/mobileid.js"></script>
	</body>
</html>
