<?php
function autonettv_relay_api_settings_fields() {

	// If plugin settings don't exist, then create them
	if( false == get_option( 'autonettv_relay_api_settings_fields' ) ) {
		add_option( 'autonettv_relay_api_settings_fields' );
	}

	// Define (at least) one section for our fields
	add_settings_section(
	// Unique identifier for the section
		'autonettv_relay_api_settings_section',
		// Section Title
		__( 'Plugin Settings Section', 'autonettv-relay-api' ),
		// Callback for an optional description
		'autonettv_relay_api_settings_section_callback',
		// Admin page to add section to
		'autonettv-relay-api'
	);

	// Input Text Field
	add_settings_field(
	// Unique identifier for field
		'autonettv_relay_api_url',
		// Field Title
		__( 'Enter the full URL of your blog: ', 'autonettv-relay-api'),
		// Callback for field markup
		'autonettv_relay_api_settings_url_callback',
		// Page to go on
		'autonettv-relay-api',
		// Section to go in
		'autonettv_relay_api_settings_section'
	);

	// Input Text Field
	add_settings_field(
	// Unique identifier for field
		'autonettv_relay_api_key',
		// Field Title
		__( 'Enter the api key: ', 'autonettv-relay-api'),
		// Callback for field markup
		'autonettv_relay_api_settings_key_callback',
		// Page to go on
		'autonettv-relay-api',
		// Section to go in
		'autonettv_relay_api_settings_section'
	);

	register_setting(
		'autonettv_relay_api_settings_fields',
		'autonettv_relay_api_settings_fields'
	);

}
add_action( 'admin_init', 'autonettv_relay_api_settings_fields' );

function autonettv_relay_api_settings_section_callback() {

	esc_html_e( 'Fill out the following two fields to connect wordpress to your AutoNetTV articles.', 'autonettv-relay-api' );

}

function autonettv_relay_api_settings_url_callback() {

	$options = get_option( 'autonettv_relay_api_settings_fields' );

	$text_input = '';
	if( isset( $options[ 'url' ] ) ) {
		$text_input = esc_html( $options['url'] );
	}

	echo '<input type="text" style="width:300px;" id="autonettv_relay_api_url" name="autonettv_relay_api_settings_fields[url]" value="' . $text_input . '" placeholder="yourdomain.autotipsblog.com" />';

}
function autonettv_relay_api_settings_key_callback() {

	$options = get_option( 'autonettv_relay_api_settings_fields' );

	$text_input = '';
	if( isset( $options[ 'key' ] ) ) {
		$text_input = esc_html( $options['key'] );
	}

	echo '<input type="text" style="width:300px;" id="autonettv_relay_api_key" name="autonettv_relay_api_settings_fields[key]" value="' . $text_input . '" placeholder="ijsdnhfiusndf78sd7fh8" />';

}
