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
		//__('Users'),
		//__('Settings'),
		__('Comments'),
		//__('Plugins')
	);
	if (!is_super_admin()) {
		$restricted[] = __('SEO');
	}
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


/**
 * Add ID attribute to registered University Header script.
 **/
function add_id_to_ucfhb($url) {
	if ( (false !== strpos($url, 'bar/js/university-header.js')) || (false !== strpos($url, 'bar/js/university-header-full.js')) ) {
	  remove_filter('clean_url', 'add_id_to_ucfhb', 10, 3);
	  return "$url' id='ucfhb-script";
	}
	return $url;
}
add_filter('clean_url', 'add_id_to_ucfhb', 10, 3);


/**
 * Returns a theme option value or NULL if it doesn't exist
 **/
function get_theme_option($key) {
	global $theme_options;
	return isset($theme_options[$key]) ? $theme_options[$key] : NULL;
}


/**
 * Update the Main Site to redirect using the Redirection plugin group defined
 * in theme options.
 * Setting $activate to false will deactivate the Redirection group.
 **/
function switchout_main_site_homepg( $activate=true ) {
	$errors = new WP_Error();

	$main_site_id          = intval( get_theme_option( 'main_site_id' ) );
	$main_site_rd_group_id = intval( get_theme_option( 'main_site_rd_group_id' ) );

	switch_to_blog( $main_site_id );

	$main_site_rd_group = Red_Group::get( $main_site_rd_group_id );

	// Activate the redirect group and its child redirects
	if ( $activate == true ) {
		// Activate the redirection plugin group with ID $main_site_rd_group_id.
		$main_site_rd_group->enable();
	}
	else {
		// Deactivate the redirection plugin group and its child redirects
		$main_site_rd_group->disable();
	}

	// Force an update on the main site homepage to trigger cache updates
	// if enabled
	$do_main_site_ban = filter_var( get_theme_option( 'main_site_homepg_switchout_ban' ), FILTER_VALIDATE_BOOLEAN );
	$main_site_homepg = get_post( intval( get_option( 'page_on_front' ) ) );
	if ( $do_main_site_ban && $main_site_homepg instanceof WP_Post ) {
		$main_site_homepg_update = wp_update_post( array( 'ID' => $main_site_homepg->ID ), true );
		if ( is_wp_error( $main_site_homepg_update ) ) {
			$errors = $main_site_homepg_update;
		}
	}

	restore_current_blog();

	// Write logs of button clickage. Note that main_site_switchover_logs
	// is NOT stored in the theme's $theme_options.
	$logs = get_option('main_site_switchover_logs');
	$user = get_user_by('id', get_current_user_id());
	$date = date('r');

	$log = (object) array('user' => $user->id, 'date' => $date, 'activated' => $activate);

	if (!is_array($logs) || empty($logs)) {
		$logs = array($log);
	}
	else {
		array_unshift($logs, $log);
		$logs = array_slice($logs, 0, 15); // max 15 logs
	}
	update_option('main_site_switchover_logs', $logs);

	// Return errors or true if everything worked
	if (!empty($errors->errors)) {
		return $errors;
	}
	else {
		return true;
	}
}


/**
 * Perform a series of checks to make sure that:
 * - The main site on this multisite instance exists,
 * - The alert site has all necessary plugins installed,
 * - The main site has all necessary plugins installed,
 * - The main site has a Redirection group saved with the ID specified in this
 *   theme's theme options,
 * - The Redirection group on the main site has at least one redirect defined.
 *
 * If everything looks valid, this function returns false.  Otherwise,
 * a WP_Error object is returned with relevant errors.
 **/
function pre_main_site_switchover_errors() {
	$errors = new WP_Error();

	// Get the Main Site ID and the ID of the page to switch to set in Theme Options.
	// If one of these is not set, return now with an error.
	$main_site_id = intval( get_theme_option( 'main_site_id' ) );
	$main_site_rd_group_id = intval( get_theme_option( 'main_site_rd_group_id' ) );
	if ( !$main_site_id ) { $errors->add( 'pre_switchover_no_siteid', 'Main Site ID is not set in Theme Options.' ); }
	if ( !$main_site_rd_group_id ) { $errors->add( 'pre_switchover_no_rd_groupid', 'Main Site Redirection Group ID is not set in Theme Options.' ); }

	// Make sure the site with the given ID exists before switching.
	// switch_to_blog() will NOT return false if the site with $main_site_id
	// doesn't exist!
	$blog_details = get_blog_details( $main_site_id );
	if ( !$blog_details ) { $errors->add( 'pre_switchover_invalid_siteid', 'Blog with ID ' . $main_site_id . ' not found on this multisite instance.' ); }

	// Make sure required plugins are activated on the alert site
	if ( !is_plugin_active( 'redirection/redirection.php' ) || !class_exists( 'Red_Group' ) ) {
		$errors->add( 'pre_switchover_deactivated_plugin_alert_rd', 'Redirection plugin not activated on the Alert Site.' );
	}

	switch_to_blog( $main_site_id );

	// Make sure required plugins are activated on the main site
	if ( !is_plugin_active( 'redirection/redirection.php' ) ) {
		$errors->add( 'pre_switchover_deactivated_plugin_ms_rd', 'Redirection plugin not activated on the Main Site.' );
	}

	// Check for a Redirection group with the ID set in theme options
	$main_site_rd_group = false;
	if ( class_exists( 'Red_Group' ) ) {
		$main_site_rd_group = Red_Group::get( $main_site_rd_group_id );
	}
	if ( !$main_site_rd_group ) {
		$errors->add( 'pre_switchover_no_rd_group', 'Main Site Redirection Group with ID ' . $main_site_rd_group_id . ' not found on the Main Site.' );
	}
	else {
		// Check for at least one redirect rule defined in the group set in theme options
		$main_site_rd_group_count = $main_site_rd_group->get_total_redirects();
		if ( intval( $main_site_rd_group_count ) < 1 ) {
			$errors->add( 'pre_switchover_no_rd_group_redirects', 'The Main Site Redirection Group with ID ' . $main_site_rd_group_id . ' does not have at least one redirect rule defined.' );
		}
	}

	// Switch back the blog and finish.
	restore_current_blog();

	if ( empty( $errors->errors ) ) {
		return false;
	}
	else {
		return $errors;
	}
}


/**
 * Determine if the Main Site's alert redirects are enabled.
 **/
function is_main_site_homepg_switched() {
	$is_switched = false;

	$main_site_id          = intval( get_theme_option( 'main_site_id' ) );
	$main_site_rd_group_id = intval( get_theme_option( 'main_site_rd_group_id' ) );

	switch_to_blog( $main_site_id );

	if ( class_exists( 'Red_Group' ) ) {
		$main_site_rd_group = Red_Group::get( $main_site_rd_group_id );
		if ( $main_site_rd_group->is_enabled() ) {
			$is_switched = true;
		}
	}

	restore_current_blog();

	return $is_switched;
}


/**
 * Add CORS support for the RSS feed.
 **/
function add_header_origin() {
	if ( is_feed() ) {
		header( 'Access-Control-Allow-Origin: *' );
	}
}
add_action( 'pre_get_posts', 'add_header_origin' );
