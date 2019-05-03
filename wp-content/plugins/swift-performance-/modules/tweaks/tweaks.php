<?php

class Swift_Performance_Tweaks {


      /**
       * Construct Tweak object
       */
      public function __construct(){
            // Background requests
            self::background_requests();
      }

      /**
       * Check request and call flush connection if the parameters are met with one rule of the ruleset
       */
      public static function background_requests(){
            foreach ((array)Swift_Performance::get_option('background-requests') as $rule) {
                  @list($key, $value) = explode('=', $rule);
                  $key        = trim($key);
                  $value      = (!isset($value) ? '' : trim($value));
                  if (isset($key) && !empty($key) && isset($_REQUEST[$key]) && $_REQUEST[$key] == $value){
                        self::flush_connection();
                  }
            }

      }

      /**
       * Close connection early and keep executing in background
       */
      public static function flush_connection(){
            //Headers
            header("Content-Type: image/gif");
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");

            ob_start();
            //1x1 Transparent Gif
            echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
            //Send full content and keep executeing
            header('Connection: close');
            header('Content-Length: '.ob_get_length());
            @ob_end_flush();
            @ob_flush();
            @flush();
            if (function_exists('fastcgi_finish_request')){
                  fastcgi_finish_request();
            }
      }

}

return new Swift_Performance_Tweaks();

?>
