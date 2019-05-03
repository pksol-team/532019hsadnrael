(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

   var WPOPTI_compress_image = function (event) {
    var element = $(event.target);
    var container = element.closest('div.wpotimiser-container');
    element.attr('disabled', 'disabled');
    container.find('span.spinner').removeClass('hidden');
    container.find('span.dashicons').remove();
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        _nonce: WPOptimiser_Admin.nonce,
        action: 'process_image_optimization',
        id: element.data('id') || element.attr('data-id')
      },
      success: function(data) {
        container.html(data);
      },
      error: function() {
        element.removeAttr('disabled');
        container.find('span.spinner').addClass('hidden');
      }
    });
  };

  $(document).ready(function(){

    $(document).on('click', 'button.wpopti-compress', WPOPTI_compress_image);

  // End Document Ready
  });

})( jQuery );
