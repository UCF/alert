<?php

$_SERVER                = Array();
$_SERVER['HTTP_HOST']   = 'webcom.dev.smca.ucf.edu';
$_SERVER['REQUEST_URI'] = '/wp3/alert/';

require('../../../../wp-load.php');

// Make sure feed fetching is turned on first
$theme_options = get_option(THEME_OPTIONS_NAME);
if ($theme_options['incoming_enabled'] == 1 && $theme_options['incoming_rss_url'] !== '') {

	$feed  = fetch_feed($theme_options['incoming_rss_url']);
	$items = $feed->get_items(0, 100);
	foreach($items as $item) {
	
		$rs_guid   = md5($item->get_id());
		$title     = $item->get_title();
		$content   = $item->get_description();
		$date_time = $item->get_date();
	
		# Remove `UCF Alert * ` prefix
		$ucf_alert_prefix = 'UCF Alert* ';
		if(stripos($title, $ucf_alert_prefix) === 0) {
			$title = substr($title, strlen($ucf_alert_prefix));
		}
		if(stripos($content, $ucf_alert_prefix) === 0) {
			$content = substr($content, strlen($ucf_alert_prefix));
		}
	
		# Remove stupid UCF Alert is powered by...
		$content = trim(str_replace('UCF ALERT is powered by Cooper Notification RSAN', '', $content));
	
		# Check to see if this alert already exists
		$alert_exists = False;
		$existing_alerts = get_posts(array(
			'post_type'   => 'alert',
			'numberposts' => -1
		));
		foreach($existing_alerts as $alert) {
			if( ($alert_rs_guid = get_post_meta($alert->ID, 'alert_roam_secure_guid', True)) !== False && $alert_rs_guid != '') {
				if($rs_guid == $alert_rs_guid) {
					$alert_exists = True;
				}
			}
		}
	
		if(!$alert_exists) {
			$wp_date_time = date('Y-m-d H:i:s');
			if( ($timestamp = strtotime($date_time)) !== False) {
				$wp_date_time = date('Y-m-d H:i:s', $timestamp);
			}
	
			$result = wp_insert_post(
				array(
					'post_type'    => 'alert',
					'post_title'   => $title,
					'post_content' => '',
					'post_status'  => 'publish',
					'post_date'    => $wp_date_time
				),
				True
			);
	
			if(is_object($result)) { # returned a WPError object
				trigger_error($result->get_error_messages(), E_ERROR);
			} else {
				add_post_meta($result, 'alert_short', $content);
				add_post_meta($result, 'alert_roam_secure_guid', $rs_guid);
			}
		}
	}
}

?>