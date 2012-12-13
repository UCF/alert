<?php
require_once('functions/base.php');   			# Base theme functions
require_once('custom-taxonomies.php');  		# Where per theme taxonomies are defined
require_once('custom-post-types.php');  		# Where per theme post types are defined
require_once('functions/admin.php');  			# Admin/login functions
require_once('functions/config.php');			# Where per theme settings are registered
require_once('shortcodes.php');         		# Per theme shortcodes

//Add theme-specific functions here.
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

?>