jQuery(document).ready(function ($) {
	var clickedButton;
	var currentForm;
	$('.wtrc-input').val('');
	$('.wtrc-submit').prop("disabled", false);
	$('.wtrc-switch').click(function () {
		$(this).siblings('.wtrc-content').slideToggle();
	});

	$('.wtrc-submit').click(function () {
		clickedButton = $(this);
		currentForm = $(this).parents('.wtrc-content');
		var post_id = currentForm.find('.post-id').val();
		var _remind_date = currentForm.find('.input-date').val();
		var _remind_time = currentForm.find('.input-time').val();
		var _comment = currentForm.find('.input-comment').val();
		var _reminder_name = currentForm.find('.input-name').val();
		var _reminder_email = currentForm.find('.input-email').val();
		clickedButton.prop("disabled", true);
		currentForm.find('.loading-img').show();
		$.ajax({
			type: 'POST',
			url: wtrcajaxhandler.ajaxurl,
			data: {
				action: 'wtrc_add_remind',
				id: post_id,
				reminder_date: _remind_date,
				reminder_time: _remind_time,
				comment: _comment,
				reminder_name: _reminder_name,
				reminder_email: _reminder_email
			},
			success: function (data, textStatus, XMLHttpRequest) {
				currentForm.find('.loading-img').hide();
				data = jQuery.parseJSON(data);
				if (data.success) {
					currentForm.find('.wtrc-message').html(data.message).addClass('success');
					currentForm.find('.wtrc-form').remove();
				}
				else {
					clickedButton.prop("disabled", false);
					currentForm.find('.wtrc-message').html(data.message).addClass('error');
				}
			},
			error: function (MLHttpRequest, textStatus, errorThrown) {
				alert(errorThrown);
			}
		});
	});
	
	$('.wtrc-timepicker').datetimepicker({
		datepicker:false,
		format:'H:i',
		step:15,
		formatTime:'g:i A'
		
	});
	
	$('.wtrc-datepicker').datetimepicker({
		timepicker:false,
		format:'Y-m-d',
		formatDate:'Y/m/d',
		minDate:'-1970/01/01'
	});
});
