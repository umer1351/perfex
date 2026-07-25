<script>
	(function($) {
		"use strict";

		init_selectpicker();
		$(".selectpicker").selectpicker('refresh');

		appValidateForm($("body").find('#add_edit_process'), {
			'process_name': 'required',
			'estimate_time': 'required',
			'person_in_charge[]': 'required',
			'order_number': 'required',
		}); 
	})(jQuery);

	var iconPickerInitialized = false;

	$(function(){
		'use strict';
		
		_formatMenuIconInput();

		$('.dd').nestable({
			maxDepth: 2
		});
		$('.icon-picker').iconpicker()
		.on({'iconpickerSetSourceValue': function(e){
			_formatMenuIconInput(e);
		}})
		iconPickerInitialized = true;
		
	});

	// + button for adding more attachments
	var addMoreAttachmentsInputKey = 1;
		//button for adding more attachment in project
	$("body").on('click', '.add_more_attachments_file', function() {
		'use strict';

		if ($(this).hasClass('disabled')) {
			return false;
		}

		var total_attachments = $('.attachments input[name*="file"]').length;
		if ($(this).data('max') && total_attachments >= $(this).data('max')) {
			return false;
		}

		var newattachment = $('.attachments').find('.attachment').eq(0).clone().appendTo('.attachments');
		newattachment.find('input').removeAttr('aria-describedby aria-invalid');
		newattachment.find('input').attr('name', 'file[' + addMoreAttachmentsInputKey + ']').val('');
		newattachment.find($.fn.appFormValidator.internal_options.error_element + '[id*="error"]').remove();
		newattachment.find('.' + $.fn.appFormValidator.internal_options.field_wrapper_class).removeClass($.fn.appFormValidator.internal_options.field_wrapper_error_class);
		newattachment.find('i').removeClass('fa-plus').addClass('fa-minus');
		newattachment.find('button').removeClass('add_more_attachments_file').addClass('remove_attachment_file').removeClass('btn-success').addClass('btn-danger');
		addMoreAttachmentsInputKey++;
	});

		// Remove attachment
	$("body").on('click', '.remove_attachment_file', function() {
		'use strict';

		$(this).parents('.attachment').remove();
	}); 

	$('input[name="duration_computation"]').on('click', function() {
		"use strict";  

		var duration_computation =$(this).val();
		if(duration_computation == 'compute_based_on_real_time'){
			$('.based_on_hide').removeClass('hide');
			$('.default_duration_hide').addClass('hide');

		}else if(duration_computation == 'set_duration_manually'){
			$('.based_on_hide').addClass('hide');
			$('.default_duration_hide').removeClass('hide');

		}
	});

	$('input[name="start_next_operation"]').on('click', function() {
		"use strict";  

		var processed =$(this).val();
		if(processed == 'once_some_products_are_processed'){
			$('.quantity_process_hide').removeClass('hide');
		}else if(processed == 'once_all_products_are_processed'){
			$('.quantity_process_hide').addClass('hide');

		}
	});


	function delete_process_attachment(wrapper, id) {
		'use strict';

		if (confirm_delete()) {
			$.get(admin_url + 'warranty_management/delete_process_attachment_file/' + id, function (response) {
				if (response.success == true) {
					$(wrapper).parents('.contract-attachment-wrapper').remove();

					var totalAttachmentsIndicator = $('.attachments-indicator');
					var totalAttachments = totalAttachmentsIndicator.text().trim();
					if(totalAttachments == 1) {
						totalAttachmentsIndicator.remove();
					} else {
						totalAttachmentsIndicator.text(totalAttachments-1);
					}
				} else {
					alert_float('danger', response.message);
				}
			}, 'json');
		}
		return false;
	}

	function preview_file(invoker){
		'use strict';
		$('#appointmentModal').modal('hide');

		var id = $(invoker).attr('id');
		var rel_id = $(invoker).attr('rel_id');
		view_file_file(id, rel_id);
	}

	function view_file_file(id, rel_id) {   
		'use strict';

		$('#contract_file_data').empty();
		$("#contract_file_data").load(admin_url + 'warranty_management/wm_view_attachment_file/' + id + '/' + rel_id+'/'+'wm_process', function(response, status, xhr) {
			if (status == "error") {
				alert_float('danger', xhr.statusText);
			}
		});
	}

	function warranty_process_init_editor(selector, settings) {

		"use strict";
		if(tinymce.majorVersion + '.' + tinymce.minorVersion == '6.8.3'){
			tinymce.remove(selector);
			
			initPurchaseEditor(selector);
		}else{
			tinymce.remove(selector);

			selector = typeof(selector) == 'undefined' ? '.tinymce' : selector;
			var _editor_selector_check = $(selector);

			if (_editor_selector_check.length === 0) { return; }

			$.each(_editor_selector_check, function() {
				if ($(this).hasClass('tinymce-manual')) {
					$(this).removeClass('tinymce');
				}
			});

	// Original settings
			var _settings = {
				branding: false,
				selector: selector,
				browser_spellcheck: true,
				height: 400,
				theme: 'modern',
				skin: 'perfex',
				language: app.tinymce_lang,
				relative_urls: false,
				inline_styles: true,
				verify_html: false,
				cleanup: false,
				autoresize_bottom_margin: 25,
				valid_elements: '+*[*]',
				valid_children: "+body[style], +style[type]",
				apply_source_formatting: false,
				remove_script_host: false,
				removed_menuitems: 'newdocument restoredraft',
				forced_root_block: false,
				autosave_restore_when_empty: false,
				fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
				setup: function(ed) {
			// Default fontsize is 12
					ed.on('init', function() {
						this.getDoc().body.style.fontSize = '12pt';
					});
				},
				table_default_styles: {
			// Default all tables width 100%
					width: '100%',
				},
				plugins: [
					'advlist autoresize autosave lists link image print hr codesample',
					'visualblocks code fullscreen',
					'media save table contextmenu',
					'paste textcolor colorpicker'
					],
				toolbar1: 'fontselect fontsizeselect | forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | image link | bullist numlist | restoredraft',
				file_browser_callback: elFinderBrowser,
			};

	// Add the rtl to the settings if is true
			isRTL == 'true' ? _settings.directionality = 'rtl' : '';
			isRTL == 'true' ? _settings.plugins[0] += ' directionality' : '';

	// Possible settings passed to be overwrited or added
			if (typeof(settings) != 'undefined') {
				for (var key in settings) {
					if (key != 'append_plugins') {
						_settings[key] = settings[key];
					} else {
						_settings['plugins'].push(settings[key]);
					}
				}
			}

	// Init the editor
			var editor = tinymce.init(_settings);
			$(document).trigger('app.editor.initialized');

			return editor;
		}
	}


</script>