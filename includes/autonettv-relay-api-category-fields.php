<?php
function autonettv_relay_api_settings_categories_fields() {

	// If plugin settings don't exist, then create them
	if( false == get_option( 'autonettv_relay_api_settings_categories_fields' ) ) {
		add_option( 'autonettv_relay_api_settings_categories_fields' );
	}

	// Define (at least) one section for our fields
	add_settings_section(
	// Unique identifier for the section
		'autonettv_relay_api_settings_categories_section',
		// Section Title
		__( 'Category Selections', 'autonettv-relay-api-category-selections' ),
		// Callback for an optional description
		'autonettv_relay_api_settings_categories_fields_callback',
		// Admin page to add section to
		'autonettv-relay-api-category-selections'
	);

	add_settings_field(
		'autonettv_relay_api_settings_categories_selections',
		__( 'Selected Categories', 'autonettv-relay-api-category-selections'),
		'autonettv_relay_api_settings_categories_callback',
		'autonettv-relay-api-category-selections',
		'autonettv_relay_api_settings_categories_section',
		[
			'1' => 'here',
			'2' => 'here2',
			'3' => 'here3',
		]
	);

	register_setting(
		'autonettv_relay_api_settings_categories_fields',
		'autonettv_relay_api_settings_categories_fields'
	);

}
add_action( 'admin_init', 'autonettv_relay_api_settings_categories_fields' );

function autonettv_relay_api_settings_categories_fields_callback() {

	esc_html_e( 'Select the categories you want to show up on your website. After you select the categories, you can click on Categories in the menu to import the articles from each category individually.', 'autonettv-relay-api-category-selections' );

}

function autonettv_relay_api_settings_categories_callback() {
	$api = get_option('autonettv_relay_api_settings_fields');
	$fileCategories = AUTONETTV_RELAYAPI_ENDPOINT . $api['url'] . "/" . $api['key'] . "/category";
	//echo $fileCategories;
	$infoCategories = wp_remote_get( $fileCategories );
	$categories     = json_decode( wp_remote_retrieve_body( $infoCategories ) );
//	print_r($categories);
//	echo "<P></P>";

	$selectedCategories = get_option( 'autonettv_relay_api_settings_categories_fields' );
//	print_r($selectedCategories);

	echo "<input type='button' value='Select All' onClick='autonettv_checkbox_toggle(this)'>";

	foreach($categories as $category) {
		$html = '';
//		$html .= 'category_id: ' . $selectedCategories[$category->category_id];
		$html .= '<div><input type="checkbox" id="autonettv_relay_api_settings_categories_selections" name="autonettv_relay_api_settings_categories_fields[' . $category->category_id . ']" value="1" ';
		if( isset( $selectedCategories[$category->category_id] ) ) {
				$html .= ' checked ';
		}
		$html .= '/>';
		$html .= '&nbsp;';
		$html .= '<label for="autonettv_relay_api_settings_categories_selections">' . $category->category_name . '</label> ';
		$html .= '</div>';

		echo $html;
	}
}

