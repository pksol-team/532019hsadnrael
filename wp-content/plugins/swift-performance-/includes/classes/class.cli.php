<?php

/**
 * Basic CLI support
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
      class Swift_Performance_CLI {

            public function __construct(){
                  // Clear all cache
                  WP_CLI::add_command( 'sp_clear_all_cache', function(){
                        Swift_Performance_Cache::clear_all_cache();
                        WP_CLI::success( __('All cache cleared', 'swift-performance') );
                  });

                  // Clear expired
                  WP_CLI::add_command( 'sp_clear_expired', function(){
                        Swift_Performance_Cache::clear_expired();
                        WP_CLI::success( __('Expired cache cleared', 'swift-performance') );
                  });

                  // Clear permalink cache
                  WP_CLI::add_command( 'sp_clear_permalink_cache', function($args){
                        Swift_Performance_Cache::clear_permalink_cache($args[1]);
                        WP_CLI::success( __('Permalink cache cleared', 'swift-performance') );
                  });

                  // Clear post cache
                  WP_CLI::add_command( 'sp_clear_post_cache', function($args){
                        Swift_Performance_Cache::clear_post_cache($args[1]);
                        WP_CLI::success( __('Post cache cleared', 'swift-performance') );
                  });

                  // Clear user cache
                  WP_CLI::add_command( 'sp_clear_user_cache', function($args){
                        Swift_Performance::clear_user_cache($args[1]);
                        WP_CLI::success( __('User cache cleared', 'swift-performance') );
                  });




            }
      }

      new Swift_Performance_CLI();
}

?>