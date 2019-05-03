<?php

function fca_pc_optincat_events() {
	wp_localize_script( 'fca_pc_client_js', 'fcaPcOptinCatEnabled', array('true') );
}
add_action( 'fca_pc_after_role_check_head', 'fca_pc_optincat_events' );