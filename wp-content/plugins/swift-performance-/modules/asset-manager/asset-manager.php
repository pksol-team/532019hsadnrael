<?php

class Swift_Performance_Asset_Manager {

      /**
	 * Intermediate image sizes
	 */
	public $image_sizes;

      /**
       * Create Instance
       */
      public function __construct(){
            do_action('swift_performance_assets_manager_before_init');

		// Set lazyload image size
		if (!defined('SWIFT_PERFORMANCE_LAZYLOAD_SIZE')){
			define('SWIFT_PERFORMANCE_LAZYLOAD_SIZE', 20);
		}

            if (Swift_Performance::check_option('merge-scripts', 1) || Swift_Performance::check_option('merge-styles', 1) || Swift_Performance::check_option('lazy-load-images', 1) || Swift_Performance::check_option('lazyload-iframes', 1) || Swift_Performance::check_option('minify-html',1) || Swift_Performance::check_option('wpcf7-smart-load',1)){
                  // Prepare JS buffer
                  $GLOBALS['swift-performance-js-buffer'] = array();

                  // Include DOM parser
                  include_once 'dom-parser.php';

			// Include CSS Min
			include_once 'CSSMin.class.php';

                  // Do the magic
                  $this->asset_manager();

                  // Proxy 3rd party assets
                  add_action('init', array('Swift_Performance_Asset_Manager', 'proxy_3rd_party_request'));
            }


            // Remove version query string from static resources
            if (Swift_Performance::check_option('normalize-static-resources', 1) && !Swift_Performance::is_admin()){
			// Prevent elementor conflict
			if ((isset($_GET['action']) && $_GET['action'] == 'elementor') || isset($_GET['elementor-preview'])){
				return;
			}

                  add_filter('style_loader_src', array($this, 'remove_static_ver'), 10, 2);
                  add_filter('script_loader_src', array($this, 'remove_static_ver'), 10, 2);
                  add_filter('get_post_metadata', array($this, 'normalize_vc_custom_css'), 10, 4);
            }

		// Disable jQuery Migrate
		if (Swift_Performance::check_option('disable-jquery-migrate', 1)){
			add_action('wp_default_scripts', function($scripts){
				if (!is_admin() && isset($scripts->registered['jquery'])){
					$script = $scripts->registered['jquery'];

					if ($script->deps){
						$script->deps = array_diff($script->deps, array('jquery-migrate'));
					}
				}
			});
		}

            /*
             * Lazy load
             */

            // Images
            if (Swift_Performance::check_option('lazy-load-images', 1) && !Swift_Performance::is_admin()){
                  add_action('init', array($this, 'intermediate_image_sizes'));
                  add_action('wp_head', function(){
                        if (Swift_Performance::check_option('load-images-on-user-interaction', 1)){
                              $fire = 'var fire=function(){window.removeEventListener("touchstart",fire);window.removeEventListener("scroll",fire);document.removeEventListener("mousemove",fire);requestAnimationFrame(ll)};window.addEventListener("touchstart",fire,true);window.addEventListener("scroll",fire,true);document.addEventListener("mousemove",fire);';
                        }
                        else{
                              $fire = 'requestAnimationFrame(ll)';
                        }

                        echo "<script data-dont-merge=\"\">(function(){function iv(a){if(typeof a.getBoundingClientRect!=='function'){return false}var b=a.getBoundingClientRect();return((a.innerHeight||a.clientHeight)>0&&b.bottom+50>=0&&b.right+50>=0&&b.top-50<=(window.innerHeight||document.documentElement.clientHeight)&&b.left-50<=(window.innerWidth||document.documentElement.clientWidth))}function ll(){var a=document.querySelectorAll('[data-swift-image-lazyload]');for(var i in a){if(iv(a[i])){a[i].onload=function(){window.dispatchEvent(new Event('resize'));};a[i].setAttribute('src',(typeof a[i].dataset.src != 'undefined' ? a[i].dataset.src : a[i].src));a[i].setAttribute('srcset',a[i].dataset.srcset);a[i].setAttribute('style',a[i].dataset.style);a[i].removeAttribute('data-swift-image-lazyload')}}requestAnimationFrame(ll)}{$fire}})();</script>";
                  },PHP_INT_MAX);
            }

            // Iframes
            if (Swift_Performance::check_option('lazyload-iframes', 1) && !Swift_Performance::is_admin()){
                  add_action('wp_head', function(){
                        if (Swift_Performance::check_option('load-iframes-on-user-interaction', 1)){
                              $fire = 'var fire=function(){window.removeEventListener("touchstart",fire);window.removeEventListener("scroll",fire);document.removeEventListener("mousemove",fire);requestAnimationFrame(ll)};window.addEventListener("touchstart",fire,true);window.addEventListener("scroll",fire,true);document.addEventListener("mousemove",fire);';
                        }
                        else{
                              $fire = 'requestAnimationFrame(ll)';
                        }

                        echo "<script data-dont-merge=\"\">(function(){function iv(a){if(typeof a.getBoundingClientRect!=='function'){return false}var b=a.getBoundingClientRect();return(b.bottom+50>=0&&b.right+50>=0&&b.top-50<=(window.innerHeight||document.documentElement.clientHeight)&&b.left-50<=(window.innerWidth||document.documentElement.clientWidth))}function ll(){var a=document.querySelectorAll('[data-swift-iframe-lazyload]');for(var i in a){if(iv(a[i])){a[i].onload=function(){window.dispatchEvent(new Event('resize'));};a[i].setAttribute('src',(typeof a[i].dataset.src != 'undefined' ? a[i].dataset.src : a[i].src));a[i].setAttribute('srcset',a[i].dataset.srcset);a[i].setAttribute('style',a[i].dataset.style);a[i].removeAttribute('data-swift-iframe-lazyload')}}requestAnimationFrame(ll)}{$fire}})();</script>";
                  },PHP_INT_MAX);
            }

            // Merge assets in background
            if (Swift_Performance::check_option('merge-background-only', 1) && Swift_Performance::check_option('enable-caching',1) && (Swift_Performance_Cache::is_cacheable() || Swift_Performance_Cache::is_cacheable_dynamic()) ){
                  add_action('wp_footer', function(){
                        echo "<script data-dont-merge>var xhr = new XMLHttpRequest();xhr.open('GET', document.location.href);xhr.setRequestHeader('X-merge-assets', 'true');xhr.send(null);</script>";
                  }, PHP_INT_MAX);
            }

            if (Swift_Performance::check_option('dns-prefetch',1)){
                  // Remove original prefetch
                  add_filter( 'wp_resource_hints', function ( $hints, $relation_type ) {
                      if ( 'dns-prefetch' === $relation_type ) {
                          return array_diff( wp_dependencies_unique_hosts(), $hints );
                      }
                      return $hints;
                  }, 10, 2 );
            }

            if (Swift_Performance::check_option('disable-emojis', 1)){
                  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
                  remove_action( 'wp_print_styles', 'print_emoji_styles' );
            }

		// EWWW compatibility fix
		add_filter( 'wp_image_editors', function($editors){
			remove_filter( 'wp_image_editors', 'ewww_image_optimizer_load_editor', 60 );
			return $editors;
		}, 59 );

		// Server Push
		if (Swift_Performance::check_option('server-push',1) && Swift_Performance::check_option('caching-mode','disk_cache_rewrite')){
			Swift_Performance::set_option('separate-js', 1);
			Swift_Performance::set_option('separate-css', 1);
			$prefix = 'desktop-';
			if (Swift_Performance::check_option('mobile-support', 1)){
				$prefix = (Swift_Performance::is_mobile() ? 'mobile-' : 'desktop-');
			}

			add_filter('swift_performance_critical_css_filename', function() use ($prefix){
				return $prefix . 'critical.css';
			});

			add_filter('swift_performance_css_filename', function($name, $key) use ($prefix){
				if ($key == 'all'){
					return $prefix . 'full.css';
				}
				return $name;
			}, 10, 2);

			add_filter('swift_performance_js_filename', function() use ($prefix){
				return $prefix . 'scripts.js';
			});
		}

            do_action('swift_performance_assets_manager_init');
      }

	/**
	 * Init Render Blocking module
	 */
	public function asset_manager() {
		if (Swift_Performance_Asset_Manager::should_optimize()){
			// Extend timeout
			Swift_Performance::set_time_limit(600, 'asset_manager');

                  //Lock thread, and unlock it on shutdown
                  Swift_Performance::lock_thread('shutdown');

                  add_action('wp_head', function(){
                        if (Swift_Performance::check_option('dns-prefetch', 1)){
                              echo '<!--DNS_PREFETCH_PLACEHOLDER-->';
                        }
                  }, 2);

			add_action('wp_head', function(){
                        if (Swift_Performance::check_option('merge-styles', 1) && Swift_Performance_Asset_Manager::should_optimize()){
				      echo '<!--CSS_HEADER_PLACEHOLDER-->';
                        }
			}, 7);

                  add_action('wp_head', function(){
                        if (Swift_Performance::check_option('merge-scripts', 1) && Swift_Performance_Asset_Manager::should_optimize()){
                              // Define collectready buffer;
                              echo '<script data-dont-merge>window.swift_performance_collectdomready = [];window.swift_performance_collectready = [];window.swift_performance_collectonload = [];</script>';
                        }
				if (Swift_Performance::check_option('lazyload-background-images', 1)){
					echo "<style>html body div:not(.swift-in-viewport),html body section:not(.swift-in-viewport){background-image:none!important;},html body article:not(.swift-in-viewport){background-image:none!important;},html body p:not(.swift-in-viewport){background-image:none!important;},html body ul:not(.swift-in-viewport){background-image:none!important;},html body ol:not(.swift-in-viewport){background-image:none!important;},html body span:not(.swift-in-viewport){background-image:none!important;}</style><script data-dont-merge=\"\">(function(){function iv(a){if(typeof a.getBoundingClientRect!=='function'){return false}var b=a.getBoundingClientRect();return((a.innerHeight||a.clientHeight)>0&&b.bottom+50>=0&&b.right+50>=0&&b.top-50<=(window.innerHeight||document.documentElement.clientHeight)&&b.left-50<=(window.innerWidth||document.documentElement.clientWidth))}function ll(){var a=document.querySelectorAll('div, section, article, p, span, ul, ol');for(var i in a){if(iv(a[i])){a[i].onload=function(){window.dispatchEvent(new Event('resize'));};a[i].classList.add('swift-in-viewport')}}requestAnimationFrame(ll)}requestAnimationFrame(ll)})();</script>";
				}
                  }, 8);

			add_action('wp_footer', function(){
                        if (Swift_Performance::check_option('merge-styles', 1) && Swift_Performance_Asset_Manager::should_optimize()){
                              echo '<!--CSS_FOOTER_PLACEHOLDER-->';
                        }

                        if (Swift_Performance::check_option('merge-scripts', 1) && Swift_Performance_Asset_Manager::should_optimize()){
                              echo '<!--JS_FOOTER_PLACEHOLDER-->';
                        }

                        echo '<!--SWIFT_PERFORMACE_OB_CONFLICT-->';
                  }, PHP_INT_MAX);

			// Manage assets
			ob_start(array($this, 'asset_manager_callback'));
		}

	}

	/**
	 * Remove render blocking assets
	 * @param string $buffer
	 * @return string
	 */
	public function asset_manager_callback($buffer){
            // Don't play with assets if the current page is not cacheable
            if (!Swift_Performance_Asset_Manager::should_optimize()){
			$path	= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$id	= Swift_Performance::get_warmup_id(Swift_Performance::home_url() . trim($path, '/'));
			Swift_Performance::mysql_query('UPDATE ' . SWIFT_PERFORMANCE_TABLE_PREFIX . 'warmup SET type = "error" WHERE id="' . $id . '" LIMIT 1');
                  Swift_Performance::log('Skip asset merging, current page is not cacheable. URL:' . $_SERVER['REQUEST_URI'] . ', Request:' . serialize($_REQUEST), 6);
                  return $buffer;
            }
            $critical_css     = $js = $early_js = $late_js = $excluded_scripts = '';
            $css              = $late_import = $lazyload_scripts_buffer = array();
		$html             = swift_performance_str_get_html(Swift_Performance_Asset_Manager::html_auto_fix($buffer));
            $schema           = (is_ssl() ? 'https://' : 'http://');

            // Stop here if something really bad happened
            if ($html === false){
                  $info = 'URL:' . $_SERVER['REQUEST_URI'] . ', Request:' . serialize($_REQUEST);
                  if (strlen($buffer) > SWIFT_MAX_FILE_SIZE){
      			$info .= 'Max buffer size (' . SWIFT_MAX_FILE_SIZE . ' bytes) was exceeded: '. strlen($buffer) . ' bytes';
      		}
			$path	= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$id	= Swift_Performance::get_warmup_id(Swift_Performance::home_url() . trim($path, '/'));
			Swift_Performance::mysql_query('UPDATE ' . SWIFT_PERFORMANCE_TABLE_PREFIX . 'warmup SET type = "error" WHERE id="' . $id . '" LIMIT 1');
			Swift_Performance::log('DOM parser failed. ' . $info, 1);
			if (!defined('SWIFT_PERFORMANCE_DISABLE_CACHE')){
				define('SWIFT_PERFORMANCE_DISABLE_CACHE', true);
			}
                  return $buffer;
            }

            // Don't merge styles and scripts for AMP pages
            if (Swift_Performance::is_amp($buffer)){
                  Swift_Performance::set_option('merge-scripts', 0);
                  Swift_Performance::set_option('merge-styles', 0);
            }

		// Should skip WPCF7?
		$contact_forms = $html->find('form.wpcf7-form');
		$skip_cf7 = (Swift_Performance::check_option('wpcf7-smart-load', 1) && empty($contact_forms));

            // Prepare lazy load scripts regex
            $lazyload_scripts       = array_filter((array)Swift_Performance::get_option('lazy-load-scripts'));
            $lazyload_scripts       = array_map(function($regex){
                  return preg_quote($regex, '/');
            }, $lazyload_scripts);
            $lazyload_scripts_regex = '/('.implode('|', $lazyload_scripts).')/';

		// Prebuild booster
		$prebuild_booster = (array)get_transient('swift_performance_prebuild_booster');

		foreach ($html->find('link[rel="stylesheet"], style, script, img, iframe') as $node){
                  // Exclude data-dont-merge
                  if (isset($node->{'data-dont-merge'})){
                        continue;
                  }

                  $media            = (isset($node->media) && !empty($node->media) ? $node->media : 'all');
                  $css[$media]      = (isset($css[$media]) ? $css[$media] : '');
                  $remove_tag       = false;

			// WPCF7 smart load
			if ($skip_cf7 && ((isset($node->href) && strpos($node->href, 'contact-form-7/includes') !== false) || (isset($node->src) && strpos($node->src, 'contact-form-7/includes') !== false) || ($node->tag == 'script' && strpos($node->innertext, 'wp-json\/contact-form-7\/v') !== false)) ){
				$node->outertext = '';
				continue;
			}

                  if (Swift_Performance::check_option('merge-styles', 1)){
				if ($node->tag == 'link'){
					// Prebuild booster
					if (isset($prebuild_booster[md5($node->href)])){
						$css[$media] .= $prebuild_booster[md5($node->href)];
						$remove_tag = true;
					}
					else {
						Swift_Performance::log('Load style: ' . $node->href, 9);
	                              $node->href = Swift_Performance::canonicalize($node->href);
	                              $src_parts = parse_url(preg_replace('~^//~', $schema, $node->href));
	                              $src = apply_filters('swift_performance_style_src', (isset($src_parts['scheme']) && !empty($src_parts['scheme']) ? $src_parts['scheme'] : 'http') . '://' . $src_parts['host'] . $src_parts['path']);

	                              // Exclude styles
	                              $exclude_strings = array_filter((array)Swift_Performance::get_option('exclude-styles'));
	                              if (!empty($exclude_strings)){
	                                    if (preg_match('~('.implode('|', $exclude_strings).')~', $src)){
	                                          continue;
	                                    }
	                              }

	                              $_css = '';
	                              $css_filepath = str_replace(apply_filters('style_loader_src', home_url(), 'dummy-handle'), ABSPATH, $src);
	                              if (strpos($src, apply_filters('style_loader_src', home_url(), 'dummy-handle')) !== false){
	                                    if (strpos($src, '.php') === false && preg_match('~\.css$~', parse_url($src, PHP_URL_PATH)) && @file_exists($css_filepath)){
	                                          $_css = file_get_contents($css_filepath);
	                                    }
	                                    else {
	                                          $response = wp_remote_get(preg_replace('~^//~', $schema, $node->href), array('sslverify' => false, 'timeout' => 15));
	                                          if (!is_wp_error($response)){
	                                                if(in_array($response['response']['code'], array(200,304))){
	                                                      $_css = $response['body'];
	                                                }
	                                                else {
	                                                      Swift_Performance::log('Loading remote file (' . $node->href . ') failed. Error: HTTP error (' . $response['response']['code'] . ')', 1);
	                                                }
	                                          }
	                                          else{
	                                                Swift_Performance::log('Loading remote file (' . $node->href . ') failed. Error: ' . $response->get_error_message(), 1);
	                                          }
	                                    }
	                                    $remove_tag = true;
	                              }
	                              else if (Swift_Performance::check_option('merge-styles-exclude-3rd-party', 1, '!=')){
	                                    $response = wp_remote_get(preg_replace('~^//~', $schema, $node->href), array('sslverify' => false, 'timeout' => 15,'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0'));
	                                    if (!is_wp_error($response)){
	                                          if(in_array($response['response']['code'], array(200,304))){
	                                                $_css = $response['body'];
								}
	                                          else {
	                                                Swift_Performance::log('Loading remote file (' . $node->href . ') failed. Error: HTTP error (' . $response['response']['code'] . ')', 1);
	                                          }

	                                          // Remove merged and missing CSS files
	                                          if (in_array($response['response']['code'], array(200, 304, 404, 500, 403, 400))){
									 $remove_tag = true;
								}
	                                    }
	                                    else{
	                                          Swift_Performance::log('Loading remote file (' . $node->href . ') failed. Error: ' . $response->get_error_message(), 1);
	                                    }
	                                    // Google Fonts
	                                    if (strpos($node->href, 'fonts.googleapis.com') !== false){
	                                          $late_import[] = $node->href;
	                                    }
	                              }

	                              // Critical font
	                              if (!Swift_Performance_Critical_Font::is_critical_font($node->href)){
	                                    foreach (Swift_Performance_Critical_Font::get_fonts() as $face=>$font){
	                                          if (Swift_Performance_Critical_Font::is_enqueued($font)){
	                                                $_css = preg_replace('~@font-face\{(.*?)font-family:(\'|")?'.$face.'(\'|")?(.*?)\}~', '', $_css);
	                                          }
	                                    }
	                              }

	      				$GLOBALS['swift_css_realpath_basepath'] = $node->href;
	      				$_css = preg_replace_callback('~@import url\((\'|")?([^\("\']*)(\'|")?\);?~', array($this, 'bypass_css_import'), $_css);
	      				$_css = preg_replace_callback('~url\((\'|")?([^\("\']*)(\'|")?\)~', array($this, 'css_realpath_url'), $_css);

						// Apply CDN settings on bg images
						if (Swift_Performance::check_option('enable-cdn', 1) && isset($GLOBALS['swift_performance']->modules['cdn-manager']->cdn['media'])){
							$_css = $GLOBALS['swift_performance']->modules['cdn-manager']->media_callback($_css);
						}

	                              // Avoid mixed content (fonts, etc)
	                              $_css = preg_replace('~(?<!(xmlns|xlink)=(\'|"))https?:~', '', $_css);

						// Normalize font URIs
						if (Swift_Performance::check_option('normalize-static-resources',1)){
							$_css = $this->normalize_font_urls($_css);
						}

						// Minify CSS
						$_css = Swift_Performance_CSSMin::minify($_css);

						if (!empty($_css)){
		      				$css[$media] .= $_css;
							$prebuild_booster[md5($node->href)] = $_css;
							Swift_Performance::safe_set_transient('swift_performance_prebuild_booster', $prebuild_booster, 600);

						}
					}
      			}
      			else if ($node->tag == 'style'){
					// Prebuild booster
					if (isset($prebuild_booster[md5($node->innertext)])){
						$css[$media] .= $prebuild_booster[md5($node->innertext)];
						$remove_tag = true;
					}
					else {
						// Exclude styles
	                              $exclude_strings = array_filter((array)Swift_Performance::get_option('exclude-inline-styles'));
	                              if (!empty($exclude_strings)){
	                                    if (preg_match('~('.implode('|', $exclude_strings).')~', $node->innertext)){
	                                          continue;
	                                    }
	                              }

						$_css = $node->innertext;

						// Apply CDN settings on bg images
						if (Swift_Performance::check_option('enable-cdn', 1) && isset($GLOBALS['swift_performance']->modules['cdn-manager']->cdn['media'])){
							$_css = $GLOBALS['swift_performance']->modules['cdn-manager']->media_callback($_css);
						}

						// Avoid mixed content (fonts, etc)
						$_css = preg_replace('~(?<!(xmlns|xlink)=(\'|"))https?:~', '', $_css);

						// Normalize font URIs
						if (Swift_Performance::check_option('normalize-static-resources',1)){
							$_css = $this->normalize_font_urls($_css);
						}

						// Minify CSS
						$_css = Swift_Performance_CSSMin::minify($_css);

	      				$css[$media] .= $_css;
	                              $remove_tag = true;

						$prebuild_booster[md5($node->innertext)] = $_css;
						Swift_Performance::safe_set_transient('swift_performance_prebuild_booster', $prebuild_booster, 600);
					}
      			}
                  }
                  if (!empty($lazyload_scripts)){
                        if($node->tag == 'script' && (!isset($node->type) || strpos(strtolower($node->type), 'javascript') !== false) && isset($node->src) && !empty($node->src)){
                              $src_parts = parse_url(preg_replace('~^//~', $schema, $node->src));
                              $src = apply_filters('swift_performance_script_src', (isset($src_parts['scheme']) && !empty($src_parts['scheme']) ? $src_parts['scheme'] : 'http') . '://' . $src_parts['host'] . $src_parts['path']);
                                    if (preg_match($lazyload_scripts_regex, $src)){
                                          $lazyload_scripts_buffer[] = $node->src;
                                          $node->outertext = '';
                                    }
                        }
                  }
                  if (Swift_Performance::check_option('merge-scripts', 1)){
				// Process Script
      			if($node->tag == 'script' && (!isset($node->type) || strpos(strtolower($node->type), 'javascript') !== false)){
					$_js = '';

      				if (isset($node->src) && !empty($node->src)){
						// Prebuild booster
						if (isset($prebuild_booster[md5($node->src)])){
							$js .= $prebuild_booster[md5($node->src)];
							$remove_tag = true;
						}
						else {
	                                    Swift_Performance::log('Load script: ' . $node->src, 9);
	                                    $src_parts = parse_url(preg_replace('~^//~', $schema, $node->src));
	                                    $src = apply_filters('swift_performance_script_src', (isset($src_parts['scheme']) && !empty($src_parts['scheme']) ? $src_parts['scheme'] : 'http') . '://' . $src_parts['host'] . $src_parts['path']);

	                                    // Exclude scripts
	                                    $exclude_strings = array_filter((array)Swift_Performance::get_option('exclude-scripts'));
	                                    if (!empty($exclude_strings)){
	                                          if (preg_match('~('.implode('|', $exclude_strings).')~', $src)){
	                                                continue;
	                                          }
	                                    }

							// Footer scripts
						     $exclude_strings = array_filter((array)Swift_Performance::get_option('footer-scripts'));
						     if (!empty($exclude_strings)){
							     if (preg_match('~('.implode('|', $exclude_strings).')~', $src)){
								     $excluded_scripts .= $node->outertext;
								     $node->outertext = '';
								     continue;
							     }
						     }

	                                    // Exclude lazy loaded scripts
	                                    if (!empty($lazyload_scripts) && preg_match($lazyload_scripts_regex, $src)){
	                                          continue;
	                                    }

	                                    $js_filepath = str_replace(apply_filters('script_loader_src', home_url(), 'dummy-handle'), ABSPATH, $src);
	                                    if (strpos($src, apply_filters('script_loader_src', home_url(), 'dummy-handle')) !== false){
	                                          if (strpos($src, '.php') !== false || !preg_match('~\.js$~', parse_url($src, PHP_URL_PATH)) || @!file_exists($js_filepath)){
	                                                $response = wp_remote_get(preg_replace('~^//~', $schema, $node->src), array('sslverify' => false, 'timeout' => 15, 'headers' => array('Referer' => home_url())));
	                                                if (!is_wp_error($response)){
	                                                      if (in_array($response['response']['code'], array(200, 304))){
										$_js = "\ntry{\n" . Swift_Performance_Asset_Manager::minify_js($response['body']) . "\n}catch(e){/*silent fail*/}\n".self::script_boundary()."\n";
	                                                      }
	                                                      else {
	                                                            Swift_Performance::log('Loading remote file (' . $src . ') failed. Error: HTTP error (' . $response['response']['code'] . ')', 1);
	                                                      }
	                                                }
	                                                else{
	                                                      Swift_Performance::log('Loading remote file (' . $src . ') failed. Error: ' . $response->get_error_message(), 1);
	                                                }
	                                          }
	                                          else {
								$_js = "\ntry{" . Swift_Performance_Asset_Manager::minify_js(file_get_contents(str_replace(apply_filters('script_loader_src', home_url(), 'dummy-handle'), ABSPATH, $src))) . "}catch(e){/*silent fail*/}\n".self::script_boundary()."\n";
	                                          }
	                                          $remove_tag = true;
	                                    }
	                                    else if (Swift_Performance::check_option('merge-scripts-exclude-3rd-party', 1, '!=')){
	                                          $response = wp_remote_get(preg_replace('~^//~', $schema, $node->src), array('sslverify' => false, 'timeout' => 15, 'headers' => array('Referer' => home_url())));
	                                          if (!is_wp_error($response)){
	                                                if (in_array($response['response']['code'], array(200, 304))){
										$_js = "\ntry{" . Swift_Performance_Asset_Manager::minify_js($response['body']) . "}catch(e){/*silent fail*/}\n".self::script_boundary()."\n";
	                                                }
	                                                else {
	                                                      Swift_Performance::log('Loading remote file (' . $node->src . ') failed. Error: HTTP error (' . $response['response']['code'] . ')', 1);
	                                                }

	                                                // Remove merged and missing js files
	                                                if (in_array($response['response']['code'], array(200, 304, 404, 500, 403, 400))){
	                                                       $remove_tag = true;
	                                                }
	                                          }
	                                          else{
	                                                Swift_Performance::log('Loading remote file (' . $node->src . ') failed. Error: ' . $response->get_error_message(), 1);
	                                          }
	                                    }

							if (!empty($_js)){
								$js .= $_js;
								$prebuild_booster[md5($node->src)] = $_js;
								Swift_Performance::safe_set_transient('swift_performance_prebuild_booster', $prebuild_booster, 600);

							}
						}
      				}
      				else if (Swift_Performance::check_option('exclude-script-localizations', 1, '!=') || strpos($node->innertext, '<![CDATA[') === false){
						// Prebuild booster
						if (isset($prebuild_booster[md5($node->innertext)])){
							$js .= $prebuild_booster[md5($node->innertext)];
							$remove_tag = true;
						}
						else {
	                                    // Get rid GA if bypass enabled
	                                    if (Swift_Performance::check_option('bypass-ga', 1) && strpos($node->innertext, "GoogleAnalyticsObject") !== false){
	                                          $node->outertext = '';
	                                          continue;
	                                    }
	                                    // Exclude scripts
	                                    $exclude_strings = array_filter((array)Swift_Performance::get_option('exclude-inline-scripts'));
	                                    if (!empty($exclude_strings)){
	                                          if (preg_match('~('.implode('|', $exclude_strings).')~', $node->innertext)){
	                                                continue;
	                                          }
	                                    }

							// Footer inline scripts
	                                    $exclude_strings = array_filter((array)Swift_Performance::get_option('footer-inline-scripts'));
	                                    if (!empty($exclude_strings)){
	                                          if (preg_match('~('.implode('|', $exclude_strings).')~', $node->innertext)){
									$excluded_scripts .= $node->outertext;
									$node->outertext = '';
	                                                continue;
	                                          }
	                                    }

							$_js = "\ntry{" . Swift_Performance_Asset_Manager::minify_js($node->innertext) . "}catch(e){/*silent fail*/}\n".self::script_boundary()."\n";
	                                    $remove_tag = true;

							$js .= $_js;
							if (!Swift_Performance::is_user_logged_in()){
								$prebuild_booster[md5($node->innertext)] = $_js;
								Swift_Performance::safe_set_transient('swift_performance_prebuild_booster', $prebuild_booster, 600);
							}

						}
      				}
      			}
                  }
                  if($node->tag == 'img'){
                        $id = '';
                        if (Swift_Performance::check_option('force-responsive-images', 1) && !isset($node->srcset)){
                              // Get image id
                              $id = Swift_Performance::get_image_id(Swift_Performance::canonicalize($node->src));

                              if (!empty($id)){
                                    $size = (isset($node->width) && isset($node->height) ? array($node->width, $node->height) : 'full');
                                    $node->outertext = wp_get_attachment_image($id, $size);
                                    preg_match('~srcset="([^"]*)"~', $node->outertext, $_srcset);
                                    preg_match('~sizes="([^"]*)"~', $node->outertext, $_sizes);
                                    $node->srcset = $_srcset[1];
                                    $node->sizes = $_sizes[1];
                              }
                              else {
                                    Swift_Performance::log('Can\'t find image id: ' . $node->src, 6);
                              }
                        }
                        if (Swift_Performance::check_option('base64-small-images', 1)){
                              // Exclude images
                              $exclude_strings = array_filter((array)Swift_Performance::get_option('exclude-base64-small-images'));
                              if (!empty($exclude_strings)){
                                    if (preg_match('~('.implode('|', $exclude_strings).')~', $node->src)){
                                          continue;
                                    }
                              }
                              $attribute  = (isset($node->{'data-src'}) ? 'data-src' : 'src');
                              $img_path   = str_replace(apply_filters('swift_performance_media_host', home_url()), ABSPATH, $node->$attribute);
                              if (file_exists($img_path) && filesize($img_path) <= Swift_Performance::get_option('base64-small-images-size')){
						$mime = preg_match('~\.jpe?g$~', $img_path) ? 'jpeg' : 'png';
						if (isset($prebuild_booster[md5($img_path)])){
							$node->$attribute = $prebuild_booster[md5($img_path)];
						}
	                              else {
							$node->$attribute = 'data:image/'.$mime.';base64,' . base64_encode(file_get_contents($img_path));
							$prebuild_booster[md5($img_path)] = $node->$attribute;
							Swift_Performance::safe_set_transient('swift_performance_prebuild_booster', $prebuild_booster, 600);
						}

                                    // Get rid srcset for inlined images
                                    if (isset($node->srcset)){
                                          unset($node->srcset);
                                    }
                                    if (isset($node->{'data-srcset'})){
                                          unset($node->{'data-srcset'});
                                    }
                                    if (isset($node->sizes)){
                                          unset($node->sizes);
                                    }
                              }
                        }
                        if (Swift_Performance::check_option('lazy-load-images', 1)){
                              // Exclude images
                              $exclude_lazy_load = array_filter((array)Swift_Performance::get_option('exclude-lazy-load'));
                              if (!empty($exclude_lazy_load)){
                                    if (preg_match('~('.implode('|', $exclude_lazy_load).')~', $node->src)){
                                          continue;
                                    }
                              }

                              $attachment = new stdClass;
                              $attributes = '';

                              // Get image id
                              if (empty($id)){
                                    $id = Swift_Performance::get_image_id(Swift_Performance::canonicalize($node->src));
                              }

                              // Collect original attributes
                              $args = array();
                              foreach ($node->attr as $key => $value) {
                                    $args[$key] = $value;
                              }

                              // Change src and srcset
                              $args = $this->lazyload_images($args, $id);

                              // Change image tag
                              if ($args !== false){
                                    foreach($args as $key=>$value){
                                          $attributes .= $key . '="' . $value . '" ';
                                    }

                                    $node->outertext = '<img '.$attributes.' data-l>';
                              }
                        }
                  }

                  if($node->tag == 'iframe'){
				if (Swift_Performance::check_option('smart-youtube-embed', 1)){
					$exclude_smart_embed = array_filter((array)Swift_Performance::get_option('exclude-youtube-embed'));
					if ((empty($exclude_smart_embed) || !preg_match('~('.implode('|', $exclude_smart_embed).')~', $node->src)) && isset($node->src)){
						preg_match('#https://www.youtube.com/embed/([^\?/\s"]+)#',$node->src, $matches);
						$height = isset($node->height) ? $node->height . 'px' : '';
						if (isset($matches[1]) && !empty($matches[1]) && !empty($height)){
	                                 	$node->src = add_query_arg('autoplay', 1, $node->src);
							$node->class = (isset($node->class) ? $node->class . ' ' : '')  . 'data-swift-youtube-player';
							$node->srcdoc = '<html><head><style>.swift-ytp{position: absolute;left: 50%;top: 50%;width: 68px;height: 48px;margin-left: -34px;margin-top: -24px;-moz-transition: opacity .25s cubic-bezier(0.0,0.0,0.2,1);-webkit-transition: opacity .25s cubic-bezier(0.0,0.0,0.2,1);transition: opacity .25s cubic-bezier(0.0,0.0,0.2,1);z-index: 63;cursor:pointer;}.swift-ytp svg {height: 100%;left: 0;position: absolute;top: 0;width: 100%;}.swift-ytp:hover .swift-ytp-bg {fill: #ff0000;}body{overflow:hidden;}</style><script>function loadFrame(){window.frameElement.removeAttribute(\'srcdoc\');window.frameElement.src+=\'&x\'}</script></head><body><div id=\'swift-ytc\' onclick=\'loadFrame();\' ontouchstart=\'loadFrame();\' style=\'position:relative;width:100%;height:'.$height.';background-image: url(https://img.youtube.com/vi/' . $matches[1] . '/0.jpg);background-size:cover;\'><div class=\'swift-ytp\' aria-label=\'Play\'><svg height=\'100%\' version=\'1.1\' viewBox=\'0 0 68 48\' width=\'100%\'><path class=\'swift-ytp-bg\' d=\'M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z\' fill=\'#212121\' fill-opacity=\'0.8\'></path><path d=\'M 45,24 27,14 27,34\' fill=\'#fff\'></path></svg></div></div><script>function r(){var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);if(h > 0){document.getElementById(\'swift-ytc\').style.height = h + \'px\'};}r();window.addEventListener(\'resize\',r);if(\'ontouchstart\' in document.documentElement && !window.frameElement.src.match(/&x$/)){function iv(a){if (typeof a.getBoundingClientRect !== \'function\'){return false}var b = a.getBoundingClientRect(); return ((a.innerHeight || a.clientHeight) > 0 && b.bottom + 50 >= 0 && b.right + 50 >= 0 && b.top - 50 <= (window.innerHeight || document.documentElement.clientHeight) && b.left - 50 <= (window.innerWidth || document.documentElement.clientWidth))}function ll(){if (iv(window.frameElement)){loadFrame();}requestAnimationFrame(ll);}requestAnimationFrame(ll);}</script></body ></html>';
						}
                              }
				}
                        if (Swift_Performance::check_option('lazyload-iframes', 1) && !isset($node->{"data-swift-youtube-id"})){
                              // Exclude iframes
                              $exclude_lazyload = array_filter((array)Swift_Performance::get_option('exclude-iframe-lazyload'));
                              if (!empty($exclude_lazyload)){
                                    if (preg_match('~('.implode('|', $exclude_lazyload).')~', $node->src)){
                                          continue;
                                    }
                              }

                              if (isset($node->src)){
                                    $node->{"data-src"} = $node->src;
                                    $node->{"data-swift-iframe-lazyload"} = 'true';
                                    $node->src = '';
                              }

                        }
                  }

                  // Remove tag
                  if ($remove_tag){
                        $node->outertext = '';
                  }
		}

		// Load manually included scripts
            $include_scripts  = array_filter((array)Swift_Performance::get_option('include-scripts'));
            $_include_scripts = array();
            if (!empty($include_scripts)){
			$early_js = 'window.swift_performance_included_scripts={}';
                  foreach ($include_scripts as $include_script){
                        $src_parts = parse_url(preg_replace('~^//~', $schema, $include_script));
                        $src = apply_filters('swift_performance_script_src', (isset($src_parts['scheme']) && !empty($src_parts['scheme']) ? $src_parts['scheme'] : 'http') . '://' . $src_parts['host'] . $src_parts['path']);

				Swift_Performance::log('Load script: ' . $src, 9);

				$js_filepath = str_replace(apply_filters('script_loader_src', home_url(), 'dummy-handle'), ABSPATH, $src);
				if (strpos($src, '.php') !== false || !preg_match('~\.js$~', parse_url($src, PHP_URL_PATH)) || !file_exists($js_filepath)){
					$response = wp_remote_get($src, array('sslverify' => false, 'timeout' => 15));
					if (!is_wp_error($response)){
						if (in_array($response['response']['code'], array(200, 304))){
						$early_js .= "\nwindow.swift_performance_included_scripts['{$include_script}']=" . json_encode('try{' . "\n" . Swift_Performance_Asset_Manager::minify_js($response['body']) . "\n" . '}catch(e){/*silent fail*/}') . ";\n".self::script_boundary()."\n";
						}
						else {
							Swift_Performance::log('Loading remote file (' . $src . ') failed. Error: HTTP error (' . $response['response']['code'] . ')', 1);
						}
					}
					else{
						Swift_Performance::log('Loading remote file (' . $src . ') failed. Error: ' . $response->get_error_message(), 1);
					}
				}
				else {
					$early_js .= "\nwindow.swift_performance_included_scripts['{$include_script}']=" . json_encode('try{' . Swift_Performance_Asset_Manager::minify_js(file_get_contents(str_replace(apply_filters('script_loader_src', home_url(), 'dummy-handle'), ABSPATH, $src))) . '}catch(e){/*silent fail*/}') . ";\n".self::script_boundary()."\n";
				}

				$_include_scripts[] = "'$include_script'";
                  }
            }


            // Load manually included styles
            $include_styles   = array_filter((array)Swift_Performance::get_option('include-styles'));
            $_include_styles  = array();
            if (!empty($include_styles)){
                  foreach ($include_styles as $include_style){
                        $src_parts = parse_url(preg_replace('~^//~', $schema, $include_style));
                        $src = apply_filters('swift_performance_style_src', (isset($src_parts['scheme']) && !empty($src_parts['scheme']) ? $src_parts['scheme'] : 'http') . '://' . $src_parts['host'] . $src_parts['path']);

                        $_css = '';
                        $css_filepath = str_replace(apply_filters('style_loader_src', home_url(), 'dummy-handle'), ABSPATH, $src);
                        if (strpos($src, apply_filters('style_loader_src', home_url(), 'dummy-handle')) !== false && file_exists($css_filepath)){
                              if (strpos($src, '.php') === false && preg_match('~\.css$~', parse_url($src, PHP_URL_PATH))){
                                    $_css = file_get_contents($css_filepath);
                              }
                              else {
                                    $response = wp_remote_get(preg_replace('~^//~', $schema, $include_style), array('sslverify' => false, 'timeout' => 15));
                                    if (!is_wp_error($response)){
                                          if(in_array($response['response']['code'], array(200,304))){
                                                $_css = $response['body'];
                                          }
                                          else {
                                                Swift_Performance::log('Loading remote file (' . $include_style . ') failed. Error: HTTP error (' . $response['response']['code'] . ')', 1);
                                          }
                                    }
                                    else{
                                          Swift_Performance::log('Loading remote file (' . $include_style . ') failed. Error: ' . $response->get_error_message(), 1);
                                    }
                              }
                        }
				else {
					$response = wp_remote_get(preg_replace('~^//~', $schema, $include_style), array('sslverify' => false, 'timeout' => 15));
					if (!is_wp_error($response)){
						if(in_array($response['response']['code'], array(200,304))){
							$_css = $response['body'];
						}
						else {
							Swift_Performance::log('Loading remote file (' . $include_style . ') failed. Error: HTTP error (' . $response['response']['code'] . ')', 1);
						}
					}
					else{
						Swift_Performance::log('Loading remote file (' . $include_style . ') failed. Error: ' . $response->get_error_message(), 1);
					}
				}

				if (!empty($css)){
					$_include_styles[] = "'$include_style'";


	                        $GLOBALS['swift_css_realpath_basepath'] = $include_style;
	                        $_css = preg_replace_callback('~@import url\((\'|")?([^\("\']*)(\'|")?\);?~', array($this, 'bypass_css_import'), $_css);
	                        $_css = preg_replace_callback('~url\((\'|")?([^\("\']*)(\'|")?\)~', array($this, 'css_realpath_url'), $_css);

					// Apply CDN settings on bg images
					if (Swift_Performance::check_option('enable-cdn', 1) && isset($GLOBALS['swift_performance']->modules['cdn-manager']->cdn['media'])){
						$_css = $GLOBALS['swift_performance']->modules['cdn-manager']->media_callback($_css);
					}

	                        // Avoid mixed content (fonts, etc)
	                        $_css = preg_replace('~https?:~', '', $_css);

					// Remove BOM
					$_css = str_replace('ï»¿', '', $_css);

					// Normalize font URIs
					if (Swift_Performance::check_option('normalize-static-resources',1)){
						$_css = $this->normalize_font_urls($_css);
					}

					// Minify CSS
					$_css = Swift_Performance_CSSMin::minify($_css);

	                        $css['all'] .= $_css;
				}
                  }
            }

            // Create critical css
            if (Swift_Performance::check_option('merge-styles', 1) && Swift_Performance::check_option('critical-css', 1) && isset($css['all'])){
                  // Move screen CSS to "all" inside media query
                  if (isset($css['screen']) && !empty($css['screen'])){
                        $css['all'] .= "@media screen {\n{$css['screen']}\n}";
                        unset($css['screen']);
                  }

                  // Collect classes from the document and js
      		preg_match_all('~class=(\'|")([^\'"]+)(\'|")~', $html . $js, $class_attributes);
                  preg_match_all('~(toggle|add)Class\s?\(\\\\?(\'|")([^\'"\\\\]+)\\\\?(\'|")\)~', $js, $js_class_attributes);
                  $class_attributes[2] = array_merge((array)$class_attributes[2], (array)$js_class_attributes[3]);

                  // Collect ids from the document and js
      		preg_match_all('~id=(\'|")([^\'"]+)(\'|")~', $html . $js, $id_attributes);

                  // Compress class names
                  $dont_short_classes  = '';
                  $should_compress_css = Swift_Performance::check_option('compress-css',1) && Swift_Performance::check_option('merge-scripts', 1) && Swift_Performance::check_option('merge-styles', 1) && Swift_Performance::check_option('disable-full-css',0);
                  if ($should_compress_css){
                        // Add padding to class tags
                        $html = preg_replace('~class=("|\')([^"\']*)("|\')~', 'class=$1 $2 $3', $html);

                        // Don't make short classes classnames which were used in regex
                        preg_match_all('~class([\^\*\$])?=("|\')?([^"\'\)]*)?("|\')?~', $html, $dont_short_classes);
                  }

                  // Use API if available
                  $api_args = array(
                        'css'                   => $css['all'],
                        'class_attributes'      => $class_attributes,
                        'id_attributes'         => $id_attributes,
                        'dont_short_classes'    => $dont_short_classes,
                        'settings'              => array(
                              'compress-css'          => $should_compress_css,
                              'remove-keyframes'      => Swift_Performance::check_option('remove-keyframes',1)
                        )
                  );

                  $response = Swift_Performance::compute_api($api_args);

                  if (!empty($response)){
                        $api                    = json_decode($response, true);
                        $critical_css           = $api['critical_css'];
                        $shortened_classes      = $api['shortened_classes'];
                  }
                  else{
                        $critical_css = $css['all'];

                        // Encode content attribute for pseudo elements before parsing
				$critical_css = preg_replace_callback('~content\s?:\s?(\'|")\s?(\(")?([^\'"]*)("\))?(\'|")~', function($matches){
				     return 'content: ' . $matches[1] . base64_encode($matches[2].$matches[3].$matches[4]) . $matches[1];
				}, $critical_css);

                        // Encode URLS
            		$critical_css = preg_replace_callback('~url\s?\(("|\')?([^"\'\)]*)("|\')?\)~i', function($matches){
            			return 'encoded_url(' . base64_encode($matches[2]) . ')';
            		}, $critical_css);

                        // Found classes
            		$found_classes = array();
            		$not_found_classes = array();
            		foreach($class_attributes[2] as $class_attribute){
            			$classes = explode(' ', $class_attribute);
            			foreach ($classes as $class){
            				$class = trim($class);
            				$found_classes[$class] = $class;
            			}
            		}

                        // Parse css rules
            		preg_match_all('~([^@%\{\}]+)\{([^\{\}]+)\}~', $critical_css, $parsed_css);

                        // Iterate through css rules, and remove unused instances
				$to_remove = array();
            		for ($i=0; $i<count($parsed_css[1]); $i++){
            			$_selector = explode(',', $parsed_css[1][$i]);
            			foreach ($_selector as $key => $selector){
                                    if (preg_match('~:(hover|active|focus|visited)~', $selector)){
                                          unset($_selector[$key]);
                                          preg_match_all('~\.([a-zA-Z0-9-_]+)~', $selector, $selector_classes);
                                          foreach($selector_classes[1] as $selecor_class){
                                                $not_found_classes[$selecor_class] = $selecor_class;
                                          }
                                    }
            				else if (strpos($selector, ':not') == false){
            					preg_match_all('~\.([a-zA-Z0-9-_]+)~', $selector, $selector_classes);

            					foreach ($selector_classes[1] as $selector_class){
            						$selector_class = trim($selector_class);
            						if (isset($not_found_classes[$selector]) || !isset($found_classes[$selector_class])){
            							unset($_selector[$key]);
            							$not_found_classes[$selector] = $selector;
            							break;
            						}
            					}
            				}
            			}


            			$_selector = array_filter($_selector);
            			if (empty($_selector)){
						$to_remove[] = $parsed_css[1][$i] . "{" . $parsed_css[2][$i] . '}';
            			}
            		}

				$critical_css = str_replace($to_remove, '', $critical_css);

                        // Found ids
            		$found_ids = array();
            		$not_found_ids = array();
            		foreach($id_attributes[2] as $id_attribute){
            			$found_ids[$id_attribute] = $id_attribute;
            		}

                        // Iterate through css rules, and remove unused instances
            		for ($i=0; $i<count($parsed_css[1]); $i++){
            			$_selector = explode(',', $parsed_css[1][$i]);
            			foreach ($_selector as $key => $selector){

            				preg_match_all('~#([a-zA-Z0-9-_]+)~', $selector, $selector_ids);

            				foreach ($selector_ids[1] as $selector_id){
            					$selector_id = trim($selector_id);
            					if (isset($not_found_ids[$selector]) || !isset($found_ids[$selector_id])){
            						unset($_selector[$key]);
            						$not_found_ids[$selector] = $selector;
            						break;
            					}
            				}

            			}


            			$_selector = array_filter($_selector);
            			if (empty($_selector)){
            				$critical_css = str_replace($parsed_css[1][$i] . "{" . $parsed_css[2][$i] . '}', '', $critical_css);
            			}
            		}

                        // Remove emptied media queries
            		$critical_css = preg_replace('~@media([^\{]+)\{\}~','',$critical_css);

                        // Remove empty rules
            		$critical_css = preg_replace('~([^\s\}\)]+)\{\}~','',$critical_css);

                        // Remove keyframes
                        if (Swift_Performance::check_option('remove-keyframes',1)){
                              $critical_css = preg_replace('~@([^\{]*)keyframes([^\{]*){((?!\}\}).)*\}\}~','',$critical_css);
                              $critical_css = preg_replace('~(-webkit-|-moz-|-o|-ms-)?animation:([^;]*);~','',$critical_css);
                        }

                        // Remove leading semicolon in ruleset
                        $critical_css = str_replace(';}', '}', $critical_css);

                        // Remove unnecessary whitespaces
                        $critical_css = preg_replace('~(;|\{|\}|"|\'|:|,)\s+~', '$1', $critical_css);
                        $critical_css = preg_replace('~\s+(;|\)|\{|\}|"|\'|:|,)~', '$1', $critical_css);

                        // Remove apostrophes and quotes
                        $critical_css = preg_replace('~\(("|\')~', '(', $critical_css);
                        $critical_css = preg_replace('~("|\')\)~', ')', $critical_css);

                        // Add back apostrophes to font formats
                        $critical_css = preg_replace('~format\(([^\)]+)\)~','format(\'$1\')',$critical_css);

                        // Compress colors
                        $critical_css = str_replace(array(
                              '#000000',
                              '#111111',
                              '#222222',
                              '#333333',
                              '#444444',
                              '#555555',
                              '#666666',
                              '#777777',
                              '#888888',
                              '#999999',
                              '#aaaaaa',
                              '#bbbbbb',
                              '#cccccc',
                              '#dddddd',
                              '#eeeeee',
                              '#ffffff',
                        ), array(
                              '#000',
                              '#111',
                              '#222',
                              '#333',
                              '#444',
                              '#555',
                              '#666',
                              '#777',
                              '#888',
                              '#999',
                              '#aaa',
                              '#bbb',
                              '#ccc',
                              '#ddd',
                              '#eee',
                              '#fff',
                        ), $critical_css);

                        // Compress class names
                        if ($should_compress_css){
                              $sc_count = 0;
                              $shortened_classes = array();
                              preg_match_all('~\.(([a-zA-Z]+)([a-zA-Z0-9-_]+))~', $critical_css, $all_classes);
                              foreach(array_unique($all_classes[1]) as $fc){
                                    if (!empty($fc)){
                                          // Skip classes which were used in regex
                                          foreach ($dont_short_classes[3] as $ds){
                                                if (!empty($ds) && strpos($fc, $ds) !== false){
                                                      continue;
                                                }
                                          }
                                          $sc = '_' .base_convert($sc_count,10,35);

                                          // Prevent using existing classes
                                          while (isset($found_classes[$sc])){
                                                $sc_count++;
                                                $sc = '_' .base_convert($sc_count,10,35);
                                          }

                                          // Avoid longer "short" names
                                          if (strlen($sc) > strlen($fc)){
                                                continue;
                                          }

                                          $shortened_classes[$fc] = $sc;

                                          $critical_css = preg_replace('~\.'.preg_quote($fc).'(\s|,|\.|\[|\{|\+|>|:+)~', '.'.$sc.'$1', $critical_css);
                                          $sc_count++;
                                    }
                              }
                        }
                  }
                  Swift_Performance::log('Critical CSS generated', 9);
            }

            $_html = (string)$html;

            // Add compressed classes to HTML
            if (isset($should_compress_css) && $should_compress_css){
                  foreach((array)$shortened_classes as $fc => $sc){
                        $_html = preg_replace('~class=("|\')([^"\']*)?\s+'.preg_quote($fc).'\s+([^"\']*)?("|\')~', 'class=$1$2 '. $fc .' ' . $sc.' $3$4', $_html);
                  }
            }

            // Decode content attribute for pseudo elements
            $critical_css = preg_replace_callback('~content\s?:\s?(\'|")([^\'"]*)(\'|")~', function($matches){
                  return 'content: ' . $matches[1] . base64_decode($matches[2]) . $matches[1];
            }, $critical_css);

            // Decode URLS
            $critical_css = preg_replace_callback('~encoded_url\(([^\)]+)\)~i', function($matches){
                  return 'url(' . base64_decode($matches[1]) . ')';
            }, $critical_css);

		// Put apostrophes back for resoures which contains space
		$critical_css = preg_replace('~url\(([^\)]+)?(\s+)([^\)]+)?\)~','url(\'$1$2$3\')',$critical_css);

            // Convert absolute paths to relative
            $critical_css = str_replace(Swift_Performance::home_url(), parse_url(Swift_Performance::home_url(), PHP_URL_PATH), $critical_css);
            $critical_css = str_replace(preg_replace('~https?:~', '', Swift_Performance::home_url()), parse_url(Swift_Performance::home_url(), PHP_URL_PATH), $critical_css);

            // Remove version tag from fonts
            $critical_css = preg_replace('~\.(woff2?|eot|ttf)\?([^\'"\)]+)~','.$1',$critical_css);

            $css_dir = apply_filters('swift_performance_css_dir', Swift_Performance::check_option('separate-css', 1) ? ltrim(trailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)),'/') . 'css' : 'css');
            $js_dir = apply_filters('swift_performance_js_dir', Swift_Performance::check_option('separate-js', 1) ? ltrim(trailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)),'/') . 'js' : 'js');

            // Save CSS
            $defered_styles = '';
            if (Swift_Performance::check_option('disable-full-css', 1, '!=') || Swift_Performance::check_option('critical-css', 1, '!=')){
                  foreach ((array)$css as $key => $content){
                        if (!empty($content)){
                              if ($key == 'all'){
                                    foreach ($late_import as $import) {
                                          $content .= '@import url(\''.$import.'\')';
                                    }

                              }

					$content = apply_filters('swift_performance_css_content', $content, $key);

                              if ($key == 'all' && Swift_Performance::check_option('inline_full_css', 1)){
                                    $defered_styles .= '<style id="full-css">'.$content.'</style>';
                              }
                              else {
						$css_filename = apply_filters('swift_performance_css_filename', md5($content) . '.css', $key);
                                    $defered_styles .= '<link rel="stylesheet" href="'.preg_replace('~https?://~', '//', apply_filters('style_loader_src', Swift_Performance_Cache::write_file(trailingslashit($css_dir) . $css_filename, $content), 'swift-performance-full-'.$key)).'" media="'.$key.'">';
                              }
                        }
                  }
            }


            // Proxy some 3rdparty assets
            if (Swift_Performance::check_option('proxy-3rd-party-assets', 1)){
                  $early_js = preg_replace_callback('~(https?:)?//([\.a-z0-9_-]*)\.(xn--clchc0ea0b2g2a9gcd|xn--hlcj6aya9esc7a|xn--hgbk6aj7f53bba|xn--xkc2dl3a5ee0h|xn--mgberp4a5d4ar|xn--11b5bs3a9aj6g|xn--xkc2al3hye2a|xn--80akhbyknj4f|xn--mgbc0a9azcg|xn--lgbbat1ad8j|xn--mgbx4cd0ab|xn--mgbbh1a71e|xn--mgbayh7gpa|xn--mgbaam7a8h|xn--9t4b11yi5a|xn--ygbi2ammx|xn--yfro4i67o|xn--fzc2c9e2c|xn--fpcrj9c3d|xn--ogbpf8fl|xn--mgb9awbf|xn--kgbechtv|xn--jxalpdlp|xn--3e0b707e|xn--s9brj9c|xn--pgbs0dh|xn--kpry57d|xn--kprw13d|xn--j6w193g|xn--h2brj9c|xn--gecrj9c|xn--g6w251d|xn--deba0ad|xn--80ao21a|xn--45brj9c|xn--0zwm56d|xn--zckzah|xn--wgbl6a|xn--wgbh1c|xn--o3cw4h|xn--fiqz9s|xn--fiqs8s|xn--90a3ac|xn--p1ai|travel|museum|post|name|mobi|jobs|info|coop|asia|arpa|aero|xxx|tel|pro|org|net|mil|int|gov|edu|com|cat|biz|zw|zm|za|yt|ye|ws|wf|vu|vn|vi|vg|ve|vc|va|uz|uy|us|uk|ug|ua|tz|tw|tv|tt|tr|tp|to|tn|tm|tl|tk|tj|th|tg|tf|td|tc|sz|sy|sx|sv|su|st|sr|so|sn|sm|sl|sk|sj|si|sh|sg|se|sd|sc|sb|sa|rw|ru|rs|ro|re|qa|py|pw|pt|ps|pr|pn|pm|pl|pk|ph|pg|pf|pe|pa|om|nz|nu|nr|np|no|nl|ni|ng|nf|ne|nc|na|mz|my|mx|mw|mv|mu|mt|ms|mr|mq|mp|mo|mn|mm|ml|mk|mh|mg|me|md|mc|ma|ly|lv|lu|lt|ls|lr|lk|li|lc|lb|la|kz|ky|kw|kr|kp|kn|km|ki|kh|kg|ke|jp|jo|jm|je|it|is|ir|iq|io|in|im|il|ie|id|hu|ht|hr|hn|hm|hk|gy|gw|gu|gt|gs|gr|gq|gp|gn|gm|gl|gi|gh|gg|gf|ge|gd|gb|ga|fr|fo|fm|fk|fj|fi|eu|et|es|er|eg|ee|ec|dz|do|dm|dk|dj|de|cz|cy|cx|cw|cv|cu|cr|co|cn|cm|cl|ck|ci|ch|cg|cf|cd|cc|ca|bz|by|bw|bv|bt|bs|br|bo|bn|bm|bj|bi|bh|bg|bf|be|bd|bb|ba|az|ax|aw|au|at|as|ar|aq|ao|an|am|al|ai|ag|af|ae|ad|ac)([\.\/a-z0-9-_]*)~i', array('Swift_Performance_Asset_Manager', 'asset_proxy_callback') , $early_js);
            }

            // Javascript to remove critical CSS after window.load
            if (Swift_Performance::check_option('disable-full-css', 1, '!=') || Swift_Performance::check_option('critical-css', 1, '!=')){
                  if (Swift_Performance::check_option('merge-styles',1) && Swift_Performance::check_option('critical-css',1)){
                        if (Swift_Performance::check_option('merge-scripts', 1)){

                              $remove_critical_css = 'try{document.getElementById("critical-css").remove();}catch(e){/*IE...*/document.getElementById("critical-css").innerHTML = ""};';
                              if (Swift_Performance::check_option('inline_full_css', 1)){
                                    $remove_critical_css .= 'document.getElementById("full-css").innerHTML = document.getElementById("full-css").innerHTML;';
                              }
                              $early_js = $remove_critical_css . $early_js;
                        }
                        else {
                              add_action('wp_footer', function(){
                                    $remove_critical_css = 'try{document.getElementById("critical-css").remove();}catch(e){/*IE...*/document.getElementById("critical-css").innerHTML = ""};';
                                    if (Swift_Performance::check_option('inline_full_css', 1)){
                                          $remove_critical_css .= 'document.getElementById("full-css").innerHTML = document.getElementById("full-css").innerHTML;';
                                    }
                                    echo '<script>'.$remove_critical_css.'</script>';
                              }, PHP_INT_MAX);
                        }
                  }
            }

		// Prevent included scripts loaded via appendChild
            if (!empty($_include_scripts)){
                  $early_js = "Element.prototype.sp_inc_scripts_appendChild = Element.prototype.appendChild;Element.prototype.appendChild = function(element){var blocked = [".implode(',',$_include_scripts)."];if (element.nodeName == 'SCRIPT' && element.src && blocked.indexOf(element.src) !== -1){eval(window.swift_performance_included_scripts[element.src]);element.type='text/blocked-script';}return this.sp_inc_scripts_appendChild(element);};" . $early_js;
			$early_js = "Element.prototype.sp_inc_scripts_insertBefore = Element.prototype.insertBefore;Element.prototype.insertBefore = function(element,existingElement){var blocked = [".implode(',',$_include_scripts)."];if (element.nodeName == 'SCRIPT' && element.src && blocked.indexOf(element.src) !== -1){eval(window.swift_performance_included_scripts[element.src]);element.type='text/blocked-script';}return this.sp_inc_scripts_insertBefore(element,existingElement);};" . $early_js;
            }

            // Prevent included styles loaded via appendChild
            if (!empty($_include_styles)){
                  $early_js = "Element.prototype.sp_inc_styles_appendChild = Element.prototype.appendChild;Element.prototype.appendChild = function(element){var blocked = [".implode(',',$_include_styles)."];if (element.nodeName == 'LINK' && element.href && blocked.indexOf(element.href) !== -1){return false;}return this.sp_inc_styles_appendChild(element);};" . $early_js;
            }

            // Lazy load scripts
            if (!empty($lazyload_scripts)){
                  $early_js = "window.sp_lazyload_scripts_html_buffer = ".json_encode($lazyload_scripts_buffer)."; window.sp_lazyload_scripts_element_buffer = []; window.sp_lazyload_fired = false; Element.prototype._appendChild = Element.prototype.appendChild; Element.prototype.appendChild = function(element){ if (window.sp_lazyload_fired == false && element.nodeName == 'SCRIPT' && element.src && element.src.match(".$lazyload_scripts_regex.")){ if (window.sp_lazyload_scripts_element_buffer.indexOf(element) == -1){ window.sp_lazyload_scripts_element_buffer.push(element); } return false; } return this._appendChild(element); }; Element.prototype._insertBefore = Element.prototype.insertBefore; Element.prototype.insertBefore = function(element, existingElement){ if (window.sp_lazyload_fired == false && element.nodeName == 'SCRIPT' && element.src && element.src.match(".$lazyload_scripts_regex.")){ if (window.sp_lazyload_scripts_element_buffer.indexOf(element) == -1){ window.sp_lazyload_scripts_element_buffer.push(element); } return false; } return this._insertBefore(element, existingElement); };" . $early_js;

                  $late_js ="setTimeout(function(){ function load_script(){ var _script = window.sp_lazyload_scripts_html_buffer.shift(); var element = document.createElement('script'); element.src = _script; if (window.sp_lazyload_scripts_html_buffer.length > 0){ element.onreadystatechange = load_script; element.onload = load_script; } else if (typeof jQuery !== 'undefined'){ for (var i in window.swift_performance_collectready){ var f = window.swift_performance_collectready.shift(); if (typeof f === 'function'){f(jQuery)}; jQuery.fn.ready = jQuery.fn.realReady; } } if (typeof _script !== 'undefined'){ document.getElementsByTagName('head')[0].appendChild(element); } } function fire(){if (typeof jQuery !== 'undefined'){ jQuery.fn.realReady = jQuery.fn.ready; jQuery.fn.ready = function(cb){window.swift_performance_collectready.push(cb);return false;}; } window.sp_lazyload_fired = true; window.removeEventListener('touchstart',fire); window.removeEventListener('scroll',fire); document.removeEventListener('mousemove',fire); load_script(); for (var i in window.sp_lazyload_scripts_element_buffer){ var element = window.sp_lazyload_scripts_element_buffer.shift(); document.getElementsByTagName('head')[0].appendChild(element); } } window.addEventListener('load', function() { window.addEventListener('touchstart',fire); window.addEventListener('scroll',fire); document.addEventListener('mousemove',fire); }); },10);" . $late_js;
            }

            // DNS prefetch

            $dns_prefetch = array();
            if (Swift_Performance::check_option('dns-prefetch',1)){
                  // Create merged js without links
                  $_js = preg_replace('~</?a(|\s+[^>]+)>~','',$js);
                  preg_match_all('~href\s?=("|\')?(https?:)?//([^"\']*)("|\')? type=("|\')?text/css("|\')?~', $_html, $stylesheet_domains);
                  if (Swift_Performance::check_option('dns-prefetch-js',1)){
                        preg_match_all('~("|\')(https?:)?(\\\\)?/(\\\\)?/(([a-z0-9\._-]*)\.([a-z0-9\._-]*))~i', $_js, $js_domains);
                  }

			// CSS
			if (isset($css['all'])){
                  	preg_match_all('~(src|url)\s?(=|\()("|\'|)?(https?:)?//([^"\'\)]*)("|\'|\))?~', $_html . $css['all'], $other_domains);
			}

                  @$domains = array_merge((array)$stylesheet_domains[3], (array)$js_domains[5], (array)$other_domains[5]);

                  $exclude_dns_prefetch = array();
                  foreach ((array)Swift_Performance::get_option('exclude-dns-prefetch') as $exclude_domain){
                        // Format url to host
                        $exclude_dns_prefetch[] = parse_url('http://' . preg_replace('~(https?:)?//~', '', $exclude_domain), PHP_URL_HOST);
                  }
                  $skip_dns_prefetch = array_merge($exclude_dns_prefetch, array(parse_url(home_url(), PHP_URL_HOST), 'www.w3.org', 'w3.org', 'github.com', 'www.github.com'));

                  foreach ((array)$domains as $domain){
                        $domain = parse_url('http://' . $domain, PHP_URL_HOST);
                        if (!empty($domain) && !in_array($domain, $skip_dns_prefetch)){
                              $dns_prefetch[$domain] = "<link rel='dns-prefetch' href='//{$domain}'>";
                        }
                  }
            }

            $_html = str_replace('<!--DNS_PREFETCH_PLACEHOLDER-->', implode("\n", $dns_prefetch), $_html);

            // Minify javascripts with Compute API
            if (Swift_Performance::check_option('minify-scripts', 1) && Swift_Performance::check_option('use-script-compute-api', 1) && Swift_Performance::check_option('exclude-script-localizations', 1)){
                  Swift_Performance::log('Minify javascript (API)', 9);
                  $api_args = array(
                        'source' => $GLOBALS['swift-performance-js-buffer'],
                  );

                  $response = Swift_Performance::compute_api($api_args, 'js');

                  if (!empty($response)){
                        $api = json_decode($response, true);
                        if (!empty($api['compressed'])){
                              $_js = base64_decode($api['compressed']);
                              if (!empty($_js)){
                                    Swift_Performance::log('Javascript minified (API)', 9);
                                    $js = $_js;
                              }
                              Swift_Performance::log('Javascript minify failed (Base64 invalid): '.$api['compressed'].' ', 1);
                        }
                        Swift_Performance::log('Javascript minify failed (JSON invalid): '.$api['compressed'].' ', 1);
                  }
                  else {
                        Swift_Performance::log('Javascript minify failed ', 1);
                  }
            }

            // Add extra critical CSS here
            $critical_css .= Swift_Performance::get_option('extra-critical-css','');

            // Write critical CSS
            if (Swift_Performance::check_option('critical-css', 1)){
			$critical_css = apply_filters('swift_performance_critical_css_content', $critical_css);
                  if (Swift_Performance::check_option('inline_critical_css', 1)){
      		      $_html = str_replace('<!--CSS_HEADER_PLACEHOLDER-->', '<style id="critical-css">'.$critical_css.'</style>',$_html);
                  }
                  else {
				$critical_css_filename = apply_filters('swift_performance_critical_css_filename', md5($critical_css) . '.css');
                        $_html = str_replace('<!--CSS_HEADER_PLACEHOLDER-->', '<link id="critical-css" rel="stylesheet" href="'.apply_filters('style_loader_src', Swift_Performance_Cache::write_file(trailingslashit($css_dir) . $critical_css_filename, $critical_css), 'swift-performance-critical').'" media="all">', $_html);
                  }
            }

            // Merged Javascripts
            if (Swift_Performance::check_option('inline-merged-scripts', 1)){
                  // Inline
                  $merged_scripts = '<script>' . apply_filters('swift_performance_js_content', $early_js . $js . $late_js) . '</script>';
            }
            else {
                  // Embedded
                  if (Swift_Performance::check_option('async-scripts', 1)){
                        $js = str_replace('DOMContentLoaded', 'SwiftDOMContentLoaded', $js);
                  }
			$js_content = apply_filters('swift_performance_js_content', $early_js . self::script_boundary() . $js . $late_js . self::script_boundary());
			$js_filename = apply_filters('swift_performance_js_filename', md5($js_content) . '.js');
                  $merged = preg_replace('~https?://~', '//',apply_filters('script_loader_src', Swift_Performance_Cache::write_file(trailingslashit($js_dir) . $js_filename, $js_content), 'swift-performance-merged'));
                  if (Swift_Performance::check_option('async-scripts', 1)){
                        $merged_scripts = '<script>(function(){ window.SwiftDOMContentLoaded = false; window.realOnload = window.onload; window.realAddEventListener = window.addEventListener; window.addEventListener = function(e,cb){if (e == "load"){window.swift_performance_collectonload.push({"e": e, "cb": cb});return false;} else {window.realAddEventListener(e, cb)}}; window.onload = function(cb){window.swift_performance_collectonload.push({"cb": cb});return false;}; function hold_onready(){ if (typeof jQuery !== "undefined" && typeof jQuery.fn.realReady === "undefined"){ jQuery.fn.realReady = jQuery.fn.ready; jQuery.fn.ready = function(cb){window.swift_performance_collectready.push(cb);return false;}; } } function release_onready(){ if (typeof jQuery !== "undefined"){ while (window.swift_performance_collectready.length > 0){ var f = window.swift_performance_collectready.shift(); if (typeof f === "function"){ f(jQuery); } } jQuery.fn.ready = jQuery.fn.realReady; } if (!window.SwiftDOMContentLoaded){ window.SwiftDOMContentLoaded = true; document.dispatchEvent(new Event("SwiftDOMContentLoaded")); } } function release_onload(){ while (window.swift_performance_collectonload.length > 0){ var f = window.swift_performance_collectonload.shift(); if (typeof f.cb === "function"){ if (typeof f.e === "undefined"){ f.cb(); } else { window.realAddEventListener("load",f.cb); } } } window.addEventListener = window.realAddEventListener; window.onload = window.realOnload; setTimeout(function(){window.dispatchEvent(new Event("load"))},1); } var li = 0; var lc = ""; var xhr = new XMLHttpRequest(); xhr.open("GET", "'.$merged.'"); xhr.onload = function () { release_onready(); release_onload(); }; xhr.onprogress = function () { var ci = xhr.responseText.length; if (li == ci){ try{eval.call(window, lc)}catch(e){}; hold_onready(); return; } var s = xhr.responseText.substring(li, ci).split("'.self::script_boundary().'"); for (var i in s){ if (i != s.length-1){ try{eval.call(window, lc + s[i])}catch(e){}; hold_onready(); lc = ""; } else { lc += s[i]; } } li = ci; }; xhr.send(); })();</script>';
                  }
                  else {
                        $merged_scripts = '<script src="'.$merged.'"></script>';
                  }
            }
		$_html = str_replace('<!--JS_FOOTER_PLACEHOLDER-->', $merged_scripts . $excluded_scripts, $_html);

            // Merged CSS
            if (Swift_Performance::check_option('critical-css', 1)){
                  $_html = str_replace('<!--CSS_FOOTER_PLACEHOLDER-->', $defered_styles, $_html);
            }
            else {
                  $_html = str_replace('<!--CSS_FOOTER_PLACEHOLDER-->', '', $_html);
                  $_html = str_replace('<!--CSS_HEADER_PLACEHOLDER-->', $defered_styles, $_html);
            }

            if (Swift_Performance::check_option('minify-html',1)){
                  // Remove empty html attributes
                  $_html = preg_replace('~ (class|style|id|alt|value)=("|\')("|\')~', ' $1=$2$3$4', $_html);

                  // Thanks for ridgerunner (http://stackoverflow.com/questions/5312349/minifying-final-html-output-using-regular-expressions-with-codeigniter)
                  $re = '%# Collapse whitespace everywhere but in blacklisted elements.
                          (?>             # Match all whitespans other than single space.
                            [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
                          | \s{2,}        # or two or more consecutive-any-whitespace.
                          ) # Note: The remaining regex consumes no text at all...
                          (?=             # Ensure we are not in a blacklist tag.
                            [^<]*+        # Either zero or more non-"<" {normal*}
                            (?:           # Begin {(special normal*)*} construct
                              <           # or a < starting a non-blacklist tag.
                              (?!/?(?:textarea|pre|script)\b)
                              [^<]*+      # more non-"<" {normal*}
                            )*+           # Finish "unrolling-the-loop"
                            (?:           # Begin alternation group.
                              <           # Either a blacklist start tag.
                              (?>textarea|pre|script)\b
                            | \z          # or end of file.
                            )             # End alternation group.
                          )  # If we made it here, we are not in a blacklist tag.
                          %Six';
                      $_html = preg_replace($re, ' ', $_html);
            }

            $_html = str_replace('<!--SWIFT_PERFORMACE_OB_CONFLICT-->', '', $_html);

		return apply_filters('swift_performance_buffer', $_html);
	}

	/**
	 * Change relative paths to absolute one
	 * depricated
	 */
	public function css_realpath($matches){
		$url = parse_url($GLOBALS['swift_css_realpath_basepath']);
		return (isset($url['scheme']) ? $url['scheme'] .':' : '') . '//' . $url['host'] . self::realpath(trailingslashit(dirname($url['path'])) . $matches[0]);
	}

	/**
	 * Change relative paths to absolute one for urls
	 */
	public function css_realpath_url($matches){
		if (preg_match('~^(http|//|data|/)~',$matches[2])){
			return $matches[0];
		}

		$url		= parse_url($GLOBALS['swift_css_realpath_basepath']);
		$path		= (isset($url['scheme']) ? $url['scheme'] .':' : '') . '//' . $url['host'] . self::realpath(trailingslashit(dirname($url['path'])) . trim($matches[2]),"'");

		// Use base64 encode
		if (Swift_Performance::check_option('base64-small-images', 1) && preg_match('~\.(jpe?g|png)$~', $path) && strpos($path, apply_filters('swift_performance_media_host', home_url())) !== false){
			$img_path   = str_replace(apply_filters('swift_performance_media_host', home_url()), ABSPATH, $path);
	            if (file_exists($img_path) && filesize($img_path) <= Swift_Performance::get_option('base64-small-images-size')){
	                  $mime = (preg_match('~\.jpe?g$~', $img_path) ? 'jpeg' : 'png');
	                  return 'url(data:image/'.$mime.';base64,' . base64_encode(file_get_contents($img_path)) . ')';
	            }
		}
		else {
			return 'url(' . $matches[1] . $path . $matches[1] . ')';
		}

	}

	/**
	 * Include imported CSS
	 */
	public function bypass_css_import($matches){
		if (preg_match('~^data~',$matches[2]) || Swift_Performance::check_option('bypass-css-import', 1, '!=')){
			return $matches[0];
		}

            if (preg_match('~^http~', $matches[2])){
                  $url = $matches[2];
            }
            else if(preg_match('~^//~', $matches[2])){
                  $url = 'http:'.$matches[2];
            }
            else {
                  $realpath   = parse_url($GLOBALS['swift_css_realpath_basepath']);
                  $url        = (isset($realpath['scheme']) ? $realpath['scheme'] : 'http') . '://' . $realpath['host'] . trailingslashit(dirname($realpath['path'])) . trim($matches[2],"'");
            }

            $response = wp_remote_get($url, array('sslverify' => false));

		if (!is_wp_error($response)){
                  $swift_css_realpath_basepath = $GLOBALS['swift_css_realpath_basepath'];
                  $GLOBALS['swift_css_realpath_basepath'] = $url;
			$response['body'] = preg_replace_callback('~@import url\((\'|")?([^\("\']*)(\'|")?\);?~', array($this, 'bypass_css_import'), $response['body']);
			$response['body'] = preg_replace_callback('~url\((\'|")?([^\("\']*)(\'|")?\)~', array($this, 'css_realpath_url'), $response['body']);
			$response['body'] = Swift_Performance_CSSMin::minify($response['body']);

                  $GLOBALS['swift_css_realpath_basepath'] = $swift_css_realpath_basepath;
			return $response['body'];
		}
            else {
                  Swift_Performance::log('Loading remote file (' . $url . ') failed. Error: ' . $response->get_error_message(), 1);
			return '';
            }
	}

      /**
       * Remove query string from JS/CSS
       * @param string $tag
       * @param srting $handle
       * @return string
       */
      public function remove_static_ver( $src ) {
            if( strpos( $src, '?ver=' ) ){
                  $src = remove_query_arg( 'ver', $src );
            }
            return $src;
      }

      /**
       * Remove query string from images
       * @param string $meta_value
	 * @param int $object_id
	 * @param string $meta_key
	 * @param boolean $single
       * @return string
       */
      public function normalize_vc_custom_css($meta_value, $object_id, $meta_key, $single ){
            global $swift_performance_get_metadata_filtering;
            if ($swift_performance_get_metadata_filtering !== true && ($meta_key == '_wpb_shortcodes_custom_css' || $meta_key == '_wpb_post_custom_css')){
                  $swift_performance_get_metadata_filtering = true;
                  $meta_value = preg_replace('~\.(jpe?g|gif|png)\?id=(\d*)~',".$1", get_post_meta( $object_id, $meta_key, true ));
                  $swift_performance_get_metadata_filtering = false;
                  return $meta_value;
            }
            return $meta_value;
      }

	/**
       * Remove query string from fonts
       * @param string $css
       * @return string
       */
      public function normalize_font_urls($css){
            return preg_replace('~\.(woff2?|ttf|eot|svg)\?([^\s"\'\)]+)~',".$1", $css);
      }

      /**
       * Lazy load images
       * @param array $args
       * @return array
       */
      public function lazyload_images($args, $id){
		if ($id <= 0 || Swift_Performance::check_option('lazyload-images-placeholder', 'transparent')){
			$lazy_load_src[0] = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";

			// Force sizes
	            $width = (isset($args['width']) ? $args['width'] .'px' : '');
	            $height = (isset($args['height']) ? $args['height'] .'px' : '');

	            if (empty($width) || empty($height)){
				if ($id > 0){
					$metadata = get_post_meta($id, '_wp_attachment_metadata', true);
				      if (!empty($metadata) && is_array($metadata) && isset($metadata['sizes'])){
					      foreach((array)$metadata['sizes'] as $is){
						      if (preg_match('~'.$is['file'].'$~', $args['src'])){
							     $width = $is['width'] . 'px';
							     $height = $is['height'] . 'px';
						      }
					      }
				      }
				}
				else {
					$src = (preg_match('~^//~', $args['src']) ? 'https:' . $args['src'] : (preg_match('~^http~', $args['src']) ? $args['src'] : parse_url(Swift_Performance::home_url(), PHP_URL_SCHEME) . '://' . $args['src']));
					$response = wp_remote_get($src, array('sslverify' => false, 'timeout' => 15, 'headers' => array('Referer' => home_url())));

					if (is_wp_error($response) || empty($response['body'])){
						return false;
					}
					else {
						$tmp_image = imagecreatefromstring( $response );
						$width	= imagesx($tmp_image);
						$height	= imagesy($tmp_image);
						imagedestroy($tmp_image);
					}
				}
	            }

		}
		else {
	            $upload_dir = wp_upload_dir();
	            // Is lazy load image exists already?
	            $intermediate = image_get_intermediate_size($id, 'swift_performance_lazyload');
	            if (!empty($intermediate)) {
	                  $lazy_load_src[0] = str_replace(basename($args['src']), $intermediate['file'], $args['src']);
	            }
	            else {
	                  require_once(ABSPATH . 'wp-admin/includes/image.php');
	                  require_once(ABSPATH . 'wp-admin/includes/file.php');
	                  require_once(ABSPATH . 'wp-admin/includes/media.php');
	                  // Regenerate thumbnails
	                  wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, get_attached_file($id) ) );
	                  // Second try
	                  $intermediate = image_get_intermediate_size($id, 'swift_performance_lazyload');
	                  if (!empty($intermediate)) {
	                        $lazy_load_src[0] = str_replace(basename($args['src']), $intermediate['file'], $args['src']);
	                  }
	                  // Give it up if we can't generate new size (eg: disk is full)
	                  else{
					$lazy_load_src[0] = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
	                  }
	            }

	            if (strpos($lazy_load_src[0], 'data:image') === false && !file_exists(str_replace(apply_filters('swift_performance_media_host',$upload_dir['baseurl']), $upload_dir['basedir'], $lazy_load_src[0]))){
	                  return $args;
	            }

	            // Force sizes
	            $width = (isset($args['width']) ? $args['width'] .'px' : '');
	            $height = (isset($args['height']) ? $args['height'] .'px' : '');

	            if (empty($width) || empty($height)){
	                  $metadata = get_post_meta($id, '_wp_attachment_metadata', true);
				if (!empty($metadata) && is_array($metadata) && isset($metadata['sizes'])){
		                  foreach((array)$metadata['sizes'] as $is){
		                        if (preg_match('~'.$is['file'].'$~', $args['src'])){
		                              $width = $is['width'] . 'px';
		                              $height = $is['height'] . 'px';
		                        }
		                  }
				}
	            }

	            if (strpos($lazy_load_src[0], 'data:image') === false && Swift_Performance::check_option('base64-lazy-load-images',1) || Swift_Performance::check_option('base64-small-images',1)){
	                  $mime		= preg_match('~\.jpg$~', $lazy_load_src[0]) ? 'jpeg' : 'png';
				$img_path	= str_replace(apply_filters('swift_performance_media_host',$upload_dir['baseurl']), $upload_dir['basedir'], $lazy_load_src[0]);

				if (isset($prebuild_booster[md5($img_path)])){
					$lazy_load_src[0] = $prebuild_booster[md5($img_path)];
				}
				else {
					$lazy_load_src[0] = 'data:image/'.$mime.';base64,' . base64_encode(file_get_contents($img_path));
					$prebuild_booster[md5($img_path)] = $lazy_load_src[0];
					Swift_Performance::safe_set_transient('swift_performance_prebuild_booster', $prebuild_booster, 600);
				}
	            }
		}

            // Override arguments
            $args['data-src'] = $args['src'];
            $args['data-srcset'] = (isset($args['srcset']) ? $args['srcset'] : '');
            $args['data-sizes'] = (isset($args['sizes']) ? $args['sizes'] : '');
            $args['src'] = $lazy_load_src[0];
            $args['data-swift-image-lazyload'] = 'true';
            $args['data-style'] = isset($args['style']) ? $args['style'] : '';
            $args['style'] = (isset($args['style']) ? trim($args['style'], ';') . ';' : '') . ' ' . 'width:' . $width.';height:' . $height;
            unset($args['srcset']);
            unset($args['sizes']);
            return $args;
      }

      /**
       * Get image sizes
       */
      public function intermediate_image_sizes() {
            // Add lazy load
            add_image_size( 'swift_performance_lazyload', SWIFT_PERFORMANCE_LAZYLOAD_SIZE, SWIFT_PERFORMANCE_LAZYLOAD_SIZE);
      }

      /**
       * Check should merge assets
       * @return boolean
       */
      public static function should_optimize(){
            // Don't optimize REST API response
            if (defined('REST_REQUEST') && REST_REQUEST){
                  return false;
            }

            $should_optimize = ((Swift_Performance::check_option('enable-caching', 1, '!=') || Swift_Performance::check_option('merge-background-only', 1, '!=') || isset($_SERVER['HTTP_X_MERGE_ASSETS'])) && (Swift_Performance_Cache::is_cacheable() || Swift_Performance_Cache::is_cacheable_dynamic()));

            if ($should_optimize && Swift_Performance::get_thread() === false){
                  return false;
            }

            return apply_filters('swift_performance_should_optimize', $should_optimize);
      }

      /**
       * Minify given javascript
       * @param string $js
       * @return string
       */
      public static function minify_js($js){
		// Remove comment blocks
		$js = preg_replace('~(\<![\-\-\s\w\>\/]*\>)~','',$js);

            if (Swift_Performance::check_option('minify-scripts', 1)){
                  Swift_Performance::log('Minify JS', 9);
                  if (Swift_Performance::check_option('use-script-compute-api', 1, '!=')){
                        try {
                              //Minify it
                              require_once 'JSMin.class.php';
                              Swift_Performance::log('Javascript minified', 9);
                              return \Swift_Performance_JSMin::minify($js);
                        } catch (Exception $e) {
                              Swift_Performance::log('Javascript minify failed '. $e->getMessage(), 1);
                              //Silent fail
                        }
                  }
                  else {
                        $GLOBALS['swift-performance-js-buffer'][] = base64_encode($js . "\n".self::script_boundary()."\n");
                  }
            }
            return $js;
      }

      /**
       * Proxy 3rd party requests and cache results
       * @return string
       */
      public static function proxy_3rd_party_request(){
            $cache_path = str_replace(ABSPATH, '', SWIFT_PERFORMANCE_CACHE_DIR);

            if (strpos($_SERVER['REQUEST_URI'], $cache_path . 'assetproxy') !== false){
                  $asset_path = preg_replace('~^/([abcdef0-9]*)/~', '', str_replace($cache_path . 'assetproxy/', '', $_SERVER['REQUEST_URI']));
                  $asset_path = str_replace(array('.assetproxy.js', '.assetproxy.swift.css'), '', $asset_path);

                  $url = (is_ssl() ? 'https://' : 'http://') . trim($asset_path,'/');
                  $included = Swift_Performance::get_option('include-3rd-party-assets');
                  if (!in_array($url, (array)$included)){
                        return;
                  }


                  $response = wp_remote_get($url);
                  if (!is_wp_error($response)){

                        // Find 3rd party assets recursively
                        $response['body'] = preg_replace_callback('~(https?:)?//([\.a-z0-9_-]*)\.(xn--clchc0ea0b2g2a9gcd|xn--hlcj6aya9esc7a|xn--hgbk6aj7f53bba|xn--xkc2dl3a5ee0h|xn--mgberp4a5d4ar|xn--11b5bs3a9aj6g|xn--xkc2al3hye2a|xn--80akhbyknj4f|xn--mgbc0a9azcg|xn--lgbbat1ad8j|xn--mgbx4cd0ab|xn--mgbbh1a71e|xn--mgbayh7gpa|xn--mgbaam7a8h|xn--9t4b11yi5a|xn--ygbi2ammx|xn--yfro4i67o|xn--fzc2c9e2c|xn--fpcrj9c3d|xn--ogbpf8fl|xn--mgb9awbf|xn--kgbechtv|xn--jxalpdlp|xn--3e0b707e|xn--s9brj9c|xn--pgbs0dh|xn--kpry57d|xn--kprw13d|xn--j6w193g|xn--h2brj9c|xn--gecrj9c|xn--g6w251d|xn--deba0ad|xn--80ao21a|xn--45brj9c|xn--0zwm56d|xn--zckzah|xn--wgbl6a|xn--wgbh1c|xn--o3cw4h|xn--fiqz9s|xn--fiqs8s|xn--90a3ac|xn--p1ai|travel|museum|post|name|mobi|jobs|info|coop|asia|arpa|aero|xxx|tel|pro|org|net|mil|int|gov|edu|com|cat|biz|zw|zm|za|yt|ye|ws|wf|vu|vn|vi|vg|ve|vc|va|uz|uy|us|uk|ug|ua|tz|tw|tv|tt|tr|tp|to|tn|tm|tl|tk|tj|th|tg|tf|td|tc|sz|sy|sx|sv|su|st|sr|so|sn|sm|sl|sk|sj|si|sh|sg|se|sd|sc|sb|sa|rw|ru|rs|ro|re|qa|py|pw|pt|ps|pr|pn|pm|pl|pk|ph|pg|pf|pe|pa|om|nz|nu|nr|np|no|nl|ni|ng|nf|ne|nc|na|mz|my|mx|mw|mv|mu|mt|ms|mr|mq|mp|mo|mn|mm|ml|mk|mh|mg|me|md|mc|ma|ly|lv|lu|lt|ls|lr|lk|li|lc|lb|la|kz|ky|kw|kr|kp|kn|km|ki|kh|kg|ke|jp|jo|jm|je|it|is|ir|iq|io|in|im|il|ie|id|hu|ht|hr|hn|hm|hk|gy|gw|gu|gt|gs|gr|gq|gp|gn|gm|gl|gi|gh|gg|gf|ge|gd|gb|ga|fr|fo|fm|fk|fj|fi|eu|et|es|er|eg|ee|ec|dz|do|dm|dk|dj|de|cz|cy|cx|cw|cv|cu|cr|co|cn|cm|cl|ck|ci|ch|cg|cf|cd|cc|ca|bz|by|bw|bv|bt|bs|br|bo|bn|bm|bj|bi|bh|bg|bf|be|bd|bb|ba|az|ax|aw|au|at|as|ar|aq|ao|an|am|al|ai|ag|af|ae|ad|ac)([\.\/a-z0-9-_]*)~i', array('Swift_Performance_Asset_Manager', 'asset_proxy_callback'), $response['body']);

                        $prefix = hash('crc32',date('Y-m-d H')) . '/';
                        Swift_Performance_Cache::write_file('assetproxy/' . $prefix . parse_url($asset_path, PHP_URL_PATH), $response['body']);
                        header('Content-Type: ' . (preg_match('~\.js$~', parse_url($asset_path, PHP_URL_PATH)) ? 'text/javascript' : 'text/css'));
                        echo $response['body'];
                  }
                  else{
                        Swift_Performance::log('Loading remote file (http://' . $asset_path . ') failed. Error: ' . $response->get_error_message(), 1);
                  }
                  die;
            }
      }

      /**
       * Clear assets cache
       */
      public static function clear_assets_cache(){
            Swift_Performance_Cache::recursive_rmdir('css');
		Swift_Performance_Cache::recursive_rmdir('js');

            Swift_Performance_Cache::recursive_rmdir('assetproxy');

            // MaxCDN
            if (Swift_Performance::check_option('enable-cdn', 1) && Swift_Performance::check_option('maxcdn-alias', '','!=') && Swift_Performance::check_option('maxcdn-key', '','!=') && Swift_Performance::check_option('maxcdn-secret', '','!=')){
                  Swift_Performance_CDN_Manager::purge_cdn();
            }

            Swift_Performance::log('Assets cache cleared', 9);
      }

      /**
       * Clear assets proxy cache
       */
      public static function clear_assets_proxy_cache(){
            Swift_Performance_Cache::recursive_rmdir('assetproxy');

            // MaxCDN
            if (Swift_Performance::check_option('enable-cdn', 1) && Swift_Performance::check_option('maxcdn-alias', '','!=') && Swift_Performance::check_option('maxcdn-key', '','!=') && Swift_Performance::check_option('maxcdn-secret', '','!=')){
                  Swift_Performance_CDN_Manager::purge_cdn();
            }

            Swift_Performance::log('Assets proxy cache cleared', 9);
      }

      /**
       * Get rid 3rd party js/css files and pass them to proxy
       * @param array $matches
       * @return string
       */
      public static function asset_proxy_callback($matches){
            // Skip excluded assets

            $included = Swift_Performance::get_option('include-3rd-party-assets');
            if (!in_array($matches[0], (array)$included)){
                  return $matches[0];
            }

            $test = false;
            // Is it js/css file?
            if (preg_match('~(\.((?!json)js|css))$~',$matches[4])){
                  $test = true;
            }
            // Really?
            if (!$test){
                  $response = wp_remote_get(preg_replace('~^//~', 'http://', $matches[0]));
                  if (!is_wp_error($response)){
                        if (preg_match('~(text|application)/javascript~', $response['headers']['content-type'])){
                              if (!preg_match('~\.js$~', $matches[4])){
                                    $matches[4] .= '.assetproxy.js';
                              }
                              $test = true;
                        }
                        else if (strpos($response['headers']['content-type'], 'text/css') !== false){
                              if (!preg_match('~\.css$~', $matches[4])){
                                    $matches[4] .= '.assetproxy.css';
                              }
                              $test = true;
                        }
                  }
            }
            if ($test){
                  $prefix = hash('crc32',date('Y-m-d H')) . '/';
                  return preg_replace('~https?:~','',SWIFT_PERFORMANCE_CACHE_URL) . 'assetproxy/' . $prefix . $matches[2] . '.' . $matches[3] . $matches[4];
            }
            return $matches[0];
      }

      /**
       * Return script boundary if async scripts are enabled
       */
      public static function script_boundary(){
            if (Swift_Performance::check_option('async-scripts', 1)){
                  return apply_filters('swift_performance_script_boundary', '/*!' . strtoupper(SWIFT_PERFORMANCE_SLUG) . '-SCRIPT-BOUNDARY*/');
            }
            return '';
      }

      /**
       * Fix some invalid HTML in given string
       * @param string $html
       * @return string
       */
      public static function html_auto_fix($html){
            if (Swift_Performance::check_option('html-auto-fix',1)){
                  return preg_replace('~(="([^"]*)")([a-z]+)~', "$1 $3", $html);
            }
            return $html;
      }

	/**
	 * Calculate realpath from a string
	 * @param string $path
	 * @return string
	 */
	public static function realpath($path){
		$realpath = array();
		foreach ((array)explode('/',$path) as $key => $value) {
			if ($value == '.'){
				continue;
			}
			if ($value == '..' && isset($prevkey) && isset($realpath[$prevkey])){
				unset($realpath[$prevkey]);
				$prevkey--;
				continue;
			}
			$realpath[$key] = $value;
			$prevkey = $key;
		}
		return implode('/', $realpath);
	}

}
return new Swift_Performance_Asset_Manager();

?>
