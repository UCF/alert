<?php get_header(); the_post();?>
<div class="row page-content">
	<div class="span7 offset1">
		<?php
			$alerts = get_posts(array(
				'post_type'   => 'alert',
				'numberposts' => -1,
				'orderby'     => 'post_date',
				'order'       => 'desc'));
			if(count($alerts) > 0) {
				foreach($alerts as $alert) {
					$published 		= strtotime($alert->post_date);
					$modified  		= strtotime($alert->post_modified);
					$modified_est 	= new DateTime(null, new DateTimeZone('America/New_York'));
					$modified_est 	= $modified_est->setTimestamp($modified);
					$short     		= get_post_meta($alert->ID, 'alert_short', True);

					echo sprintf('<h2 class="page-header">%s<br /><small>%s</small></h2>', esc_html($alert->post_title), date('F j, Y', $published));
					if($alert->post_content != '') {
						echo sprintf('<div class="alert-content">%s</div>', str_replace(']]>', ']]&gt;', apply_filters('the_content', $alert->post_content)));
					} else if($short != '') {
						echo sprintf('<div class="alert-content">%s</div>', $short);
					} else {
						echo '<p class="lead">There is no additional information available at this time.</p>';
					}
					echo sprintf('<p class="muted">This information was last updated on <strong>%s at %s EST</strong></p>', date('F j, Y', $modified), $modified_est->format('g:i A'));
				}
			} else {
				echo '<p class="well lead">There are currently no active alerts.</p>';
			}
		?>
	</div>
	<div class="span3" id="sidebar">
		<div class="about">
			<h3>About this Page</h3>
			<p>
				This page is the official source of alert information for the University of Central Florida. In the event of an emergency, check this page for updated information. This page is updated and maintained by the Office of News and Information.
			</p>
		</div>
		<hr />
		<div class="contact">
			<?php 
				$contacts = get_posts(array(
					'post_type'    => 'contact_information',
					'numberposts'  => -1));
				foreach($contacts as $contact) {
					$value = get_post_meta($contact->ID, 'contact_information_value', True);
					echo sprintf('<h3>%s</h3><p>%s</p>', apply_filters('the_title', $contact->post_title), $value);
				} 
			?>
		</div>
	</div>
</div>
<?php get_footer(); ?>