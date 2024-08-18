jQuery(document).ready(function($){

    // on upload button click
	$( 'body' ).on( 'click', '.gcpb-upload', function( event ){
		event.preventDefault(); // prevent default link click and page refresh
		
		const button = $(this)
		const imageId = button.next().next().val();
		
		const customUploader = wp.media({
			title: 'Insert image', // modal window title
			library : {
				// uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
				type : 'image'
			},
			button: {
				text: 'Use this image' // button label text
			},
			multiple: false
		}).on( 'select', function() { // it also has "open" and "close" events
			const attachment = customUploader.state().get( 'selection' ).first().toJSON();
			button.removeClass( 'button' ).html( '<img src="' + attachment.url + '">'); // add image instead of "Upload Image"
			button.next().show(); // show "Remove image" link
			button.next().next().val( attachment.id ); // Populate the hidden field with image ID
		})
		
		// already selected images
		customUploader.on( 'open', function() {

			if( imageId ) {
			  const selection = customUploader.state().get( 'selection' )
			  attachment = wp.media.attachment( imageId );
			  attachment.fetch();
			  selection.add( attachment ? [attachment] : [] );
			}
			
		})

		customUploader.open()
	
	});
    
	// on remove button click
	$( 'body' ).on( 'click', '.gcpb-remove', function( event ){
		event.preventDefault();
		const button = $(this);
		button.next().val( '' ); // emptying the hidden field
		button.hide().prev().addClass( 'button' ).html( 'Upload Icon' ); // replace the image with text
	});

    $( 'body' ).on( 'click', '.gcpb-remove-media', function( event ){
		event.preventDefault();
		const button = $(this);
		button.next().val( '' ); // emptying the hidden field
		button.hide().prev().addClass( 'button' ).html( 'Upload Image' ); // replace the image with text
	});    
 
    // $(document).ready(function( $ ){
    //     $( '#add-row' ).on('click', function() {
    //         var row = $( '.empty-row.screen-reader-text' ).clone(true);
    //         var data_id = $( '#repeatable-fieldset-one tbody>tr:last' ).find('input[name="data_id[]"]').val();
    //         var id = parseInt(data_id)+1;
            
    //         $( '.empty-row.screen-reader-text' ).find('input[name="data_id[]"]').val(id);
    //         row.find('input[name="data_id[]"]').val(id);
    //         row.removeClass( 'empty-row screen-reader-text' );
    //         row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
    //         return false;
    //     });

    //     $( '.remove-row' ).on('click', function() {
            
    //         $(this).parents('tr').remove();
    //         return false;
    //     });
    // });

    // var repeater  = $('.gcpb-repeater').repeater({
    //     initval: 1,
    //         // initEmpty: false,
    //         //  show: function () {
    //         //     $(this).slideDown();
        
    //         //     // Re-init select2
    //         //     $(this).find('[data-repeater="select2"]').select2({
    //         //         width: '200px',
    //         //         placeholder: "Please select attribute",
    //         //     });
    //         // },        
    //         // ready: function(setIndexes){
    //         // //    $dragAndDrop.on('drop', setIndexes);

    //         //     // Init select2
    //         //     $('[data-repeater="select2"]').select2({
    //         //         width: '200px',
    //         //         placeholder: "Please select attribute",
    //         //     });
    //         // },
    //         repeaters: [{
    //             // initEmpty: false,
    //             selector: '.gcpb-inner-repeater',
    //             // show: function () {
    //             //     $(this).slideDown();
            
    //             //     // Re-init select2
    //             //     $(this).find('[data-repeater="select2"]').select2({
    //             //         width: '200px',
    //             //         placeholder: "Please select attribute",
    //             //     });
    //             // },
    //         },

    //     ],
    // });

    // $('.js-gcpb-select2').select2({
    //     width: '200px',
    //     placeholder: "Please select attribute",
    // });
  
    // $(document).bind('click', '.js-gcpb-inner-add-row', function(){
    //     // $('.js-gcpb-select2').select2({
    //     //     width: '200px',
    //     // });
    // });

    // $(".mt-repeater").repeater({
    //     initEmpty: true,
    //     initval: 1,
    //     show: function () {
    //         $(this).find('.color-picker').minicolors();
    //         $(this).slideDown();
    //     }
    // });
    
    // create the first group
    // $('[data-repeater-create]').trigger('click');  
    
    // var repeater = $('.repeater-default').repeater({
    //     initval: 1,
    //   });
       
         /** inner row */
    $(document).on('click', '.js-gcpb-inner-add-row',function(event){
        var clone_inner = $('#child-clone').html();

        $(this).before(clone_inner);

        // $(clone_inner).insertBefore("p");

        renumber_blocks();
        attachDragDrop();
        //initialize_select2();    
    });

    /** parent row */
    $(document).on('click', '.js-gcpb-add-row',function(event){

        var clone_parent = $('#parent-clone').html();

       // $(this).parents('.gcpb-repeater').find('.repeater-main').append(clone_parent);

        $(this).before(clone_parent);

        renumber_blocks();
        attachDragDrop();
       // initialize_select2();
    });

    $(document).on('click', '[data-repeater-delete]', function(event){
        event.preventDefault();

        $(this).closest('[data-repeater-item]').remove();
        renumber_blocks();
    });
 

    function initialize_select2() {
       // $('.js-gcpb-select2').trigger('change.select2');
    }
    // function renumber_blocks() {
    //     var wrap = $('.gcpb-repeater');
    //     var parent_name = wrap.find('.gcpb-parent').data('repeater-parent-list');
    //     var elements = wrap.find('.gcpb-repeater-field');

    //     elements.each(function(index) {
    //         var parent_key = $(this).parents('.repeater-block').data('repeater-name');
    //         var p_name = '';
            
    //         console.log(parent_key);
    //         console.log($(this).attr('name'));
    //         var field_name = $(this).attr('name');
    //         p_name = parent_key+'['+field_name+']';

    //         // $(this).find("select").each(function() {
    //         //    this.name = this.name.replace(/items\[\d+\]/, prefix);   
    //         // });
    //     });
    // }
    
    renumber_blocks();

    function renumber_blocks() {

        var $wrap = $('.gcpb-repeater');
        var $repeater_main = $wrap.find('.repeater-main').data('repeater-name');
 
        var elements = $wrap.find('.gcpb-repeater-field');

        var data = [
            {
                'text-input': 'set-a',
                'inner-group': [{ 'inner-text-input': 'set-b' }]
            },
            { 'text-input': 'set-foo' }
        ];

        $wrap.find('[data-repeater-item="repeater"]').each(function(index){

            // var $repeater_main = $(this).data('repeater-name');
            var $type = $(this).data('repeater-item');

             $(this).find('.gcpb-repeater-parent .gcpb-repeater-field').each(function(index_parent){
  
                var name;
                name = $(this).data('name');
                name = $repeater_main+'['+index+']'+'['+name+']';
                $(this).attr('name',name);
 
            });

            //console.log($type);
            var parent_name = $repeater_main+'['+index+']';

            $(this).find('[data-repeater-item="sub-repeater"]').each(function(sub_repeater){
                var $sub_repeater_name = $(this).data('repeater-name');

                $(this).find('.gcpb-repeater-child .gcpb-repeater-field').each(function(index_parent){
  
                    var name;
                    name = $(this).data('name');
                    name = $repeater_main+'['+index+']'+'['+$sub_repeater_name+']'+'['+sub_repeater+']'+'['+name+']';
                    $(this).attr('name',name);
     
                });
            });

            console.log(parent_name);
 
         });


         //elements.each(function(index) {
        //     var repeater_name = $(this).parents('[data-repeater-item]').parent('[data-repeater-name]').data('repeater-name');
        //     // $('[data-repeater-item]').each(function(index){

        //         // var type = $(this).data('repeater-item');
        //         //var repeater_name = $(this).parents('[data-repeater-name]').data('repeater-name');

        //         //$(this).find('[data-repeater-name]');

        //         console.log(repeater_name);
        //         // console.log(type);
        //     // });
        // });

        // $repater_main

        // var elements = wrap.find('.gcpb-repeater-field');

        // elements.each(function(index) {
        //     var parent_key = $(this).parents('.repeater-block').data('repeater-name');
        //     var p_name = '';
            
        //     console.log(parent_key);
        //     console.log($(this).attr('name'));
        //     var field_name = $(this).attr('name');
        //     p_name = parent_key+'['+field_name+']';

        //     // $(this).find("select").each(function() {
        //     //    this.name = this.name.replace(/items\[\d+\]/, prefix);   
        //     // });
        // });
    }
    
    attachDragDrop();

    function attachDragDrop() {
        jQuery(".drag").sortable({
            axis: "y",
            cursor: 'pointer',
            opacity: 0.5,
            placeholder: "row-dragging",
            delay: 150,
            update: function(event, ui) {
                renumber_blocks();
            }
            // update: function(event, ui) {
            //     $('.repeater-default').repeater( 'setIndexes' );
            // }
        
        }).disableSelection();
    }

 
});