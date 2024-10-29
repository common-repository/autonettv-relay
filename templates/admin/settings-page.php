<div class="wrap">

	<h1><?php esc_html_e( get_admin_page_title() ); ?></h1>
	<?php
	$options = get_option('autonettv_relay_api_settings_fields');

    if(isset($options)) {
	    echo "<strong>BLOG URL:</strong> ";
        if(isset($options['url'])) {
	        echo "<a href='https://" . $options['url'] . "' target='_blank'>" . $options['url'] . "</a>";

        } else {
            echo "No URL has been saved";
        }
        echo "<br>";

	    echo "<strong>API KEY:</strong> ";
	    if(isset($options['key'])) {
		    echo $options['key'];

	    } else {
		    echo "No API KEY has been saved";
	    }
	    echo "<br>";
    }
	?>
	<form method="post" action="options.php">
    <!-- Display necessary hidden fields for settings -->
    <?php settings_fields( 'autonettv_relay_api_settings_fields' ); ?>

    <!-- Display the settings sections for the page -->
    <?php do_settings_sections( 'autonettv-relay-api' ); ?>
    <!-- Default Submit Button -->
    <?php submit_button(); ?>
  </form>

    <?php
    if( isset( $options['url'] ) && isset( $options['key'] ) && trim($options['url']) != "" && trim($options['key']) != "" ) {
	    $fileCategories = AUTONETTV_RELAYAPI_ENDPOINT . $options['url'] . "/" . $options['key'] . "/category";
//        echo $fileCategories . "<br>";
	    $infoCategories = wp_remote_get($fileCategories);
	    $categories     = json_decode(wp_remote_retrieve_body( $infoCategories ));

        if( isset($categories) ) {
            echo "<p><strong>" . count($categories) . " Categories are available!</strong> Click Category Selections in the menu to select the categories you want to be imported.</p>";
        } else {
            echo "No Categories Found";
        }

//        echo "<ul>";
//        foreach(json_decode($categories) as $category) {
//            echo "<li>" . $category->category_name . " (" . $category->post_count . ")</li>";
//        }
//	    echo "</ul>";
//
	    $filePosts = AUTONETTV_RELAYAPI_ENDPOINT . $options['url'] . "/" . $options['key'] . "/posts?limit=2";
	    $infoPosts = wp_remote_get($filePosts);
	    $posts     = json_decode(wp_remote_retrieve_body( $infoPosts ));
	    if( isset($filePosts) ) {
		    echo "<p>Posts are available! Click on Categories to import them by category</p>";
	    } else {
		    echo "No Posts Found";
	    }

//	    echo $filePosts . "<br>";
//	    $infoPosts = wp_remote_get($filePosts);
//	    $posts = wp_remote_retrieve_body($infoPosts);
//
//	    echo "<ul>";
//	    foreach(json_decode($posts) as $post) {
//		    echo "<li>" . $post->post_date . ": " . $post->title . "</li>";
//	    }
//	    echo "</ul>";
    }
    ?>

</div>

<div>
    <?php
    echo get_option('autonettv-relay-api-categories');
    ?>
</div>