
jQuery(document).ready(function($){

  if($('.bbwp_fields_wp_color_picker' ).length >= 1){
    $('.bbwp_fields_wp_color_picker' ).wpColorPicker();
  }

  if($('.bbwp_fields_select_two_input' ).length >= 1){
    $('.bbwp_fields_select_two_input' ).select2();
  }


  if($('.bbwp_fields_tag_add_button' ).length >= 1){
    $("body").on("click", ".bbwp_fields_tag_add_button", function(){
      new_tag_value = $(this).parent().find("input.bbwp_fields_tags_add").val();
      if(new_tag_value && new_tag_value != "" && new_tag_value != " "){
        parent_input_field = $(this).parent().find("input.bbwp_fields_tags_add");
        new_tag = '<span>';
  
        input_name = parent_input_field.data("name")+'[]';
        data_repeater = '';
        data_name = '';
        if(parent_input_field.data('repeater-field') == 'yes'){
          data_repeater = ' data-repeater="yes"';
          data_name = ' data-name="'+parent_input_field.data("name")+'"';
        }
        new_tag += '<input class="regular-text" type="text" value="'+new_tag_value+'" name="'+input_name+'"'+data_repeater+data_name+' /><a href="#" class="bbwp_dismiss_icon bbwp_fields_delete_id">&nbsp;</a>';
        new_tag += '</span>';
        $(this).parent().find(".bbwp_fields_tag_check_list").append(new_tag);
        $(this).parent().find("input.bbwp_fields_tags_add").val('');
  
        if (typeof window.bbwp_repeater_adjust_item_keys === 'function'){
          window.bbwp_repeater_adjust_item_keys();
        }
  
      }
      return false;
    });
  }
  

  $("body").on("click", ".bbwp_fields_delete_id", function(){
		$(this).parent().remove();
		return false;
	});

});