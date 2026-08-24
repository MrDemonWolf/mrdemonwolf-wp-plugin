/**
 * MRDW_Push Admin JavaScript
 *
 * Handles AJAX operations for send, device management, groups, and meta box.
 */
(function($) {
	'use strict';

	// ── Helpers ─────────────────────────────────────────────────

	/**
	 * Show a WordPress-style admin notice inside #mrdw-push-app.
	 *
	 * @param {string} message The message to display.
	 * @param {string} type    Notice type: 'error', 'success', 'warning', 'info'.
	 */
	function mrdwPushNotice(message, type) {
		type = type || 'error';
		var $existing = $('#mrdw-push-app > .notice');
		$existing.remove();
		var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p></p></div>');
		$notice.find('p').text(message);
		var $dismiss = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>');
		$dismiss.find('.screen-reader-text').text(mrdwPush.strings.dismiss || 'Dismiss');
		$notice.append($dismiss);
		$('#mrdw-push-app .mrdw-push-page-header').after($notice);
		$dismiss.on('click', function() { $notice.fadeOut(200, function() { $(this).remove(); }); });
		setTimeout(function() { $notice.fadeOut(200, function() { $(this).remove(); }); }, 5000);
	}

	/**
	 * Show a styled confirmation modal using existing .mrdw-push-modal-* CSS.
	 *
	 * @param {string}   message   The confirmation message.
	 * @param {Function} onConfirm Callback executed when user clicks Confirm.
	 */
	function mrdwPushConfirm(message, onConfirm) {
		var $overlay = $('<div class="mrdw-push-modal-overlay" style="position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;" role="dialog" aria-modal="true" aria-label="Confirmation dialog"></div>');
		var $panel = $('<div class="mrdw-push-modal-panel" style="max-width:400px;width:90%;"></div>');
		var $header = $('<div class="mrdw-push-modal-header"></div>');
		$header.append($('<h3></h3>').text(mrdwPush.strings.confirm || 'Confirm'));
		$panel.append($header);
		var $body = $('<div class="mrdw-push-modal-body"></div>');
		$body.append($('<p></p>').text(message));
		var $actions = $('<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;"></div>');
		$actions.append($('<button type="button" class="button ts-confirm-cancel"></button>').text(mrdwPush.strings.cancel || 'Cancel'));
		$actions.append($('<button type="button" class="button button-primary ts-confirm-ok"></button>').text(mrdwPush.strings.confirm || 'Confirm'));
		$body.append($actions);
		$panel.append($body);
		$overlay.append($panel);
		$('body').append($overlay);
		$overlay.on('click', '.ts-confirm-cancel', function() { $overlay.remove(); });
		$overlay.on('click', '.ts-confirm-ok', function() { $overlay.remove(); onConfirm(); });
	}

	// ── Send Notification Page ──────────────────────────────────

	// Target type radio toggle
	$(document).on('change', '.mrdw-push-target-radio', function() {
		var value = $(this).val();
		$('#mrdw-push-specific-devices').toggle(value === 'specific');
		$('#mrdw-push-group-select').prop('disabled', value !== 'group');
	});

	// Schedule radio toggle
	$(document).on('change', '.mrdw-push-when-radio', function() {
		var isSchedule = $(this).val() === 'schedule';
		var $btn = $('#mrdw-push-send-btn');
		if (!$btn.data('original-text')) {
			$btn.data('original-text', $btn.text());
		}
		$('#mrdw-push-schedule-datetime').prop('disabled', !isSchedule);
		$btn.text(isSchedule ?
			(mrdwPush.strings.schedule || 'Schedule') :
			$btn.data('original-text')
		);
	});

	// Send notification form
	$(document).on('submit', '#mrdw-push-send-form', function(e) {
		e.preventDefault();

		var $btn = $('#mrdw-push-send-btn');
		var $status = $('#mrdw-push-send-status');
		var originalText = $btn.text();

		// Require a datetime when scheduling.
		var sendWhen = $('[name="send_when"]:checked').val();
		if (sendWhen === 'schedule' && !$('#mrdw-push-schedule-datetime').val()) {
			mrdwPushNotice(mrdwPush.strings.schedule_datetime_required || 'Please choose a date and time before scheduling.', 'error');
			return;
		}

		$btn.prop('disabled', true).text(mrdwPush.strings.sending);
		$status.text('').removeClass('mrdw-push-status-success mrdw-push-status-error');

		var data = {
			action: 'mrdw_push_send_notification',
			nonce: mrdwPush.nonce,
			title: $('[name="title"]', this).val(),
			body: $('[name="body"]', this).val(),
			image_url: $('[name="image_url"]', this).val(),
			data: $('[name="data"]', this).val(),
			target_type: $('[name="target_type"]:checked', this).val()
		};

		// Target IDs
		if (data.target_type === 'group') {
			data.target_ids = [$('#mrdw-push-group-select').val()];
		} else if (data.target_type === 'specific') {
			data.target_ids = [];
			$('.mrdw-push-device-checkbox:checked').each(function() {
				data.target_ids.push($(this).val());
			});
		}

		// Schedule
		if (sendWhen === 'schedule') {
			data.scheduled_at = $('#mrdw-push-schedule-datetime').val();
		}

		$.post(mrdwPush.ajax_url, data, function(response) {
			if (response.success) {
				$status.text(response.data.message).addClass('mrdw-push-status-success');
				if (sendWhen !== 'schedule') {
					$btn.text(mrdwPush.strings.sent);
				} else {
					$btn.text(mrdwPush.strings.scheduled);
					setTimeout(function() { location.reload(); }, 1500);
				}
			} else {
				$status.text(response.data.message).addClass('mrdw-push-status-error');
				$btn.text(originalText);
			}
			$btn.prop('disabled', false);
		}).fail(function() {
			$status.text(mrdwPush.strings.error).addClass('mrdw-push-status-error');
			$btn.prop('disabled', false).text(originalText);
		});
	});

	// Cancel scheduled notification
	$(document).on('click', '.mrdw-push-cancel-scheduled', function() {
		var $btn = $(this);
		var id = $btn.data('id');

		mrdwPushConfirm(mrdwPush.strings.confirm_delete, function() {
			$.post(mrdwPush.ajax_url, {
				action: 'mrdw_push_cancel_scheduled',
				nonce: mrdwPush.nonce,
				notification_id: id
			}, function(response) {
				if (response.success) {
					$btn.closest('tr').fadeOut(function() { $(this).remove(); });
				} else {
					mrdwPushNotice(response.data.message, 'error');
				}
			}).fail(function() {
				mrdwPushNotice(mrdwPush.strings.error, 'error');
			});
		});
	});

	// Placeholder quick-fill buttons
	$(document).on('click', '.mrdw-push-placeholder-btn', function() {
		var targetId = $(this).data('target');
		var value = $(this).data('value');
		var $field = $('#' + targetId);
		var el = $field[0];

		if (el && el.setSelectionRange) {
			var start = el.selectionStart;
			var end = el.selectionEnd;
			var text = $field.val();
			$field.val(text.substring(0, start) + value + text.substring(end));
			el.selectionStart = el.selectionEnd = start + value.length;
			$field.trigger('input');
		} else {
			$field.val($field.val() + value);
			$field.trigger('input');
		}
		$field.focus();
	});

	// Character counters
	function updateCharCount($field) {
		var $counter = $('.mrdw-push-char-count[data-target="' + $field.attr('id') + '"]');
		if (!$counter.length) return;
		var len = $field.val().length;
		var limit = parseInt($counter.data('limit'), 10);
		$counter.text(len + ' / ' + limit);
		$counter.toggleClass('mrdw-push-char-over', len > limit);
	}

	$(document).on('input', '#mrdw-push-title, #mrdw-push-body', function() {
		updateCharCount($(this));
	});

	// Live preview update (iOS + Android)
	$(document).on('input', '#mrdw-push-title, #mrdw-push-body, #mrdw-push-image-url', function() {
		var title = $('#mrdw-push-title').val();
		var body = $('#mrdw-push-body').val();
		var imageUrl = $('#mrdw-push-image-url').val();
		var defaultTitle = 'Notification Title';
		var defaultBody = 'Notification body text will appear here...';
		var sanitizedUrl = imageUrl ? 'url("' + imageUrl.replace(/["()]/g, '') + '")' : '';
		var hasImage = imageUrl && /^https?:\/\/.+/i.test(imageUrl);

		// iOS preview
		$('#mrdw-push-preview-title').text(title || defaultTitle);
		$('#mrdw-push-preview-body').text(body || defaultBody);
		var $img = $('#mrdw-push-preview-image');
		if (hasImage) {
			$img.css('background-image', sanitizedUrl).show();
		} else {
			$img.hide().css('background-image', '');
		}

		// Android preview
		$('#mrdw-push-preview-title-android').text(title || defaultTitle);
		$('#mrdw-push-preview-body-android').text(body || defaultBody);
		var $imgAndroid = $('#mrdw-push-preview-image-android');
		if (hasImage) {
			$imgAndroid.css('background-image', sanitizedUrl).show();
		} else {
			$imgAndroid.hide().css('background-image', '');
		}
	});

	// Preview platform toggle (iOS / Android)
	$(document).on('click', '.mrdw-push-preview-toggle-btn', function() {
		var platform = $(this).data('preview');
		$('.mrdw-push-preview-toggle-btn').removeClass('active');
		$(this).addClass('active');
		$('.mrdw-push-preview-variant').hide();
		$('#mrdw-push-preview-' + platform).show();
	});

	// Fill Test Data button
	$(document).on('click', '#mrdw-push-fill-test', function() {
		$('#mrdw-push-title').val('Test Notification from MRDW_Push').trigger('input');
		$('#mrdw-push-body').val('This is a test push notification. If you received this, MRDW_Push is working correctly!').trigger('input');
		$('#mrdw-push-image-url').val('https://placehold.co/1200x630/0FACED/white?text=MRDW_Push+Test').trigger('input');
		$('input[name="target_type"][value="dev"]').prop('checked', true).trigger('change');
	});

	// WordPress Media Library picker
	$(document).on('click', '#mrdw-push-choose-image', function(e) {
		e.preventDefault();

		if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
			console.warn('MRDW_Push: wp.media not available. Media library scripts may not be loaded.');
			return;
		}

		var frame = wp.media({
			title: mrdwPush.strings.choose_image || 'Choose Image',
			button: { text: mrdwPush.strings.use_image || 'Use this image' },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			$('#mrdw-push-image-url').val(attachment.url).trigger('input');
		});

		frame.open();
	});

	// ── History Page ───────────────────────────────────────────

	// Delete All History
	$(document).on('click', '#mrdw-push-delete-all-history', function() {
		var $btn = $(this);

		mrdwPushConfirm(mrdwPush.strings.confirm_delete_all, function() {
			$btn.prop('disabled', true).text(mrdwPush.strings.deleting);

			$.post(mrdwPush.ajax_url, {
				action: 'mrdw_push_delete_all_notifications',
				nonce: mrdwPush.nonce
			}, function(response) {
				if (response.success) {
					location.reload();
				} else {
					mrdwPushNotice(response.data.message, 'error');
					$btn.prop('disabled', false).text(mrdwPush.strings.delete_all_history);
				}
			}).fail(function() {
				mrdwPushNotice(mrdwPush.strings.error, 'error');
				$btn.prop('disabled', false).text(mrdwPush.strings.delete_all_history);
			});
		});
	});

	// ── Dashboard Clear All Recent ────────────────────────────

	$(document).on('click', '#mrdw-push-clear-recent', function() {
		var $btn = $(this);

		mrdwPushConfirm(mrdwPush.strings.confirm_delete_all, function() {
			$btn.prop('disabled', true);

			$.post(mrdwPush.ajax_url, {
				action: 'mrdw_push_delete_all_notifications',
				nonce: mrdwPush.nonce
			}, function(response) {
				if (response.success) {
					location.reload();
				} else {
					mrdwPushNotice(response.data.message, 'error');
					$btn.prop('disabled', false);
				}
			}).fail(function() {
				mrdwPushNotice(mrdwPush.strings.error, 'error');
				$btn.prop('disabled', false);
			});
		});
	});

	// ── Dashboard Chart ────────────────────────────────────────

	if (typeof Chart !== 'undefined' && typeof mrdw_push_chart !== 'undefined' && mrdw_push_chart.labels.length) {
		var ctx = document.getElementById('mrdw-push-chart');
		if (ctx) {
			// Format month labels (2025-01 → Jan)
			var monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
			var formattedLabels = mrdw_push_chart.labels.map(function(label) {
				var parts = label.split('-');
				return monthNames[parseInt(parts[1], 10) - 1] || label;
			});

			new Chart(ctx, {
				type: 'bar',
				data: {
					labels: formattedLabels,
					datasets: [
						{
							label: 'Successful',
							data: mrdw_push_chart.success,
							backgroundColor: 'rgba(34, 197, 94, 0.8)',
							hoverBackgroundColor: '#22c55e',
							borderRadius: 4,
							borderSkipped: false
						},
						{
							label: 'Failed',
							data: mrdw_push_chart.failed,
							backgroundColor: 'rgba(239, 68, 68, 0.8)',
							hoverBackgroundColor: '#ef4444',
							borderRadius: 4,
							borderSkipped: false
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					interaction: {
						intersect: false,
						mode: 'index'
					},
					plugins: {
						legend: {
							position: 'bottom',
							labels: {
								boxWidth: 10,
								boxHeight: 10,
								padding: 20,
								font: { size: 12, weight: '500' },
								usePointStyle: true,
								pointStyle: 'rectRounded'
							}
						},
						tooltip: {
							backgroundColor: '#1e293b',
							titleFont: { size: 13, weight: '600' },
							bodyFont: { size: 12 },
							padding: 10,
							cornerRadius: 6,
							displayColors: true,
							boxPadding: 4
						}
					},
					scales: {
						x: {
							stacked: true,
							grid: { display: false },
							border: { display: false },
							ticks: {
								font: { size: 11, weight: '500' },
								color: '#94a3b8'
							}
						},
						y: {
							stacked: true,
							beginAtZero: true,
							grid: {
								color: 'rgba(0, 0, 0, 0.04)',
								drawBorder: false
							},
							border: { display: false },
							ticks: {
								stepSize: 1,
								font: { size: 11 },
								color: '#94a3b8',
								padding: 8
							}
						}
					}
				}
			});
		}
	}

	// ── Devices Page ────────────────────────────────────────────

	// Edit device label
	$(document).on('click', '.mrdw-push-edit-device', function(e) {
		e.preventDefault();
		var id = $(this).data('id');
		var label = $(this).data('label') || '';
		$('#mrdw-push-edit-device-id').val(id);
		$('#mrdw-push-edit-label').val(label);
		$('#mrdw-push-edit-dialog').show();
	});

	$('#mrdw-push-edit-cancel').on('click', function() {
		$('#mrdw-push-edit-dialog').hide();
	});

	$('#mrdw-push-edit-save').on('click', function() {
		var id = $('#mrdw-push-edit-device-id').val();
		var label = $('#mrdw-push-edit-label').val();

		$.post(mrdwPush.ajax_url, {
			action: 'mrdw_push_update_device',
			nonce: mrdwPush.nonce,
			device_id: id,
			user_label: label
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				mrdwPushNotice(response.data.message, 'error');
			}
		});
	});

	// Toggle dev flag
	$(document).on('click', '.mrdw-push-toggle-dev', function(e) {
		e.preventDefault();
		var id = $(this).data('id');
		var isDev = $(this).data('dev');

		$.post(mrdwPush.ajax_url, {
			action: 'mrdw_push_toggle_dev',
			nonce: mrdwPush.nonce,
			device_id: id,
			is_dev: isDev
		}, function(response) {
			if (response.success) {
				location.reload();
			} else {
				mrdwPushNotice(response.data.message, 'error');
			}
		});
	});

	// Import CSV toggle
	$('#mrdw-push-import-btn').on('click', function() {
		$('#mrdw-push-import-form').toggle();
	});

	$('#mrdw-push-import-cancel').on('click', function() {
		$('#mrdw-push-import-form').hide();
	});

	// Import CSV upload
	$(document).on('submit', '#mrdw-push-import-upload', function(e) {
		e.preventDefault();
		var formData = new FormData(this);
		var $status = $('#mrdw-push-import-status');

		$.ajax({
			url: mrdwPush.rest_url + 'devices/import',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			headers: { 'X-WP-Nonce': mrdwPush.rest_nonce },
			success: function(response) {
				$status.text(response.message).removeClass('mrdw-push-status-error').addClass('mrdw-push-status-success');
				setTimeout(function() { location.reload(); }, 2000);
			},
			error: function(xhr) {
				var msg = xhr.responseJSON ? xhr.responseJSON.message : mrdwPush.strings.error;
				$status.text(msg).removeClass('mrdw-push-status-success').addClass('mrdw-push-status-error');
			}
		});
	});

	// ── Groups Page ─────────────────────────────────────────────

	// Show create group form
	$('#mrdw-push-create-group-btn').on('click', function() {
		$('#mrdw-push-group-form').show();
		$('#mrdw-push-group-form input[name="group_id"]').val('');
		$('#mrdw-push-group-name').val('');
		$('#mrdw-push-group-description').val('');
		$('#mrdw-push-group-form input[type="checkbox"]').prop('checked', false);
		updateDeviceSelectedCount();
	});

	// Update the selected device count display
	function updateDeviceSelectedCount() {
		var $counter = $('#mrdw-push-device-selected-count');
		if ( ! $counter.length ) return;
		var checked = $('#mrdw-push-group-save-form input[name="device_ids[]"]:checked').length;
		var total   = $('#mrdw-push-group-save-form input[name="device_ids[]"]').length;
		var template = mrdwPush.strings.selected_count || '%1$s / %2$s selected';
		$counter.text( template.replace('%1$s', checked).replace('%2$s', total) );
	}

	// Device search in groups
	$('#mrdw-push-group-device-search').on('input', function() {
		var query = $(this).val().toLowerCase();
		$('.mrdw-push-device-option').each(function() {
			var label = $(this).data('label') || '';
			$(this).toggle(label.indexOf(query) !== -1);
		});
	});

	// Select all / Deselect all
	$('#mrdw-push-select-all-devices').on('click', function() {
		$('.mrdw-push-device-option:visible input[type="checkbox"]').prop('checked', true);
		updateDeviceSelectedCount();
	});

	$('#mrdw-push-deselect-all-devices').on('click', function() {
		$('.mrdw-push-device-option:visible input[type="checkbox"]').prop('checked', false);
		updateDeviceSelectedCount();
	});

	// Update count on any checkbox change
	$(document).on('change', '#mrdw-push-group-save-form input[name="device_ids[]"]', function() {
		updateDeviceSelectedCount();
	});

	// Save group
	$(document).on('submit', '#mrdw-push-group-save-form', function(e) {
		e.preventDefault();

		var $status = $('#mrdw-push-group-status');
		var deviceIds = [];
		$('#mrdw-push-group-save-form input[name="device_ids[]"]:checked').each(function() {
			deviceIds.push($(this).val());
		});

		$.post(mrdwPush.ajax_url, {
			action: 'mrdw_push_save_group',
			nonce: mrdwPush.nonce,
			group_id: $('[name="group_id"]', this).val(),
			name: $('[name="name"]', this).val(),
			description: $('[name="description"]', this).val(),
			device_ids: deviceIds
		}, function(response) {
			if (response.success) {
				$status.text(response.data.message).removeClass('mrdw-push-status-error').addClass('mrdw-push-status-success');
				setTimeout(function() {
					window.location.href = mrdwPush.ajax_url.replace('admin-ajax.php', 'admin.php?page=mrdw-push-groups');
				}, 1000);
			} else {
				$status.text(response.data.message).removeClass('mrdw-push-status-success').addClass('mrdw-push-status-error');
			}
		});
	});

	// Delete group
	$(document).on('click', '.mrdw-push-delete-group', function() {
		var $btn = $(this);
		var id = $btn.data('id');

		mrdwPushConfirm(mrdwPush.strings.confirm_delete, function() {
			$.post(mrdwPush.ajax_url, {
				action: 'mrdw_push_delete_group',
				nonce: mrdwPush.nonce,
				group_id: id
			}, function(response) {
				if (response.success) {
					$btn.closest('tr').fadeOut(function() { $(this).remove(); });
				} else {
					mrdwPushNotice(response.data.message, 'error');
				}
			});
		});
	});

	// ── Meta Box (Quick Send) ───────────────────────────────────

	$(document).on('click', '.mrdw-push-quick-send-btn', function() {
		var $btn = $(this);
		var originalText = $btn.text();
		var $status = $btn.siblings('.mrdw-push-quick-send-status');
		var postId = $btn.data('post-id');
		var $metaBox = $btn.closest('.mrdw-push-meta-box');

		var targetType = $metaBox.find('[name="mrdw_push_quick_target"]:checked').val();
		var targetIds = null;

		if (targetType === 'group') {
			targetIds = [$metaBox.find('.mrdw-push-quick-group-select').val()];
		}

		$btn.prop('disabled', true).text(mrdwPush.strings.sending);
		$status.text('');

		$.post(mrdwPush.ajax_url, {
			action: 'mrdw_push_quick_send',
			nonce: mrdwPush.nonce,
			post_id: postId,
			title: $metaBox.find('.mrdw-push-quick-title').val(),
			body: $metaBox.find('.mrdw-push-quick-body').val(),
			target_type: targetType,
			target_ids: targetIds,
			include_image: $metaBox.find('[name="mrdw_push_include_image"]:checked').length ? '1' : '0'
		}, function(response) {
			if (response.success) {
				$status.text(response.data.message).removeClass('mrdw-push-status-error').addClass('mrdw-push-status-success');
			} else {
				$status.text(response.data.message).removeClass('mrdw-push-status-success').addClass('mrdw-push-status-error');
			}
			$btn.prop('disabled', false).text(originalText);
		}).fail(function() {
			$status.text(mrdwPush.strings.error).removeClass('mrdw-push-status-success').addClass('mrdw-push-status-error');
			$btn.prop('disabled', false).text(originalText);
		});
	});

})(jQuery);
