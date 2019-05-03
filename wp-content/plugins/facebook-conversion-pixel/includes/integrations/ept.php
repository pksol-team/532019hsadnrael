<?php

function fca_pc_ept_events() {
	wp_localize_script( 'fca_pc_client_js', 'fcaPcEptEnabled', array('true') );
}
add_action( 'fca_pc_after_role_check_head', 'fca_pc_ept_events' );