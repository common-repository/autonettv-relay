<?php

function autonettv_relay_api_settings_schedule_fields() {

	// If plugin settings don't exist, then create them
	if( false == get_option( 'autonettv_relay_api_settings_schedule_fields' ) ) {
		add_option( 'autonettv_relay_api_settings_schedule_fields' );
	}

	add_settings_section(
		'autonettv_relay_api_settings_schedule_fields_section',
		'Schedule',
		'autonettv_relay_api_settings_schedule_fields_section_callback',
		'autonettv-relay-api-schedule-selections'
	);

	add_settings_field(
		'autonettv_relay_api_start_date',
		'Start Date',
		'autonettv_relay_api_settings_schedule_start_date',
		'autonettv-relay-api-schedule-selections',
		'autonettv_relay_api_settings_schedule_fields_section'
	);

	add_settings_field(
		'autonettv_relay_api_end_date',
		'End Date',
		'autonettv_relay_api_settings_schedule_end_date',
		'autonettv-relay-api-schedule-selections',
		'autonettv_relay_api_settings_schedule_fields_section'
	);

	add_settings_field(
		'autonettv_relay_api_frequency',
		'Sync Frequency',
		'autonettv_relay_api_settings_schedule_frequency',
		'autonettv-relay-api-schedule-selections',
		'autonettv_relay_api_settings_schedule_fields_section',
		[
			'five_minutes'  => 'Every Five Minutes',
			'hourly'        => 'Once Hourly',
			'twicedaily'    => 'Twice Daily' ,
			'daily'         => 'Once Daily' ,
			'weekly'        => 'Once Weekly'
		]
	);

	register_setting(
		'autonettv_relay_api_settings_schedule_fields',
		'autonettv_relay_api_settings_schedule_fields'
	);

}
add_action( 'admin_init', 'autonettv_relay_api_settings_schedule_fields' );

add_filter( 'cron_schedules', 'autonettv_add_cron_interval' );
function autonettv_add_cron_interval( $schedules ) {
	$schedules['five_minutes'] = array(
		'interval' => 300,
		'display'  => esc_html__( 'Every Five Minutes' ), );
	return $schedules;
}

function autonettv_relay_api_settings_schedule_fields_section_callback() {
	echo "<h4>Based off your settings below, your posts will be updated accordingly.</h4>
	<p>Remember, you shouldn't update any of your articles in WordPress but in your admin area of the Relay API
	as if you modify them here, they will be overwritten when the articles sync runs.</p>
	<p>Updating this setting will also run sync when you click save.</p>";
}

function autonettv_relay_api_settings_schedule_start_date() {

	$schedule = get_option( 'autonettv_relay_api_settings_schedule_fields' );

	if(!isset($schedule['start_date'])) {
		$start_date = wp_date('Y-m-d',strtotime('2015-01-01'));
	} else {
		$start_date = $schedule['start_date'];
	}

	echo '<input type="text" id="autonettv_relay_api_settings_schedule_fields" name="autonettv_relay_api_settings_schedule_fields[start_date]" value="' . $start_date . '">';

}

function autonettv_relay_api_settings_schedule_end_date() {

	$schedule = get_option( 'autonettv_relay_api_settings_schedule_fields' );

	if(!isset($schedule['end_date'])) {
		$end_date = wp_date( 'Y-m-d' );
	} else {
		$end_date = $schedule['end_date'];
	}

	echo '<input type="text" id="autonettv_relay_api_settings_schedule_fields" name="autonettv_relay_api_settings_schedule_fields[end_date]" value="' . $end_date . '">
These dates don\'t matter unless you want to set a start and end date, the plugin will grab all your posts';

}

function autonettv_relay_api_settings_schedule_frequency( $args ) {

	$schedule = get_option( 'autonettv_relay_api_settings_schedule_fields' );

	$select = '';
	if( isset( $schedule[ 'frequency' ] ) ) {
		$select = esc_html( $schedule['frequency'] );
	} else {
		$select = "weekly";
	}

	$html = '<select id="autonettv_relay_api_frequency" name="autonettv_relay_api_settings_schedule_fields[frequency]">';

		//$html .= '<option value="five_minutes"' . selected( $select, 'five_minutes', false) . '>' . $args['five_minutes'] . '</option>';
		$html .= '<option value="hourly"' . selected( $select, 'hourly', false) . '>' . $args['hourly'] . '</option>';
		$html .= '<option value="twicedaily"' . selected( $select, 'twicedaily', false) . '>' . $args['twicedaily'] . '</option>';
		$html .= '<option value="daily"' . selected( $select, 'daily', false) . '>' . $args['daily'] . '</option>';
		$html .= '<option value="weekly"' . selected( $select, 'weekly', false) . '>' . $args['weekly'] . '</option>';

	$html .= '</select>';

	echo $html;

}

if (!function_exists('write_log')) {

	function write_log($log) {
		if (true === WP_DEBUG) {
			if (is_array($log) || is_object($log)) {
				error_log(print_r($log, true));
			} else {
				error_log($log);
			}
		}
	}

}



$schedule = get_option( 'autonettv_relay_api_settings_schedule_fields' );
function autonettv_relay_api_events_callback() {
	global $schedule;
	$parentCategory = AUTONETTV_RELAYAPI_PARENT_CATEGORY;

	$options = get_option('autonettv_relay_api_settings_fields');
//	write_log( 'options: ' .  print_r( $options ) );

	$selectedCategories = get_option( 'autonettv_relay_api_settings_categories_fields' );

	$fileCategories = AUTONETTV_RELAYAPI_ENDPOINT . $options['url'] . "/" . $options['key'] . "/category";
//	write_log( 'fileCategories: ' .  $fileCategories );

	$infoCategories = wp_remote_get( $fileCategories );
	$categories     = json_decode(wp_remote_retrieve_body( $infoCategories ));
	if( !get_option('autonettv_relay_api_categories')) {
		add_option('autonettv_relay_api_categories',$categories);
	} else {
		update_option('autonettv_relay_api_categories',$categories);
	}

	$totalCount     = 0;
	$categoryCount = 0;
	$postCount = wp_count_posts( $type = 'post' );
	write_log("Pre Post Count: " . $postCount->publish);
	$posts = 0;

	if(isset($categories) && !empty($selectedCategories)) {
		foreach ( $categories as $category ) {
//			write_log("category_name: " . $category->category_name . " parentCategory: " . $parentCategory);
			if ( isset( $selectedCategories[ $category->category_id ] ) ) {
				write_log("ADD category_name: " . $category->category_name . " parentCategory: " . $parentCategory);
				write_log("category_exists: " . category_exists( $category->category_name, $parentCategory ));
				if ( ! category_exists( $category->category_name, $parentCategory ) ) {
					wp_create_category( $category->category_name, $parentCategory );
				}
				$idObj = get_category_by_slug( $category->category_name );

				if ( $idObj instanceof WP_Term ) {
					$wp_category_id = $idObj->term_id;
					write_log("RUNNING: autonettv_relay_api_events_posts_callback() for Category " . $category->category_name);
					$posts += autonettv_relay_api_events_posts_callback($options, $parentCategory, $category->category_id, $wp_category_id);
				}

			} else {
				// remove category if not in selected categories
				$idObj = get_category_by_slug( $category->category_name );

				if ( $idObj instanceof WP_Term ) {
					$wp_category_id = $idObj->term_id;
				}
				wp_delete_category( $wp_category_id );
			}
		}

		$endingPostCount = $postCount = wp_count_posts( $type = 'post' );
		$wpPostDiff = $endingPostCount->publish-$postCount->publish;

		write_log("Initial Post Count: " . $postCount->publish . " Posts Added: " . $posts);
		write_log("Total Ending Post Count: " . $endingPostCount->publish . " Difference: " . $wpPostDiff);

		$blogname = get_option('blogname');
		$to = get_option('admin_email');
		$subject = "AutoNetTV Post(s) have Updated on " . $blogname;
		$body = "<h1>" . $blogname . " has new Articles!</h1>
		<h3>Your new post count in WordPress is " . $endingPostCount->publish . ".</h3>
        <h3>" . $postCount->publish-$posts . " posts were updated and " . $posts . " posts were added.</h3>";
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$mailSend = wp_mail( $to, $subject, $body, $headers );
	}

	write_log($fileCategories);

	return $totalCount;
}

add_action('autonettv_relay_api_events_hook','autonettv_relay_api_events_callback');

function autonettv_relay_api_events_posts_callback($options, $parentCategory, $relay_category_id, $wp_category_id ) {
	global $wpdb;

	if ( ! is_admin() ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-community-events.php';
		require_once( ABSPATH . 'wp-admin/includes/post.php' );
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
	}
	//Add in the pluggable functions
	include_once( ABSPATH . WPINC . '/pluggable.php' );
	wp_set_auth_cookie( 1 );
	$wpPostDiff = 0;

//	$user = wp_get_current_user();
//	if($user->ID <= 0) { $currentUser = 1; } else { $currentUser = $user->ID; }
//	write_log("Current User: " . $currentUser);
	$currentUser = 1;

	if( isset( $wp_category_id ) ) {
		if($wp_category_id > 0) {
			$filePosts = AUTONETTV_RELAYAPI_ENDPOINT . $options['url'] . "/" . $options['key'] . "/posts?category_id=" . $relay_category_id . "&limit=1";
			write_log($filePosts);
			$infoPosts = wp_remote_get($filePosts);
			$posts = wp_remote_retrieve_body($infoPosts);
			$wpPosts = wp_count_posts( $type = 'post' );

			//echo "<h3>Your current post count in WordPress is " . $wpPosts->publish . "</h3>";

			if(isset($posts)) {
				//print_r($posts);
				$posts_decoded = json_decode($posts);
				if(is_array($posts_decoded)) {
					$lineNo = 0;
					//echo "<h3>Inserting/Updating Posts from API in Category ID " . $category_id . "</h3>";
					foreach($posts_decoded as $post) {
						$lineNo++;
						write_log("--------------------NEW RECORD-----------------");

						//check to see if post exists
						$args = array(
							'author' => 1,
							'cat' => array( $wp_category_id ),
							'post_type' => 'post',
							'p' => $post->post_id
						);
						$query = new WP_Query( $args );

						write_log("Query post_count: " . $query->post_count);

						$post_data = array(
							//'ID' => $post->post_id,
							'post_title' => $post->title,
							'post_content' => $post->content,
							'post_date' => $post->post_date,
							'import_id' => $post->post_id,
							'tags_input' => $post->category_name,
							'post_status' => 'publish', // Automatically publish the post.
							'post_author' => 1,
							'post_category' => array( $wp_category_id ),
							'post_type' => 'post' // defaults to "post". Can be set to CPTs.
						);

						write_log($post_data);

						$postExists = post_exists($post_data['post_title'],'', $post_data['post_date'],$post_data['post_type'],'publish');
						$postExistsByID = is_string( get_post_status( $post->post_id ) );
						if($postExistsByID > 0) { $insertPost = true; } else { $insertPost = false; }
						write_log("insertPost: " . $insertPost);
						write_log("postExistsByID: " . $postExistsByID . " post->post_id: " . $post->post_id);

						write_log("Post Exists: " . $postExists);
						remove_all_filters("content_save_pre");

						if ($query->post_count <= 0) {
							// Let's insert the post now.
							$insertedPost = wp_insert_post( $post_data, '', 'false' );
							$postURL = get_permalink($insertedPost);
							write_log('URL: ' . $postURL);
							//echo $lineNo . ") <span style='color:#1b8b1b;font-weight:bold;'>Inserted New Post: " . $post->title . "</span>";
							write_log("Inserted new post: " . $post->title);
						} else {
							$post_data['ID'] = intval($post->post_id);
							$postURL = get_permalink($post->post_id);
							// Let's update the post now.
							$insertedPost = wp_update_post( $post_data, '', 'false');
							//echo $lineNo . ") <span style='color:#b01616;font-weight:bold;'>Updated Existing Post from RelayAPI: " . $post->title . "</span>";
							write_log('URL: ' . $postURL);
							write_log("Updated existing post: " . $post->title);
						}

						if(isset($post->thumb_url)) {
							if($post->thumb_url != "") {
								write_log('Thumbnail: ' . $post->thumb_url);
								write_log('PostID: ' . $insertedPost);
								$imagesize = getimagesize($post->thumb_url);
								write_log($imagesize);

								$image = media_sideload_image( $post->thumb_url, $post->post_id, $post->title,'id' );
								set_post_thumbnail( $post->post_id, $image );
							}
						}

						// Do raw query. wp_get_post_revisions() is filtered.
						$revision_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = %s" , intval($post->post_id) , 'revision') );

						if(isset($revision_ids)) {
							write_log($revision_ids);

							// Use wp_delete_post (via wp_delete_post_revision) again. Ensures any meta/misplaced data gets cleaned up.
							foreach ( $revision_ids as $revision_id ) {
								wp_delete_post_revision( $revision_id );
							}
						}

						if(is_wp_error($insertedPost)){
							write_log("wp error: " . $insertedPost);
						}

					}

					$wpPostsNew = wp_count_posts( $type = 'post' );

					$wpPostDiff = $wpPostsNew->publish - $wpPosts->publish;
					if($lineNo > $wpPostDiff) {
						write_log( "New: " . $wpPostsNew->publish . "  - Returned: " . $wpPosts->publish);
						write_log( "One or more posts were not able to be imported, this can be caused by elements of the content to break the import.");
					}



				} else {
					//echo "For some reason there is an error in your posts, there could be a character not allowed or something else.";
				}
			} else {
				//echo "No Posts Returned for This Category";
			}
		}
	}
	return $wpPostDiff;
}