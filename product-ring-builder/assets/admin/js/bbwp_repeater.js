
jQuery(document).ready(function($){



  if($(".bbwp_repeater_wrapper").length >= 1){

    $("body").on('click', '.bbwp_repeater_add_new_item_button', function(e){
      parent_container = $(this).parent(".bbwp_repeater_parent_wrapper");
      html_container = $(this).attr("data-target");
      parent_container.find("ul.bbwp_repeatable_fields").append('<li>'+$("."+html_container).html()+'</li>');
      
      if(parent_container.find("ul.bbwp_repeatable_fields li").length <= 0){
        parent_container.find("ul.bbwp_repeatable_fields li .bbwp_repeater_delete_item").hide();
      }else{
        parent_container.find("ul.bbwp_repeatable_fields li .bbwp_repeater_delete_item").show();
      }

      window.bbwp_repeater_adjust_item_keys();

      e.preventDefault();
      return false;
    });
  
  
    $("body").on('click', '.bbwp_repeater_delete_item', function(e){
      e.preventDefault();
      if($(this).parents("ul").children("li").length <= 0){
        $(this).parents("ul").find(".bbwp_repeater_delete_item").hide();
      }else{
        $(this).parents("ul").find(".bbwp_repeater_delete_item").show();
      }
      $(this).parents("li").remove();
      window.bbwp_repeater_adjust_item_keys();
      return false;
    });


    $("body").on('change', '.bbwp_fields_checkbox_repeater', function(e){
      default_value = 0;
      if($(this).is(':checked')){
        default_value = 1;
      }
      $(this).next().val(default_value);
      e.preventDefault();
      return false;
    });
    
    window.bbwp_repeater_adjust_item_keys = function(){
      if( $(".bbwp_repeatable_fields li").length >= 1){
        $(".bbwp_repeatable_fields li").each(function(index){
          $(this).find('input[data-repeater="yes"]').each(function(){
            input_name = $(this).data('name');
            input_name = input_name.replaceAll('[', '');
            input_name = input_name.replaceAll(']', '');
            $(this).attr('name', input_name+'['+index+'][]');
          });
          //
        });
      }
    }
    window.bbwp_repeater_adjust_item_keys();
    
  }

});
