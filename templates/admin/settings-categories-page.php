<div class="wrap">

	<h1><?php esc_html_e( get_admin_page_title() ); ?></h1>
    <?php

    if ( !category_exists(AUTONETTV_RELAYAPI_PARENT_NAME) ) {
	    wp_create_category(AUTONETTV_RELAYAPI_PARENT_NAME);
    }
    $idObj = get_category_by_slug( AUTONETTV_RELAYAPI_PARENT_SLUG );

    if ( $idObj instanceof WP_Term ) {
	    $parentCategory = $idObj->term_id;
    }

    $api = get_option('autonettv_relay_api_settings_fields');

    if(isset($api)) {
	    $fileCategories = AUTONETTV_RELAYAPI_ENDPOINT . $api['url'] . "/" . $api['key'] . "/category";
	    //echo $fileCategories;
	    $infoCategories = wp_remote_get( $fileCategories );
	    $categories     = json_decode( wp_remote_retrieve_body( $infoCategories ) );

        if ( ! get_option( 'autonettv_relay_api_categories' ) ) {
		    add_option( 'autonettv_relay_api_categories', $categories );
	    } else {
		    update_option( 'autonettv_relay_api_categories', $categories );
	    }

        if ( ! get_option( 'autonettv_relay_api_settings_categories_fields' ) ) {
		    add_option( 'autonettv_relay_api_settings_categories_fields', $categories );
	    }

	    $savedCategories = get_option('autonettv_relay_api_settings_categories_fields');
    }
    ?>

    <form method="post" action="options.php" id="category_selections">
        <!-- Display necessary hidden fields for settings -->
		<?php settings_fields( 'autonettv_relay_api_settings_categories_fields' ); ?>

        <!-- Display the settings sections for the page -->
		<?php do_settings_sections( 'autonettv-relay-api-category-selections' ); ?>

        <!-- Default Submit Button -->
		<?php submit_button(); ?>
    </form>
