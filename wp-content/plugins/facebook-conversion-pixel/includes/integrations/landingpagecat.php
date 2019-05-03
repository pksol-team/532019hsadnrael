<?php

function fca_pc_landingpagecat_events() {
	wp_localize_script( 'fca_pc_client_js', 'fcaPcLandingPageCatEnabled', array('true') );
}
add_action( 'fca_pc_after_role_check_head', 'fca_pc_landingpagecat_events' );