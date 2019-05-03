<?php

class Swift_Performance_GA{

       /**
        * Build instance
        */
       public function __construct(){
             // Tracking
             $this->tracking();

             // Print tracking pixel
             add_action('wp_footer', array($this, 'print_tracking_pixel'), PHP_INT_MAX);
       }


       /**
        * Print the tracking pixel
        */
       public function print_tracking_pixel(){
             if (Swift_Performance::check_option('delay-ga-collect', 1)){
                   echo '<script>window.sp_lazyload_ga_buffer = []; function ga(action, type, event_category, event_action, event_label, event_value) { action = action || ""; type = type || ""; event_category = event_category || ""; event_action = event_action || ""; event_label = event_label || ""; event_value = event_value || ""; window.sp_lazyload_ga_buffer.push({"action" : action, "type" : type, "event_category" : event_category, "event_action" : event_action, "event_label" : event_label, "event_value" : event_value}); } document.addEventListener("DOMContentLoaded", function(event) { ga("send", "pageview"); }); (function(){ function fire(){ window.removeEventListener("touchstart",fire); window.removeEventListener("scroll",fire); document.removeEventListener("mousemove",fire); window.ga = function(action, type, event_category, event_action, event_label, event_value) { function q(key) { return (window.location.search.replace(new RegExp("^(?:.*[&\\?]" + encodeURIComponent(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1")); } var img = new Image(); var ec = event_category || ""; var ea = event_action || ""; var el = event_label || ""; var ev = event_value || 0; var event_parameters = ""; if (type == "event") { event_parameters = "&ec=" + encodeURIComponent(ec) + "&ea=" + encodeURIComponent(ea) + "&el=" + encodeURIComponent(el) + "&ev=" + encodeURIComponent(ev); } img.src = "'.home_url() . '?swift-performance-tracking=1&spr=" + parseInt(Math.random() * 1000000000) + "&t=" + type + "&dp=" + encodeURIComponent(window.location.pathname) + "&dr=" + encodeURIComponent(document.referrer) + "&cs=" + q("utm_source") + "&cm=" + q("utm_medium") + "&cn=" + q("utm_campaign") + "&ck=" + q("utm_term") + "&cc=" + q("utm_content") + "&gclid=" + q("gclid") + "&dclid=" + q("dclid") + "&sr=" + window.screen.availWidth + "x" + window.screen.availHeight + "&vp=" + window.innerWidth + "x" + window.innerHeight + "&de=" + document.charset + "&sd=" + screen.colorDepth + "-bits" + "&dt=" + encodeURIComponent(document.title) + event_parameters; }; while(window.sp_lazyload_ga_buffer.length > 0){ var r = window.sp_lazyload_ga_buffer.shift(); if (typeof r !== "undefined"){ window.ga(r["action"], r["type"], r["event_category"], r["event_action"], r["event_label"], r["event_value"]); } } } window.addEventListener("load", function() { window.addEventListener("touchstart",fire); window.addEventListener("scroll",fire); document.addEventListener("mousemove",fire); }); })();</script>';
             }
             else{
                   echo '<script>document.addEventListener("DOMContentLoaded", function(event) {ga("send", "pageview");});function ga(action, type, event_category, event_action, event_label, event_value){ function q(key) {return (window.location.search.replace(new RegExp("^(?:.*[&\\?]" + encodeURIComponent(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1"));} var img = new Image(); var ec = event_category || ""; var ea = event_action || ""; var el = event_label || ""; var ev = event_value || 0; var event_parameters = ""; if (type == "event"){ event_parameters = "&ec=" + encodeURIComponent(ec) + "&ea=" + encodeURIComponent(ea) + "&el=" + encodeURIComponent(el) + "&ev=" + encodeURIComponent(ev); } img.src = "'.home_url() . '?swift-performance-tracking=1&spr=" + parseInt(Math.random()*1000000000) + "&t=" + type + "&dp=" + encodeURIComponent(window.location.pathname) + "&dr=" + encodeURIComponent(document.referrer) + "&cs=" + q("utm_source") + "&cm=" + q("utm_medium") + "&cn=" + q("utm_campaign") + "&ck=" + q("utm_term") + "&cc=" + q("utm_content") + "&gclid=" + q("gclid") + "&dclid="+q("dclid") + "&sr=" + window.screen.availWidth + "x" + window.screen.availHeight + "&vp=" + window.innerWidth + "x" + window.innerHeight + "&de=" + document.charset + "&sd=" + screen.colorDepth + "-bits" + "&dt=" + encodeURIComponent(document.title) + event_parameters;}</script>';
             }
       }

       /**
        * Fire Google Analytics Tracking
        */
       public function tracking(){
             $tid       = Swift_Performance::get_option('ga-tracking-id');
             $ipsource  = Swift_Performance::get_option('ga-ip-source');
             if (isset($_GET['swift-performance-tracking']) && $_GET['swift-performance-tracking'] == 1 && !empty($tid)){
                  if (isset($_COOKIE['spcid'])){
                   	$cid = $_COOKIE['spcid'];
                   }
                   else {
                        if (Swift_Performance::check_option('cookies-disabled', 1, '!=')){
                   	      $cid = uniqid("",true);
                              setcookie('spcid', $cid, time() + (3600 * 24 * 180), "/");
                        }
                   }


                  $query = array(
                        'v'         => 1,
                        'tid'       => $tid,
                        'ds'        => 'web',
                        'cn'        => $_GET['cn'],
                        'cs'        => $_GET['cs'],
                        'cm'        => $_GET['cm'],
                        'ck'        => $_GET['ck'],
                        'cc'        => $_GET['cc'],
                        'gclid'     => $_GET['gclid'],
                        'dclid'     => $_GET['dclid'],
                        'sr'        => $_GET['sr'],
                        'vp'        => $_GET['vp'],
                        'de'        => $_GET['de'],
                        'sd'        => $_GET['sd'],
                        'ul'        => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
                        'dt'        => $_GET['dt'],
                        'cid'       => $cid,
                        't'         => $_GET['t'],
                        'dp'        => $_GET['dp'],
                        'dr'        => $_GET['dr'],
                        'uip'       => $_SERVER[$ipsource],
                        'ua'        => $_SERVER['HTTP_USER_AGENT'],
                        'ec'        => isset($_GET['ec']) ? $_GET['ec'] : '',
                        'ea'        => isset($_GET['ea']) ? $_GET['ea'] : '',
                        'el'        => isset($_GET['el']) ? $_GET['el'] : '',
                        'ev'        => isset($_GET['ev']) ? $_GET['ev'] : '',
                  );

                  // Anonymize IP
                  if (Swift_Performance::check_option('ga-anonymize-ip',1)){
                        $query['aip'] = 1;
                  }

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
                  ob_end_flush();
                  @ob_flush();
                  flush();

                  $excluded_roles = Swift_Performance::get_option('ga-exclude-roles');
                  if (!empty($excluded_roles) && isset($_COOKIE[LOGGED_IN_COOKIE]) && !empty($_COOKIE[LOGGED_IN_COOKIE])){
                        @list($login, ) = explode('|', $_COOKIE[LOGGED_IN_COOKIE]);
                        if (!empty($login)){
                              global $wpdb;
                              $roles = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} LEFT JOIN {$wpdb->users} ON user_id = ID WHERE user_login = %s AND meta_key LIKE '{$wpdb->prefix}capabilities'", $login)));
                              foreach ($excluded_roles as $excluded_role){
                                    if ( isset($roles[$excluded_role]) ) {
                                        // Role is excluded, stop here
                                        die;
                                    }
                              }
                        }
                  }

                  $response = wp_remote_post('https://www.google-analytics.com/collect', array(
                        'ssl_verify'      => false,
                        'body'            => array_filter($query)
                  ));
                  if (is_wp_error($response)){
                        Swift_Performance::log('Google Analytics bypass failed. Error: ' . $response->get_error_message(), 1);
                  }
                  die;
            }

       }

 }

 // Let's do it
 new Swift_Performance_GA();
