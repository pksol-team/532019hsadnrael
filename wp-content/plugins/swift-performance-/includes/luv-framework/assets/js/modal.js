jQuery(function(){
      jQuery(document).on('click', '[data-luv-close-modal]', function(e){
            e.preventDefault();
            jQuery(this).closest('.luv-modal').addClass('luv-modal-hide');
      });
});