/**
 * Admin Settings JavaScript
 * 
 * @package MedicalBookingSystem
 * @since 1.2.0
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize Color Picker
		if ($.fn.wpColorPicker) {
			$('.mbs-color-picker').wpColorPicker();
		}

		// Form validation
		$('form').on('submit', function(e) {
			var errors = [];

			// Validate email addresses
			$('input[type="email"]').each(function() {
				var email = $(this).val();
				if (email && !isValidEmail(email)) {
					errors.push('Invalid email address: ' + email);
				}
			});

			// Validate numbers
			$('input[type="number"]').each(function() {
				var val = parseInt($(this).val());
				var min = parseInt($(this).attr('min'));
				var max = parseInt($(this).attr('max'));

				if (val < min || val > max) {
					var label = $(this).closest('tr').find('label').first().text();
					errors.push(label + ' must be between ' + min + ' and ' + max);
				}
			});

			if (errors.length > 0) {
				e.preventDefault();
				alert('Please fix the following errors:\n\n' + errors.join('\n'));
				return false;
			}
		});

		// Checkbox handling for yes/no values
		$('input[type="checkbox"]').each(function() {
			var $checkbox = $(this);
			var name = $checkbox.attr('name');

			// If checkbox is not checked, add a hidden field with 'no' value
			if (!$checkbox.is(':checked')) {
				$checkbox.after('<input type="hidden" name="' + name + '" value="no" />');
			}

			// On change, update hidden field
			$checkbox.on('change', function() {
				var $hidden = $(this).siblings('input[type="hidden"]');
				if ($(this).is(':checked')) {
					$hidden.remove();
				} else {
					if ($hidden.length === 0) {
						$(this).after('<input type="hidden" name="' + name + '" value="no" />');
					}
				}
			});
		});

		// Email template placeholders helper
		$('textarea[name*="_body"]').each(function() {
			var $textarea = $(this);
			var $helper = $('<div class="email-placeholders-helper" style="margin-top: 10px; padding: 10px; background: #f0f0f1; border-left: 4px solid #2271b1;">' +
				'<strong>Available Placeholders:</strong><br>' +
				'<code>{patient_name}</code> - Patient name<br>' +
				'<code>{appointment_date}</code> - Appointment date<br>' +
				'<code>{appointment_time}</code> - Appointment time<br>' +
				'<code>{doctor_name}</code> - Doctor name<br>' +
				'<code>{service_name}</code> - Service name<br>' +
				'<code>{business_name}</code> - Business name<br>' +
				'<code>{cancellation_reason}</code> - Cancellation reason (only for cancellation email)' +
				'</div>');

			$textarea.after($helper);
		});

		// Character counter for textareas
		$('textarea').each(function() {
			var $textarea = $(this);
			var maxLength = 1000;
			var currentLength = $textarea.val().length;

			var $counter = $('<div class="character-counter" style="margin-top: 5px; color: #666; font-size: 12px;">' +
				currentLength + ' / ' + maxLength + ' characters' +
				'</div>');

			$textarea.after($counter);

			$textarea.on('input', function() {
				var length = $(this).val().length;
				$counter.text(length + ' / ' + maxLength + ' characters');

				if (length > maxLength * 0.9) {
					$counter.css('color', '#d63638');
				} else {
					$counter.css('color', '#666');
				}
			});
		});

		// Timezone search
		if ($('#timezone').length) {
			var $select = $('#timezone');
			var $search = $('<input type="text" placeholder="Search timezone..." style="width: 100%; margin-bottom: 5px; padding: 5px;" />');

			$select.before($search);

			$search.on('keyup', function() {
				var searchTerm = $(this).val().toLowerCase();
				$select.find('option').each(function() {
					var text = $(this).text().toLowerCase();
					if (text.indexOf(searchTerm) > -1) {
						$(this).show();
					} else {
						$(this).hide();
					}
				});
			});
		}

		// Show/hide conditional fields
		$('input[name="mbs_settings[allow_patient_cancellation]"]').on('change', function() {
			var $cancellationFields = $('#cancellation_deadline_hours, input[name="mbs_settings[cancellation_reason_required]"]').closest('tr');
			if ($(this).val() === 'yes' && $(this).is(':checked')) {
				$cancellationFields.fadeIn();
			} else {
				$cancellationFields.fadeOut();
			}
		}).trigger('change');

		$('input[name="mbs_settings[enable_email_notifications]"]').on('change', function() {
			var $emailFields = $('#email_from_name, #email_from_address, #admin_notification_email, #email_appointment_confirmation_subject, #email_appointment_reminder_subject, #email_appointment_cancellation_subject').closest('tr');
			if ($(this).is(':checked')) {
				$emailFields.fadeIn();
			} else {
				$emailFields.fadeOut();
			}
		}).trigger('change');

		$('input[name="mbs_settings[enforce_2fa]"]').on('change', function() {
			var $recommend2FA = $('input[name="mbs_settings[recommend_2fa]"]').closest('tr');
			if ($(this).val() === 'no' && $(this).is(':checked')) {
				$recommend2FA.fadeIn();
			} else {
				$recommend2FA.fadeOut();
			}
		}).trigger('change');

		// Save button confirmation
		$('#submit').on('click', function(e) {
			// Optional: Add confirmation for critical settings changes
			// This is commented out by default but can be enabled if needed
			/*
			if (!confirm('Are you sure you want to save these settings?')) {
				e.preventDefault();
				return false;
			}
			*/
		});

		// Success message auto-hide
		$('.updated, .notice-success').delay(3000).fadeOut();
	});

	/**
	 * Validate email address
	 */
	function isValidEmail(email) {
		var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return re.test(email);
	}

})(jQuery);

