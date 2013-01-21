/**
 * @version     1.0.0
 * @package     mobileid-helper
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.md
 * @author      Swisscom (Schweiz AG)
 */

jQuery('[placeholder]').focus(function() {
	var input = jQuery(this);
	
	if (input.val() == input.attr('placeholder')) {
		input.val('');
		input.removeClass('placeholder');
	}
	}).blur(function() {
		var input = jQuery(this);
		
		if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		}
	}).blur().parents('form').submit(function() {
  
	jQuery(this).find('[placeholder]').each(function() {
		var input = jQuery(this);
    
		if (input.val() == input.attr('placeholder')) {
			input.val('');
		}
	})
});

jQuery('#mid_lang_zone').click(function() {
	var placeholder = jQuery("#mid_msg").attr("placeholder");
	
	if (!placeholder.length) {
		return;
	}

	jQuery('#mid_msg').val('');
	
	getDefaultMessageIe();
});

function getDefaultMessageIe() {
	
	var checked_lang = jQuery('input:radio[name=mid_lang]:checked').val();
	var ajax_url;
	
	ajax_url = 'form.php?request=default_msg';
	ajax_url = ajax_url+'&lang='+checked_lang;
	
	jQuery.ajax({
		url: ajax_url,
		success: function(data) {
			jQuery("#mid_msg").val(data);
		}
	});
}
