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
	
	foreach(Config::$styles as $style){Config::add_css($style);}
	foreach(Config::$scripts as $script){Config::add_script($script);}
	
	global $timer;
	$timer = Timer::start();
	
	wp_deregister_script('l10n');
	set_defaults_for_options();
}
add_action('after_setup_theme', '__init__');



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
define('GA_ACCOUNT', $theme_options['ga_account']);
define('CB_UID', $theme_options['cb_uid']);
define('CB_DOMAIN', $theme_options['cb_domain']);

define('ROAM_SECURE_RSS_URL', 'https://alert.ucf.edu/rssfeed.php');

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
			'value'       => $theme_options['gw_verify'],
		)),
		new TextField(array(
			'name'        => 'Google Analytics Account',
			'id'          => THEME_OPTIONS_NAME.'[ga_account]',
			'description' => 'Example: <em>UA-9876543-21</em>. Leave blank for development.',
			'default'     => null,
			'value'       => $theme_options['ga_account'],
		)),
	),
	'Incoming Alert Options' => array(
		new RadioField(array(
			'name'        => 'Enable automated retrieval of alerts',
			'id'          => THEME_OPTIONS_NAME.'[incoming_enabled]',
			'description' => 'Turn this option on to enable automatic incoming alert data fetching.
							  Unchecking this box will stop automatic retrieval of data from 
							  the Incoming Alert RSS Feed specified below.',
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

// Protocol-agnostic URL schemes aren't supported before WP 3.5,
// so we have to determine the protocol before registering
// any non-relative resources.
$protocol = is_ssl() ? 'https://' : 'http://';

# Header links
Config::$links = array(
	array('rel' => 'shortcut icon', 'href' => THEME_IMG_URL.'/favicon.ico',),
	array('rel' => 'alternate', 'type' => 'application/rss+xml', 'href' => get_bloginfo('rss_url'),),
);

# Header styles
Config::$styles = array(
	array('admin' => True, 'src' => THEME_CSS_URL.'/admin.css',),
	$protocol.'www.ucf.edu/wp-content/themes/Main-Site-Theme/static/css/university-header.css',
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
	array('name' => 'jquery', 'src' => $protocol.'ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js',),
	array('admin' => True, 'src' => THEME_JS_URL.'/admin.js',),
	$protocol.'www.ucf.edu/wp-content/themes/Main-Site-Theme/static/js/university-header.js',
	THEME_STATIC_URL.'/bootstrap/bootstrap/js/bootstrap.js',
);

# Header Meta
Config::$metas = array(
	array('charset' => 'utf-8',),
);

if ($theme_options['gw_verify']){
	Config::$metas[] = array(
		'name'    => 'google-site-verification',
		'content' => htmlentities($theme_options['gw_verify']),
	);
}