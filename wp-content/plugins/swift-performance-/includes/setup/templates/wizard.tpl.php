<?php
global $title, $hook_suffix, $current_screen, $wp_locale, $pagenow, $wp_version,
		$update_title, $total_update_count, $parent_file, $swift_performance_setup;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo sprintf(esc_html__('%s setup', 'swift-performance'), SWIFT_PERFORMANCE_PLUGIN_NAME);?></title>
	<script type="text/javascript">
	addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
	var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
		pagenow = '',
		typenow = '',
		adminpage = '',
		thousandsSeparator = '<?php echo addslashes( $wp_locale->number_format['thousands_sep'] ); ?>',
		decimalPoint = '<?php echo addslashes( $wp_locale->number_format['decimal_point'] ); ?>',
		isRtl = <?php echo (int) is_rtl(); ?>;
	</script>
	<?php do_action( 'swift_performance_setup_enqueue_scripts' ); ?>
	<?php do_action( 'admin_print_styles' ); ?>
	<?php do_action( 'admin_print_scripts' );?>
	<?php do_action( 'admin_head' ); ?>
</head>
<body class="wp-core-ui swift-setup">
	<div class="swift-setup-wrapper">
            <img class="swift-setup-logo" src="<?php echo SWIFT_PERFORMANCE_SETUP_URI . 'images/logo.png'?>">
            <div class="swift-setup-slide<?php echo(Swift_Performance::check_option('purchase-key', '') ? ' active' : '');?>" id="purchase-key">
                  <h2><?php echo sprintf(esc_html__('%s Setup Wizard', 'swift-performance'), SWIFT_PERFORMANCE_PLUGIN_NAME);?></h2>
                  <div class="purchase-key-inner" data-animation-in="fadeIn" data-animation-out="fadeOut">
                        <label><?php esc_html_e('Enter Purchase Key')?></label><br><br>
                        <input type="text" name="purchase-key" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx">
                        <div class="swift-buttonset swift-pull-right">
                              <a href="#" id="set-performance-key" class="swift-btn swift-btn-green"><?php esc_html_e('Save', 'swift-performance')?></a>
                              <a href="#" data-swift-setup-slide="dashboard" class="swift-btn swift-btn-gray"><?php esc_html_e('Skip', 'swift-performance')?></a>
                        </div>
                  </div>
            </div>
            <div class="swift-setup-slide<?php echo(Swift_Performance::check_option('purchase-key', '') ? '' : ' active');?>" id="dashboard">
                  <h2><?php echo sprintf(esc_html__('%s Setup Wizard', 'swift-performance'), SWIFT_PERFORMANCE_PLUGIN_NAME);?></h2>
                  <ul class="swift-setup-tiles two-columns">
                        <li class="swift-setup-tile" data-animation-in="fadeIn" data-animation-out="fadeOut">
                              <i class="fas fa-screwdriver"></i>
                              <strong><?php esc_html_e('Manual Configuration', 'swift-performance')?></strong>
                              <a href="<?php echo esc_url(add_query_arg(array('page' => SWIFT_PERFORMANCE_SLUG, 'subpage' => 'settings'), admin_url('tools.php')));?>" class="swift-setup-manual-config"></a>
                        </li>
                        <li class="swift-setup-tile" data-animation-in="fadeIn" data-animation-out="fadeOut">
                              <i class="fas fa-file-import"></i>
                              <strong><?php esc_html_e('Import Settings', 'swift-performance')?></strong>
                              <a href="#" data-swift-setup-slide="import"></a>
                        </li>
                        <li class="swift-setup-tile" data-animation-in="fadeIn" data-animation-out="fadeOut">
                              <i class="fas fa-clipboard-check"></i>
                              <strong><?php esc_html_e('Use Preset', 'swift-performance')?></strong>
                              <a href="#" data-swift-setup-slide="preset"></a>
                        </li>
                        <li class="swift-setup-tile" data-animation-in="fadeIn" data-animation-out="fadeOut">
                              <i class="fas fa-robot"></i>
                              <strong><?php esc_html_e('Autoconfig', 'swift-performance')?></strong>
                              <a href="#" data-swift-setup-slide="autoconfig"></a>
                        </li>
                  </ul>
            </div>

            <div class="swift-setup-slide" id="import">
                  <div data-animation-in="fadeIn">
                        <a href="#" data-swift-setup-slide="dashboard" class="swift-setup-btn"><i class="fas fa-chevron-left"></i> <?php esc_html_e('Back', 'swift-performance')?></a>
                        <h2><?php esc_html_e('Import Settings', 'swift-performance');?></h2>
                        <div class="swift-autoconfig-welcome">
                              <div class="swift-setup-info"><?php echo esc_html_e('Upload previously exported configuration file.', 'luv-framework'); ?></div>
                              <div class="swift-import-file-container" data-animation-in="fadeIn" data-animation-out="fadeOut">
                                    <label><i class="fas fa-cloud-upload-alt"></i> <?php esc_html_e('Choose file', 'swift-performance');?></label>
                                    <input type="file" class="swift-import-file">
                                    <textarea class="swift-import swift-hidden"></textarea>
                              </div>
                        </div>
                  </div>
            </div>

            <div class="swift-setup-slide" id="preset">
                  <div data-animation-in="fadeIn">
                        <a href="#" data-swift-setup-slide="dashboard" class="swift-setup-btn"><i class="fas fa-chevron-left"></i> <?php esc_html_e('Back', 'swift-performance')?></a>
                        <h2><?php esc_html_e('Use Preset', 'swift-performance');?></h2>
                        <ul class="swift-preset-list">
                              <li class="swift-preset-list-item">
                                    <div class="swift-preset-title">
                                          <i class="fas fa-chart-line"></i>
                                          <h2><?php esc_html_e('Simple Caching', 'swift-performance');?></h2>
                                    </div>
                                    <span><?php esc_html_e('Disk caching + PHP (timebased) with basic improvements. High compatibility, limited optimization.');?></span>
                                    <ul>
                                          <li><strong><?php esc_html_e('Caching', 'swift-performance');?></strong></li>
                                          <li><strong><?php esc_html_e('Normalize Static Resources', 'swift-performance');?></strong></li>
                                          <li><strong><?php esc_html_e('DNS Prefetch', 'swift-performance');?></strong></li>
                                          <li><strong><?php esc_html_e('Minify HTML', 'swift-performance');?></strong></li>
                                    </ul>
                                    <div class="swift-buttonset swift-pull-right">
                                          <a href="<?php echo esc_url(add_query_arg(array('page' => SWIFT_PERFORMANCE_SLUG), admin_url('tools.php')));?>" class="swift-setup-use-preset luv-framework-button bordered" data-preset="simple"><?php esc_html_e('Use Preset', 'swift-performance');?></a>
                                    </div>
                                    <textarea id="preset-simple" class="swift-hidden">{"minify-html":"1","lazy-load-images":"0"}</textarea>
                              </li>
                              <li class="swift-preset-list-item">
                                    <div class="swift-preset-title">
                                          <i class="fas fa-server"></i>
                                          <h2><?php esc_html_e('Limited Hosting', 'swift-performance');?></h2>
                                    </div>
                                    <span><?php esc_html_e('Disk caching + PHP (timebased) with basic improvements and basic optimization. It is ideal for limited shared hosting or very small VPS.');?></span>
                                    <ul>
                                          <li><?php esc_html_e('Caching', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Normalize Static Resources', 'swift-performance');?></li>
                                          <li><?php esc_html_e('DNS Prefetch', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Minify HTML', 'swift-performance');?></li>
                                          <li><strong><?php esc_html_e('Merge Styles', 'swift-performance');?></strong></li>
                                          <li><strong><?php esc_html_e('Basic CSS Minification', 'swift-performance');?></strong></li>
                                          <li><strong><?php esc_html_e('Bypass CSS Import', 'swift-performance');?></strong></li>
                                    </ul>
                                    <div class="swift-buttonset swift-pull-right">
                                          <a href="<?php echo esc_url(add_query_arg(array('page' => SWIFT_PERFORMANCE_SLUG), admin_url('tools.php')));?>" class="swift-setup-use-preset luv-framework-button bordered" data-preset="limited"><?php esc_html_e('Use Preset', 'swift-performance');?></a>
                                    </div>
                                    <textarea id="preset-limited" class="swift-hidden">{"minify-html":"1","merge-styles":"1","critical-css":"0","minify-css":"1","lazy-load-images":"0"}</textarea>
                              </li>
                              <li class="swift-preset-list-item">
                                    <div class="swift-preset-title">
                                          <i class="fas fa-hdd"></i>
                                          <h2><?php esc_html_e('Moderate Optimization', 'swift-performance');?></h2>
                                    </div>
                                    <span><?php esc_html_e('Disk caching + rewrites (timebased), basic CSS/JS optimization + Critical CSS. Good compatibility, improved optimization.');?></span>
                                    <ul>
                                          <li><?php esc_html_e('Caching', 'swift-performance');?></li>
                                          <li><strong><?php esc_html_e('Prebuild Cache', 'swift-performance');?></strong></li>
                                          <li><?php esc_html_e('Normalize Static Resources', 'swift-performance');?></li>
                                          <li><?php esc_html_e('DNS Prefetch', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Minify HTML', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Merge Styles', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Basic CSS Minification', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Bypass CSS Import', 'swift-performance');?></li>
                                          <li><strong><?php esc_html_e('Critical CSS', 'swift-performance');?></strong></li>
                                          <li><strong><?php esc_html_e('Merge JS', 'swift-performance');?></strong></li>
                                    </ul>
                                    <div class="swift-buttonset swift-pull-right">
                                          <a href="<?php echo esc_url(add_query_arg(array('page' => SWIFT_PERFORMANCE_SLUG), admin_url('tools.php')));?>" class="swift-setup-use-preset luv-framework-button bordered" data-preset="moderate"><?php esc_html_e('Use Preset', 'swift-performance');?></a>
                                    </div>
                                    <textarea id="preset-moderate" class="swift-hidden">{"use-compute-api":"1","caching-mode":"disk_cache_rewrite","minify-html":"1","merge-styles":"1","minify-css":"1","critical-css":"1","merge-scripts":"1","lazy-load-images":"0","automated_prebuild_cache":"1","optimize-prebuild-only":"1"}</textarea>
                              </li>
                              <li class="swift-preset-list-item">
                                    <div class="swift-preset-title">
                                          <i class="fas fa-tachometer-alt"></i>
                                          <h2><?php esc_html_e('Improved Optimization', 'swift-performance');?></h2>
                                    </div>
                                    <span><?php esc_html_e('Disk caching + rewrites (timebased), CSS/JS optimization + Critical CSS and lazyload for images. Good compatibility, well optimized.');?></span>
                                    <ul>
                                          <li><?php esc_html_e('Caching', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Prebuild Cache', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Normalize Static Resources', 'swift-performance');?></li>
                                          <li><?php esc_html_e('DNS Prefetch', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Minify HTML', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Merge Styles', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Basic CSS Minification', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Bypass CSS Import', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Critical CSS', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Merge JS', 'swift-performance');?></li>
                                          <li><strong><?php esc_html_e('Lazyload for Images', 'swift-performance');?></strong></li>
                                    </ul>
                                    <div class="swift-buttonset swift-pull-right">
                                          <a href="<?php echo esc_url(add_query_arg(array('page' => SWIFT_PERFORMANCE_SLUG), admin_url('tools.php')));?>" class="swift-setup-use-preset luv-framework-button bordered" data-preset="improved"><?php esc_html_e('Use Preset', 'swift-performance');?></a>
                                    </div>
                                    <textarea id="preset-improved" class="swift-hidden">{"use-compute-api":"1","caching-mode":"disk_cache_rewrite","minify-html":"1","merge-styles":"1","minify-css":"1","critical-css":"1","merge-scripts":"1","automated_prebuild_cache":"1","optimize-prebuild-only":"1"}</textarea>
                              </li>
                              <li class="swift-preset-list-item">
                                    <div class="swift-preset-title">
                                          <i class="fas fa-magic"></i>
                                          <h2><?php esc_html_e('Maximum Optimization', 'swift-performance');?></h2>
                                    </div>
                                    <span><?php esc_html_e('Disk caching + rewrites (timebased), CSS/JS optimization + Critical CSS and lazyload for images. Good compatibility, improved optimization.');?></span>
                                    <ul>
                                          <li><?php esc_html_e('Disk Caching', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Prebuild Cache', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Normalize Static Resources', 'swift-performance');?></li>
                                          <li><?php esc_html_e('DNS Prefetch', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Minify HTML', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Merge Styles', 'swift-performance');?></li>
                                          <li><strong><?php esc_html_e('Full CSS Minification', 'swift-performance');?></strong></li>
                                          <li><?php esc_html_e('Bypass CSS Import', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Critical CSS', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Merge JS', 'swift-performance');?></li>
                                          <li><?php esc_html_e('Lazyload for Images', 'swift-performance');?></li>
                                          <li><strong><?php esc_html_e('Async Execute for JS', 'swift-performance');?></strong></li>
                                          <li><strong><?php esc_html_e('Server Push', 'swift-performance');?></strong></li>
                                          <?php if ($is_woocommerce_active):?>
                                          <li><strong><?php esc_html_e('Cache Empty Minicart for WooCommerce', 'swift-performance');?></strong></li>
                                          <?php endif;?>
                                    </ul>
                                    <div class="swift-buttonset swift-pull-right">
                                          <a href="<?php echo esc_url(add_query_arg(array('page' => SWIFT_PERFORMANCE_SLUG), admin_url('tools.php')));?>" class="swift-setup-use-preset luv-framework-button bordered" data-preset="max"><?php esc_html_e('Use Preset', 'swift-performance');?></a>
                                    </div>
                                    <textarea id="preset-max" class="swift-hidden">{"use-compute-api":"1","caching-mode":"disk_cache_rewrite","minify-html":"1","merge-styles":"1","minify-css":"1","critical-css":"1","merge-scripts":"1","async-scripts":"1","server-push":"1","automated_prebuild_cache":"1","optimize-prebuild-only":"1","cache-empty-minicart":"1"}</textarea>
                              </li>
                        </ul>
                  </div>
            </div>

            <div class="swift-setup-slide" id="autoconfig">
                  <div data-animation-in="fadeIn">
                        <h2><?php _e('Auto-configuration', 'swift-performance');?></h2>
                        <div class="swift-autoconfig-welcome">
                              <p><?php esc_html_e('Please note, that if you run the wizard it will reset current settings to the default!', 'swift-performance'); ?>
                                    <br><br>
                                    <?php
                                    $plugin_conflicts = self::get_plugin_conflicts();
                                    if (!empty($plugin_conflicts['hard'])){
                                          echo sprintf(_n(
                                                '<strong>%s</strong> will be deactivated during the setup.',
                                                'The following plugins will be deactivated during the setup: <strong>%s</strong>',
                                                count($plugin_conflicts['hard']),
                                                'swift-performance'
                                          ), implode(', ', $plugin_conflicts['hard']));
                                    }
                                    ?>
                              </p>

                              <div class="swift-buttonset swift-pull-right">
                                    <a href="<?php echo esc_url(add_query_arg(array('page' => SWIFT_PERFORMANCE_SLUG), admin_url('tools.php')));?>" class="swift-autoconfig-start swift-btn swift-btn-green"><?php esc_html_e('Start', 'swift-performance');?></a>
                                    <a href="#" data-swift-setup-slide="dashboard" class="swift-btn swift-btn-gray"><?php esc_html_e('Cancel', 'swift-performance')?></a>
                              </div>
                        </div>
                        <div class="swift-autoconfig swift-hidden">
                              <ul class="swift-autoconfig-list">
                                    <li data-step="reset-defaults"><i class="fas fa-minus"></i> <?php esc_html_e('Reset settings', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="timeout"><i class="fas fa-minus"></i> <?php esc_html_e('Timeout test', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="max-connections"><i class="fas fa-minus"></i> <?php esc_html_e('Check Max Connections', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="api"><i class="fas fa-minus"></i> <?php esc_html_e('API connection', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="webserver"><i class="fas fa-minus"></i> <?php esc_html_e('Webserver & Rewrites', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="loopback"><i class="fas fa-minus"></i> <?php esc_html_e('Loopback', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="varnish-proxy"><i class="fas fa-minus"></i> <?php esc_html_e('Detect Varnish & Cloudflare cache', 'swift-performance');?><span class="result"></span></li>
						<li data-step="server-level-cache"><i class="fas fa-minus"></i> <?php esc_html_e('Detect Server Level Caching', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="php-settings"><i class="fas fa-minus"></i> <?php esc_html_e('PHP settings', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="plugins"><i class="fas fa-minus"></i> <?php esc_html_e('Detect 3rd party plugins', 'swift-performance');?><span class="result"></span></li>
                                    <li data-step="configure-cache"><i class="fas fa-minus"></i> <?php esc_html_e('Configure cache', 'swift-performance');?><span class="result"></span></li>
                              </ul>
                              <div class="swift-buttonset swift-pull-right">
                                    <a href="#" class="swift-autoconfig-finish swift-btn swift-btn-green" data-swift-setup-slide="finish"><?php esc_html_e('Next', 'swift-performance');?></a>
                              </div>
                        </div>
                  </div>
            </div>
            <div class="swift-setup-slide" id="cloudflare">
                  <h2><?php esc_html_e('Cloudflare', 'swift-performance')?></h2>
                  <div data-animation-in="fadeIn">
                        <div class="swift-p-row">
                              <input type="checkbox" name="cloudflare-auto-purge" value="1" id="cloudflare-auto-purge">
                              <label for="cloudflare-auto-purge">
                                    <?php esc_html_e('Enable Auto Purge', 'swift-performance');?>
                              </label><br><br>
                              <p><em><?php esc_html_e('If you enable this option the plugin will purge the cache on Cloudflare as well when it clears plugin cache. It is recommended to enable this option if you are using Cloudflare with caching.', 'swift-performance')?></em></p>
                        </div>
                        <div class="swift-p-row" id="cloudflare-email-container">
                              <label for="cloudflare-email">
                                    <?php esc_html_e('Cloudflare Account E-mail', 'swift-performance');?>
                              </label><br><br>
                              <input type="text" name="cloudflare-email" id="cloudflare-email" value="<?php echo esc_attr(Swift_Performance::get_option('cloudflare-email'));?>">
                              <p><em><?php esc_html_e('Your e-mail address which related to the Cloudflare account what you are using for the site.', 'swift-performance')?></em></p>
                        </div>
                        <div class="swift-p-row" id="cloudflare-api-key-container">
                              <label for="cloudflare-api-key">
                                    <?php esc_html_e('Cloudflare API Key', 'swift-performance');?>
                              </label><br><br>
                              <input type="text" name="cloudflare-api-key" id="cloudflare-api-key" value="<?php echo esc_attr(Swift_Performance::get_option('cloudflare-api-key'));?>">
                              <p><em><?php echo sprintf(esc_html__('  The generated API key for your Cloudflare account. %sGlobal API key%s', 'swift-performance'), '<a href="https://support.cloudflare.com/hc/en-us/articles/200167836-Where-do-I-find-my-Cloudflare-API-key-" target="_blank">', '</a>')?></em></p>
                        </div>
                        <div class="swift-buttonset swift-pull-right">
                              <a href="#" id="set-cloudflare-api" class="swift-btn swift-btn-green"><?php esc_html_e('Save', 'swift-performance')?></a>
                              <a href="#" data-swift-setup-slide="finish" class="swift-btn swift-btn-gray"><?php esc_html_e('Skip', 'swift-performance')?></a>
                        </div>
                  </div>
            </div>
            <div class="swift-setup-slide" id="finish">
                  <h2><?php esc_html_e('Your website is ready!', 'swift-performance'); ?></h2>
                  <div data-animation-in="fadeIn">
                        <?php if (!defined('SWIFT_PERFORMANCE_WHITELABEL') || SWIFT_PERFORMANCE_WHITELABEL === false):?>
                        <p>
                        	<em><?php _e('You can check advanced settings also to improve your results.<br> If you need any help, check the <a href="https://swiftperformance.io/faq/" target="_blank">FAQ</a>, <a href="https://kb.swteplugins.com/swift-performance/" target="_blank">Knowledge Base</a>, <a href="https://swteplugins.com/support/" target="_blank">Open a support ticket</a>, or join to the <a href="https://www.facebook.com/groups/SwiftPerformanceUsers/" target="_blank">Facebook Community!</a>', 'swift-performance');?></em>
                        </p>
                        <?php endif;?>
                        <p>
                        	<span class="swift-image-optimizer <?php echo (Swift_Performance::check_option('purchase-key', '') ? ' swift-hidden' : '');?>">
                        		<a href="<?php echo esc_url(add_query_arg('subpage', 'image-optimizer', menu_page_url(SWIFT_PERFORMANCE_SLUG, false))); ?>" class="swift-btn swift-btn-green"><?php echo esc_html__('Optimize images', 'swift-performance'); ?></a>
                        	</span>
                        	<a href="<?php echo esc_url(add_query_arg('subpage', 'settings', menu_page_url(SWIFT_PERFORMANCE_SLUG, false))); ?>" class="swift-btn swift-btn-gray"><?php echo sprintf(esc_html__('%s Settings', 'swift-performance'), SWIFT_PERFORMANCE_PLUGIN_NAME); ?></a>
                        	<a href="<?php echo esc_url(menu_page_url(SWIFT_PERFORMANCE_SLUG, false)); ?>" class="swift-btn swift-btn-gray"><?php echo sprintf(esc_html__('%s Dashboard', 'swift-performance'), SWIFT_PERFORMANCE_PLUGIN_NAME); ?></a>
                        </p>
                        <?php if (!defined('SWIFT_PERFORMANCE_WHITELABEL') || SWIFT_PERFORMANCE_WHITELABEL === false):?>
                        <p><?php esc_html_e('What\'s next?', 'swift-performance'); ?></p>
                        <div class="swift-setup-row">
                        	<div class="swift-setup-col">
                        		<ul>
                        			<li><a href="https://www.facebook.com/groups/SwiftPerformanceUsers/" target="_blank"><?php esc_html_e('Join to the Facebook Community');?></a></li>
                        			<li><a href="https://www.facebook.com/swiftplugin/" target="_blank"><?php esc_html_e('Follow us on Facebook', 'swift-performance'); ?></a></li>
                        			<li><a href="https://twitter.com/swiftplugin" target="_blank"><?php esc_html_e('Follow us on Twitter', 'swift-performance'); ?></a></li>
                        		</ul>
                        	</div>
                        </div>
                        <?php endif;?>
                  </div>
            </div>
      </div>
      <?php do_action( 'admin_print_footer_scripts' ); ?>
      <a class="back-to-dashboard" href="<?php echo admin_url()?>"><?php esc_html_e('Back to Dashboard', 'swift-performance')?></a>

      <div class="luv-modal luv-modal-hidden" data-modal></div>

      <div class="luv-framework-confirm-import luv-hidden">
            <h6 class="luv-modal__title"><?php esc_html_e('Hey!', 'swift-performance');?></h6>
            <p class="luv-modal__text"><?php esc_html_e('Import configuration file will override current settings. Do you proceed?', 'swift-performance');?></p>
            <a href="#" class="swift-btn swift-btn-green" data-luv-proceed-import><?php esc_html_e('Import', 'swift-performance');?></a>
            <a href="#" class="swift-btn swift-btn-brand" data-luv-close-modal><?php esc_html_e('Cancel', 'swift-performance');?></a>
      </div>
</body>
</html>
