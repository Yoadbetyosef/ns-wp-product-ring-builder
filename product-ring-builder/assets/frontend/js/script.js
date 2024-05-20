if (typeof setLocalStorage != 'function') {
    function setLocalStorage(key, value){
        if (typeof(Storage) !== "undefined") {
            localStorage.setItem(key, value);
        }
    }
}

if (typeof getLocalStorage != 'function') {
    function getLocalStorage(key){
        if (typeof(Storage) !== "undefined") {
            return localStorage.getItem(key);
        }else{
            return '';
        }
    }
}

if (typeof setSessionStorage != 'function') {
    function setSessionStorage(key, value){
      if (typeof(Storage) !== "undefined") {
        window.sessionStorage.setItem(key, value);
      }
    }
  }
  
if (typeof getSessionStorage != 'function') {
    function getSessionStorage(key){
        if (typeof(Storage) !== "undefined") {
            return window.sessionStorage.getItem(key);
        }else{
            return '';
        }
    }
}

jQuery(document).ready(function($){
    // console.log(otw_woo_ring_builder.wp_is_mobile);
    // console.log('testsss');

    // $(document).on('click', '.gcpb-product-image picture img', function(event){
        // $(this).wrap('<a href="' + $(this).attr("src") + '" rel="lightbox" data-lightbox="image-1" />');
        // $(this).parent('a').trigger('click');
    // });

});