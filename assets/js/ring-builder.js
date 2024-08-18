var otw_gcpb_current_post_request;

if (typeof mobileAndTabletCheck != 'function') {
function mobileAndTabletCheck() {
    let check = false;
    // @ts-expect-error copied
    (function(a) {if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true})(navigator.userAgent || navigator.vendor || window.opera)
    return check
}
}

if (typeof setup_three_d_viewer != 'function') {
    function setup_three_d_viewer(viewer) {
        // viewer.addPlugin(DebugPlugin);
        // Setting some quality
        if(!mobileAndTabletCheck()){
            viewer.renderer.displayCanvasScaling = 2;
        }else{
            viewer.renderer.displayCanvasScaling = Math.min(window.devicePixelRatio, 3);
        }
        // viewer.renderer.displayCanvasScaling = Math.min(window.devicePixelRatio, 3);
        // alert(window.devicePixelRatio);
        
        // viewer.renderer.displayCanvasScaling = Math.min(window.devicePixelRatio, 3);
        // console.log(viewer.renderer.displayCanvasScaling, 10)
        // console.log(Math.min(window.devicePixelRatio, 2));
        viewer.getPluginByType('AssetManagerLoadingBarPlugin').enabled = false;
        viewer.scene.addEventListener("addSceneObject", (e) => {
            // console.log("addSceneObject", e);
            jQuery('.gcpb-product-card.loading').removeClass('loading');
            jQuery('.gcpb-product-image-wrapper').removeClass('otw_container_loading');
            jQuery('webgi-viewer').removeClass('loading');
            viewer.scene.activeCamera.controls.enableZoom = false;
        });
        viewer.scene.addEventListener("environmentChanged", (e) => {
        // console.log("environmentChanged", e);
        });
    
        viewer.getManager().importer.addEventListener("importFile", (e) => {
            // console.log("importFile", e);
        });

        //
    }
}

if (typeof generateRandomString != 'function') {
    function generateRandomString(length) {
        var result           = '';
        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
          result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
      return result;
    }  
}

if (typeof gcpb_load_video != 'function') {
    function gcpb_load_video(video_object = false) {
        if(video_object){
            if(!(video_object.find("source").length >= 1) && video_object.data("video_url")){
                video_object.html('<source src="'+video_object.data("video_url")+'" type="video/mp4">');
            }
        }
        else{
            jQuery("video[data-video_url]").each(function(){
                if(!(jQuery(this).find("source").length >= 1) && jQuery(this).data("video_url")){
                    jQuery(this).html('<source src="'+jQuery(this).data("video_url")+'" type="video/mp4">');
                }
            });
        }
        
        // console.log(jQuery("video[data-video_url]").length);
    }
}

if (typeof dom_setup_three_d_viewer != 'function') {
    function dom_setup_three_d_viewer() {
        jQuery("webgi-viewer").each(function(){
            jQuery(this)[0].addEventListener("initialized", () => {
                if(typeof jQuery(this)[0].viewer != 'undefined'){
                    // console.log('three_d_viewer');
                    // console.log(jQuery(this)[0].viewer);
                    setup_three_d_viewer(jQuery(this)[0].viewer);
                }
            });
        });
    }
}

if (typeof dispose_all_webgi_viewers != 'function') {
    function dispose_all_webgi_viewers() {
        jQuery("webgi-viewer").remove();
    }
}

if (typeof scroll_to_specific_element != 'function') {
    function scroll_to_specific_element(element) {
        let offset = element.offset();
        // let offset_x = offset.left - jQuery(window).scrollLeft();
        // let offset_y = offset.top - jQuery(window).scrollTop();
        // window.scroll(offset_x, offset_y);
        jQuery("html").animate({ scrollTop: offset.top-200}, "slow");
        
    }
}

if (typeof scroll_current_element_position != 'function') {
    function scroll_current_element_position(elm) {
        console.log(elm);
        setTimeout(function() {
            console.log(2);
            console.log(elm);
            var yPos = elm.offset().top;
            jQuery("html").animate({ scrollTop: yPos-80}, "slow");
        
        }, 600);
    }
}

if (typeof get_selected_setting_filter_mobile != 'function') {
    function get_selected_setting_filter_mobile() {

        let new_mobile_show_filter_html = '';
        if(jQuery(".gcpb-shape-filter-container .gcpb-custom-filter-button.selected").length >= 1){
            new_mobile_show_filter_html += '<span class="gcpb-active-shape">'+jQuery(".gcpb-shape-filter-container .gcpb-custom-filter-button.selected").find(".gcpb-custom-filter-button-popup-text").text()+'</span>';
        }
        if(jQuery(".gcpb-eo_metal_attr-filter-container .gcpb-custom-filter-button.selected").length >= 1){
            if(new_mobile_show_filter_html){
                new_mobile_show_filter_html += ' | ';
            }
            let selected_eo_metal_text = jQuery(".gcpb-eo_metal_attr-filter-container .gcpb-custom-filter-button.selected").find(".gcpb-custom-filter-button-popup-text").text();
            if(selected_eo_metal_text){
                selected_eo_metal_text = selected_eo_metal_text.replace("14K", "");
                selected_eo_metal_text = selected_eo_metal_text.replace("18K", "");
                // selected_eo_metal_text = selected_eo_metal_text.split(/(\s)/);
                // if(selected_eo_metal_text.length >= 1)
                //     selected_eo_metal_text = selected_eo_metal_text[0];
            }
            console.log(selected_eo_metal_text);
            new_mobile_show_filter_html += '<span class="gcpb-active-metal">'+selected_eo_metal_text+'</span>';
        }
        if(jQuery(".gcpb-ring-style-filter-container .gcpb-custom-filter-button.selected").length >= 1){
            if(new_mobile_show_filter_html){
                new_mobile_show_filter_html += ' | ';
            }
            new_mobile_show_filter_html += '<span class="gcpb-active-style">'+jQuery(".gcpb-ring-style-filter-container .gcpb-custom-filter-button.selected").find(".gcpb-custom-filter-button-popup-text").text()+'</span>';
        }
        if(new_mobile_show_filter_html){
            new_mobile_show_filter_html = 'Shown with: ' + new_mobile_show_filter_html;
            jQuery(".gcpb-mobile-active-filters").html(new_mobile_show_filter_html);
        }else{
            jQuery(".gcpb-mobile-active-filters").html('');
        }
    }
}

if (typeof get_selected_diamond_filter_mobile != 'function') {
    function get_selected_diamond_filter_mobile() {

        let new_mobile_show_filter_html = '';
        if(jQuery(".gcpb-shape-filter .gcpb-custom-filter-button.selected").length >= 1){
            new_mobile_show_filter_html += '<span class="gcpb-active-shape">'+jQuery(".gcpb-shape-filter .gcpb-custom-filter-button.selected").find(".gcpb-custom-filter-button-popup-text").text()+'</span>';
        }
        if(new_mobile_show_filter_html){
            new_mobile_show_filter_html = new_mobile_show_filter_html;
            jQuery(".gcpb-mobile-active-filter-shape").html(new_mobile_show_filter_html);
        }else{
            jQuery(".gcpb-mobile-active-filter-shape").html('');
        }
        
        new_mobile_show_filter_html = '';
        if(jQuery('input#gcpb-carat-filter-slider').hasClass('active_diamond_search')){
            // if(new_mobile_show_filter_html)
            //     new_mobile_show_filter_html += ' | ';
            
            new_mobile_show_filter_html += '<span class="gcpb-active-carat"> '+jQuery('input#gcpb-carat-filter-slider').val()+'</span>';
        }
        if(new_mobile_show_filter_html){
            // new_mobile_show_filter_html = new_mobile_show_filter_html;
            jQuery(".gcpb-mobile-active-filter-carat").html(new_mobile_show_filter_html);
        }else{
            jQuery(".gcpb-mobile-active-filter-carat").html('');
        }


        new_mobile_show_filter_html = '';
        if(jQuery('input#gcpb-price-filter-slider').hasClass('active_diamond_search')){
            // if(new_mobile_show_filter_html)
            //     new_mobile_show_filter_html += ' | ';
            
            new_mobile_show_filter_html += '<span class="gcpb-active-price"> $'+jQuery('input#gcpb-price-filter-slider').val()+'</span>';
        }
        if(new_mobile_show_filter_html){
            // new_mobile_show_filter_html = new_mobile_show_filter_html;
            jQuery(".gcpb-mobile-active-filter-price").html(new_mobile_show_filter_html);
        }else{
            jQuery(".gcpb-mobile-active-filter-price").html('');
        }


        new_mobile_show_filter_html = '';
        let color_string = get_selected_color_data();
        if(color_string){
            // if(new_mobile_show_filter_html)
            //     new_mobile_show_filter_html += ' | ';
            new_mobile_show_filter_html += color_string;
            new_mobile_show_filter_html = new_mobile_show_filter_html.replace("&color=", "");
        }
        if(new_mobile_show_filter_html){
            // new_mobile_show_filter_html = new_mobile_show_filter_html;
            jQuery(".gcpb-mobile-active-filter-color").html(new_mobile_show_filter_html);
        }else{
            jQuery(".gcpb-mobile-active-filter-color").html('');
        }

        new_mobile_show_filter_html = '';
        let clarity_string = get_selected_clarity_data();
        if(clarity_string){
            // if(new_mobile_show_filter_html)
            //     new_mobile_show_filter_html += ' | ';
            new_mobile_show_filter_html += clarity_string;
            new_mobile_show_filter_html = new_mobile_show_filter_html.replace("&clarity=", "");
        }
        if(new_mobile_show_filter_html){
            // new_mobile_show_filter_html = new_mobile_show_filter_html;
            jQuery(".gcpb-mobile-active-filter-clarity").html(new_mobile_show_filter_html);
        }else{
            jQuery(".gcpb-mobile-active-filter-clarity").html('');
        }
        
        

        
    }
}

if (typeof get_selected_single_setting_filter_mobile != 'function') {
    function get_selected_single_setting_filter_mobile() {

        jQuery(".gcpb-product-content-wrapper .gcpb-product-customization .gcpb-available-container").each(function(){
            let new_mobile_show_filter_html = '';
            if(jQuery(this).find(".gcpb-swatch-box.selected").length >= 1){
                let attribute_name = jQuery(this).find("ul").data('attribute');
                if(attribute_name == 'shape'){
                    attribute_name = 'Shape';
                }else if(attribute_name == 'eo_metal_attr'){
                    attribute_name = "Metal";
                }

                new_mobile_show_filter_html += '<div class="current_active_variation_label_mobile gcpb-mobile-only"><span class="attribute_name">'+attribute_name+': </span><span>'+jQuery(this).find(".gcpb-swatch-box.selected").find(".gcpb-available-title").text()+'</span></div>';
                jQuery(this).find(".current_active_variation_label_mobile").remove();
                jQuery(this).prepend(new_mobile_show_filter_html);
            }
        });
       
    }
}

if (typeof get_selected_clarity_data != 'function') {
    function get_selected_clarity_data() {
        var output = '';
        if(jQuery('.gcpb-clarity-filter input.active_diamond_search').length >= 1){
            var clarity_counter = 1;
            var clarity_from = '';
            var clarity_to = '';
            jQuery('.gcpb-clarity-filter input.active_diamond_search').each(function(index){
                if(jQuery(this).is(':checked')){
                    if(clarity_counter == 1){
                        clarity_from = jQuery(this).siblings('label').text();
                    }
                    clarity_to = jQuery(this).siblings('label').text();
                    clarity_counter++;
                }
            });
            if(clarity_from)
                output += "&clarity="+clarity_from;
            if(clarity_to && clarity_from)
            output += "-"+clarity_to;
        }
        return output;
    }
}

if (typeof get_selected_color_data != 'function') {
    function get_selected_color_data() {
        var output = '';
        if(jQuery('.gcpb-color-filter input.active_diamond_search').length >= 1){
            var color_counter = 1;
            var color_from = '';
            var color_to = '';
            jQuery('.gcpb-color-filter input.active_diamond_search').each(function(index){
                if(jQuery(this).is(':checked')){
                    if(color_counter == 1){
                        color_from = jQuery(this).siblings('label').text();
                        
                    }
                    color_to = jQuery(this).siblings('label').text();
                    color_counter++;
                }
            });
            if(color_from)
                output += "&color="+color_from;
            if(color_to && color_from)
                output += "-"+color_to;
        }
        return output;
    }
}

if (typeof create_selected_color_data != 'function') {
    function create_selected_color_data(color_from, color_to = '') {
        if(jQuery('.gcpb-color-filter input').length >= 1){
            let color_from_found = false;
            jQuery('.gcpb-color-filter input').each(function(index){
                if(jQuery(this).attr('id') == 'color-'+color_from.toLowerCase()){
                    jQuery(this).prop('checked', true);
                    color_from_found = true;
                }

                if(color_from_found && color_to){
                    jQuery(this).prop('checked', true);
                    if(jQuery(this).attr('id') == 'color-'+color_to.toLowerCase()){
                        color_from_found = false;
                    }
                }
                
            });
        }
    }
}

if (typeof create_selected_clarity_data != 'function') {
    function create_selected_clarity_data(clarity_from, clarity_to = '') {
        if(jQuery('.gcpb-clarity-filter input').length >= 1){
            let clarity_from_found = false;
            jQuery('.gcpb-clarity-filter input').each(function(index){
                if(jQuery(this).attr('id') == 'clarity-'+clarity_from.toLowerCase()){
                    jQuery(this).prop('checked', true);
                    clarity_from_found = true;
                }

                if(clarity_from_found && clarity_to){
                    jQuery(this).prop('checked', true);
                    if(jQuery(this).attr('id') == 'clarity-'+clarity_to.toLowerCase()){
                        clarity_from_found = false;
                    }
                }
                
            });
        }
    }
}

jQuery(document).ready(function($){
    dom_setup_three_d_viewer();
});

jQuery(document).ready(function($){
    // gcpb_new_products_loaded();
    if (!otw_woo_ring_builder.wp_is_mobile) {
        gcpb_load_video();
    }

    get_selected_setting_filter_mobile();

    get_selected_single_setting_filter_mobile();

    get_selected_diamond_filter_mobile();

    var query_string;

    if($('.js-product-archive-grid').length > 0) {
        // console.log(otw_woo_ring_builder);
        if(jQuery('body').hasClass('page-id-45109') || jQuery('body').hasClass('parent-pageid-45109')){
            // $('.gcpb-shape-filter-container button[data-filter-value="'+otw_woo_ring_builder.diamond_shape+'"]').addClass('selected');
        }else{
            if(typeof otw_woo_ring_builder.diamond_shape != undefined && otw_woo_ring_builder.diamond_shape){
                $('.gcpb-shape-filter-container button[data-filter-value="'+otw_woo_ring_builder.diamond_shape+'"]').addClass('selected');
            }
        }
        

        if(typeof otw_woo_ring_builder.attribute_slug != undefined && otw_woo_ring_builder.attribute_slug && otw_woo_ring_builder.attribute_slug == 'pa_shape'){
            $('.gcpb-shape-filter-container').hide();
        }
        
        if(typeof otw_woo_ring_builder.attribute_slug != undefined && otw_woo_ring_builder.attribute_slug && otw_woo_ring_builder.attribute_slug == 'pa_ring-style')
            $('.gcpb-ring-style-filter-container').hide();
        
        
        trigger_product_filter(query_string, false);
    }

    function build_filter_query(query_params = {}) {

        var query_string = 'filter=1';
        if(typeof ajax_ring_obj.current_page_id != undefined && ajax_ring_obj.current_page_id){
            query_string += '&current_page_id='+ajax_ring_obj.current_page_id;
        }
        
        var params = {
            sort: 'best-selling',
            paged: 1
        };
       
        if( typeof ajax_ring_obj.gcpb_products_per_page != 'undefined' && ajax_ring_obj.gcpb_products_per_page != '') {
            params.limit = ajax_ring_obj.gcpb_products_per_page;
        }

        if(typeof otw_woo_ring_builder.attribute_slug != undefined && otw_woo_ring_builder.attribute_slug){
            if(typeof otw_woo_ring_builder.attribute_term != undefined && otw_woo_ring_builder.attribute_term)
                $('.gcpb-filters-wrapper button[data-filter-value="'+otw_woo_ring_builder.attribute_term+'"]').addClass('selected');
            
        }

        var settings_args = $('.js-product-archive-grid').data('settings');

        if( typeof settings_args != 'undefined' && settings_args != '' ) {
            params.settings_args = settings_args;
        }
        console.log(query_string);
        $('.gcpb-custom-filter-button.selected').each(function(){
            var value = $(this).data('filter-value');
            var name = $(this).data('filter-name');
            query_string += '&'+name+'='+value;
        });
        console.log(query_string);
        var sort = $('.gcpb-select-options .gcpb-select-option.selected').data('value');

        if( typeof query_params.sort != 'undefined' && query_params.sort != '' ) {
            params.sort = query_params.sort;
        } 

        if( typeof query_params.paged != 'undefined' && query_params.paged != '' ) {
            params.paged = query_params.paged;
        } 
 
        params = $.param(params);
        query_string += '&'+params;

        return query_string;
    }

    function trigger_product_filter( add_query_params, filter_action = false ) {
 
        get_selected_setting_filter_mobile();
        var query_string = build_filter_query(add_query_params);
        
        console.log(query_string);
        if(jQuery('body').hasClass('page-id-45109') || jQuery('body').hasClass('parent-pageid-45109')){
            if(getLocalStorage('eo_metal_attr') && query_string.indexOf('eo_metal_attr') === -1){
                query_string += '&eo_metal_attr='+getLocalStorage('eo_metal_attr');
                jQuery(".gcpb-eo_metal_attr-filter-container .gcpb-custom-filter-button[data-filter-value="+getLocalStorage('eo_metal_attr')+"]").addClass('selected');
            }
            if(getLocalStorage('ring-style') && query_string.indexOf('ring-style') === -1){
                // query_string += '&ring-style='+getLocalStorage('ring-style');
                // jQuery(".gcpb-ring-style-filter-container .gcpb-custom-filter-button[data-filter-value="+getLocalStorage('ring-style')+"]").addClass('selected');
            }
            if(getLocalStorage('shape') && query_string.indexOf('shape') === -1){
                query_string += '&shape='+getLocalStorage('shape');
                jQuery(".gcpb-shape-filter-container .gcpb-custom-filter-button[data-filter-value="+getLocalStorage('shape')+"]").addClass('selected');
            }
        }
        $.ajax({
            type: 'POST',
            url: ajax_ring_obj.ajaxurl,
            async:   true,
            data: { 
                'action': 'fetch_products', 
                'query_string': query_string,
            },
            beforeSend: function() {
                if(filter_action) {
                    $('body').addClass('gcpb-loading');
                }
            },
            success: function(data){
                var result = JSON.parse(data);
                // console.log(result);

                $('.js-product-archive-grid').removeClass('gcpb-preload');
                $('body').removeClass('gcpb-loading');
                $('.product-loader').remove();

                if(filter_action) {
                    $('.js-product-archive-grid').empty();
                }

                if(typeof result.product_data != 'undefined') {
                    $('.js-product-archive-grid').append(result.product_data);
                }

                if(typeof result.product_loader != 'undefined' && result.product_loader != null) {
                    $(result.product_loader).insertAfter('.js-product-archive-grid');
                }

                if (typeof add_horizontal_scroll_bar_on_desktop == 'function') {
                    add_horizontal_scroll_bar_on_desktop();
                }
                if (typeof gcpb_new_products_loaded == 'function') 
                    gcpb_new_products_loaded();

                if(filter_action){
                    // scroll_current_element_position(jQuery(".gcpb-card-toggle-icon"));
                    scroll_to_element = jQuery('.gcpb-product-archive-grid');
                    if(jQuery(window).scrollTop() > scroll_to_element.offset().top-200){
                        scroll_to_specific_element(scroll_to_element);
                    }
                    
                }
            },
            error: function(xhr) { // if error occured
                alert("Error occured.please try again");
            },
        });
    }

    $(document).on('click', '.gcpb-custom-filter-button', function(event){

        setLocalStorage($(this).data('filter-name'), $(this).data('filter-value'));

        $(this).addClass('current_clicked_item');
        event.preventDefault();

        var $this = $(this);
        var $attr_title = $this.data('filter-display-value');
        $this.parents('.gcpb-filter').siblings('.gcpb-filter-title').find('.gcpb-selected-item').text($attr_title);
 
        $this.parents('.gcpb-filter').find('.gcpb-custom-filter-button:not(.current_clicked_item)').removeClass('selected');
        
        $this.toggleClass('selected');
        $this.removeClass('current_clicked_item');
        if($(this).hasClass('gcpb-custom-filter-button-stone')){
            $(".show_more_diamond_pagination").data('page_number', 1);
            $(".show_more_diamond_pagination").data('page_number_vdb', 1);
            $(".show_more_diamond_pagination").data('page_number_nivoda', 1);
            trigger_stone_filter(query_string, true);
            // console.log('stone filter');
        }else{
            trigger_product_filter(query_string, true);
        }
        

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
        // console.log(query_string);
    });

    /* specific product variation loader inside loop  */
    function get_single_product_variation_data($this, variation_query) {
        // console.log(variation_query);
        $.ajax({
            type: 'POST',
            // dataType: 'json',
            url: ajax_ring_obj.ajaxurl,
            async:   true,
            data: { 
                'action': 'fetch_product_single_variation', 
                'variation_query': variation_query,
            },
            beforeSend: function() {
                $this.parents('.gcpb-product-card').addClass('loading');
            },
            success: function(data){
                
                var result = JSON.parse(data);
                $this.parents('.gcpb-product-card').removeClass('loading');
                
                $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images img').remove();
                $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images video').remove();
                $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images .gcpb_swiper').remove();
                $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images webgi-viewer').remove();
                
                
                // $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images img').remove();
                // console.log(result);
                if( result.image_org != '') {
                    $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images').prepend(result.image_org);
                }

                if( typeof result.variation_id != 'undefined' && result.variation_id != '') {
                    // console.log(result.variation_id);
                    $this.parents('.gcpb-product-card').attr('data-variation-id', result.variation_id);
                }
                
                if(typeof result.image_hover != 'undefined' && result.image_hover) {

                    if($this.parents('.gcpb-product-card').find('.gcpb-prod-card__images webgi-viewer').attr('src') == result.image_hover){
                        $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images webgi-viewer').remove();
                    }

                    $this.parents('.gcpb-product-card').data('3d-model', result.image_hover);
                    // gcpb_product_loop_data.display_3d_model($this.parents('.gcpb-product-card'));
                    // console.log($this.parents('.gcpb-product-card').data('3d-model'));
                    // $this.parents('.gcpb-product-card').removeClass('loading');
                    // $("#viewer_id_test").attr('src', result.image_hover);
                }

                if(getLocalStorage('gcpb_selected_media_tab') != 'webgi-viewer'){
                    $this.parents('.gcpb-product-card').removeClass('loading');
                }
                

                if(typeof result.gallery_images != 'undefined' && typeof result.variation_video != 'undefined')
                    $this.parents('.gcpb-product-card').find('.gcpb_prod_card_hidden_media_data').empty().append(result.gallery_images+result.variation_video);
                

                if( result.price != '') {
                    $this.parents('.gcpb-product-card').find('.gcpb-product-price').empty().append(result.price);
                }
                // console.log(result.product_link);
                if( result.product_link != '') {
                    $this.parents('.gcpb-product-card').find('.js-product-link').attr('href', result.product_link);
                }

                
                
                if($this.parents('.gcpb-product-card').find('.otw_select_diamond_dynamic_url').length >= 1){
                    let next_step_button_object = $this.parents('.gcpb-product-card').find('.otw_select_diamond_dynamic_url');
                    next_step_button_object.data('variation_id', result.variation_id);

                    if(typeof otw_woo_ring_builder.diamond_shape != undefined && otw_woo_ring_builder.diamond_shape){
                        let current_selected_variation = $this.parents('.gcpb-product-card').find(".gcpb-available-wrapper .gcpb-swatch-box.selected").data('value');
                        if(current_selected_variation == otw_woo_ring_builder.diamond_shape){
                            next_step_button_object.html(next_step_button_object.data('link_text_complete'));
                            next_step_button_object.data('stone-url', next_step_button_object.data('complete-url-link'));
                        }else{
                            next_step_button_object.html(next_step_button_object.data('link_text'));
                            next_step_button_object.data('stone-url', next_step_button_object.data('stone-url-link'));
                        }
                        next_step_button_object.attr('href', next_step_button_object.data('stone-url'));
                        // $('.gcpb-shape-filter-container button[data-filter-value="'+otw_woo_ring_builder.diamond_shape+'"]').addClass('selected');
                    }
                }

                
                gcpb_utility_functions.click_on_selected_tab($this.parents('.gcpb-product-card'));
                /**/
                // $this.parents('.gcpb-product-card').removeClass('loading');
                
            },
            error: function(xhr) {
                alert("Error occured.please try again");
            },
        });
    }

    $(document).on('click', '.gcpb-product-card .gcpb-swatch-box', function(event){
        // console.log('gcpb-product-card click');
        // if(otw_woo_ring_builder.wp_is_mobile)
        //     return false;
        event.preventDefault();

        var $this = $(this);
        
        if($this.hasClass('selected')) {
            $this.removeClass('selected');
        } else {
            $this.addClass('selected').siblings().removeClass('selected');
        }
        
        var $product_card = $this.parents('.gcpb-product-card');
        var $attributes = $product_card.find('.gcpb-swatch-box.selected');
        
        var $product_id = $product_card.data('product-id');
        var query_string = 'variation_query=1&product_id='+$product_id;

        $($attributes).each(function(){
 
            var $value = $(this).data('value');
            var $attribute = $(this).parents('.js-gcpb-loop-attribute').data('attribute');
    
            if( typeof $value != 'undefined' && $value != '') {
                query_string += '&'+$attribute+'='+$value;
            }
        });

        // console.log(query_string);//
       
        get_single_product_variation_data($this, query_string);
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
        
    });

    $(document).on('mouseleave','.gcpb-product-card', function(){
        if(otw_woo_ring_builder.wp_is_mobile)
            return false;
        $(this).removeClass('opened');
        // $(this).removeClass('card-opened');
        $('body').removeClass("card-opened");
        $('.gcpb-product-card').find('iframe').remove();
        $(this).find(".gcpb_swiper").remove();
        // gcpb_product_loop_data.click_on_selected_tab($(this));
        $(this).trigger('focusout');
        $(this).trigger('blur');
        $(this).find(".gcpb-prod-card__images video").remove();
        // jQuery("video").remove();
        // $(this).find(".gcpb-prod-card__images").trigger('focusout');
        // $('body').trigger('click');
        // $(this).find(".gcpb-media-tabs-product-loop .tab-3d").trigger('click');
    });
    
    /* laod 3d model image on hover */
    $(document).on('mouseleave','.gcpb-prod-card__images:not(.gcpb-prod-card__images.model-loaded,.gcpb-prod-card__images.model-loading)', function(){
        if (otw_woo_ring_builder.wp_is_mobile) {
            return false;
        }

        $(this).removeClass('gcpb-mouseover');
    });

    if(!otw_woo_ring_builder.wp_is_mobile){
        $(document).on('mouseover','.gcpb-product-card-setting', function(event){
            let event_object = $(event.target);
            // console.log(event_object);
            if($(this).hasClass("gcpb-product-card-setting")){
                var parent_li_object = $(this);
            }
            else{
                var parent_li_object = $(this).parents(".gcpb-product-card");
            }
            var $this = $(this).find(".gcpb-prod-card__images");
            $this.addClass('gcpb-mouseover');
            
            if($this.hasClass('gcpb-mouseover')){
                setLocalStorage('gcpb_selected_media_tab', 'gallery');
                
                var $product_card = $this.parents('.gcpb-product-card');
        
                let three_d_model = $product_card.data('3d-model');
                if(three_d_model){
                    // console.log($this.find("webgi-viewer").length);
                    if(!($this.find("webgi-viewer").length >= 1)){
                        dispose_all_webgi_viewers();
                    }
                    if(!parent_li_object.hasClass('opened'))
                        gcpb_utility_functions.click_on_selected_tab($product_card);
                }else{
                    $this.parents('.gcpb-product-card').addClass('gcpb_no_canvas');
                }
            }
            parent_li_object.addClass('opened');
        });
    }
    
    $(document).on('click','.gcpb-product-card-setting:not(.gcpb-product-card-setting.opened)', function(event){

        
        // if(otw_woo_ring_builder.wp_is_mobile)
        // return false;
        let event_object = $(event.target);
        if($(this).hasClass("gcpb-product-card-setting")){
            var parent_li_object = $(this);
        }
        else{
            var parent_li_object = $(this).parents(".gcpb-product-card");
        }

        if(parent_li_object.hasClass('opened')){
            // event.preventDefault();
            // event.stopPropagation();
            // event.stopImmediatePropagation();
            // return false;
        }
        // console.log($(this));
        // console.log(parent_li_object);
        
        // if((event_object.hasClass('gcpb-swatch-box') || event_object.parent().hasClass('gcpb-swatch-box')) && otw_woo_ring_builder.wp_is_mobile)
        //     return false;

        // console.log(typeof event);
        // console.log(event);
        // console.log('gcpb-product-card mouse hover');
        // if(otw_woo_ring_builder.wp_is_mobile)
        //     return false;
        var $this = $(this).find(".gcpb-prod-card__images");
        // if(!otw_woo_ring_builder.wp_is_mobile)
            $this.addClass('gcpb-mouseover');
        
        // $(this).parents(".gcpb-product-card").addClass('opened');
        $('.gcpb-product-card-setting').removeClass('opened');
        // $('.gcpb-product-card-setting').removeClass('card-opened');
        parent_li_object.addClass('opened');
        // parent_li_object.addClass('card-opened');
        if(otw_woo_ring_builder.wp_is_mobile){
            $('body').addClass("card-opened");
        }
        
        
        // if(otw_woo_ring_builder.wp_is_mobile && parent_li_object.find(".gcpb-card-toggle-icon").length >= 1)
        // {
            
        //     scroll_current_element_position(parent_li_object.find(".gcpb-card-toggle-icon"));
        // }
        

        // setTimeout(function() {
            if($this.hasClass('gcpb-mouseover')){

                setLocalStorage('gcpb_selected_media_tab', 'gallery');
                
                // $this.addClass('model-loading');
                var $product_card = $this.parents('.gcpb-product-card');
        
                var variation_id = $product_card.data('variation-id');
                var product_id = $product_card.data('product-id');
                let three_d_model = $product_card.data('3d-model');

                
                

                if(three_d_model){
                    // console.log($this.find("webgi-viewer").length);
                    if(!($this.find("webgi-viewer").length >= 1)){
                        dispose_all_webgi_viewers();
                        //find("webgi-viewer")
                        // console.log(three_d_model);
                        /*$this.parents('.gcpb-product-card').addClass('loading');
                        let three_d_viewer_id = generateRandomString(8);
                        // /autoManageViewers="true"
                        // let embed_html = '<webgi-viewer src="'+three_d_model+'" id="'+three_d_viewer_id+'" disposeOnRemove="true" style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" />';
                        let embed_html = '<webgi-viewer src="'+three_d_model+'" id="'+three_d_viewer_id+'" disposeOnRemove="true"  style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" />';
                        
                        // if($("webgi-viewer").length >= 1){
                        //     let embed_html = $('webgi-viewer').detach();
                        //     $this.append(embed_html);
                        //     $("webgi-viewer").attr('src', three_d_model);
                        // }else{}
                            $this.append(embed_html);
                            document.getElementById(three_d_viewer_id).addEventListener("initialized", () => {
                                if(typeof document.getElementById(three_d_viewer_id).viewer != 'undefined'){
                                    // console.log('three_d_viewer');
                                    // console.log(document.getElementById(three_d_viewer_id).viewer);
                                    setup_three_d_viewer(document.getElementById(three_d_viewer_id).viewer);
                                }
                                
                            });*/
                        
                        
                        
                        // $this.parents('.gcpb-product-card').removeClass('loading');

                        

                    }
                    
                    if(otw_woo_ring_builder.wp_is_mobile) {
                        gcpb_utility_functions.click_on_selected_tab($product_card);
                    }
                  
                }else{
                    $this.parents('.gcpb-product-card').addClass('gcpb_no_canvas');
                }
                
                /*$.ajax({
                    type: 'POST',
                    url: ajax_ring_obj.ajaxurl,
                    async:   true,
                    data: { 
                        'action': 'gcpb_fetch_3d_model_image', 
                        'variation_id': variation_id,
                        'product_id': product_id
                    },
                    beforeSend: function() {
                        $this.parents('.gcpb-product-card').addClass('loading');
                    },
                    success: function(data){
                        var result = JSON.parse(data);
                        if(result.success) {
                            // $this.append(result.model_image);
                            // console.log(result.model_image);
                            if(!($this.find("webgi-viewer").length >= 1)){
                                $this.parents('.gcpb-product-card').addClass('loading');
                                let three_d_viewer_id = generateRandomString(8);
                                let embed_html = '<webgi-viewer src="'+result.model_image+'" id="'+three_d_viewer_id+'" style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" />';
                                $this.append(embed_html);

                                // const element = document.getElementById(three_d_viewer_id);
                                
                                document.getElementById(three_d_viewer_id).addEventListener("initialized", () => {
                                    console.log('three_d_viewer');
                                    console.log(document.getElementById(three_d_viewer_id).viewer);
                                    setup_three_d_viewer(document.getElementById(three_d_viewer_id).viewer);
                                });

                            }
                            // $this.append('<p>test</p>'); 
                            // console.log(result.model_image);
                        }else{
                            $this.parents('.gcpb-product-card').addClass('gcpb_no_canvas');
                        }
        
                        $this.addClass('model-loaded');
                        $this.removeClass('model-loading');
                        $this.parents('.gcpb-product-card').removeClass('loading');
                    }
                }, function(){
            
                }); */
            }
        // }, 1);
    });
     
    /* reset filter */
    $(document).on('click', '.gcpb-filter-reset-btn-setting', function(event){
        event.preventDefault();
        $('.gcpb-custom-filter-button').removeClass('selected');
                
        $('.gcpb-filter-title').each(function(){
            $(this).find('span').removeClass('gcpb-selected-item').text('');
        });

        $('.gcpb-select-option').each(function(){
            var value = $(this).data('value');
            $(this).removeClass('selected');

            if(value == 'best-selling') {
                $(this).addClass('selected');
                $('.gcpb-select-button-selected-value').text('Best Selling');
            }
        });
 
        setLocalStorage('eo_metal_attr', '');
        setLocalStorage('ring-style', '');
        setLocalStorage('shape', '');

        trigger_product_filter(query_string, true);

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    $(document).on('click', '.gcpb-filter-reset-btn-diamond', function(event){

        setLocalStorage('diamond_filter_price', '');
        setLocalStorage('diamond_filter_carat', '');
        setLocalStorage('diamond_filter_color', '');
        setLocalStorage('diamond_filter_clarity', '');
        
        // $(".gcpb-diamond-filters").
        // $('input:checkbox').removeAttr('checked');
        $('.gcpb-custom-filter-button').removeClass('selected');
        // $('.gcpb-diamond-filter input').removeClass("active_diamond_search").removeAttr('checked');
        $('.gcpb-diamond-filter input').each(function(){
            if($(this).attr('name') == 'diamond-type')
                return;
            else
                $(this).removeClass("active_diamond_search").removeAttr('checked');
        });

        $("#gcpb-carat-filter-slider").addClass("active_diamond_search");
        $("#gcpb-carat-filter-slider").addClass('skip_search');
        let gcpb_carat_filter_slider_range = $("#gcpb-carat-filter-slider-range");
        let gcpb_carat_filter_slider_range_options = gcpb_carat_filter_slider_range.slider( 'option' );
        gcpb_carat_filter_slider_range.slider( 'values', [ gcpb_carat_filter_slider_range_options.min, gcpb_carat_filter_slider_range_options.max ] );
        gcpb_carat_filter_slider_range.parents(".gcpb-diamond-filter").find(".slider_start").html(gcpb_carat_filter_slider_range_options.min);
        gcpb_carat_filter_slider_range.parents(".gcpb-diamond-filter").find(".slider_end").html(gcpb_carat_filter_slider_range_options.max);
        $("#gcpb-carat-filter-slider")/*.removeClass("active_diamond_search")*/.removeClass("skip_search").val('0.3-14.5');

        $("#gcpb-price-filter-slider").removeClass("active_diamond_search").addClass('skip_search');
        let gcpb_price_filter_slider_range = $("#gcpb-price-filter-slider-range");
        let gcpb_price_filter_slider_range_options = gcpb_price_filter_slider_range.slider( 'option' );
        gcpb_price_filter_slider_range.slider( 'values', [ gcpb_price_filter_slider_range_options.min, gcpb_price_filter_slider_range_options.max ] );
        gcpb_price_filter_slider_range.parents(".gcpb-diamond-filter").find(".slider_start").html(gcpb_price_filter_slider_range_options.min);
        gcpb_price_filter_slider_range.parents(".gcpb-diamond-filter").find(".slider_end").html(gcpb_price_filter_slider_range_options.max);
        $("#gcpb-price-filter-slider").removeClass("active_diamond_search").removeClass("skip_search");

        // console.log(jQuery('input#gcpb-carat-filter-slider').attr('class'));
        // jQuery('input#gcpb-carat-filter-slider').addClass('active_diamond_search');
        trigger_stone_filter({}, true);
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });


    /* load more products */
    $(document).on('click', '.js-load-products', function(event){
        event.preventDefault();

        var paged = $(this).data('paged');

        $(this).addClass('gcpb-loading');

        if( typeof paged != 'undefined') {
            var query_params = {
                paged: paged
            };  
    
            trigger_product_filter(query_params, false);
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });
    
    $(document).on('click', '.gcpb-card-toggle-icon', function(event){
        $(this).parents('.gcpb-product-card').toggleClass('opened');
        $("body").toggleClass('card-opened');

        var elm = $(this);
        console.log($(this));
        // console.log($(this).parents('.gcpb-product-card').attr('class'));
        if($(this).parents('.gcpb-product-card').hasClass('opened')) {
            if($(this).parents('.gcpb-product-card').hasClass('gcpb-product-card-stone') && typeof render_3d_stone_with_iframe == 'function'){
                render_3d_stone_with_iframe($(this).parents('.gcpb-product-card').find(".gcpb-prod-card__images"));
            }
            gcpb_utility_functions.click_on_selected_tab($(this).parents('.gcpb-product-card'));
            // scroll_current_element_position(elm);
        }else{
            $('.gcpb-product-card').find('iframe').remove();

            $(this).parents('.gcpb-product-card').find(".gcpb-prod-card__images .gcpb_swiper").remove();
            $(this).parents('.gcpb-product-card').find(".gcpb-prod-card__images video").remove();
            // $(this).parents('.gcpb-product-card').find("video").remove();
            
            // $(this).parents('.gcpb-product-card').find(".gcpb-media-tabs-product-loop .tab-3d").trigger('click');
        }
        // else{
        //     $(this).parents('.gcpb-product-card').find(".gcpb-prod-card__images").removeClass('gcpb-mouseover')
        // }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    /* product column display */
    $(document).on('click', '.gcpb-columns-amounts .gcpb-column-amount', function(event){
        event.preventDefault();
        var column = $(this).data('columns');

        $(this).addClass('active selected').siblings().removeClass('active selected');
        $('.js-product-archive-grid').css({'--columns': column})
       
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    /* product sorting */
    $(document).on('click', '.gcpb-select-options .gcpb-select-option', function(event){
        event.preventDefault();

        var sort = $(this).data('value');
        var label = $(this).text();

        $(this).addClass('selected').siblings().removeClass('selected');
        $('.gcpb-select-button-selected-value').text(label);
        $('.gcpb-select-options').slideUp();

        var query_params = {
            sort: sort
        };  
        
        trigger_product_filter(query_params, true);

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    $(document).on('click', '.gcpb-select-button', function(event){
        event.preventDefault();
 
        $(this).toggleClass('open');
        $('.gcpb-select-options').slideToggle();

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });
 
    // $('body:not(body .gcpb-select)').on('click',function(e) {

    //     if($('.gcpb-select-options').is(":visible")) {
    //         $('.gcpb-select-options').slideUp();
    //         $('.gcpb-select-button').removeClass('open');
    //     } 
    // });

    /* mobile filter popup */
    $(document).on('click','.gcpb-mobile-filter-btn', function(){
        $('body').toggleClass('gcpb-mobile-filters-active');
    });

    $(document).on('click', '.gcpb-mobile-filter-toggle.gcpb-close-btn', function(){
        $('body').removeClass('gcpb-mobile-filters-active');
    });

    $(document).on('click', '.gcpb-mobile-filter-toggle.gcpb-close-btn', function(){
        $('body').removeClass('gcpb-mobile-filters-active');
    });

    $(document).on('click', '.gcpb-gallery-toggle',function(event){
        $('.gcpb-thumbnails').addClass('open');
    });

    $(document).on('click', '.gcpb-close-gallery',function(event){
        $('.gcpb-thumbnails').removeClass('open');
    });

    $(document).on('click', '.js-gcpb-gallery-thumb', function(){
        $('.gcpb-product-image').find('img').attr('data-lazy-src', '');
        var $src = $(this).data('thumb');
        // console.log($src);
        // console.log($('.gcpb-product-image').find('img').attr('src'));
        // console.log($('.gcpb-product-image').find('img').attr('src'));

        $('.gcpb-product-image').find('a').attr('href', $src);
        $('.gcpb-product-image').find('a').find('img').attr('src', $src);

        if($(this).parent().hasClass('gcpb-product-360-image')){
            
            if($('.gcpb-product-image').find('webgi-viewer').length >= 1){
                $('.gcpb-product-image').find('webgi-viewer').show();
                $('.gcpb-product-image').find('img').hide();
            }else if($('.gcpb-product-image').find('.diamond_3d_stone').length >= 1 && $('.gcpb-product-image').find('.diamond_3d_stone').attr('data-src')){
                $('.gcpb-product-image').find('.diamond_3d_stone').show();
                $('.gcpb-product-image').find('img').hide();
            }else{
                $('.gcpb-product-image').find('img').show();
                $('.gcpb-product-image').find('webgi-viewer').hide();
            }
            
        }else{
            $('.gcpb-product-image').find('img').show();
            $('.gcpb-product-image').find('webgi-viewer').hide();
            $('.gcpb-product-image').find('.diamond_3d_stone').hide();
        }

        $('.gcpb-product-image').find('img').attr('src', $src);
        $('.gcpb-product-image').find('a').attr('href', $src);
        $('.gcpb-product-image').find('a').find('img').attr('src', $src);

        // console.log($src);
        // $(this).attr('src', $src);
        
    });

    /* single product accordian */

    $('.gcpb-accordion-title').on('click', function(event){
        event.preventDefault();
        $(this).toggleClass('open');
        $(this).parents('.gcpb-product-detail').find('.gcpb-accordion-content').slideToggle();
        
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    /* product variation loader on single page */
    function gcpb_single_product_variation_response($this, variation_query) {

        var variation_data  = null;

        $.ajax({
            type: 'POST',
            // dataType: 'json',
            url: ajax_ring_obj.ajaxurl,
            async:   true,
            data: { 
                'action': 'gcpb_single_product_variation_data', 
                'variation_query': variation_query,
            },
            beforeSend: function() {
                $this.parents('.gcpb-product-wrapper').addClass('gcpb-loading');
                $('.gcpb-product-image-wrapper').addClass('otw_container_loading');
                
            },
            success: function(result){
                // variation_data = result;
                $this.parents('.gcpb-product-wrapper').find(".gcpb-product-image picture webgi-viewer").remove();

                var variation_data = JSON.parse(result);
                // console.log(variation_data);
                // console.log(variation_data.variation_id);
                if($(".otw_select_diamond_dynamic_url").length >= 1 && typeof variation_data.variation_id != 'undefined' && variation_data.variation_id){
                    $this.parents('.gcpb-product-wrapper').find(".gcpb-product-image").data('variation_id', variation_data.variation_id);
                    $(".otw_select_diamond_dynamic_url").data('variation_id', variation_data.variation_id);
                }

                if( typeof variation_data.gcpb_fragments != 'undefined' && variation_data.gcpb_fragments ) {
                    // console.log(variation_data.gcpb_fragments);
                    $.each(variation_data.gcpb_fragments, function(element, fragment) {
                        // console.log(element);
                        $(element).empty();
                        $(element).append(fragment);
                    });
        
                }

                $('.gcpb-product-wrapper').removeClass('gcpb-loading');
                
                if(typeof variation_data.variation_3d_model != 'undefined' && variation_data.variation_3d_model){
                    $this.parents('.gcpb-product-wrapper').find(".gcpb-product-image").data('3d-model', variation_data.variation_3d_model);
                    if(!otw_woo_ring_builder.wp_is_mobile){
                        let three_model_html = '<webgi-viewer class="loading" src="'+variation_data.variation_3d_model+'" style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" disposeOnRemove="true" />';
                        // $this.parents('.gcpb-product-wrapper').find(".gcpb-product-image picture").prepend(three_model_html);
                    }
                    
                }else{
                    $('.gcpb-product-image-wrapper').removeClass('otw_container_loading');
                }
                dom_setup_three_d_viewer();
                if(!otw_woo_ring_builder.wp_is_mobile)
                    gcpb_load_video(); 
                get_selected_single_setting_filter_mobile();
                gcpb_utility_functions.click_on_selected_tab(jQuery(".gcpb-product-wrapper"));
                // $(".gcpb-media-tabs .gcpb-media-tab").removeClass('active');
                // $(".gcpb-media-tabs .tab-3d").addClass('active');
                // var result = JSON.parse(data);
                 
                // console.log(result.price)//;

                // if( result.image_url != '') {
                //     $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images').empty().append(result.image_url);
                // }

                // if( result.image_hover != '') {
                //     $this.parents('.gcpb-product-card').find('.gcpb-prod-card__images').empty().append(result.image_hover);
                // }

                // if( data.price != '') {
                //     $this.parents('.gcpb-product-card').find('.gcpb-product-price').empty().append(result.price);
                // }
                //  console.log(data);
                // $this.parents('.gcpb-product-card').removeClass('loading');
            },
            error: function(xhr) { // if error occured
                alert("Error occured.please try again");
            },
        });

        return variation_data;
    }

    $(document).on('click', '.js-gcpb-single-variation .gcpb-swatch-box', function(event){
        
        event.preventDefault();

        var $this = $(this);
        
        if($this.hasClass('selected')) {
            $this.removeClass('selected');
        } else {
            $this.addClass('selected').siblings().removeClass('selected');
        }
        
        var $product_card = $this.parents('.gcpb-product-customization');
        var $attributes = $product_card.find('.gcpb-swatch-box.selected');
        
        var $product_id = $product_card.data('product-id');
        var query_string = 'variation_query=1&product_id='+$product_id;

        $($attributes).each(function(){
 
            var $value = $(this).data('value');
            var $attribute = $(this).parents('.js-gcpb-single-variation').data('attribute');
            
            // console.log($value);
            if( typeof $value != 'undefined' && $value != '') {
                query_string += '&'+$attribute+'='+$value;
            }
        });

        // console.log(query_string);//
       
        gcpb_single_product_variation_response($this, query_string);

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
        
    });

    $(document).on('click', 'button.gcpb-advanced-filters-button', function(event){

        $(".gcpb-advance-filters").toggleClass('open');

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });
});

const gcpb_utility_functions = {
    init: function () {
        if(!getLocalStorage('gcpb_selected_media_tab')){
            setLocalStorage('gcpb_selected_media_tab', 'gallery');
        }
    },
    create_swiper: function(data, target){
        if(!data.length >= 1)
            return false;
        
        let random_id = generateRandomString(6);
        let slider_html = '<div id="gcpb_swiper_'+random_id+'" class="gcpb_swiper">';

        slider_html += '<div class="gcpb_swiper_arrows"><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>';
    
        slider_html += '<div class="swiper-container">';
        slider_html += '<div class="swiper-wrapper">';
    
        for (let i = 0; i < data.length; i++) {
            slider_html += '<div class="swiper-slide">'+data[i]+'</div>';
        }

        
        slider_html += '</div>';
    
        slider_html +='<div class="gcpb-pagination"></div>';
    
        slider_html += '</div>';
        slider_html += '</div>';
    
        target.append(slider_html);
    
        new Swiper('#gcpb_swiper_'+random_id+' .swiper-container', {
            slidesPerView: 1,
            spaceBetween: 10,
            loop: true,
            autoplay: false,
            // loopedSlides: 4,
            navigation: {
                nextEl: '#gcpb_swiper_'+random_id+' .gcpb_swiper_arrows .swiper-button-next',
                prevEl: '#gcpb_swiper_'+random_id+' .gcpb_swiper_arrows .swiper-button-prev',
            },
            pagination: {
                el: '#gcpb_swiper_'+random_id+' .gcpb-pagination',
            },
        });
    },
    display_3d_model: function(parent_container){
        let image_hover = parent_container.data('3d-model');
        let variation_id = parent_container.data('variation-id');
        //https://stackoverflow.com/questions/2596833/how-to-move-child-element-from-one-parent-to-another-using-jquery
        if(parent_container.find('webgi-viewer').length >= 1){
            parent_container.removeClass('loading');
            if(parent_container.find('webgi-viewer').attr('src') == image_hover)
                return true;
            // let image_hover_html = jQuery('webgi-viewer').detach();
            // console.log(image_hover_html);
            // parent_container.find('.gcpb-prod-card__images').append(image_hover_html);
            // parent_container.find('p.testing_p').remove();
            // prepend('<p>'+image_hover+'</p>');
            // parent_container.prepend('<p class="testing_p">'+image_hover+'</p>');
            let viewerElement = parent_container.find('webgi-viewer')[0];
            
            viewerElement.clearViewer();
            viewerElement.viewer.getPluginByType("Diamond").disposeAllCacheMaps();
            viewerElement.viewer.getManager().importer._cachedAssets = [];
            viewerElement.viewer.getPluginByType('AssetManagerLoadingBarPlugin').enabled = false;
            // viewerElement.viewer.getManager().importer.clearCache();
            // console.log(parent_container.find('.gcpb-prod-card__images webgi-viewer')[0]);
            parent_container.find('webgi-viewer').attr('src', image_hover);
            
        }else{
            parent_container.addClass('loading');
            //autoManageViewers="true"
            
            // parent_container.find('.gcpb-prod-card__images webgi-viewer').attr('src', image_hover);
            parent_container.find('webgi-viewer').remove();
            if(jQuery('body').hasClass("page-template-template-setting-single")){
                
                jQuery('.gcpb-product-image-wrapper').addClass('otw_container_loading');

                let image_hover_html = '<webgi-viewer class="loading" id="viewer_'+variation_id+'" src="'+image_hover+'" style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" disposeOnRemove="true"></webgi-viewer>';
                parent_container.find('picture').append(image_hover_html);
            }else{
                let image_hover_html = '<webgi-viewer class="loading" id="viewer_'+variation_id+'" src="'+image_hover+'" disposeOnRemove="true" style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" ></webgi-viewer>';
                
                if(parent_container.find('.gcpb-prod-card__images .gcpb-prod-card__loading__bar').length >= 1)
                    parent_container.find('.gcpb-prod-card__images .gcpb-prod-card__loading__bar').before(image_hover_html);
                else
                    parent_container.find('.gcpb-prod-card__images').append(image_hover_html);
            }
            
            document.getElementById("viewer_"+variation_id).addEventListener("initialized", () => {
                if(typeof document.getElementById("viewer_"+variation_id).viewer != 'undefined')
                    setup_three_d_viewer(document.getElementById("viewer_"+variation_id).viewer);
            });
        }
    },
    click_on_selected_tab: function(parent_container){
        // parent_container.find('.gcpb-media-tabs .gcpb-media-tab').removeClass('active');
        let selected_media_tab = getLocalStorage('gcpb_selected_media_tab');
        // console.log(selected_media_tab); 
        // console.log(parent_container);
        if(selected_media_tab == 'video'){
            parent_container.find('.tab-video').trigger('click');
        }
        else if(selected_media_tab == 'gallery'){
            parent_container.find('.tab-gallery').trigger('click');
        }
        else{
            parent_container.find('.tab-3d').trigger('click');
        }
        
    },
    getParameterByName: function(name, url = window.location.href) {
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }
}

jQuery(document).ready(function($){

    gcpb_utility_functions.init();

    let shape_mismatch = gcpb_utility_functions.getParameterByName('shape');
    if(shape_mismatch != 'undefined' && shape_mismatch && shape_mismatch == 'mismatch')
        $(".gcpb-diamond-out-of-stock").show();
});

const gcpb_product_loop_data = {

    // view: {},
    // active_status: '',

    init: function () {

        
        
        // this.view = groupix_notifications_view;
        // this.active_status = 'unread';

        // if($(".gcpb-media-tabs-product-loop").length >= 1){
            jQuery(document).on('click', '.gcpb-media-tabs-product-loop .gcpb-media-tab', function(event){
                
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
        
                // if(jQuery(this).hasClass("active"))
                //     return false;
        
                let gcbp_product_card = jQuery(this).closest(".gcpb-product-card");
                let gcbp_product_card_media_wrapper = gcbp_product_card.find(".gcpb-product-card-wrapper .gcpb-prod-card__images");

                    
                if(jQuery(this).hasClass("tab-video")){

                    setLocalStorage('gcpb_selected_media_tab', 'video');

                    if(!gcbp_product_card_media_wrapper.find('video').length >= 1){
                        gcbp_product_card_media_wrapper.append(gcbp_product_card.find('.gcpb_prod_card_hidden_media_data video').clone());
                        gcpb_load_video(gcbp_product_card_media_wrapper.find('video'));
                    }
                    gcbp_product_card_media_wrapper.find('video').show();
                    gcbp_product_card_media_wrapper.find('webgi-viewer').hide();
                    gcbp_product_card_media_wrapper.find('.gcpb_swiper').hide();
                    
                }else if(jQuery(this).hasClass("tab-gallery")){

                    setLocalStorage('gcpb_selected_media_tab', 'gallery');

                    if(!gcbp_product_card_media_wrapper.find('.gcpb_swiper').length >= 1){
                        let gallery_images = [];
                        if(gcbp_product_card.find('.gcpb_prod_card_hidden_media_data span.loop_gallery_images').length >= 1){

                            gcbp_product_card.find('.gcpb_prod_card_hidden_media_data span.loop_gallery_images').each(function(){
                                if(jQuery(this).data('src')){
                                    gallery_images.push('<img src="'+jQuery(this).data('src')+'" />');
                                }
                            });
                        }
                        gcpb_utility_functions.create_swiper(gallery_images, gcbp_product_card_media_wrapper);
                    }

                    gcbp_product_card_media_wrapper.find('video').hide();
                    gcbp_product_card_media_wrapper.find('webgi-viewer').hide();
                    gcbp_product_card_media_wrapper.find('.gcpb_swiper').show();
                }else{
                    
                    setLocalStorage('gcpb_selected_media_tab', 'webgi-viewer');

                    gcbp_product_card_media_wrapper.find('video').hide();
                    gcbp_product_card_media_wrapper.find('webgi-viewer').show();
                    gcbp_product_card_media_wrapper.find('.gcpb_swiper').hide();

                    gcpb_utility_functions.display_3d_model(gcbp_product_card);
                }
                
                gcbp_product_card.find('.gcpb-media-tabs .gcpb-media-tab').removeClass('active');
                jQuery(this).addClass("active");
                return false;
            });
    },
}

jQuery(document).ready(function($){
    gcpb_product_loop_data.init();

    $(document).on('click','.gcpb-product-card .gcpb_swiper .swiper-slide', function(event){
        console.log('ddd');
        var url = $(this).parents('.gcpb-product-card').find('.product-slide-link').attr('href');
        window.location.href = url;
    });
});

function render_3d_stone_with_iframe($this){
    if($this.hasClass('gcpb-mouseover')){
        // $this.addClass('model-loading');
        let $product_card = $this.parents('.gcpb-product-card');
        let three_d_model = $product_card.data('3d-model');
        let three_d_model_id = $product_card.data('3d-model-id');
        if(otw_woo_ring_builder.wp_is_mobile)
            jQuery(".gcpb-product-archive-grid .diamond_3d_stone").remove();
        if(three_d_model){
            
            if(!($this.find(".diamond_3d_stone").length >= 1)){
                // jQuery(".diamond_3d_stone").remove();
                // console.log(three_d_model);
                // $this.parents('.gcpb-product-card').removeClass('loading');
                $this.parents('.gcpb-product-card').addClass('loading_iframe');
                //height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2));
                let iframe_height = '';
                if(three_d_model.indexOf('viw-us.s3.amazonaws.com') !== -1 && !otw_woo_ring_builder.wp_is_mobile){
                    // $product_card.find("iframe.diamond_3d_stone").css("height", '1000px');
                    iframe_height = ' height="1000px"';
                }
                let embed_html = '<iframe class="diamond_3d_stone" src="'+three_d_model+'" style="width: 100%; z-index: 3; display: block;" scrolling="no" onload="gcpb_remove_iframe_loading_class(this)"'+iframe_height+'></iframe>';
                // let embed_html = '<div class="diamond_3d_stone Sirv" id="'+three_d_model_id+'" data-src="'+three_d_model+'"></div>';
                $this.append(embed_html);
                // let spin = document.getElementById(three_d_model_id);
                // Sirv.instance(spin).play();
                
            }
        }else{
            $product_card.addClass('gcpb_no_canvas');
        }
    }
}

function build_filter_stone_query(query_params = {}) {
    let query_string = 'filter=1';
    jQuery('.gcpb-custom-filter-button.selected').each(function(){
        let value = jQuery(this).data('filter-value');
        let name = jQuery(this).data('filter-name');
        query_string += '&'+name+'='+value;
    });
    return query_string;
}

function trigger_stone_filter( add_query_params = {}, filter_action = false ) {
 
    get_selected_diamond_filter_mobile();

    let query_string = build_filter_stone_query();
    // console.log(query_string);
    query_string += "&type=Lab_grown_Diamond";
    if(jQuery('input[type=radio][name=diamond-type]:checked').length >= 1){
        query_string += "&type="+jQuery('input[type=radio][name=diamond-type]:checked').val();
    }
    

    
    // if(jQuery('input#gcpb-color-filter-slider').hasClass('active_diamond_search'))
    //     query_string += "&color="+jQuery('input#gcpb-color-filter-slider').val();
    // if(jQuery('input#gcpb-clarity-filter-slider').hasClass('active_diamond_search'))
    //     query_string += "&clarity="+jQuery('input#gcpb-clarity-filter-slider').val();
    /*if(jQuery('input#gcpb-cut-filter-slider').hasClass('active_diamond_search'))
        query_string += "&cut="+jQuery('input#gcpb-cut-filter-slider').val();
    if(jQuery('input#gcpb-polish-filter-slider').hasClass('active_diamond_search'))
        query_string += "&polish="+jQuery('input#gcpb-polish-filter-slider').val();
    if(jQuery('input#gcpb-symmetry-filter-slider').hasClass('active_diamond_search'))
        query_string += "&symmetry="+jQuery('input#gcpb-symmetry-filter-slider').val();*/
    if(jQuery('input#gcpb-price-filter-slider').hasClass('active_diamond_search'))
        query_string += "&price="+jQuery('input#gcpb-price-filter-slider').val();
    if(jQuery('input#gcpb-carat-filter-slider').hasClass('active_diamond_search')){
        // console.log(jQuery('input#gcpb-carat-filter-slider').val());
        query_string += "&carat="+jQuery('input#gcpb-carat-filter-slider').val();
    }
    if(jQuery('.show_more_diamond_pagination').data('page_number'))
        query_string += "&page_number="+jQuery('.show_more_diamond_pagination').data('page_number');
    if(jQuery('.show_more_diamond_pagination').data('page_number_nivoda'))
        query_string += "&page_number_nivoda="+jQuery('.show_more_diamond_pagination').data('page_number_nivoda');
    if(jQuery('.show_more_diamond_pagination').data('page_number_vdb'))
        query_string += "&page_number_vdb="+jQuery('.show_more_diamond_pagination').data('page_number_vdb');

        // console.log(query_string);
    query_string += get_selected_clarity_data();
    query_string += get_selected_color_data();
    
    let query_string_params = new URLSearchParams(query_string);
    // console.log(query_string_params.get('price'));
    // const myParam = urlParams.get('myParam');

    if(getLocalStorage('diamond_filter_price') && query_string.indexOf('price') === -1){
        let saved_price_values = getLocalStorage('diamond_filter_price');
        let saved_price_values_array = saved_price_values.split("-");
        query_string += '&price='+saved_price_values;
        if(saved_price_values_array.length >= 2){
            otw_woo_ring_builder.diamond_min_price_filter_value = saved_price_values_array[0];
            otw_woo_ring_builder.diamond_max_price_filter_value = saved_price_values_array[1];
            if(jQuery( "#gcpb-price-filter-slider-range" ).slider( "instance" ) != 'undefined'){
                jQuery("#gcpb-price-filter-slider").addClass('skip_search');
                jQuery('input#gcpb-price-filter-slider-range').slider( "option", "values", [ parseInt(saved_price_values_array[0]), parseInt(saved_price_values_array[1]) ] );
                jQuery("#gcpb-price-filter-slider").removeClass('skip_search');
            }
        }
        
        // if(otw_woo_ring_builder.ip == '182.178.182.113' || otw_woo_ring_builder.ip == '154.80.53.45'){}
    }
    if(query_string_params.get('price'))
        setLocalStorage('diamond_filter_price', query_string_params.get('price'));


    if(getLocalStorage('diamond_filter_carat') && query_string.indexOf('carat') === -1){
        let saved_carat_values = getLocalStorage('diamond_filter_carat');
        let saved_carat_values_array = saved_carat_values.split("-");
        query_string += '&carat='+saved_carat_values;
        if(saved_carat_values_array.length >= 2){
            otw_woo_ring_builder.diamond_min_carat_filter_value = saved_carat_values_array[0];
            otw_woo_ring_builder.diamond_max_carat_filter_value = saved_carat_values_array[1];
            if(jQuery( "#gcpb-carat-filter-slider-range" ).slider( "instance" ) != 'undefined'){
                jQuery('input#gcpb-carat-filter-slider-range').slider( "option", "values", [ parseFloat(saved_carat_values_array[0]), parseFloat(saved_carat_values_array[1]) ] );
            }
        }
    }
    if(query_string_params.get('carat'))
        setLocalStorage('diamond_filter_carat', query_string_params.get('carat'));


    if(getLocalStorage('diamond_filter_color') && query_string.indexOf('color') === -1){
        let saved_color_values = getLocalStorage('diamond_filter_color');
        
        let saved_color_values_array = saved_color_values.split("-");
        query_string += '&color='+saved_color_values;
        if(saved_color_values_array.length >= 1){
            if(saved_color_values_array.length == 1){
                create_selected_color_data(saved_color_values_array[0]);
            }else if(saved_color_values_array.length == 2){
                create_selected_color_data(saved_color_values_array[0], saved_color_values_array[1]);
            }
            
        }
        
    }
    if(query_string_params.get('color'))
        setLocalStorage('diamond_filter_color', query_string_params.get('color'));

    
    
    if(getLocalStorage('diamond_filter_clarity') && query_string.indexOf('clarity') === -1){
        let saved_clarity_values = getLocalStorage('diamond_filter_clarity');
        let saved_clarity_values_array = saved_clarity_values.split("-");
        query_string += '&clarity='+saved_clarity_values;
        if(saved_clarity_values_array.length >= 1){
            if(saved_clarity_values_array.length == 1){
                create_selected_clarity_data(saved_clarity_values_array[0]);
            }else if(saved_clarity_values_array.length == 2){
                create_selected_clarity_data(saved_clarity_values_array[0], saved_clarity_values_array[1]);
            }
        }
    }
    if(query_string_params.get('clarity'))
        setLocalStorage('diamond_filter_clarity', query_string_params.get('clarity'));
    
        
    console.log(query_string);
    if(typeof otw_gcpb_current_post_request != 'undefined' && otw_gcpb_current_post_request)
        otw_gcpb_current_post_request.abort();

    otw_gcpb_current_post_request = jQuery.ajax({
        type: 'POST',
        url: ajax_ring_obj.ajaxurl,
        async:   true,
        data: { 
            'action': 'fetch_stones', 
            'query_string': query_string,
        },
        beforeSend: function() {
            // if(filter_action) {
                jQuery('body').addClass('gcpb-loading');
            // }
        },
        success: function(data){ 
            otw_gcpb_current_post_request = false;
            if(typeof data != 'undefined' && typeof data.success != 'undefined' && data.success == true && typeof data.data != 'undefined' && typeof data.data.data != 'undefined'){
                // var result = JSON.parse(data.data.data);
                jQuery('.js-product-archive-grid').removeClass('gcpb-preload');
                jQuery('body').removeClass('gcpb-loading');
                jQuery('.product-loader').remove();
                jQuery('.show_more_diamond_pagination').remove();
                if(typeof data.data.page_number != 'undefined' && data.data.page_number && parseInt(data.data.page_number) >= 2){
                    jQuery(".diamonds-grid").append(data.data.data);
                }else{
                    jQuery(".diamonds-grid").html(data.data.data);
                }
                if(typeof data.data.pagination_html != 'undefined' && data.data.pagination_html){
                    jQuery(".diamonds-grid").after(data.data.pagination_html);
                    trigger_stone_filter_more();
                }
            }else if(typeof data != 'undefined' && typeof data.success != 'undefined' && data.success == false && typeof data.data != 'undefined'){
                jQuery('.js-product-archive-grid').removeClass('gcpb-preload');
                jQuery('body').removeClass('gcpb-loading');
                jQuery('.product-loader').remove();
                jQuery('.show_more_diamond_pagination').remove();
                jQuery(".diamonds-grid").html(data.data);
            }

            update_total_fetched_stones();
            // const offset = jQuery('.diamonds-grid').offset();
            // if(filter_action) {
                // scroll_to_specific_element(jQuery('.diamonds-grid'));
            // }
            
            // console.log(data);
        },
        error: function(xhr) { // if error occured
            otw_gcpb_current_post_request = false;
            // alert("Error occured. please try again");
        },
    });
}

if (typeof update_total_fetched_stones != 'function') {
    function update_total_fetched_stones() {
        console.log(jQuery(".diamonds-grid .gcpb-product-card").length);
        jQuery(".otw_total_fetched_stones").html(jQuery(".diamonds-grid .gcpb-product-card").length);
        console.log(jQuery(".otw_total_fetched_stones").html());
    }
}

function trigger_stone_filter_more() {
    if(jQuery(".show_more_diamond_pagination").length >= 1){
        if(jQuery(".diamonds-grid .gcpb-product-card-stone").length <= 4){
            jQuery('.show_more_diamond_pagination button').trigger("click");
        }
    }
}

if (typeof make_between_color_checked != 'function') {
    function make_between_color_checked() {
        var gcpb_total_selected_color = jQuery('.gcpb-color-filter input:checked').length;
        if(gcpb_total_selected_color >= 2){
            var gcpb_first_selected_color = [];
            var gcpb_selected_color_counter = 0;
            jQuery('.gcpb-color-filter input').each(function(index){
                

                if(jQuery(this).is(':checked')){
                    gcpb_selected_color_counter++;
                }
                if(gcpb_selected_color_counter >= 1 && gcpb_selected_color_counter <= gcpb_total_selected_color){
                    jQuery(this).prop('checked', true).addClass('active_diamond_search');
                }
                if(gcpb_selected_color_counter == gcpb_total_selected_color)
                    return false; // breaks
            });
        }
    }
}

if (typeof remove_checked_color_except_fancy != 'function') {
    function remove_checked_color_except_fancy(event = false) {
        // var gcpb_total_selected_color = jQuery('.gcpb-color-filter input:checked').length;
        // if(gcpb_total_selected_color >= 2){
            jQuery('.gcpb-color-filter input').each(function(index){
                if(jQuery(this).attr('id') != 'color-fancy'){
                    jQuery(this).prop('checked', false).removeClass('active_diamond_search');
                }
            });
        // }
    }
}

if (typeof make_between_clarity_checked != 'function') {
    function make_between_clarity_checked() {
        var gcpb_total_selected_clarity = jQuery('.gcpb-clarity-filter input:checked').length;
        if(gcpb_total_selected_clarity >= 2){
            var gcpb_first_selected_clarity = [];
            var gcpb_selected_clarity_counter = 0;
            jQuery('.gcpb-clarity-filter input').each(function(index){
                

                if(jQuery(this).is(':checked')){
                    gcpb_selected_clarity_counter++;
                }
                if(gcpb_selected_clarity_counter >= 1 && gcpb_selected_clarity_counter <= gcpb_total_selected_clarity){
                    jQuery(this).prop('checked', true).addClass('active_diamond_search');
                }
                if(gcpb_selected_clarity_counter == gcpb_total_selected_clarity)
                    return false; // breaks
            });
        }
    }
}

if (typeof gcpb_remove_iframe_loading_class != 'function') {
    function gcpb_remove_iframe_loading_class(){
        jQuery(document).ready(function($){
            $('.gcpb-product-card').removeClass('loading_iframe');
            // $(this).parents('.gcpb-product-card').removeClass('loading_iframe');
            console.log($('.gcpb-product-card').attr('class'));
        });
    }
}

jQuery(document).ready(function($){

    // window.addEventListener(
        // "message",
        // (event) => {
            // console.log(event);
        //   if (event.origin !== "http://example.org:8080") return;
      
          // …
        // },
        // false,
    // );

    

    // var diamonds_color = ['D', 'E', 'F', 'G', 'H',  'I', "J", 'K', "L"];
    // var diamonds_clarity = ['FL', 'IF', 'VVS1', 'VVS2', 'VS1',  'VS2', "SI1", 'SI2'];
    if($('body').hasClass('page-template-template-diamonds')){
        trigger_stone_filter({}, true);

        // trigger_stone_filter_more();
        update_total_fetched_stones();
        // console.log("");
        

        $(document).on('change', 'input[type=radio][name=diamond-type], input#gcpb-cut-filter-slider, input#gcpb-polish-filter-slider, input#gcpb-symmetry-filter-slider', function(event){
            $(this).addClass('active_diamond_search');
            $(".show_more_diamond_pagination").data('page_number', 1);
            trigger_stone_filter({}, true);
            // $(".gcpb-shape-filter .gcpb-custom-filter-button-stone.selected").trigger("click");
        });

        $(document).on('change', '.gcpb-color-filter input', function(event){

            
            
            if($(this).attr('id') == 'color-fancy'){
                // $(this).toggleClass('active_diamond_search');
                if($(this).is(':checked')){
                    $(this).addClass('active_diamond_search');
                    // jQuery('#color-fancy').prop('checked', false).removeClass('active_diamond_search');
                    // setLocalStorage('diamond_filter_color', '');
                }else{
                    // jQuery('#color-fancy').prop('checked', true).addClass('active_diamond_search');
                    $(this).removeClass('active_diamond_search');
                    setLocalStorage('diamond_filter_color', '');
                }
                
                remove_checked_color_except_fancy(event);
                make_between_color_checked();
            }else{
                console.log(jQuery('#color-fancy').prop('checked', false));
                make_between_color_checked();
                $(this).addClass('active_diamond_search');
            }
            
            
            
            $(".show_more_diamond_pagination").data('page_number', 1);
            trigger_stone_filter({}, true);
            // $(".gcpb-shape-filter .gcpb-custom-filter-button-stone.selected").trigger("click");
        });

        $(document).on('change', '.gcpb-clarity-filter input', function(event){
            make_between_clarity_checked();
            
            $(this).addClass('active_diamond_search');
            $(".show_more_diamond_pagination").data('page_number', 1);
            trigger_stone_filter({}, true);
            // $(".gcpb-shape-filter .gcpb-custom-filter-button-stone.selected").trigger("click");
        });
        



        /*$( "#gcpb-color-filter-slider-range" ).slider({
            range: true,
            min: 0,
            max: 8,
            values: [ 0, 8 ],
            change: function( event, ui ) {
                // $(this).parents(".gcpb-diamond-filter").find(".slider_start").html(diamonds_color[ui.values[0]]);
                // $(this).parents(".gcpb-diamond-filter").find(".slider_end").html(diamonds_color[ui.values[1]]);
                $(".show_more_diamond_pagination").data('page_number', 1);
                $("#gcpb-color-filter-slider").val(ui.values[0] + "-" + ui.values[1]).addClass('active_diamond_search');
                // $(".gcpb-shape-filter .gcpb-custom-filter-button-stone.selected").trigger("click");
                trigger_stone_filter({}, true);
                // console.log( ui.values[ 0 ] + "-" + ui.values[ 1 ]);
            //   $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
            }
        });*/

        $( "#gcpb-carat-filter-slider-range" ).slider({
            range: true,
            min: parseFloat(otw_woo_ring_builder.diamond_min_carat_filter),
            max: parseFloat(otw_woo_ring_builder.diamond_max_carat_filter),
            step: 0.1,
            values: [ parseFloat(otw_woo_ring_builder.diamond_min_carat_filter_value), parseFloat(otw_woo_ring_builder.diamond_max_carat_filter_value) ],
            slide: function( event, ui ) {
                $(this).parents(".gcpb-diamond-filter").find(".slider_start").html(ui.values[0]);
                $(this).parents(".gcpb-diamond-filter").find(".slider_end").html(ui.values[1]);
            },
            change: function( event, ui ) {
                if(typeof ui == 'undefined' || typeof ui.values == 'undefined' || typeof ui.values[0] == 'undefined' || typeof ui.values[1] == 'undefined' || Number.isNaN(ui.values[0]) || Number.isNaN(ui.values[1]))
                    return true;
                if($("#gcpb-carat-filter-slider").hasClass('skip_search'))
                    return true;

                $(".show_more_diamond_pagination").data('page_number', 1);
                $("#gcpb-carat-filter-slider").val(ui.values[0] + "-" + ui.values[1]).addClass('active_diamond_search');
                // $(".gcpb-shape-filter .gcpb-custom-filter-button-stone.selected").trigger("click");
                trigger_stone_filter({}, true);
                // console.log( ui.values[ 0 ] + "-" + ui.values[ 1 ]);
            //   $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
            },
            create: function( event, ui ) {
                $(this).parents(".gcpb-diamond-filter").find(".slider_start").html($(this).slider( 'option' ).values[0]);
                $(this).parents(".gcpb-diamond-filter").find(".slider_end").html($(this).slider( 'option' ).values[1]);
            }
        });

        $( "#gcpb-price-filter-slider-range" ).slider({
            range: true,
            min: parseInt(otw_woo_ring_builder.diamond_min_price_filter),
            max: parseInt(otw_woo_ring_builder.diamond_max_price_filter),
            step: 100,
            values: [ parseInt(otw_woo_ring_builder.diamond_min_price_filter_value), parseInt(otw_woo_ring_builder.diamond_max_price_filter_value)],
            slide: function( event, ui ) {
                $(this).parents(".gcpb-diamond-filter").find(".slider_start").html(ui.values[0]);
                $(this).parents(".gcpb-diamond-filter").find(".slider_end").html(ui.values[1]);
            },
            change: function( event, ui ) {

                if(typeof ui == 'undefined' || typeof ui.values == 'undefined' || typeof ui.values[0] == 'undefined' || typeof ui.values[1] == 'undefined' || Number.isNaN(ui.values[0]) || Number.isNaN(ui.values[1]))
                    return true;
                if($("#gcpb-price-filter-slider").hasClass('skip_search'))
                    return true;

                $(".show_more_diamond_pagination").data('page_number', 1);
                $("#gcpb-price-filter-slider").val(ui.values[0] + "-" + ui.values[1]).addClass('active_diamond_search');
                // $(".gcpb-shape-filter .gcpb-custom-filter-button-stone.selected").trigger("click");
                trigger_stone_filter({}, true);
                // console.log( ui.values[ 0 ] + "-" + ui.values[ 1 ]);
            //   $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
            },
            create: function( event, ui ) {
                // console.log(ui.values);
                $(this).parents(".gcpb-diamond-filter").find(".slider_start").html($(this).slider( 'option' ).values[0]);
                $(this).parents(".gcpb-diamond-filter").find(".slider_end").html($(this).slider( 'option' ).values[1]);
            }
        });
        // $("#gcpb-price-filter-slider").parents(".gcpb-diamond-filter").find(".slider_start").html(otw_woo_ring_builder.diamond_min_price_filter_value);
        // $("#gcpb-price-filter-slider").parents(".gcpb-diamond-filter").find(".slider_end").html(otw_woo_ring_builder.diamond_max_price_filter_value);
        // console.log('price slider initiated');
        /*$( "#gcpb-clarity-filter-slider-range" ).slider({
            range: true,
            min: 0,
            max: 7,
            step: 1,
            values: [ 0, 7 ],
            change: function( event, ui ) {
                // $(this).parents(".gcpb-diamond-filter").find(".slider_start").html(ui.values[0]);
                // $(this).parents(".gcpb-diamond-filter").find(".slider_end").html(ui.values[1]);
                $(".show_more_diamond_pagination").data('page_number', 1);
                $("#gcpb-clarity-filter-slider").val(ui.values[0] + "-" + ui.values[1]).addClass('active_diamond_search');
                // $(".gcpb-shape-filter .gcpb-custom-filter-button-stone.selected").trigger("click");
                trigger_stone_filter({}, true);
                // console.log( ui.values[ 0 ] + "-" + ui.values[ 1 ]);
            //   $( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
            }
        });*/
        $(document).on('click','.show_more_diamond_pagination button', function(event){
            let page_number = parseInt($(".show_more_diamond_pagination").data('page_number'));
            if(page_number){
                page_number++;
                $(".show_more_diamond_pagination").data('page_number', page_number);
            }
            // $(".gcpb-shape-filter .gcpb-custom-filter-button-stone.selected").trigger("click");
            trigger_stone_filter({}, false);
        });

        //   $( "#amount" ).val( "$" + $( "#gcpb-color-filter-slider-range" ).slider( "values", 0 ) +
        //     " - $" + $( "#gcpb-color-filter-slider-range" ).slider( "values", 1 ) );
    }
    
    
    

    // DIAMOND ARCHIVE COLOR AND CLARITY POPUP
    $('.gcpb-clarity-color-popup-toggle').on('click', function(event) {
        event.preventDefault();
        $('.color-clarity-popup').toggleClass('open');
    });
            
    // Add a click event listener to buttons with the data-content attribute
    $('button[data-content]').on('click', function() {
        // Remove the "active" class from all buttons with the data-content attribute
        $('button[data-content]').removeClass('active');

        // Add the "active" class to the clicked button
        $(this).addClass('active');

        // Get the value of the data-content attribute from the clicked button
        var dataContentValue = $(this).data('content');

        // Remove the "active" class from all divs with the data-content attribute
        $('div[data-content]').removeClass('active');

        // Find the div with the matching data-content attribute and add the "active" class to it
        $('div[data-content="' + dataContentValue + '"]').addClass('active');
    });

    $('.diamond-buttons a').on('click', function() {
        // Remove the 'active' class from all links
        $('.diamond-buttons a').removeClass('active');

        // Add the 'active' class to the clicked link
        $(this).addClass('active');
    });

    function gcpb_product_card_toggle(current_object, event){

        let event_object = $(event.target);
        console.log('mouseover');
        if(current_object.hasClass("gcpb-product-card-stone")){
            var parent_li_object = current_object;
        }
        else{
            var parent_li_object = current_object.parents(".gcpb-product-card");
        }

        if(!parent_li_object.hasClass('opened')){
            $('.gcpb-product-card-stone').find('iframe').remove();
            if(otw_woo_ring_builder.wp_is_mobile){
                $('body').addClass("card-opened");
            }

            

            $('.gcpb-product-card-stone').removeClass('opened');
            parent_li_object.addClass('opened');
            
            let $this = current_object.find(".gcpb-prod-card__images");
            // if(!otw_woo_ring_builder.wp_is_mobile)
                $this.addClass('gcpb-mouseover');
                // $this.parents('.gcpb-product-card').addClass('opened');
            // setTimeout(function() {
                render_3d_stone_with_iframe($this);
            // }, 500);
        }
    }
    
    if(otw_woo_ring_builder.wp_is_mobile){
        $(document).on('click','.gcpb-product-card-stone', function(event){
            gcpb_product_card_toggle($(this), event);
        });
    }else{
        $(document).on('mouseover','.gcpb-product-card-stone', function(event){
            gcpb_product_card_toggle($(this), event);
        });
    }
});

/****************************** */
/***** Select Diamonds Page *******/
/****************************** */

/****************************** */
/***** Single product page script *******/
/****************************** */

/****************************** */
/***** Product loop page javascript *******/
/****************************** */

const gcpb_single_product_data = {
    init: function () {
        jQuery(document).on('click','.gcpb-media-tabs .gcpb-media-tab', function(event){
            event.preventDefault();

            event.stopPropagation();

            event.stopImmediatePropagation();

            jQuery('.gcpb-product-image-wrapper').removeClass('otw_container_loading');    
                
            if(jQuery(this).hasClass("tab-video")){
                setLocalStorage('gcpb_selected_media_tab', 'video');

                if(!jQuery(".gcpb-product-image-wrapper .gcpb-product-image video").length >= 1){
                    jQuery(".gcpb-product-image-wrapper .gcpb-product-image picture").append(jQuery(".gcpb-thumbnails__content video").clone());
                    if(jQuery('body').hasClass("page-template-template-setting-single") && otw_woo_ring_builder.wp_is_mobile){
                        jQuery(".gcpb-thumbnails__content video").remove();
                    }
                    gcpb_load_video();
                }

                jQuery(".gcpb-product-image-wrapper .gcpb-product-image video").show();

                jQuery(".gcpb-product-image-wrapper .gcpb-product-image webgi-viewer").hide();

                jQuery(".gcpb-product-image-wrapper .gcpb-product-image .gcpb_swiper").hide();
            }else if(jQuery(this).hasClass("tab-gallery")){                
                setLocalStorage('gcpb_selected_media_tab', 'gallery');

                let gcbp_product_card_media_wrapper = jQuery(".gcpb-product-image-wrapper .gcpb-product-image");
                
                if(!gcbp_product_card_media_wrapper.find('.gcpb_swiper').length >= 1){
                    let gallery_images = [];

                    jQuery(".gcpb-thumbnails__content img").each(function(){
                        if(jQuery(this).hasClass("gcpb-product-gallery-thumb")){
                            if(jQuery(this).attr('data-lazy-src') && jQuery(this).attr('data-lazy-src') != 'undefined')
                                gallery_images.push('<img src="'+jQuery(this).attr('data-lazy-src')+'" />');
                            else if(jQuery(this).attr('data-thumb') && jQuery(this).attr('data-thumb') != 'undefined')
                                gallery_images.push('<img src="'+jQuery(this).attr('data-thumb')+'" />');
                            else{
                                gallery_images.push('<img src="'+jQuery(this).attr('src')+'" />');
                            }
                        }   
                    });
                   
                    gcpb_utility_functions.create_swiper(gallery_images, gcbp_product_card_media_wrapper.find('picture'));
                }

                jQuery(".gcpb-product-image-wrapper .gcpb-product-image video").hide();

                jQuery(".gcpb-product-image-wrapper .gcpb-product-image webgi-viewer").hide();

                jQuery(".gcpb-product-image-wrapper .gcpb-product-image .gcpb_swiper").show();
            }else{
                setLocalStorage('gcpb_selected_media_tab', 'webgi-viewer');

                jQuery(".gcpb-product-image-wrapper .gcpb-product-image video").hide();
                jQuery(".gcpb-product-image-wrapper .gcpb-product-image webgi-viewer").show();
                jQuery(".gcpb-product-image-wrapper .gcpb-product-image .gcpb_swiper").hide();

                gcpb_utility_functions.display_3d_model(jQuery(".gcpb-product-image-wrapper .gcpb-product-image"));
            }
    
            jQuery('.gcpb-media-tabs .gcpb-media-tab').removeClass('active');

            jQuery(this).addClass("active");

            return false;
        });
        
    }  
};

/****************************** */
/***** Single product page script *******/
/****************************** */

jQuery(document).ready(function($){
    $(document).on('click','.otw_select_diamond_dynamic_url', function(event){
        let redirect_url = $(this).data('stone-url');

        let product_id = $(this).data('product_id');

        let variation_id = $(this).data('variation_id');

        let stock_num = $(this).data('stock_num');

        if(product_id)
            redirect_url += "?product_id="+product_id;

        if(variation_id)
            redirect_url += "&variation_id="+variation_id;

        if(stock_num)
            redirect_url += "&stock_num="+stock_num;

        window.location.href = redirect_url;
        
        event.preventDefault();

        event.stopPropagation();

        event.stopImmediatePropagation();

        return false;
    });

    if($('body').hasClass("page-template-template-setting-single")){

        gcpb_single_product_data.init();
        // setLocalStorage('gcpb_selected_media_tab', 'gallery');
        $(".gcpb-thumbnails").find('.tab-gallery').trigger('click');
        gcpb_utility_functions.click_on_selected_tab($(".gcpb-product-wrapper .gcpb-product-content-wrapper"));
        
    }
    
    
});

/****************************** */
/***** checkout page script *******/
/****************************** */

jQuery(document).ready(function($){
    $('.gcpb-diamond-certificate-toggle').on('click', function(event) {
		event.preventDefault();
		$('.gcpb-diamond-certificate-popup').toggleClass('open');
        $('body').toggleClass('card-opened');
	});
    
    $(document).on('click','.gcpb_variation_featured_image', function(event){
        $(".gcpb-selected-images__featured_setting picture img").show();
        $(".gcpb-selected-images__featured_setting picture webgi-viewer").css("display", "none");
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    $(document).on('click','.gcpb_variation_three_d_image', function(event){
        $(".gcpb-selected-images__featured_setting picture img").hide();
        $(".gcpb-selected-images__featured_setting picture webgi-viewer").css("display", "block");
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    $(document).on('click','.gcpb_stone_featured_image', function(event){
        $(".gcpb-selected-images__featured_stone picture img").show();
        $(".gcpb-selected-images__featured_stone picture .diamond_3d_stone").css("display", "none");
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    $(document).on('click','.gcpb_stone_three_d_image', function(event){
        $(".gcpb-selected-images__featured_stone picture img").hide();
        $(".gcpb-selected-images__featured_stone picture .diamond_3d_stone").css("display", "block");
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    $(document).on('click','.gcpb-product-content-wrapper form button.gcpb-button', function(event){
        let size_value = $(this).parents('form').find('#size-selector').val();

        if(size_value && size_value != '' && size_value != '0'){
                return true;
        }else{
            let form_error = $(this).parents('form').find('.form_error');
            if(form_error.length <= 0){
                $(this).parents('form').find('.gcpb-completed-total-price').after('<p class="form_error"></p>');
            }
            $(this).parents('form').find('.form_error').html('Please select ring size.');
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        return false;
    });

    $(document).on('change','#size-selector', function(event){
        $(this).parents('form').find('.form_error').html('');
    });
});

/****************************** */
/***** checkout page script *******/
/****************************** */

jQuery(document).ready(function($){
    $(document).on('click','.gcpb-completed-wrapper form .gcpb-checkout-button', function(event){
        let size_selector_value = $("#size-selector").val();
        
        if (size_selector_value && size_selector_value != '0') return true;
        
        $(".required_fields_message").show();
        
        event.preventDefault();
        
        event.stopPropagation();
        
        event.stopImmediatePropagation();

        return false;
    });

    $(document).on('click','.gcpb-completed-wrapper form .gcpb-add-to-cart-button', function(event){
        let size_selector_value = $(this).parents('form').find('#size-selector').val();

        let product_id = $(this).data('product_id');

        let variation_id = $(this).data('variation_id');

        if (
            size_selector_value && 
            size_selector_value != '' && 
            size_selector_value != '0' && 
            product_id && 
            variation_id
        ) {
            $(this).parents('form').find('.required_fields_message').hide();

            $.ajax({
                type: 'POST',
                url: ajax_ring_obj.ajaxurl,
                async:   true,
                data: { 
                    'action': 'gcpb_add_to_cart', 
                    'size-selector': size_selector_value,
                    'variation_id': variation_id,
                    'product_id': product_id,
                },
                beforeSend: function() {
                    $('body').addClass('otw_container_loading');
                },
                success: function(data){
                    if(typeof data == 'object'){
                        for (var key in data) {
                            if (data.hasOwnProperty(key)) {
                                if($(key).length >= 1){
                                    $(key).html(data[key]);
                                }
                            }
                        }

                        $(".elementor-menu-cart__subtotal strong").html("Total: ");

                        $(".sidecart-toggle .cart-icon").trigger('click');
                    }

                    $('body').removeClass('otw_container_loading');
                },
                error: function(xhr) {
                    alert("Error occured.please try again");

                    $('body').removeClass('otw_container_loading');
                },
            });
        } else {
            let form_error = $(this).parents('form').find('.required_fields_message');

            if(form_error.length <= 0){
                $(this).parents('form').find('.gcpb-completed-total-price').after('<p class="required_fields_message"></p>');
            }

            $(this).parents('form').find('.required_fields_message').html('Please select size.').show();
        }

        event.preventDefault();

        event.stopPropagation();

        event.stopImmediatePropagation();

        return false;
    });
});

/****************************** */
/***** All pages script *******/
/****************************** */

jQuery(document).ready(function($){
    $(".elementor-menu-cart__subtotal strong").html("Total: ");

    $(document).on('click','.gcpb-step1, .gcpb-step2', function(event){
        if ($(this).hasClass('gcpb-step-completed')) {
            return true;
        }
        
        if ($('body').hasClass("page-template-template-setting-single")){
            $(".gcpb-product-actions .otw_select_diamond_dynamic_url").trigger('click');
            return true;
        }

        if ($('body').hasClass("page-template-template-stone-single")){
            $(".gcpb-product-content-wrapper a.gcpb-product-select-btn").trigger('click');
            return true;
        }

        let event_object = $(event.target);

        if (event_object.hasClass('gcpb-view-btn') || event_object.hasClass('gcpb-remove-btn') || event_object.hasClass('gcpb-step__title')) {
            return true;
        }
        
        event.preventDefault();

        event.stopPropagation();

        event.stopImmediatePropagation();

        window.location.href = $(this).find(".gcpb-step__title_link").attr('href');

        return false;
    });

    $(document).on('click','.gcpb-step3', function(event){
        if (!$(this).data('url')) {
            return true;
        }
        
        event.preventDefault();

        event.stopPropagation();

        event.stopImmediatePropagation();

        window.location.href = $(this).data('url');

        return false;
    });
});
