<?php
require_once('functions/base.php');   			# Base theme functions
require_once('custom-taxonomies.php');  		# Where per theme taxonomies are defined
require_once('custom-post-types.php');  		# Where per theme post types are defined
require_once('functions/admin.php');  			# Admin/login functions
require_once('functions/config.php');			# Where per theme settings are registered
require_once('shortcodes.php');         		# Per theme shortcodes

//Add theme-specific functions here.

/**
 * Remove unneeded admin menu items.
 **/
function remove_menus () {
	global $menu;
	$restricted = array(
		//__('Dashboard'),
		__('Posts'),
		__('Media'),
		__('Links'),
		__('Pages'),
		__('Appearance'),
		__('Tools'),
		__('Users'),
		__('Settings'),
		__('Comments'),
		__('Plugins')
	);
	end ($menu);
	while (prev($menu)){
		$value = explode(' ',$menu[key($menu)][0]);
		if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
	}
}
add_action('admin_menu', 'remove_menus');


/**
 * Force the default RSS2 feed to use our feed template
 * when query var 'post_type' equals 'alert' and prevent
 * it from caching.
 **/
function clear_rss_cache() {
	global $wpdb;

	update_option('clear_rss_cache', date( 'U' ) );
	$wpdb->query("DELETE FROM `wp_options` WHERE `option_name` LIKE ('_transient%_feed_%')");
}
function alert_rss_feed($for_comments) {
	
	$rss_template = get_template_directory().'/feeds/feed-alert-rss2.php';

	if(get_query_var('post_type') == 'alert' and file_exists($rss_template)) {
		load_template($rss_template);
	} else {
		do_feed_rss2($for_comments); // Call default function
	}
}
remove_all_actions('do_feed_rss2');
add_action('do_feed_rss2', 'clear_rss_cache', 10, 0);
add_action('do_feed_rss2', 'alert_rss_feed', 10, 1 );


/**
 * Add a 'Last Modified' date column to the All Alerts display
 * in the admin.
 **/
 
// Override the default columns 
function edit_alert_columns() {
	$columns = array(
		'cb' 			=> '<input type="checkbox" />',
		'title' 		=> 'Name',
		'last_modified' => 'Last Modified (EST)',
		'publish_date' 	=> 'Publish Date (EST)'
	);
	return $columns;
}
add_action('manage_edit-alert_columns', 'edit_alert_columns');

// Custom columns content
function manage_alert_columns( $column, $post_id ) {
	global $post;
	switch ( $column ) {
		case 'last_modified':
			$modified	= strtotime($post->post_modified);
			$modified_est 	= new DateTime(null, new DateTimeZone('America/New_York'));
			$modified_est 	= $modified_est->setTimestamp($modified);
			print $modified_est->format('Y/m/d g:i A');
			break;
		case 'publish_date':
			if ($post->post_status == 'publish') {
				$published 		= strtotime($post->post_date);
				$published_est 	= new DateTime(null, new DateTimeZone('America/New_York'));
				$published_est 	= $published_est->setTimestamp($published);
				print $published_est->format('Y/m/d g:i A');
			}
			else {
				print 'N/A (not published)';
			}
			break;
		default:
			break;
	}
}
add_action('manage_alert_posts_custom_column', 'manage_alert_columns', 10, 2);

// Sortable custom columns
function sortable_alert_columns( $columns ) {
	$columns['last_modified'] = 'last_modified';
	$columns['publish_date'] = 'publish_date';
	return $columns;
}
add_action('manage_edit-alert_sortable_columns', 'sortable_alert_columns'); 
 
 
/**
 * Prevent Wordpress from trying to redirect to a "loose match" post when
 * an invalid URL is requested.  WordPress will redirect to 404.php instead.
 *
 * See http://wordpress.stackexchange.com/questions/3326/301-redirect-instead-of-404-when-url-is-a-prefix-of-a-post-or-page-name
 **/ 
function no_redirect_on_404($redirect_url) {
    if (is_404()) {
        return false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'no_redirect_on_404');


/**
 * By default, Wordpress will return a 404 if no post content is returned for
 * an RSS feed.  That's bad.  We hook into template_redirect so we can
 * update the status header before the rest of the template loads.
 *
 * http://core.trac.wordpress.org/ticket/18505
 **/
function allow_empty_rss() {
    global $wp_query;

    if (is_feed()) {
        status_header(200);
        $wp_query->is_404 = false;
    }
} 
add_filter('template_redirect', 'allow_empty_rss'); 
 
?>