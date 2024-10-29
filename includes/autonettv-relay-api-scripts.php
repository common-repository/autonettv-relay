<?php
// Load JS on all admin pages
function autonettv_relay_api_admin_scripts( $hook ) {

	wp_register_script(
		'autonettv-relay-api-admin',
		AUTONETTV_RELAYAPI_PLUGIN_URL . 'admin/js/autonettv-relay-api-admin.js',
		['jquery'],
		time()
	);

	$screen = get_current_screen();

	if( $screen->id == 'autonettv_page_autonettv-relay-api-category-selections' ) {
		wp_enqueue_script( 'autonettv-relay-api-admin' );
	}

}
add_action( 'admin_enqueue_scripts', 'autonettv_relay_api_admin_scripts', 100 );