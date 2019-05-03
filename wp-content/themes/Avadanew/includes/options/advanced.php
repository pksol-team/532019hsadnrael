<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Advanced settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_advanced( $sections ) {

	$embed_types = array();
	if ( class_exists( 'Avada_Privacy_Embeds' ) ) {
		$embed_types    = Avada()->privacy_embeds->get_embed_defaults( true );
		$embed_defaults = array_keys( $embed_types );
	}

	$sections['advanced'] = array(
		'label'    => esc_html__( 'Advanced', 'Avada' ),
		'id'       => 'heading_advanced',
		'is_panel' => true,
		'priority' => 25,
		'icon'     => 'el-icon-puzzle',
		'fields'   => array(
			'tracking_head_body_section' => array(
				'label'       => esc_html__( 'Code Fields (Tracking etc.)', 'Avada' ),
				'id'          => 'tracking_head_body_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'google_analytics' => array(
						'label'       => esc_html__( 'Tracking Code', 'Avada' ),
						'description' => esc_html__( 'Paste your tracking code here. This will be added into the header template of your theme. Place code inside &lt;script&gt; tags.', 'Avada' ),
						'id'          => 'google_analytics',
						'default'     => '',
						'type'        => 'code',
						'choices'     => array(
							'language' => 'html',
							'height'   => 300,
							'theme'    => 'chrome',
						),
					),
					'space_head' => array(
						'label'       => esc_html__( 'Space before &lt;/head&gt;', 'Avada' ),
						'description' => esc_html__( 'Only accepts javascript code wrapped with &lt;script&gt; tags and HTML markup that is valid inside the &lt;/head&gt; tag.', 'Avada' ),
						'id'          => 'space_head',
						'default'     => '',
						'type'        => 'code',
						'choices'     => array(
							'language' => 'html',
							'height'   => 350,
							'theme'    => 'chrome',
						),
					),
					'space_body' => array(
						'label'       => esc_html__( 'Space before &lt;/body&gt;', 'Avada' ),
						'description' => esc_html__( 'Only accepts javascript code, wrapped with &lt;script&gt; tags and valid HTML markup inside the &lt;/body&gt; tag.', 'Avada' ),
						'id'          => 'space_body',
						'default'     => '',
						'type'        => 'code',
						'choices'     => array(
							'language' => 'html',
							'height'   => 350,
							'theme'    => 'chrome',
						),
					),
				),
			),
			'theme_features_section' => array(
				'label'       => esc_html__( 'Theme Features', 'Avada' ),
				'id'          => 'theme_features_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'dependencies_status' => array(
						'label'       => esc_attr__( "Avada's Option Network Dependencies", 'Avada' ),
						'description' => esc_html__( "Avada's Option Network consists of Fusion Theme Options, Page Options, Builder options and each of them have dependent options ON by default. This means the only options you see are the only ones currently available for your selection. However, if you wish to disable this feature, simply turn this option off, and all dependencies will be disabled (requires save & refresh).", 'Avada' ),
						'id'          => 'dependencies_status',
						'default'     => '1',
						'type'        => 'switch',
					),
					'pw_jpeg_quality' => array(
						'label'       => esc_html__( 'WordPress jpeg Quality', 'Avada' ),
						/* translators: "Regenerate Thumbnails" plugin link. */
						'description' => sprintf( esc_html__( 'Controls the quality of the generated image sizes for every uploaded image. Ranges between 0 and 100 percent. Higher values lead to better image qualities but also higher file sizes. NOTE: After changing this value, please install and run the %s plugin once.', 'Avada' ), '<a target="_blank" href="' . admin_url( 'plugin-install.php?s=Regenerate+Thumbnails&tab=search&type=term' ) . '" title="' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '">' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '</a>' ),
						'id'          => 'pw_jpeg_quality',
						'default'     => '82',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '1',
							'max'  => '100',
							'step' => '1',
						),
					),
					'smooth_scrolling' => array(
						'label'       => esc_html__( 'Smooth Scrolling', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable smooth scrolling. This will replace default browser scrollbar with a dark scrollbar.', 'Avada' ),
						'id'          => 'smooth_scrolling',
						'default'     => '0',
						'type'        => 'switch',
					),
					'disable_code_block_encoding' => array(
						'label'       => esc_html__( 'Code Block Encoding', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable encoding in the Fusion Builder code block and syntax highlighting elements.', 'Avada' ),
						'id'          => 'disable_code_block_encoding',
						'default'     => '1',
						'type'        => 'switch',
					),
					'disable_megamenu' => array(
						'label'       => esc_html__( 'Mega Menu', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Avada\'s mega menu.', 'Avada' ),
						'id'          => 'disable_megamenu',
						'default'     => '1',
						'type'        => 'switch',
					),
					'avada_rev_styles' => array(
						'label'       => esc_html__( 'Avada Styles For Revolution Slider', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada styles and use the default Revolution Slider styles.', 'Avada' ),
						'id'          => 'avada_rev_styles',
						'default'     => '1',
						'type'        => 'switch',
					),
					'avada_styles_dropdowns' => array(
						'label'       => esc_html__( 'Avada Dropdown Styles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the Avada styles for dropdown/select fields site wide. This should be done if you experience any issues with 3rd party plugin dropdowns.', 'Avada' ),
						'id'          => 'avada_styles_dropdowns',
						'default'     => '1',
						'type'        => 'switch',
					),
					'use_animate_css' => array(
						'label'       => esc_html__( 'CSS Animations', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable CSS animations on elements.', 'Avada' ),
						'id'          => 'use_animate_css',
						'default'     => '1',
						'type'        => 'switch',
					),
					'disable_mobile_animate_css' => array(
						'label'       => esc_html__( 'CSS Animations on Mobiles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable CSS animations on mobiles.', 'Avada' ),
						'id'          => 'disable_mobile_animate_css',
						'default'     => '0',
						'type'        => 'switch',
					),
					'disable_mobile_image_hovers' => array(
						'label'       => esc_html__( 'CSS Image Hover Animations on Mobiles', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable CSS image hover animations on mobiles.', 'Avada' ),
						'id'          => 'disable_mobile_image_hovers',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_yt' => array(
						'label'       => esc_html__( 'Youtube API Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Youtube API scripts.', 'Avada' ),
						'id'          => 'status_yt',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_vimeo' => array(
						'label'       => esc_html__( 'Vimeo API Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable Vimeo API scripts.', 'Avada' ),
						'id'          => 'status_vimeo',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_gmap' => array(
						'label'       => esc_html__( 'Google Map Scripts', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable google map.', 'Avada' ),
						'id'          => 'status_gmap',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_totop' => array(
						'label'       => esc_html__( 'ToTop Script', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the ToTop script which adds the scrolling to top functionality.', 'Avada' ),
						'id'          => 'status_totop',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_totop_mobile' => array(
						'label'       => esc_html__( 'ToTop Script on mobile', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the ToTop script on mobile devices.', 'Avada' ),
						'id'          => 'status_totop_mobile',
						'default'     => '0',
						'type'        => 'switch',
					),
					'status_fusion_slider' => array(
						'label'       => esc_html__( 'Fusion Slider', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the fusion slider.', 'Avada' ),
						'id'          => 'status_fusion_slider',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_eslider' => array(
						'label'       => esc_html__( 'Elastic Slider', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable the elastic slider.', 'Avada' ),
						'id'          => 'status_eslider',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_fontawesome' => array(
						'label'       => esc_html__( 'FontAwesome', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable font awesome icons.', 'Avada' ),
						'id'          => 'status_fontawesome',
						'default'     => '1',
						'type'        => 'switch',
					),
					'status_opengraph' => array(
						'label'       => esc_html__( 'Open Graph Meta Tags', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable open graph meta tags which is mainly used when sharing pages on social networking sites like Facebook.', 'Avada' ),
						'id'          => 'status_opengraph',
						'default'     => '1',
						'type'        => 'switch',
					),
					'disable_date_rich_snippet_pages' => array(
						'label'       => esc_html__( 'Rich Snippets', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable rich snippets data site wide. If set to "On", you can also control individual items below. If set to "Off" all items will be disabled.', 'Avada' ),
						'id'          => 'disable_date_rich_snippet_pages',
						'default'     => '1',
						'type'        => 'switch',
					),
					'disable_rich_snippet_title' => array(
						'label'       => esc_html__( 'Rich Snippets Title', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable title rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_title',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'disable_rich_snippet_author' => array(
						'label'       => esc_html__( 'Rich Snippets Author Info', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable author rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_author',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'disable_rich_snippet_date' => array(
						'label'       => esc_html__( 'Rich Snippets Publish Date', 'Avada' ),
						'description' => esc_html__( 'Turn on to enable date rich snippet data site wide.', 'Avada' ),
						'id'          => 'disable_rich_snippet_date',
						'default'     => '1',
						'type'        => 'switch',
						'required'    => array(
							array(
								'setting'  => 'disable_date_rich_snippet_pages',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
			),
			'privacy' => array(
				'label'       => esc_html__( 'Privacy', 'Avada' ),
				'id'          => 'privacy_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'privacy_note' => array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> The options in this section will help to easier comply with data privacy regulations, like the European GDPR. When the "Embeds Privacy" option is used, Avada will create a cookie with the name <b>"privacy_embeds"</b> on user clients browsing your site to manage and store user consent to load the different third party embeds. You may want to add information about this cookie to your privacy page.', 'Avada' ) . '</div>',
						'id'          => 'privacy_note',
						'type'        => 'custom',
					),
					'gfonts_load_method' => array(
						'id'          => 'gfonts_load_method',
						'label'       => esc_html__( 'Google Fonts Mode', 'Avada' ),
						'description' => esc_html__( 'When set to "Local", the Google fonts set in Theme Options will be downloaded to your server. Set to "CDN" to use the Google CDN.', 'Avada' ),
						'type'        => 'radio-buttonset',
						'default'     => 'cdn',
						'choices'     => array(
							'local' => esc_attr__( 'Local', 'Avada' ),
							'cdn'   => esc_attr__( 'CDN', 'Avada' ),
						),
					),
					'privacy_embeds' => array(
						'label'       => esc_html__( 'Embeds Privacy', 'Avada' ),
						'description' => esc_html__( 'Turn on to prevent embeds from loading until user consent is given.', 'Avada' ),
						'id'          => 'privacy_embeds',
						'default'     => '0',
						'type'        => 'switch',
					),
					'privacy_expiry' => array(
						'label'       => esc_html__( 'Embeds Cookie Expiration', 'Avada' ),
						'description' => esc_html__( 'Controls how long the consent cookie should be stored for.  In days.', 'Avada' ),
						'id'          => 'privacy_expiry',
						'default'     => '30',
						'type'        => 'slider',
						'choices'     => array(
							'min'  => '1',
							'max'  => '366',
							'step' => '1',
						),
						'required'    => array(
							array(
								'setting'  => 'privacy_embeds',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'privacy_embed_types' => array(
						'label'       => esc_html__( 'Embed Types', 'Avada' ),
						'description' => esc_html__( 'Select the types of embeds which you would like to require consent.', 'Avada' ),
						'id'          => 'privacy_embed_types',
						'default'     => $embed_defaults,
						'type'        => 'select',
						'multi'       => true,
						'choices'     => $embed_types,
						'required'    => array(
							array(
								'setting'  => 'privacy_embeds',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'privacy_bg_color' => array(
						'label'       => esc_html__( 'Embed Placeholder Background Color', 'Avada' ),
						'description' => esc_html__( 'Controls the background color for the embed placeholders.', 'Avada' ),
						'id'          => 'privacy_bg_color',
						'type'        => 'color-alpha',
						'default'     => 'rgba(0,0,0,0.1)',
						'required'    => array(
							array(
								'setting'  => 'privacy_embeds',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					'privacy_color' => array(
						'label'       => esc_html__( 'Embed Placeholder Text Color', 'Avada' ),
						'description' => esc_html__( 'Controls the text color for the embed placeholders.', 'Avada' ),
						'id'          => 'privacy_color',
						'type'        => 'color-alpha',
						'default'     => 'rgba(0,0,0,0.3)',
						'required'    => array(
							array(
								'setting'  => 'privacy_embeds',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
			),
			'dynamic_compiler_section' => array(
				'label'       => esc_html__( 'Dynamic CSS & JS', 'Avada' ),
				'id'          => 'dynamic_compiler_section',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => array(
					'css_cache_method' => array(
						'label'       => esc_html__( 'CSS Compiling method', 'Avada' ),
						'description' => esc_html__( 'Select "File" mode to compile the dynamic CSS to files (a separate file will be created for each of your pages & posts inside of the uploads/fusion-styles folder), "Database" mode to cache the CSS in your database, or select "Disabled" to disable.', 'Avada' ),
						'id'          => 'css_cache_method',
						'default'     => 'file',
						'type'        => 'radio-buttonset',
						'choices'     => array(
							'file' => esc_attr__( 'File', 'Avada' ),
							'db'   => esc_attr__( 'Database', 'Avada' ),
							'off'  => esc_attr__( 'Disabled', 'Avada' ),
						),
					),
					'cache_server_ip' => array(
						'label'       => esc_html__( 'Cache Server IP', 'Avada' ),
						'description' => esc_html__( 'For unique cases where you are using cloud flare and a cache server, ex: varnish cache. Enter your cache server IP to clear the theme options dynamic CSS cache. Consult with your server admin for help.', 'Avada' ),
						'id'          => 'cache_server_ip',
						'default'     => '',
						'type'        => 'text',
					),
					'js_compiler_note' => ( apply_filters( 'fusion_compiler_js_file_is_readable', get_transient( 'fusion_dynamic_js_readable' ) ) ) ? array() : array(
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> JS Compiler is disabled. File does not exist or access is restricted.', 'Avada' ) . '</div>',
						'id'          => 'js_compiler_note',
						'type'        => 'custom',
					),
					'js_compiler' => array(
						'label'       => esc_html__( 'Enable JS Compiler', 'Avada' ),
						'description' => ( Fusion_Dynamic_JS::is_http2() ) ? esc_html__( 'We have detected that your server supports HTTP/2. We recommend you leave the compiler disabled as that will improve performance of your site by allowing multiple JS files to be downloaded simultaneously.', 'Avada' ) : esc_html__( 'By default all the javascript files are combined. Disabling the JS compiler will load non-combined javascript files. This will have an impact on the performance of your site.', 'Avada' ),
						'id'          => 'js_compiler',
						'default'     => ( Fusion_Dynamic_JS::is_http2() ) ? '0' : '1',
						'type'        => 'switch',
					),
					'reset_caches_button' => array(
						'label'       => esc_html__( 'Reset Fusion Caches', 'Avada' ),
						/* translators: %1$s: <code>uploads/fusion-styles</code>. %2$s: <code>uploads/fusion-scripts</code>. */
						'description' => ( is_multisite() && is_main_site() ) ? sprintf( esc_html__( 'Resets all Dynamic CSS & Dynamic JS, cleans-up the database and deletes the %1$s and %2$s folders. When resetting the caches on the main site of a multisite installation, caches for all sub-sites will be reset. IMPORTANT NOTE: On large multisite installations with a low PHP timeout setting, bulk-resetting the caches may timeout.', 'Avada' ), '<code>uploads/fusion-styles</code>', '<code>uploads/fusion-scripts</code>' ) : sprintf( esc_html__( 'Resets all Dynamic CSS & Dynamic JS, cleans-up the database and deletes the %1$s and %2$s folders.', 'Avada' ), '<code>uploads/fusion-styles</code>', '<code>uploads/fusion-scripts</code>' ),
						'id'          => 'reset_caches_button',
						'default'     => '',
						'type'        => 'raw',
						'content'     => '<a class="button button-secondary" href="#" onclick="fusionResetCaches(event);" target="_self" >' . esc_attr__( 'Reset Fusion Caches', 'Avada' ) . '</a><span class="spinner fusion-spinner"></span>',
						'full_width'  => false,
					),
				),
			),
		),
	);

	return $sections;

}
