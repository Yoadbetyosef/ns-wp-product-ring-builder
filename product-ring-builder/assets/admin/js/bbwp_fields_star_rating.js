(function ( $ ) {
 
  $.fn.wpbbfontstar = function (options, callBackFunction) {
      $.fn.wpbbfontstar.callBackFunction = callBackFunction || function () {}

      $.fn.wpbbfontstar.defaults = $.extend({}, $.fn.wpbbfontstar.defaults, options);

      this.each(function(i) {
          $.fn.wpbbfontstar.star.init(this);
      });

      
      
      return this;
  };

  $.fn.wpbbfontstar.defaults = {
      icon: "far fa-star",
      iconfull: "fa fa-star",
      hovercolor: "#F39F25",
      starcolor: "#969696",
      selectable: true,
      stardirection: "right",
      starsize: "16px",
      disable_zoro:true,
  default_value: "",
  };

  $.fn.wpbbfontstar.star = {
      ul: [],
      li: [],
      selectValue: [],
      init: function (self) {
          var ul = $("<ul>").addClass("wpbbfontstar");
          $(self).after(ul);
          var me = this;
          
          var length = this.li.length;
          this.selectValue[length] = $(self).val();
          this.li[length] = [];

          $(self).find('option').each(function () {
              
              var lit = $('<li>').html('');
              if ($(this).val() != "" && ($.fn.wpbbfontstar.defaults.disable_zoro && $(this).val() != "0")) {
                  lit.attr('class', $.fn.wpbbfontstar.defaults.icon);
                  lit.css('color', $.fn.wpbbfontstar.defaults.starcolor);
                  lit.css({
                      'list-style': 'none',
                      'float': $.fn.wpbbfontstar.defaults.stardirection,
                      'padding': '0 2px',
                      'font-size': $.fn.wpbbfontstar.defaults.starsize,
                  });
                  if ($.fn.wpbbfontstar.defaults.selectable) {
                      lit.css('cursor', 'pointer');
                  }else{
                      lit.css('cursor', 'default');
                  }
                  lit.attr('data-value', $(this).val());
                  lit.attr('data-text', $(this).text());
                  lit.attr('data-index', length);
                  lit.appendTo(ul);
                  
                  me.li[length].push(lit);
              }
          });


          var me = this;
          
          $.each(me.li, function (index, value) {
              if (me.selectValue[index] && me.selectValue[index] != "" && ($.fn.wpbbfontstar.defaults.disable_zoro && me.selectValue[index] != "0")) {
                  me.redrawStar(me.selectValue[index], me.li[index]);
              }
          });
$(self).hide();
      },
      redrawStar: function (value, li) {
          var loopEnded = false;
          if(value == "" || ($.fn.wpbbfontstar.defaults.disable_zoro && value == "0")){
              loopEnded = true;
          }
          
          $.each(li, function (i, val) {
              
              if (!loopEnded) {
                  $(this).css('color', $.fn.wpbbfontstar.defaults.hovercolor);
                  $(this).attr('class', $.fn.wpbbfontstar.defaults.iconfull);
                  
              } else {
                  $(this).css('color', $.fn.wpbbfontstar.defaults.starcolor);
                  $(this).attr('class', $.fn.wpbbfontstar.defaults.icon);
              }                
              if (value == $(this).attr('data-text')) {
                  loopEnded = true;
              }

          });
      }
  };




}( jQuery ));


jQuery(document).ready(function($){
$('body').on("click"/*, mouseout*/, "ul.wpbbfontstar li", function(){
    var parent_select_list = $(this).parents('ul').prev('select');
          var label = $(this).attr('data-text');
          var value = $(this).attr('data-text');
          var index = $(this).attr('data-index');
    if(parent_select_list.val() === value){
              value = $.fn.wpbbfontstar.defaults.default_value;
          }
          //$.fn.wpbbfontstar.star.redrawStar(label, $.fn.wpbbfontstar.star.li[index]);
          $.fn.wpbbfontstar.star.redrawStar(label, $(this).parents('ul').find("li"));
          $(this).parents('ul').prev('select').val(value);
          $.fn.wpbbfontstar.star.selectValue[index] = value;
          $.fn.wpbbfontstar.callBackFunction($.fn.wpbbfontstar.star.selectValue[index], self);
      });

      $('body').on("mouseenter"/*, mouseout*/, "ul.wpbbfontstar li", function(){
          var label = $(this).attr('data-text');
          var value = $(this).attr('data-text');
          var index = $(this).attr('data-index');
          //$.fn.wpbbfontstar.star.redrawStar(label, $.fn.wpbbfontstar.star.li[index]);
          $.fn.wpbbfontstar.star.redrawStar(label, $(this).parents('ul').find("li"));
          //$(this).parents('ul').prev('select').val(value);
          //$.fn.wpbbfontstar.star.selectValue[index] = value;
          //$.fn.wpbbfontstar.callBackFunction($.fn.wpbbfontstar.star.selectValue[index], self);
      });

      $('body').on("mouseout"/*, */, "ul.wpbbfontstar li", function(){
          current_selected_value = $(this).parents('ul').prev('select').val();
          //console.log(current_selected_value);
          $.fn.wpbbfontstar.star.redrawStar(current_selected_value, $(this).parents('ul').find("li"));
      });




/*	$('.voting select').wpbbfontstar({default_value:0},function(value,self){
    //console.log("hello "+value);
  });

  $( document ).ajaxComplete(function() {
    if($('.voting select:visible').length >= 1)
{
$('.voting select:visible').wpbbfontstar({default_value:0},function(value,self){});
}

});*/

});