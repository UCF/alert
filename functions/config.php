<?php

/**
 * Responsible for running code that needs to be executed as wordpress is
 * initializing.  Good place to register scripts, stylesheets, theme elements,
 * etc.
 *
 * @return void
 * @author Jared Lang
 **/
function __init__(){
	add_theme_support('menus');
	add_theme_support('post-thumbnails');
	register_nav_menu('header-menu', __('Header Menu'));
	register_nav_menu('footer-menu', __('Footer Menu'));

	global $timer;
	$timer = Timer::start();

	set_defaults_for_options();
}
add_action('after_setup_theme', '__init__');

function alert_is_admin_asset( $asset ) {
	return isset( $asset['admin'] ) && $asset['admin'] === true ? true : false;
}

function alert_add_scripts() {
	foreach( Config::$styles as $style ) {
		if ( ! alert_is_admin_asset( $style ) ) {
			Config::add_css( $style );
		}
	}

	foreach( Config::$scripts as $script ) {
		if ( ! alert_is_admin_asset( $script ) ) {
			Config::add_script( $script );
		}
	}

	wp_deregister_script('l10n');
}

add_action( 'wp_enqueue_scripts', 'alert_add_scripts' );

function alert_add_admin_scripts() {
	foreach( Config::$styles as $style ) {
		if ( alert_is_admin_asset( $style ) ) {
			Config::add_css( $style );
		}
	}

	foreach( Config::$scripts as $script ) {
		if ( alert_is_admin_asset( $script ) ) {
			Config::add_script( $script );
		}
	}
}

add_action( 'admin_enqueue_scripts', 'alert_add_admin_scripts' );

# Set theme constants
#define('DEBUG', True);                  # Always on
#define('DEBUG', False);                 # Always off
define('DEBUG', isset($_GET['debug'])); # Enable via get parameter
define('THEME_URL', get_bloginfo('stylesheet_directory'));
define('THEME_ADMIN_URL', get_admin_url());
define('THEME_DIR', get_stylesheet_directory());
define('THEME_INCLUDES_DIR', THEME_DIR.'/includes');
define('THEME_STATIC_URL', THEME_URL.'/static');
define('THEME_IMG_URL', THEME_STATIC_URL.'/img');
define('THEME_JS_URL', THEME_STATIC_URL.'/js');
define('THEME_CSS_URL', THEME_STATIC_URL.'/css');
define('THEME_OPTIONS_GROUP', 'settings');
define('THEME_OPTIONS_NAME', 'theme');
define('THEME_OPTIONS_PAGE_TITLE', 'Theme Options');

$theme_options = get_option(THEME_OPTIONS_NAME);
define('GA_ACCOUNT', isset( $theme_options['ga_account'] ) ? $theme_options['ga_account'] : null );
define('CB_UID', isset ( $theme_options['cb_uid'] ) ? $theme_options['cb_uid'] : null );
define('CB_DOMAIN', isset( $theme_options['cb_domain'] ) ? $theme_options['cb_domain'] : null );

define('ROAM_SECURE_RSS_URL', 'https://alert.ucf.edu/rssfeed.php');

# Protocol-agnostic URL schemes aren't supported before WP 3.5,
# so we have to determine the protocol before registering
# any non-relative resources.
define('CURRENT_PROTOCOL', is_ssl() ? 'https://' : 'http://');

/**
 * Set config values including meta tags, registered custom post types, styles,
 * scripts, and any other statically defined assets that belong in the Config
 * object.
 **/
Config::$custom_post_types = array(
	'Alert',
	'ContactInformation',
);

Config::$custom_taxonomies = array(
);

/**
 * Configure theme settings, see abstract class Field's descendants for
 * available fields. -- functions/base.php
 **/
Config::$theme_settings = array(
	'Analytics' => array(
		new TextField(array(
			'name'        => 'Google WebMaster Verification',
			'id'          => THEME_OPTIONS_NAME.'[gw_verify]',
			'description' => 'Example: <em>9Wsa3fspoaoRE8zx8COo48-GCMdi5Kd-1qFpQTTXSIw</em>',
			'default'     => null,
			'value'       => isset( $theme_options['gw_verify'] ) ? $theme_options['gw_verify'] : null,
		)),
		new TextField(array(
			'name'        => 'Google Analytics Account',
			'id'          => THEME_OPTIONS_NAME.'[ga_account]',
			'description' => 'Example: <em>UA-9876543-21</em>. Leave blank for development.',
			'default'     => null,
			'value'       => isset( $theme_options['ga_account'] ) ? $theme_options['ga_account'] : null,
		)),
	),
	'Incoming Alert Options' => array(
		new RadioField(array(
			'name'        => 'Enable automated retrieval of alerts',
			'id'          => THEME_OPTIONS_NAME.'[incoming_enabled]',
			'description' => 'Turn this option on to enable automatic incoming alert data fetching.
							  Unchecking this box will stop automatic retrieval of data from
							  the Incoming Alert RSS Feed specified below.<br>
							  <strong>NOTE:</strong> a cron task must be set up to run the
							  check-roam-secure.php script in this theme for this option to take any
							  effect.',
			'default' 	  => 0,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['incoming_enabled'],
		)),
		new TextField(array(
			'name'        => 'Incoming Alert RSS Feed',
			'id'          => THEME_OPTIONS_NAME.'[incoming_rss_url]',
			'description' => 'URL to the RSS feed for incoming alert data.',
			'default'     => 'https://alert.ucf.edu/rssfeed.php',
			'value'       => $theme_options['incoming_rss_url'],
		)),
	),
	'Outgoing Alert Feed Options' => array(
		new TextField(array(
			'name'        => 'Alert Text Length',
			'id'          => THEME_OPTIONS_NAME.'[outgoing_text_length]',
			'description' => 'Max number of characters allowed for the alert content that
							 is displayed on ucf.edu. (Words will not be truncated)<br/>
							 Default value is 350.',
			'default'     => '350',
			'value'       => $theme_options['outgoing_text_length'],
		)),
	),
	'Main Site Options' => array(
		new TextField(array(
			'name'        => 'Main Site ID',
			'id'          => THEME_OPTIONS_NAME.'[main_site_id]',
			'description' => 'ID of the Main Site on this multisite network. This setting
							 is required for managing Main Site switchovers from this site.',
			'default'     => '1',
			'value'       => $theme_options['main_site_id'],
		)),
		new TextField(array(
			'name'        => 'Main Site Alert Redirection Group ID',
			'id'          => THEME_OPTIONS_NAME.'[main_site_rd_group_id]',
			'description' => 'ID of the Redirection plugin redirect group on the Main Site
							 that should be enabled when the Text-Only Main Site Switchover
							 is activated. The group will be disabled upon deactivating the
							 switchover. This setting is required for managing Main Site
							 switchovers from this site.',
			'value'       => isset( $theme_options['main_site_rd_group_id'] ) ? $theme_options['main_site_rd_group_id'] : null,
		)),
		new RadioField(array(
			'name'        => 'Try to Clear Cache on Main Site Homepage upon Switchover Activation/Deactivation',
			'id'          => THEME_OPTIONS_NAME.'[main_site_homepg_switchout_ban]',
			'description' => 'Turn this option on to allow the Main Site\'s homepage to be
							  updated programmatically when the Main Site Switchover is
							  activated or deactivated. Doing so should trigger a cache
							  ban/purge on most cache-related plugins. Note that this will
							  fail if the Main Site is not configured to display a static
							  page as the front page (is set to display "latest posts"
							  in Settings > Reading).',
			'default' 	  => 1,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['main_site_homepg_switchout_ban'],
		)),
	),
	'Styles' => array(
		new RadioField(array(
			'name'        => 'Enable Responsiveness',
			'id'          => THEME_OPTIONS_NAME.'[bootstrap_enable_responsive]',
			'description' => 'Turn on responsive styles provided by the Twitter Bootstrap framework.  This setting should be decided upon before building out subpages, etc. to ensure content is designed to shrink down appropriately.  Turning this off will enable the single 940px-wide Bootstrap layout.',
			'default'     => 1,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['bootstrap_enable_responsive'],
	    )),
	),
);


# Header links
Config::$links = array(
	array('rel' => 'shortcut icon', 'href' => THEME_IMG_URL.'/favicon.ico',),
	array('rel' => 'alternate', 'type' => 'application/rss+xml', 'href' => get_bloginfo('rss_url'),),
);

# Header styles
Config::$styles = array(
	array('admin' => True, 'src' => THEME_CSS_URL.'/admin.css',),
	THEME_STATIC_URL.'/bootstrap/bootstrap/css/bootstrap.css',
);

if ($theme_options['bootstrap_enable_responsive'] == 1) {
	array_push(Config::$styles,
		THEME_STATIC_URL.'/bootstrap/bootstrap/css/bootstrap-responsive.css'
	);
}

# Only include gravity forms styles if the plugin is active
include_once(ABSPATH.'wp-admin/includes/plugin.php' );
if(is_plugin_active('gravityforms/gravityforms.php')) {
	array_push(Config::$styles,
		plugins_url( 'gravityforms/css/forms.css' )
	);
}

array_push(Config::$styles,
	THEME_STATIC_URL.'/css/base.css',
	get_bloginfo('stylesheet_url')
);

# Must be loaded after style.css
if ($theme_options['bootstrap_enable_responsive'] == 1) {
	array_push(Config::$styles,
		THEME_URL.'/style-responsive.css'
	);
}

# Scripts (output in footer)
Config::$scripts = array(
	array('name' => 'jquery', 'src' => CURRENT_PROTOCOL.'ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js',),
	array('admin' => True, 'src' => THEME_JS_URL.'/admin.js',),
	'//universityheader.ucf.edu/bar/js/university-header.js?use-bootstrap-overrides=1',
	THEME_STATIC_URL.'/bootstrap/bootstrap/js/bootstrap.js',
);

# Header Meta
Config::$metas = array(
	array('charset' => 'utf-8',),
);

if ( isset( $theme_options['gw_verify'] ) && $theme_options['gw_verify'] ) {
	Config::$metas[] = array(
		'name'    => 'google-site-verification',
		'content' => htmlentities( $theme_options['gw_verify'] ),
	);
}
