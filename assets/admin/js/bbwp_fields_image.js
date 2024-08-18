jQuery(document).ready(function($){

  var bbwp_fields_wp_uploader;
  var bbwp_fields_multiple_wp_uploader;

	 // single file uploader
   $("body").on("click", ".bbwp_fields_image_file_upload_button", function(e){
	$(this).closest('.woocommerce_variation').addClass('variation-needs-update');
			inputobject = $(this).parent().find("input[type='text']");
			e.preventDefault();

			// If the media frame already exists, reopen it.
			if (bbwp_fields_wp_uploader) {
				bbwp_fields_wp_uploader.open();
				return;
			}

			 // Create a new media frame
			bbwp_fields_wp_uploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose File',
				button: {
					text: 'Choose File'
				},
				multiple: false
			});

			//When a file is selected, grab the URL and set it as the text field's value
			bbwp_fields_wp_uploader.on('select', function() {
				attachment = bbwp_fields_wp_uploader.state().get('selection').first().toJSON();
				if(inputobject.parent().find(".bbwp_fields_image_single_preview").length >= 1){
					inputobject.parent().find(".bbwp_fields_image_single_preview").html('<span><img src="'+attachment.url+'" /><a href="#" class="bbwp_dismiss_icon">&nbsp;</a></span>');
				}
				inputobject.val(attachment.url);
			});

			//Open the uploader dialog
			bbwp_fields_wp_uploader.open();
			return false;
	});

	// multiple file uploader
  $("body").on("click", ".bbwp_fields_image_multiple_upload_button", function(e){
	$(this).closest('.woocommerce_variation').addClass('variation-needs-update');
		inputobject = $(this);
		e.preventDefault();

		if (bbwp_fields_multiple_wp_uploader) {
			bbwp_fields_multiple_wp_uploader.open();
			return;
		}

		bbwp_fields_multiple_wp_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Files',
			button: {
				text: 'Choose Files'
			},
			multiple: true
		});

		bbwp_fields_multiple_wp_uploader.on('select', function() {
			attachments = bbwp_fields_multiple_wp_uploader.state().get('selection').toJSON();
			if(attachments.constructor === Array){
				if(inputobject.parent().find(".bbwp_fields_image_multiple_preview").length >= 1){
					$.each(attachments, function( index, value ) {
            input_repeater_data = 'no';
            if(inputobject.data('repeater-field') == 'yes'){
              input_repeater_data = 'yes';
            }

            input_name = inputobject.attr("data-name");
            output_html = '<span><img src="'+value.url+'" /><a href="#" class="bbwp_fields_delete_id bbwp_dismiss_icon">&nbsp;</a><input type="hidden" data-name="'+input_name+'" name="'+input_name+'[]" value="'+value.url+'" data-repeater="'+input_repeater_data+'" /></span>';
						//console.log(value.id);
						inputobject.parent().find(".bbwp_fields_image_multiple_preview").prepend(output_html);
            if (typeof window.bbwp_repeater_adjust_item_keys === 'function'){
              window.bbwp_repeater_adjust_item_keys();
            }
					});
				}
			}
		});

		bbwp_fields_multiple_wp_uploader.open();
		return false;
	});

  $("body").on("click", ".bbwp_fields_image_single_preview a", function(){
	$(this).closest('.woocommerce_variation').addClass('variation-needs-update');
		$(this).parents(".bbwp_fields_image_single_preview").parent().find("input[type='text']").val("");
		$(this).parent().remove();
		return false;
	});

});
