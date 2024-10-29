<?php

function autonettv_settings_pages()
{
	add_menu_page(
		'AutoNetTV Relay API Plugin',
		'AutoNetTV',
		'manage_options',
		'autonettv-relay-api',
		'autonettv_relay_api_home',
		'dashicons-car',
		100
	);

    add_submenu_page(
	    'autonettv-relay-api',
	    'AutoNetTV Relay API Settings',
	    'Settings',
        'manage_options',
	    'autonettv-relay-api-settings',
	    'autonettv_relay_api_settings',
	    90
    );

	add_submenu_page(
		'autonettv-relay-api',
		'AutoNetTV Relay API Category Selections',
		'Category Selections',
		'manage_options',
		'autonettv-relay-api-category-selections',
		'autonettv_relay_api_category_selections',
		100
	);

//    add_submenu_page(
//	    'autonettv-relay-api',
//	    'AutoNetTV Relay API Categories',
//	    'Article Import',
//        'manage_options',
//	    'autonettv-relay-api-categories',
//	    'autonettv_relay_api_categories',
//	    92
//    );

	add_submenu_page(
		'autonettv-relay-api',
		'AutoNetTV Relay API Schedule',
		'Article Sync Schedule',
		'manage_options',
		'autonettv-relay-api-schedule-selections',
		'autonettv_relay_api_schedule_selections',
		91
	);

//	add_submenu_page(
//		'autonettv-relay-api',
//		'AutoNetTV Relay API Cron Test',
//		'Cron Test',
//		'manage_options',
//		'autonettv-relay-api-cron-test',
//		'autonettv_relay_api_cron_test',
//		90
//	);
}
add_action( 'admin_menu', 'autonettv_settings_pages' );

function autonettv_relay_api_home()
{
	// Double check user capabilities
	if ( !current_user_can('manage_options') ) {
		return;
	}
	?>
	<h1><?php echo esc_html_e( get_admin_page_title() ); ?></h1>

<?php
	include( AUTONETTV_RELAYAPI_PLUGIN_DIR . 'docs/antv-relay-api-disclaimer.phtml');
}
function autonettv_relay_api_settings()
{
	// Double check user capabilities
	if ( !current_user_can('manage_options') ) {
		return;
	}

	include( AUTONETTV_RELAYAPI_PLUGIN_DIR . 'templates/admin/settings-page.php');
}
//function autonettv_relay_api_categories()
//{
//	// Double check user capabilities
//	if ( !current_user_can('manage_options') ) {
//		return;
//	}
//
//	include( AUTONETTV_RELAYAPI_PLUGIN_DIR . 'includes/autonettv-relay-api-categories.php');
//}
function autonettv_relay_api_category_selections() {
	// Double check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	include( AUTONETTV_RELAYAPI_PLUGIN_DIR . 'templates/admin/settings-categories-page.php' );
}

function autonettv_relay_api_schedule_selections()
{
	// Double check user capabilities
	if ( !current_user_can('manage_options') ) {
		return;
	}

	include( AUTONETTV_RELAYAPI_PLUGIN_DIR . 'templates/admin/settings-schedule-page.php');
}

//function autonettv_relay_api_cron_test()
//{
//	// Double check user capabilities
//	if ( !current_user_can('manage_options') ) {
//		return;
//	}
//
//	include( AUTONETTV_RELAYAPI_PLUGIN_DIR . 'templates/admin/settings-cron-test.php');
//}