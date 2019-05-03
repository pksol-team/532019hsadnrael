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

   var process_bulk_optimize = function( step, data, self ) {

      $.ajax({
      	type: 'POST',
      	url: ajaxurl,
      	data: {
          _nonce: WPOptimiser_Admin.nonce,
      		form: data,
      		action: 'process_bulk_image_optimization',
      		step: step,
      	},
      	dataType: "json",
      	success: function( response ) {
          var bulk_form;

      		if( 'done' === response.step ) {

      			bulk_form = $('form.bulk-optimize');

      			bulk_form.find('.spinner').remove();
      			bulk_form.find('.bulk-progress-bar').remove();
            $('div#bulk-optimiz-table > #overlay').remove();

      			window.location = response.url;

      		}
          else if( 'error' === response.step ) {

      			bulk_form = $('form.bulk-optimize');

      			bulk_form.find('.spinner').remove();
      			bulk_form.find('.bulk-progress-panel').remove();
            $('div#bulk-optimiz-table > #overlay').remove();

            bulk_form.prepend( '<div class="error">'+response.message+'<div>' );
      		}
          else {

      			$('.bulk-progress-bar div').animate({
      				width: response.percentage + '%',
      			}, 50, function() {
      				// Animation complete.
      			});
      			process_bulk_optimize( parseInt( response.step ), data, self );
      		}
      	}
      }).fail(function (response) {
      	if ( window.console && window.console.log ) {
      		console.log( response );
      	}
      });
  };

  var process_db_optimization = function( dbAction) {

     $.ajax({
       type: 'POST',
       url: ajaxurl,
       data: {
         _nonce: WPOptimiser_Admin.nonce,
         action: 'process_db_optimization',
         dbaction: dbAction,
       },
       dataType: "json",
       success: function( response ) {
         var dbActionTD = $('td.'+response.dbaction);
         var replaceTD;

         if( 'done' === response.result ) {

           if(response.dbaction === 'db-optimize') {
             replaceTD = dbActionTD.prev();
             replaceTD.find(response.replace).replaceWith(response.html);
             process_db_update();
           }
           else {
             replaceTD = dbActionTD.prev();
             replaceTD.find(response.replace).replaceWith(response.html);
           }

           dbActionTD.html('<div class="button-action done">Completed<span class="dashicons dashicons-yes"></span></div>');
           //window.location = response.url;

         }
         else if( 'error' === response.result ) {

           dbActionTD.html('<div class="button-action err">Error Occurred - Please Try Again</div>');
           $('.wrap').prepend( '<div class="error">'+response.message+'<div>' );
         }
       }
     }).fail(function (response) {
       if ( window.console && window.console.log ) {
         console.log( response );
       }
     });
  };

  var process_db_update = function( ) {
    $('.db-efficiency').easyPieChart({
      barColor: '#46d746',
      trackColor: '#f05959',
      scaleColor: false,
      lineCap: 'butt',
      lineWidth: 8,
      animate:  2000,
      size:55
    });
  };

  $(document).ready(function(){

    $('.easypiechart').easyPieChart({
      barColor: '#9abc32',
      trackColor: '#EEEEEE',
      scaleColor: false,
      lineCap: 'round',
      lineWidth: 10,
      animate:  2000,
      size:100
    });

    process_db_update();

    $('input[name^="wpoptimiser-bulkoptimizeimgs-options[compimg]"]').on('change', function() {

      $('div#bulk-optimiz-table').append('<div id="overlay"><div class="overlay-spinner"></div></div>');
      $.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          _nonce: WPOptimiser_Admin.nonce,
          action: "update_bulk_compress_stats",
          sizes: $('table.bulk-optimize :input').serialize()
        },
        success: function(data) {
          $('.uncomp-images span.infobox-data-number').html(data.available_unoptimized_sizes);
          $('span.uncomp-images').html(data.available_unoptimized_sizes);
          $('.est-costs span.infobox-data-number').html(data.estimated_costs);
          $('div#bulk-optimiz-table > #overlay').remove();
        },
        error: function(xhr, textStatus, errorThrown) {
          $('div#bulk-optimiz-table > #overlay').remove();
        }
      });
    });

    $('form.bulk-optimize').on( 'submit', function(e) {
      e.preventDefault();

      var data = $(this).serialize();

      $('.stats-info').remove();
      $('div#bulk-optimiz-table').append('<div id="overlay"></div>');
      $(this).prepend( '<div class="bulk-progress-panel stats-info"><div>Processing Images...<span class="spinner is-active"></span></div><div class="bulk-progress-bar"><div></div></div></div>' );

      // start the process
      process_bulk_optimize( 1, data, self );
    });

    $('input.button-action').on( 'click', function(e) {
      e.preventDefault();

      // Confirm user has a backup
      if(!confirm("It is highly recommended to have a backup of your database before performing the optimization. Press OK if you are happy to continue.")) {
        return;
      }

      var dbAction = $(this).data('action');

      $(this).replaceWith( '<div class="button-action">Performing Optimization...<span class="spinner is-active"></span></div>' );

      // start the process
      process_db_optimization( dbAction );
    });

    $( "#start-site-profile" ).click( function() {

      // Make sure user wants to wait
      if(!confirm('Are you sure you want to start the profiler? This may take several minutes and you must leave this window open to allow the profiler to complete successfully.')) {
        return;
      }

      // Replace profile button with progress bar and spinner
      $('p.submit').html( '<div class="bulk-progress-panel"><div>Profiling Site Performance...<span class="spinner is-active"></span></div><div class="bulk-progress-bar"><div></div></div></div>' );

      // Show iFrame
      $( "tr.hidden" ).show();

      // Show progress bar

      // Start profiler
			WPOPTI_Site_Profiler.start();
		});

  // End Document Ready
  });

  var WPOPTI_Site_Profiler = {

  		// Current page
  		current_page: 0,

  		// Create a random string
  		random: function(length) {
  			var ret = "";
  			var alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  			for ( var i = 0 ; i < length ; i++ ) {
  				ret += alphabet.charAt( Math.floor( Math.random() * alphabet.length ) );
  			}
  			return ret;
  		},

  		// Start
  		start: function() {

  			// If cache prevention is disabled, remove WPOPTI_NOCACHE from the pages
  			if ( $( '#wpopti-cache-preventer' ).prop( 'checked' ) ) {
  				for ( i = 0 ; i < WPOPSP_Pages.length ; i++ ) {
  					if ( WPOPSP_Pages[i].indexOf('?') > -1 ) {
  						WPOPSP_Pages[i] += '&WPOPTI_NOCACHE=' + WPOPTI_Site_Profiler.random(8);
  					} else {
  						WPOPSP_Pages[i] += '?WPOPTI_NOCACHE=' + WPOPTI_Site_Profiler.random(8);
  					}
  				}
  			}

  			// Form data
  			var data = {
          'ip': WPOPSP_IP,
  				'action' : 'WPOPTI_start_profiling',
  				'nonce' : WPOptimiser_Admin.nonce
  			};

  			// Turn on the profiler
  			$.post( ajaxurl, data, function( response ) {

  				// Start scanning pages
          $( "#wpopti-profiler-frame" ).attr( "src", WPOPSP_Pages[0] );
  				$( "#wpopti-profiler-frame" ).attr( "onload", "window.WPOPTIS_Profiler.next_page();" );
  				WPOPTI_Site_Profiler.current_page = 0;
  				WPOPTI_Site_Profiler.update_display();
  			});
  		},

  		// Stop
  		stop: function() {
  			// Turn off the profiler
  			var data = {
          'action' : 'WPOPTI_stop_profiling',
          'nonce' : WPOptimiser_Admin.nonce
  			};
  			$.post( ajaxurl, data, function( response ) {
          alert('The profiling is complete, the page will be refreshed and the profile report will be displayed.');
          window.location.replace(WPOPSP_Location);
  			});
  		},

  		// Update the display
  		update_display : function() {
  			var percentage = ( WPOPTI_Site_Profiler.current_page / ( WPOPSP_Pages.length - 1 ) ) * 100;
        $('.bulk-progress-bar div').animate({ width: percentage + '%' }, 50);
  		},

  		// Look at the next page
  		next_page : function() {

  			// Is it time to stop?
  			if ( WPOPTI_Site_Profiler.current_page >= WPOPSP_Pages.length - 1 ) {
  				WPOPTI_Site_Profiler.stop();
  				return true;
  			}

  			// Next page
  			$( "#wpopti-profiler-frame" ).attr( "src", WPOPSP_Pages[++WPOPTI_Site_Profiler.current_page] );

  			// Update the display
  			WPOPTI_Site_Profiler.update_display();
  		}
  	};

    window.WPOPTIS_Profiler = WPOPTI_Site_Profiler;
})( jQuery );
