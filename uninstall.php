<?php
/**
 * Uninstall all AutoNetTV Media, Inc. Posts and DB Entries.
 *
 * @since 3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

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

write_log("------------------- BEGINNING AUTONETTV DELETION ------------- ");

$endpoint = "http://relayapi.autonettv.com/antv/v1/";

$idObj = get_category_by_slug( 'car-care' );

if ( $idObj instanceof WP_Term ) {

	$parentCategory = $idObj->term_id;
}
//write_log("parentCategory: " . $parentCategory);

$options = get_option('autonettv_relay_api_settings_fields');
//write_log($options);

$fileCategories = $endpoint . $options['url'] . "/" . $options['key'] . "/category";
$infoCategories = wp_remote_get( $fileCategories );
$categories     = json_decode(wp_remote_retrieve_body( $infoCategories ));
//write_log($categories);

if(isset($categories)) {
	foreach ( $categories as $category ) {
		if ( category_exists( $category->category_name, $parentCategory ) ) {
			$idObj = get_category_by_slug( $category->category_name );

			if ( $idObj instanceof WP_Term ) {

				$wp_category_id = $idObj->term_id;

				$filePosts = $endpoint . $options['url'] . "/" . $options['key'] . "/posts?category_id=" . $category->category_id . "&limit=250";
				//write_log( $filePosts );
				$infoPosts = wp_remote_get( $filePosts );
				$posts     = wp_remote_retrieve_body( $infoPosts );
				//write_log("wp_remote_retrieve_body()");
				//write_log($posts);
				$wpPosts   = wp_count_posts( $type = 'post' );

				if(isset($posts)) {
					//print_r($posts);
					$posts_decoded = json_decode($posts);
					if(is_array($posts_decoded)) {
						foreach ( $posts_decoded as $post ) {

//							$myplugin_post_args = array('posts_per_page' => -1);
//							$myplugin_posts = get_posts($myplugin_post_args);
//
//							foreach ($myplugin_posts as $post) {
//								delete_post_meta( $post->ID, '_thumbnail_id' );
//								delete_post_meta( $post->ID, '_wp_attached_file');
//								delete_post_meta( $post->ID, '_wp_attachment_metadata');
//								delete_post_meta( $post->ID, '_source_url');
//							}
							wp_delete_attachment( $post->post_id, true );
							wp_delete_post( $post->post_id, true );
							write_log( "wp_delete_post: " . $post->post_id );
						}
					} else {
						write_log("!is_array posts decoded: ");
						//write_log($posts_decoded);
					}
				} else {
					write_log( "!isset posts: ");
					//write_log($posts);
				}
				wp_delete_category( $wp_category_id );
			} else {

				write_log("idObj error: " . $idObj);
			}
		} else {
			write_log("category->category_name: " . $category->category_name . " parent: " . $parentCategory . " does not exist");
		}
	}
}

wp_delete_category( $parentCategory );

$optionsToRemove = [
	'autonettv_relay_api_settings_fields',
	'autonettv_relay_api_categories',
	'autonettv_relay_api_settings_categories_fields',
	'autonettv_relay_api_settings_schedule_fields',
];

// Delete plugin options.
foreach ( $optionsToRemove as $option ) {
	delete_option( $option );
}

// Delete plugin settings.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'autonettv_relay_api%'" );

remove_action('autonettv_relay_api_events_hook', 'autonettv_relay_api_events()');

write_log("------------------- AUTONETTV DELETION ENDED ------------- ");