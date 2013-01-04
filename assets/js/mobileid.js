/**
 * @since		1.0
 * @package		mobileid_app
 * @copyright	Copyright © 2012 - All rights reserved.
 * @license		GNU/GPL
 * @author		Swisscom
 */

jQuery(document).ready(function() {
	
	// Remove value from the forms
	jQuery('#submit_btn_remove').click(function() {
		setRemoveFormValues();
	});	

	// Submit the form
	jQuery('#submit_btn_send').click(function() {

		if (!validatePhone()) {
			jQuery('#msg_error').show();
			return;
		}
		
		prepareSubmit();
		submitFormValues();
	});
	
	jQuery('#container').keyup(function(e) {
		
		if(e.keyCode == 13) {
			if (!validatePhone()) {
				jQuery('#msg_error').show();
				return;
			}

			prepareSubmit();
			submitFormValues();
		}
	});
});

function validatePhone() {

    var value  = jQuery('#mid_phone').val();
    var filter = /^[0-9+]+$/;
    
    if (!filter.test(value)) {
        return false;
    }
    
    if (value.substring(0,1) != '0' && value.substring(0,1) != '+') {
		return false;
	}

    if (value.substring(0,1)+value.substring(1,2) == '00' && value.length != 13) {
		return false;
	}

    if (value.substring(0,1) == '+' && value.length != 12) {
		return false;		
	}

    if (value.substring(0,1) == '0' && value.substring(1,2) != '0' && value.length != 10) {
		return false;		
	}

    return true;
}

function setRemoveFormValues() {
	
	// Set form field empty
	jQuery('#mid_phone').val('');
	
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
	
	// Enable submit button
	jQuery('#submit_btn_send').removeAttr('disabled');
}

function prepareSubmit() {

	// Show waiting message
	jQuery('#msg_wait').show();
	jQuery('#msg_error').hide();
	jQuery('#msg_result').hide();
	
	// Disable submit button
	jQuery('#submit_btn_send').attr("disabled", "true");
}

function submitFormValues() {
	
	// Prepare the ajax/json request
	var mid_lang = jQuery('input:radio[name=mid_lang]:checked').val();
	var phone    = jQuery('#mid_phone').val();
	var lang     = jQuery('#mid_lang_default').val();
	
	var jsonRequest = '{"mid_phone":"'+phone+'","mid_lang":"'+mid_lang+'"}';
	var ajax_url;
	
	ajax_url = 'form.php?request='+jsonRequest;
	ajax_url = ajax_url+'&lang='+lang;
	
	jQuery.ajax({
		url: ajax_url,
		success: function(data) {
			jQuery('#msg_result').addClass('success');
			jQuery('#msg_result').html(data);

			jQuery('#msg_result').show();
			jQuery('#msg_wait').hide();
		},
		error: function(data) {
			if (data.status == '400') {
				jQuery('#msg_result').addClass('error');
				jQuery('#msg_result').html(data.statusText);
			}

			if (data.status == '401') {
				jQuery('#msg_result').addClass('warning');
				jQuery('#msg_result').html(data.statusText);
			}

			jQuery('#msg_result').show();
			jQuery('#msg_wait').hide();
		}
	});
}
