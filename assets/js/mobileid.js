/**
 * @version     1.0.0
 * @package     mobileid-helper
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */

jQuery(document).ready(function() {
	
	// Remove value from the forms
	jQuery('#submit_btn_remove').click(function() {
		setRemoveFormValues();
	});	

	// Submit the form
	jQuery('#submit_btn_send').click(function() {		
		prepareSubmit();
		submitFormValues();
	});
	
	jQuery("#container").bind("keypress", function(e) {
		if (e.keyCode == 13) {
			prepareSubmit();
			submitFormValues();
			return false;
		}
	});
	
	jQuery('#mid_lang_zone').click(function() {
		var placeholder = jQuery("#mid_msg").attr("placeholder");
		
		if (!placeholder.length) {
			return;
		}

		jQuery('#mid_msg').val('');

		getDefaultMessage();
	});
});

function setRemoveFormValues() {
	
	// Set form field empty
	jQuery('#mid_phone').val('');
	jQuery('#mid_msg').val('');
	jQuery('#msg_result').html('');

	// Remove class for message result
	jQuery("#msg_result").removeClass("success");
	jQuery("#msg_result").removeClass("error");
	jQuery("#msg_result").removeClass("warning");
	
	// Hide waiting, error & result messages
	jQuery('#msg_wait').hide();
	jQuery('#msg_error').hide();
	jQuery('#msg_result').hide();
	
	// Check the default language
	jQuery('input[name="mid_lang"]').prop('checked', false);
	jQuery("#mid_lang_"+jQuery('#mid_lang_default').val()).trigger("click");

	// Set default message
	getDefaultMessage();
}

function prepareSubmit() {

	// Show waiting message
	jQuery('#msg_wait').show();
	jQuery('#msg_error').hide();
	jQuery('#msg_result').hide();	

	// Remove class for message result
	jQuery("#msg_result").removeClass("success");
	jQuery("#msg_result").removeClass("error");
	jQuery("#msg_result").removeClass("warning");

	// Disable submit and clear button
	jQuery('#submit_btn_remove').attr("disabled", "true");
	jQuery('#submit_btn_send').attr("disabled", "true");
}

function endSubmit() {
	// Enable submit and clear button
	jQuery('#submit_btn_remove').removeAttr('disabled');
	jQuery('#submit_btn_send').removeAttr('disabled');
}

function submitFormValues() {
		
	// Prepare the ajax/json request
	var mid_phone = jQuery('#mid_phone').val();
	var mid_lang  = jQuery('input:radio[name=mid_lang]:checked').val();
	var mid_msg   = jQuery('#mid_msg').val();
	var lang      = jQuery('#mid_lang_default').val();

	var jsonRequest;
	var ajax_url;

	jsonRequest = '{';
	jsonRequest = jsonRequest+'"mid_phone":"'+mid_phone+'"';
	jsonRequest = jsonRequest+',"mid_lang":"'+mid_lang+'"';
	
	if (mid_msg.length > 0) {
		jsonRequest = jsonRequest+',"mid_msg":"'+encodeURIComponent(mid_msg)+'"';
	}
	
	jsonRequest = jsonRequest+'}';
	
	ajax_url = 'form.php?request='+jsonRequest;
	ajax_url = ajax_url+'&lang='+lang;

	jQuery.ajax({
		url: ajax_url,
		success: function(data) {
			jQuery('#msg_result').addClass('success');
			jQuery('#msg_result').html(data);

			jQuery('#msg_result').show();
			jQuery('#msg_wait').hide();
			
			endSubmit();
		},
		error: function(data) {
			if (data.status == '400') {
				jQuery('#msg_result').addClass('error');
				jQuery('#msg_result').html(data.responseText);
			}

			if (data.status == '401') {
				jQuery('#msg_result').addClass('warning');
				jQuery('#msg_result').html(data.responseText);
			}

			jQuery('#msg_result').show();
			jQuery('#msg_wait').hide();
			
			endSubmit();
		}
	});
}

function getDefaultMessage() {
	
	var checked_lang = jQuery('input:radio[name=mid_lang]:checked').val();
	var ajax_url;
	
	ajax_url = 'form.php?request=default_msg';
	ajax_url = ajax_url+'&lang='+checked_lang;

	jQuery.ajax({
		url: ajax_url,
		success: function(data) {
			jQuery("#mid_msg").attr("placeholder", data).placeholder();
		}
	});
}
