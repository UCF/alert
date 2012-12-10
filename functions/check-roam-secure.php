<?php

$_SERVER              = Array();
$_SERVER['HTTP_HOST'] = 'webcom.dev.smca.ucf.edu';
$_SERVER['REQUEST_URI'] = '/wp3/alert/';

require('../../../wp-load.php');

$feed  = fetch_feed(ROAM_SECURE_RSS_URL);
$items = $feed->get_items(0, 1);
foreach($items as $item) {

	$rs_guid   = md5($item->get_id());
	$title     = $item->get_title();
	$content   = $item->get_description();
	$date_time = $item->get_date();

	# Check to see if this alert already exists
	$alert_exists = False;
	$existing_alerts = get_posts(array(
		'post_type'   => 'roam_secure_alert',
		'numberposts' => -1
	));
	foreach($existing_alerts as $alert) {
		if( ($alert_rs_guid = get_post_meta($alert->ID, 'roam_secure_alert_guid', True)) !== False && $alert_rs_guid != '') {
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
				'post_type'    => 'roam_secure_alert',
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_date'    => $wp_date_time
			),
			True
		);

		if(is_object($result)) { # returned a WPError object
			trigger_error($result->get_error_messages(), E_ERROR);
		} else {
			add_post_meta($result, 'roam_secure_alert_guid', $rs_guid);
		}
	}
}

?>