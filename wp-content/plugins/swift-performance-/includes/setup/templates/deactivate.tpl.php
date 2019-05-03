<?php
	$custom_htaccess = Swift_Performance::get_option('custom-htaccess');
	$custom_htaccess = trim($custom_htaccess);
?>
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
                  <div class="swift-setup-slide active">
                        <h2><?php echo sprintf(esc_html__('Deactivate %s', 'swift-performance'), SWIFT_PERFORMANCE_PLUGIN_NAME); ?></h2>
                        <div class="swift-p-row">
                              <input type="checkbox" class="ios8-switch" name="keep-settings" value="enabled" id="keep-settings" checked>
                              <label for="keep-settings">
                                    <?php esc_html_e('Keep Settings', 'swift-performance');?>
                              </label>
                              <div><em><?php esc_html_e('If you enable this option, the plugin will keep current settings in DB after deactivate the plugin.', 'swift-performance')?></em></div>
                        </div>
                        <br>
                        <?php if (!empty($custom_htaccess) && Swift_Performance::server_software() == 'apache'):?>
                        <div class="swift-p-row">
                              <input type="checkbox" class="ios8-switch" name="keep-custom-htaccess" value="enabled" id="keep-custom-htaccess" checked>
                              <label for="keep-custom-htaccess">
                                    <?php esc_html_e('Keep Custom htaccess', 'swift-performance');?>
                              </label>
                              <p><em><?php esc_html_e('If you enable this option, the plugin will keep custom htaccess rules after deactivate the plugin.', 'swift-performance')?></em></p>
                        </div>
                        <br>
                        <?php endif; ?>
                        <div class="swift-p-row">
                              <input type="checkbox" class="ios8-switch" name="keep-warmup-table" value="enabled" id="keep-warmup-table" checked>
                              <label for="keep-warmup-table">
                                    <?php esc_html_e('Keep Warmup Table', 'swift-performance');?>
                              </label>
                              <div><em><?php esc_html_e('If you enable this option, the plugin will keep Warmup Table in DB after deactivate the plugin.', 'swift-performance')?></em></div>
                        </div>
                        <br>
                        <div class="swift-p-row">
                              <input type="checkbox" class="ios8-switch" name="keep-image-optimizer-table" value="enabled" id="keep-image-optimizer-table" checked>
                              <label for="keep-image-optimizer-table">
                                    <?php esc_html_e('Keep Image Optimizer Table', 'swift-performance');?>
                              </label>
                              <div><em><?php esc_html_e('If you enable this option, the plugin will keep Image Optimizer Table in DB after deactivate the plugin.', 'swift-performance')?></em></div>
                        </div>
                        <br>
                        <div class="swift-p-row">
                              <input type="checkbox" class="ios8-switch" name="keep-logs" value="enabled" id="keep-logs">
                              <label for="keep-logs">
                                    <?php esc_html_e('Keep Logs', 'swift-performance');?>
                              </label>
                              <div><em><?php esc_html_e('If you enable this option, the plugin will keep logs after deactivate the plugin.', 'swift-performance')?></em></div>
                        </div><br><br>
                        <div class="swift-buttonset swift-pull-right">
                              <a href="<?php echo wp_nonce_url(sprintf( admin_url( 'plugins.php?action=deactivate&plugin=%s&plugin_status=all&paged=1&s' ), SWIFT_PERFORMANCE_PLUGIN_BASENAME ), 'deactivate-plugin_' . SWIFT_PERFORMANCE_PLUGIN_BASENAME); ?>" id="deactivate-plugin" class="swift-btn swift-btn-brand"><?php esc_html_e('Deactivate', 'swift-performance');?></a>
                              <a href="<?php echo esc_url(add_query_arg(array('page' => SWIFT_PERFORMANCE_SLUG), admin_url('tools.php')));?>" class="swift-btn swift-btn-gray"><?php esc_html_e('Cancel', 'swift-performance')?></a>
                        </div>
                  </div>
            </div>
            <?php do_action( 'admin_print_footer_scripts' ); ?>
            <a class="back-to-dashboard" href="<?php echo esc_url(menu_page_url(SWIFT_PERFORMANCE_SLUG, false)); ?>"><?php esc_html_e('Back to Dashboard', 'swift-performance')?></a>
      </body>
</html>
