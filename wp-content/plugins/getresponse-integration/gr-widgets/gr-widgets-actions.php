<?php

use Getresponse\WordPress\GrWidget;

defined( 'ABSPATH' ) || exit;

add_action( 'widgets_init', 'gr_register_widgets');
add_action( 'customize_controls_enqueue_scripts', 'gr_customizer_scripts' );

function gr_register_widgets() {
	register_widget(GrWidget::class);

	wp_register_style( 'GrStyle', gr()->asset_path . '/css/getresponse-integration.css' );
	wp_register_style( 'GrCustomsStyle', gr()->asset_path . '/css/getresponse-custom-field.css' );
	wp_register_script( 'GrCustomsJs', gr()->asset_path . '/js/getresponse-custom-field.src-verified.js' );
}

function gr_customizer_scripts() {
	wp_enqueue_script( 'GrCustomizerScript' );
}
