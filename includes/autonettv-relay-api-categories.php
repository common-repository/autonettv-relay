<h1><?php echo esc_html_e( get_admin_page_title() ); ?></h1>
<?php
//echo "<br>" . get_plugin_page_hook( 'autonettv-relay-api-categories', 'autonettv-relay-api' ) . "<br>";
//echo "<br>" . get_admin_url( 'autonettv-relay-api-categories', 'autonettv-relay-api' ) . "<br>";
//echo "<br>" . self_admin_url( 'admin.php?page=autonettv-relay-api-categories', 'admin' ) . "<br>";

if ( !category_exists(AUTONETTV_RELAYAPI_PARENT_NAME) ) {
	wp_create_category(AUTONETTV_RELAYAPI_PARENT_NAME);
}
$idObj = get_category_by_slug( AUTONETTV_RELAYAPI_PARENT_SLUG );

if ( $idObj instanceof WP_Term ) {
	$parentCategory = $idObj->term_id;
}

$options = get_option('autonettv_relay_api_settings_fields');
$selectedCategories = get_option( 'autonettv_relay_api_settings_categories_fields' );
//print_r($options);
echo '<h2>This information is coming straight from the Relay API Endpoint</h2>
<h3>Click on a category to import the articles in that category. If you do not want a category on your website do not click it. To remove the articles you will need to remove them manually through the Posts area of WordPress.</h3>
<h4>If you have already imported articles from the selected category, if you click it again it will update the articles you have saved in wordpress from the content saved in the Relay API</h4>';

$autonetrelayapi_proceed = 0;
if(isset( $options['url'] )) {
	if(trim($options['url']) == "") {
		echo "<P style='color:red;font-weight:bold;'>The URL is empty or missing, click <a href='" . self_admin_url( 'admin.php?page=autonettv-relay-api-settings', 'admin' ) . "'>Settings</a> under AutoNetTV to fix.</P>";
		die;
	}
}
if(isset( $options['key'] )) {
	if(trim($options['key']) == "") {
		echo "<P style='color:red;font-weight:bold;'>The API is empty or missing, click <a href='" . self_admin_url( 'admin.php?page=autonettv-relay-api-settings', 'admin' ) . "'>Settings</a> under AutoNetTV to fix.</P>";
		die;
	}
}

echo '<div style="width:300px;float:left;padding:5px;margin-top:20px;" class="content">';

    $fileCategories = AUTONETTV_RELAYAPI_ENDPOINT . $options['url'] . "/" . $options['key'] . "/category";
    //echo $fileCategories;
    $infoCategories = wp_remote_get( $fileCategories );
    $categories     = json_decode(wp_remote_retrieve_body( $infoCategories ));
    if( !get_option('autonettv_relay_api_categories')) {
        add_option('autonettv_relay_api_categories',$categories);
    } else {
        update_option('autonettv_relay_api_categories',$categories);
    }

    $totalCount     = 0;
    $categoryCount = 0;
    if(isset($categories) && !empty($selectedCategories)) {
        foreach ( $categories as $category ) {
            if( isset( $selectedCategories[$category->category_id] ) ) {
	            if ( ! category_exists( $category->category_name, $parentCategory ) ) {
		            wp_create_category( $category->category_name, $parentCategory );
	            }
                $categoryCount++;
	            $totalCount = $totalCount + $category->post_count;
	            echo "<div class='text'>
            <a style='text-decoration:none;' title='Click here to import all " . $category->category_name . " posts' href='" . self_admin_url( 'admin.php?page=autonettv-relay-api-categories', 'admin' ) . "&category_id=" . $category->category_id . "'>";
	            if ( isset( $_REQUEST['category_id'] ) ) {
		            if ( $_REQUEST['category_id'] == $category->category_id ) {
			            echo "<span style='font-weight:bold;font-size:20px;color:orangered;text-decoration:none;'>>>";
		            }
	            }
	            echo $category->category_name;
	            if ( isset( $_REQUEST['category_id'] ) ) {
		            if ( $_REQUEST['category_id'] == $category->category_id ) {
			            echo "</span>";
		            }
	            }
	            echo "</a> (" . $category->post_count . ")</div>";
            }
        }
    } else {
        echo "<span style='color:red;'>No Categories Found or you have not selected any in Category Selections</span>";
        $totalCount = 0;
    }

    if( !empty( $selectedCategories ) ) {
        echo '<div>
            <p>You have ' . $totalCount . ' Posts in your selected ' . $categoryCount . ' categories.</p>
            <p>You have ' . count($categories)-$categoryCount . ' more categories you can add in Category Selections.</p>
        </div>';
    }

echo '</div>
<div style="float:left;width:auto;padding:5px;padding-top:20px;margin-top:20px;">';
if( isset( $_REQUEST['category_id'] ) ) {
    if($_REQUEST['category_id'] > 0) {
        $filePosts = AUTONETTV_RELAYAPI_ENDPOINT . $options['url'] . "/" . $options['key'] . "/posts?category_id=" . $_REQUEST['category_id'] . "&limit=250";
        //echo $filePosts . "<br>";
        $infoPosts = wp_remote_get($filePosts);
        $posts = wp_remote_retrieve_body($infoPosts);
        $wpPosts = wp_count_posts( $type = 'post' );

        echo "<h3>Your current post count in WordPress is " . $wpPosts->publish . "</h3>";

        if(isset($posts)) {
            //print_r($posts);
            $posts_decoded = json_decode($posts);
            if(is_array($posts_decoded)) {
                $lineNo = 0;
                echo "<h3>Inserting/Updating Posts from API in Category ID " . $_REQUEST['category_id'] . "</h3>";
                foreach($posts_decoded as $post) {
                    $lineNo++;
                    if ( !category_exists($post->category_name) ) {
                        wp_create_category($post->category_name);
                    }
                    $idObj = get_category_by_slug( $post->category_name );

                    if ( $idObj instanceof WP_Term ) {
                        $category_id = $idObj->term_id;
                    }

                    $post_data = array(
                            //'ID' => $post->post_id,
                            'post_title' => $post->title,
                            'post_content' => $post->content,
                            'post_date' => $post->post_date,
                            'import_id' => $post->post_id,
                            'tags_input' => $post->category_name,
                            'post_status' => 'publish', // Automatically publish the post.
                            'post_author' => get_current_user_id(),
                            'post_category' => array( $category_id ),
                            'post_type' => 'post' // defaults to "post". Can be set to CPTs.
                        );
                    $postExists = post_exists($post->title);

                    if ($postExists <= 0) {
                        // Let's insert the post now.
                        $insertedPost = wp_insert_post( $post_data );
	                    $postURL = get_permalink($insertedPost);
	                    echo $lineNo . ") <span style='color:#1b8b1b;font-weight:bold;'>Inserted New Post: " . $post->title . "</span>";
                    } else {
                        $post_data['ID'] = $post->post_id;
	                    $postURL = get_permalink($post->post_id);
	                    // Let's update the post now.
                        $insertedPost = wp_update_post( $post_data,);
	                    echo $lineNo . ") <span style='color:#b01616;font-weight:bold;'>Updated Existing Post from RelayAPI: " . $post->title . "</span>";

                    }

                    echo " <a href='" . $postURL . "' target='_blank'>View</a> <small>post id: " . $insertedPost . "</small><br>";

                    //echo $insertedPost;

                    if(is_wp_error($insertedPost)){
                        echo "Failed to insert post. Wordpress error(s)";
                    }


//                    echo "<div class='text'>-- " . $insertedPost . " -- " . $post->post_date . " " . $post->title . "</div>";
                }

	            $wpPostsNew = wp_count_posts( $type = 'post' );

                $wpPostDiff = $wpPostsNew->publish - $wpPosts->publish;
                if($lineNo > $wpPostDiff) {
                    //echo "New: " . $wpPostsNew->publish . "  - Returned: " . $wpPosts->publish;
                    //echo "<span style='color:#b01616;font-weight:bold;'>One or more posts were not able to be imported, this can be caused by elements of the content to break the import.</span>";
                }

                echo "<h3>Your new post count in WordPress is " . $wpPostsNew->publish . ".</h3>
                <h3>" . $lineNo . " posts were processed and " . $wpPostDiff . " new posts were added.</h3>";
            } else {
                echo "For some reason there is an error in your posts, there could be a character not allowed or something else.";
            }
        } else {
            echo "No Posts Returned for This Category";
        }
    }
}
echo '</div>';
